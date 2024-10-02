<?php

namespace App\Http\Controllers;

use App\Models\Division;
use App\Models\Entries;
use App\Models\Organizational;
use App\Models\Report;
use App\Models\SuccessIndicator;
use App\Models\User;
use PDF;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Excel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index(){

        $user=Auth::user();

        $currentYear = Carbon::now()->format('Y');
        $currentUser = Auth::user();
        $entriesCount = SuccessIndicator::whereNull('deleted_at')
            ->whereHas('org', function ($query) {
                $query->where('status', 'Active');
            })
            ->with('org')
            ->whereYear('created_at', $currentYear);

        $indicators = $entriesCount->get();

        $userDivisionIds = json_decode($currentUser->division_id, true);
        $filteredIndicators = $indicators->filter(function($indicator) use ($userDivisionIds) {
            $indicatorDivisionIds = json_decode($indicator->division_id, true);

            return !empty(array_intersect($userDivisionIds, $indicatorDivisionIds));
        });

        $currentMonth = Carbon::now()->format('m');
        $current_Year = Carbon::now()->format('Y');

        $currentDate = Carbon::now();

        if ($currentDate->day > 5) {
            $targetMonth = $currentDate->month;
            // $targetMonth = $currentDate->addMonth()->month;
        } else {
            $targetMonth = $currentDate->subMonth()->month;
        }

        $filteredIndicators = $filteredIndicators->filter(function($indicator) use ($targetMonth, $current_Year) {
            $completedEntries = Entries::where('indicator_id', $indicator->id)
                                    ->where('months', $targetMonth)
                                    ->whereYear('created_at', $current_Year)
                                    ->where('status', 'Completed')
                                    ->where('user_id',  Auth::user()->id)
                                    ->exists();
            return !$completedEntries;
        });

            // $entriesCount = Entries::whereNull('deleted_at')->with('indicator')->where('status', 'Pending')->count();
        $entriesCount = $filteredIndicators->count();
        return view('generate.index', compact('user', 'entriesCount'));
    }

    public function pdf(Request $request){
        return view('generate.pdf',);

    }

    public function generatePDF(Request $request)
    {
        $orgOutcomes = array();
        $entries = '';
        $divisionIds = '';
        $year = $request->input('year');
        $period = $request->input('period');
        $semiannual = $request->input('semiannual');
        $divisionIds = $request->input('division_id');
        $province = $request->input('province');
        $userIdsArray = '';

        // Fetch all indicators
        $indicators = SuccessIndicator::all();

        // Initialize the collection of indicator IDs
        $indicatorIds = collect();

        if (!empty($divisionIds)) {

            // Filter indicators based on the divisionIds
            $filteredIndicators = $indicators->filter(function ($indicator) use ($divisionIds) {
                $indicatorDivisionIds = json_decode($indicator->division_id, true);
                return !empty(array_intersect($divisionIds, $indicatorDivisionIds));
            });

            $userIds = User::where(function($query) use ($divisionIds) {
                foreach ($divisionIds as $divisionId) {
                    $query->orWhereJsonContains('division_id', $divisionId);
                }
            })->pluck('id');

            $userIdsArray = $userIds->toArray();
            $indicatorIds = $filteredIndicators->pluck('id');
        } else {
            $indicatorIds = $indicators->pluck('id');
        }

        // If no matching indicators, do not show organizational outcomes
        if ($indicatorIds->isEmpty()) {
            return PDF::loadView('generate.pdf', compact('orgOutcomes', 'entries', 'divisionIds'))
            ->setPaper('a4', 'landscape')->stream('OPCR-RO5.pdf');
        }

        // Fetch organizational outcomes with their success indicators based on filters
        $orgOutcomes = Organizational::with(['successIndicators' => function($query) use ($year, $period, $indicatorIds, $divisionIds) {
            if ($indicatorIds->isNotEmpty()) {
                $query->whereIn('id', $indicatorIds);
            }
            if ($year) {
                $query->whereYear('created_at', $year);
            }
            // if ($period) {
            //     $months = $this->getMonthsForPeriod($period);
            //     $query->whereIn(DB::raw('MONTH(created_at)'), $months);
            // }

        }])
        ->where(function($query) use ($year, $period, $indicatorIds, $divisionIds) {
            $query->whereHas('successIndicators', function($query) use ($year, $period, $indicatorIds, $divisionIds) {
                if ($indicatorIds->isNotEmpty()) {
                    $query->whereIn('id', $indicatorIds);
                }
                if ($year) {
                    $query->whereYear('created_at', $year);
                }
                // if ($period) {
                //     $months = $this->getMonthsForPeriod($period);
                //     $query->whereIn(DB::raw('MONTH(created_at)'), $months);
                // }

            });
            // ->orWhereDoesntHave('successIndicators');
        })
        ->orderBy('order','ASC')
        ->get();

        // Fetch entries based on filters
        if (Auth::user()->role->name === 'IT' || Auth::user()->role->name === 'SAP') {
            $entry = Entries::whereYear('created_at', $year)
                        ->when($period, function($query) use ($period) {
                            $months = $this->getMonthsForPeriod($period);
                            $query->whereIn(DB::raw('MONTH(created_at)'), $months);
                        })
                        ->when($province, function($query) use ($province) {
                            $query->whereHas('user', function($query) use ($province) {
                                $query->where('province', $province);
                            });
                        })
                        ->when($userIdsArray, function($query) use ($userIdsArray) {
                            $query->whereIn('user_id', $userIdsArray);
                        });

            $entries = $entry->get()
            ->groupBy(function ($entry) {
                return $entry->indicator_id; // Group by each indicator
            });

        }else{
            $entry = Entries::whereYear('created_at', $year)
            ->where('created_by', Auth::user()->user_name)
            ->when($period, function($query) use ($period) {
                $months = $this->getMonthsForPeriod($period);
                $query->whereIn(DB::raw('MONTH(created_at)'), $months);
            })
            ->when($province, function($query) use ($province) {
                $query->whereHas('user', function($query) use ($province) {
                    $query->where('province', $province);
                });
            })
            ->when($userIdsArray, function($query) use ($userIdsArray) {
                $query->whereIn('user_id', $userIdsArray);
            });

            $entries = $entry->get()
            ->groupBy(function ($entry) {
                return $entry->indicator_id; // Group by each indicator
            });

            // $entries = Entries::whereYear('created_at', $year)

            //     ->when($period, function($query) use ($period) {
            //         $months = $this->getMonthsForPeriod($period);
            //         $query->whereIn(DB::raw('MONTH(created_at)'), $months);
            //     })
            //     ->when($province, function($query) use ($province) {
            //         $query->whereHas('user', function($query) use ($province) {
            //             $query->where('province', $province);
            //         });
            //     })
            //     ->get()
            //     ->groupBy('indicator_id');

        }

        $entryCount = $entry->count();


        $pdf = PDF::loadView('generate.pdf', compact('orgOutcomes', 'entries', 'divisionIds', 'entryCount', 'entry'))
                ->setPaper('a4', 'landscape');

        return $pdf->stream('OPCR-RO5.pdf');
    }

    private function getMonthsForPeriod($period)
    {
        switch ($period) {
            case 'Q1':
                return [1, 2, 3];
            case 'Q2':
                return [4, 5, 6];
            case 'Q3':
                return [7, 8, 9];
            case 'Q4':
                return [10, 11, 12];
            case 'H1':
                return [1, 2, 3, 4, 5, 6];
            case 'H2':
                return [7, 8, 9, 10, 11, 12];
        }
    }

    private function getPreviousQuarter($period)
    {
        switch ($period) {
            case 'Q2':
                return 'Q1';
            case 'Q3':
                return 'Q2';
            case 'Q4':
                return 'Q3';
            case 'H2':
                return 'H1';
            default:
                return '';
        }
    }

    public function exportQuarterOne(Request $request)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $selectedQuarter = $request->input('period');
        $year = $request->input('year');

        // Dynamically set the name of the quarter
        $quarterNames = [
            'Q1' => '1st QUARTER',
            'Q2' => '2nd QUARTER',
            'Q3' => '3rd QUARTER',
            'Q4' => '4th QUARTER',
        ];

        $quarterName = isset($quarterNames[$selectedQuarter]) ? $quarterNames[$selectedQuarter] : 'QUARTER';

        // First level headers
        $headerRowStart = 1;
        $headerRowEnd = 3;

        // Add the header rows dynamically
        $sheet->setCellValue('A' . $headerRowStart, 'INDICATORS');
        $sheet->setCellValue('B' . $headerRowStart, 'TARGETS');
        $sheet->setCellValue('D' . $headerRowStart, 'ACCOMPLISHMENTS');
        $sheet->setCellValue('I' . $headerRowStart, 'Percentage');
        $sheet->setCellValue('K' . $headerRowStart, 'Quarter Balance');
        $sheet->setCellValue('L' . $headerRowStart, 'Annual Balance');
        $sheet->setCellValue('M' . $headerRowStart, 'Remark');

        $sheet->setCellValue('B' . ($headerRowStart + 1), 'Annual');
        $sheet->setCellValue('C' . ($headerRowStart + 1), $quarterName);

        $sheet->setCellValue('D' . ($headerRowStart + 1), $quarterName);
        $sheet->setCellValue('G' . ($headerRowStart + 1), 'QTR. TOTAL');
        $sheet->setCellValue('H' . ($headerRowStart + 1), 'ANNUAL TOTAL');
        $sheet->setCellValue('I' . ($headerRowStart + 1), 'Qtr');
        $sheet->setCellValue('J' . ($headerRowStart + 1), 'Annual');

         // Merge cells for 1st level headers
        $sheet->mergeCells('A' . $headerRowStart . ':A' . $headerRowEnd); // INDICATORS
        $sheet->mergeCells('B' . $headerRowStart . ':C' . $headerRowStart); // TARGETS
        $sheet->mergeCells('D' . $headerRowStart . ':H' . $headerRowStart); // ACCOMPLISHMENTS
        $sheet->mergeCells('I' . $headerRowStart . ':J' . $headerRowStart); // Percentage
        $sheet->mergeCells('K' . $headerRowStart . ':K' . $headerRowEnd); // Quarter Balance
        $sheet->mergeCells('L' . $headerRowStart . ':L' . $headerRowEnd); // Annual Balance
        $sheet->mergeCells('M' . $headerRowStart . ':M' . $headerRowEnd); // Remark

        // Merge second level headers
        $sheet->mergeCells('C' . ($headerRowStart + 1) . ':C' . $headerRowEnd);
        $sheet->mergeCells('B' . ($headerRowStart + 1) . ':B' . $headerRowEnd);
        $sheet->mergeCells('D' . ($headerRowStart + 1) . ':F' . ($headerRowStart + 1));
        $sheet->mergeCells('G' . ($headerRowStart + 1) . ':G' . $headerRowEnd);
        $sheet->mergeCells('H' . ($headerRowStart + 1) . ':H' . $headerRowEnd);
        $sheet->mergeCells('I' . ($headerRowStart + 1) . ':I' . $headerRowEnd);
        $sheet->mergeCells('J' . ($headerRowStart + 1) . ':J' . $headerRowEnd);


        // // Third level headers
        $months = $this->getMonthsForPeriod($selectedQuarter);
        $monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];

        // Insert dynamic month headers
        if ($months) {
            foreach ($months as $index => $month) {
                $sheet->setCellValue(chr(68 + $index) . ($headerRowStart + 2), $monthNames[$month - 1]); // Row 3 is now headerRowStart + 2
            }
        }


         // Apply the header style to the sheet (this style is already defined in your code)
         $sheet->getStyle('A1:M' . ($headerRowStart + 2))->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '8B0000']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'FFFFFF']]],
        ]);

        // Set column width for better appearance (optional)
        foreach (range('A', 'M') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        //DATA
        $orgOutcomes = DB::table('org_otc')->where('status', 'Active')
        ->whereYear('created_at', $year)
        ->orderBy('order','ASC')
        ->get();

        $row = $headerRowEnd + 1; // Start inserting data below the headers

        foreach ($orgOutcomes as $outcome) {
            // Insert the Organizational Outcome (Yellow row)
            $sheet->setCellValue('A' . $row, $outcome->order . '.' .$outcome->organizational_outcome );

            // Style the Organizational Outcome row
            $sheet->getStyle('A' . $row . ':M' . $row)->applyFromArray([
                'font' => ['bold' => true, 'size' => 14], // Thicker weight
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'fcd654'], // Yellow highlight
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN, // Border style
                        'color' => ['rgb' => 'FFFFFF'], // White color for the border
                    ],
                ],
            ]);

            // Set row height for organizational outcome
            $sheet->getRowDimension($row)->setRowHeight(30); // Increase height

            $row++;

            // Fetch success indicators related to the current organizational outcome
            $successIndicators = DB::table('success_indc')
                ->whereYear('created_at', $year)
                ->where('org_id', $outcome->id)
                ->get();

            foreach ($successIndicators as $indicator) {
                // Insert Success Indicators (Pink row)
                $sheet->setCellValue('A' . $row, $indicator->measures); // Measures under INDICATORS
                $sheet->setCellValue('B' . $row, $indicator->target); // Annual Target
                $sheet->setCellValue('C' . $row, '=SUM(D' . $row . ':F' . $row . ')'); //1st QUARTER
                $sheet->setCellValue('G' . $row, '=SUM(D' . $row . ':F' . $row . ')');  //QTR.TOTAL
                $sheet->setCellValue('H' . $row, '=SUM(D' . $row . ':F' . $row . ')'); //ACCOMPLISHMENT: ANNUAL TOTAL
                $sheet->setCellValue('I' . $row, '=(G' . $row . '/C' . $row . ')'); // percenatge QTR
                $sheet->setCellValue('J' . $row, '=(G' . $row . '/B' . $row . ')'); //Percenatge Annual
                $sheet->setCellValue('K' . $row, '=(C' . $row . '-G' . $row . ')'); //  QTR BALANCE
                $sheet->setCellValue('L' . $row, '=(B' . $row . '-H' . $row . ')'); //  Annual BALANCE
                $sheet->getStyle('I' . $row)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_PERCENTAGE);
                $sheet->getStyle('J' . $row)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_PERCENTAGE);



                // Initialize an array to hold the total accomplishments for each month
                $totalAccomplishmentsByMonth = [];
                $monthsForPeriod = $this->getMonthsForPeriod($selectedQuarter);
                foreach ($monthsForPeriod as $month) {
                    $totalAccomplishmentsByMonth[$month] = 0; // Initialize each month with 0
                }

                $sheet->getStyle('A' . $row . ':M' . $row)->applyFromArray([
                    'font' => ['bold' => true, 'size' => 12], // Thicker weight
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'FFC0CB'], // Pink highlight
                    ],
                    'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN, // Border style
                        'color' => ['rgb' => 'FFFFFF'], // White color for the border
                    ],
                ],
                ]);


                $row++;

                $dvisions = Division::where('division_name', 'like', '%PO%')->get();

                foreach ($dvisions as $division) {
                    $divisionName = str_replace(' PO', '', $division->division_name);

                    $targetField = str_replace(' ', '_', $divisionName) . '_target'; // Convert to lowercase with underscores
                    $targetValue = $indicator->$targetField ?? 0; // Get target value, default to 0 if not set

                    // Insert division name and target value in respective columns
                    $sheet->setCellValue('A' . $row, $divisionName . ' NFO'); //Division Name
                    $sheet->setCellValue('B' . $row, $targetValue); // Corresponding Target

                    $entries = Entries::where('indicator_id', $indicator->id)
                    ->whereYear('created_at', $year)
                    ->whereIn(DB::raw('MONTH(created_at)'), $this->getMonthsForPeriod($selectedQuarter))
                    ->get();

                   // Initialize month accomplishments for the current division
                    $accomplishmentsByMonth = [];
                    foreach ($monthsForPeriod as $month) {
                        $accomplishmentsByMonth[$month] = 0; // Initialize each month with 0
                    }

                    // Aggregate the accomplishments for the respective months
                    foreach ($entries as $entry) {
                        $month = date('n', strtotime($entry->created_at)); // Get month as number (1-12)
                        $accomField = str_replace(' ', '_', $divisionName) . '_accomplishment';
                        $accomplishmentsByMonth[$month] += $entry->$accomField ?? 0; // Sum the accomplishments

                        // Sum the accomplishments for the total array
                        $totalAccomplishmentsByMonth[$month] += $entry->$accomField ?? 0;
                    }

                    // Insert accomplishments into respective month columns
                    foreach ($monthsForPeriod as $monthIndex => $month) {
                        $columnLetter = chr(68 + $monthIndex); // Calculate the column letter based on the index
                        $sheet->setCellValue($columnLetter . $row, $accomplishmentsByMonth[$month]); // Set accomplishment in the correct column
                    }

                    $sheet->setCellValue('G' . $row, '=SUM(D' . $row . ':F' . $row . ')'); //QTR.TOTAL
                    $sheet->setCellValue('H' . $row, '=SUM(D' . $row . ':F' . $row . ')'); // //ACCOMPLISHMENT: ANNUAL TOTAL
                    $sheet->setCellValue('I' . $row, '=(G' . $row . '/C' . $row . ')'); // percenatge QTR
                    $sheet->setCellValue('J' . $row, '=(G' . $row . '/B' . $row . ')'); //Percenatge Annual
                    $sheet->setCellValue('K' . $row, '=(C' . $row . '-G' . $row . ')'); //  QTR BALANCE
                    $sheet->setCellValue('L' . $row, '=(B' . $row . '-H' . $row . ')'); //  Annual BALANCE
                    $sheet->getStyle('I' . $row)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_PERCENTAGE);
                    $sheet->getStyle('J' . $row)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_PERCENTAGE);


                    $row++;
                }

                 // Insert total accomplishments for the indicator row
                foreach ($monthsForPeriod as $monthIndex => $month) {
                    $columnLetter = chr(68 + $monthIndex);
                    $sheet->setCellValue($columnLetter . ($row - 7), $totalAccomplishmentsByMonth[$month]);
                }
            }
        }

        // Stream the file
        $callback = function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $file = fopen('php://output', 'w');
            fclose($file);

            // Output the file to the browser
            $writer->save('php://output');
        };

        $headers = [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="export.xlsx"',
        ];

        return new StreamedResponse($callback, 200, $headers);
    }

    public function exportQuarterTwo(Request $request)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $selectedQuarter = 'Q2';
        $year = $request->input('year');

        // Dynamically set the name of the quarter
        $quarterNames = [
            'Q1' => '1st QUARTER',
            'Q2' => '2nd QUARTER',
            'Q3' => '3rd QUARTER',
            'Q4' => '4th QUARTER',
        ];

        $quarterName = isset($quarterNames[$selectedQuarter]) ? $quarterNames[$selectedQuarter] : 'QUARTER';

        $previousQuarter = $this->getPreviousQuarter($selectedQuarter);
        $PreviousQuarterName = isset($quarterNames[$previousQuarter]) ? $quarterNames[$previousQuarter] : '';

        // First level headers
        $headerRowStart = 1;
        $headerRowEnd = 3;

        // Add the header rows dynamically
        $sheet->setCellValue('A' . $headerRowStart, 'INDICATORS');
        $sheet->setCellValue('B' . $headerRowStart, 'TARGETS');
        $sheet->setCellValue('G' . $headerRowStart, 'ACCOMPLISHMENTS');
        $sheet->setCellValue('M' . $headerRowStart, 'Percentage');
        $sheet->setCellValue('O' . $headerRowStart, 'Quarter Balance');
        $sheet->setCellValue('P' . $headerRowStart, 'Annual Balance');
        $sheet->setCellValue('Q' . $headerRowStart, 'Remark');

        //Second Level

        //TARGET
        $sheet->setCellValue('B' . ($headerRowStart + 1), 'Annual Target');
        $sheet->setCellValue('C' . ($headerRowStart + 1), 'Annual Balance');
        $sheet->setCellValue('D' . ($headerRowStart + 1), $PreviousQuarterName);
        $sheet->setCellValue('E' . ($headerRowStart + 1), $quarterName);
        $sheet->setCellValue('F' . ($headerRowStart + 1), '2nd Total.');

        //ACCOMPLISHMEMNT
        $sheet->setCellValue('G' . ($headerRowStart + 1), 'Total Accomp');
        $sheet->setCellValue('H' . ($headerRowStart + 1), $quarterName);
        $months = $this->getMonthsForPeriod($selectedQuarter);
        $monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];

        // Insert dynamic month headers
        if ($months) {
            foreach ($months as $index => $month) {
                $sheet->setCellValue(chr(72 + $index) . ($headerRowStart + 2), $monthNames[$month - 1]); // Row 3 is now headerRowStart + 2
            }
        }

        $sheet->setCellValue('K' . $headerRowEnd, 'QTR. TOTAL');
        $sheet->setCellValue('L' . ($headerRowStart + 1), 'ANNUAL TOTAL');

        //PERCENTAGE
        $sheet->setCellValue('M' . ($headerRowStart + 1), 'Qtr');
        $sheet->setCellValue('N' . ($headerRowStart + 1), 'Annual');


         // Merge cells for 1st level headers
        $sheet->mergeCells('A' . $headerRowStart . ':A' . $headerRowEnd); // INDICATORS
        $sheet->mergeCells('B' . $headerRowStart . ':F' . $headerRowStart); // TARGETS
        $sheet->mergeCells('G' . $headerRowStart . ':L' . $headerRowStart); // ACCOMPLISHMENTS
        $sheet->mergeCells('M' . $headerRowStart . ':N' . $headerRowStart); // Percentage
        $sheet->mergeCells('O' . $headerRowStart . ':O' . $headerRowEnd); // Quarter Balance
        $sheet->mergeCells('P' . $headerRowStart . ':P' . $headerRowEnd); // Annual Balance
        $sheet->mergeCells('Q' . $headerRowStart . ':Q' . $headerRowEnd); // Remark

        // Merge second level headers
        $sheet->mergeCells('B' . ($headerRowStart + 1) . ':B' . $headerRowEnd); // Annual Target
        $sheet->mergeCells('C' . ($headerRowStart + 1) . ':C' . $headerRowEnd); // Annual Balance
        $sheet->mergeCells('D' . ($headerRowStart + 1) . ':D' . $headerRowEnd); // 1st quarter
        $sheet->mergeCells('E' . ($headerRowStart + 1) . ':E' . $headerRowEnd); // 2nd quarter
        $sheet->mergeCells('F' . ($headerRowStart + 1) . ':F' . $headerRowEnd); // 2nd Total
        $sheet->mergeCells('G' . ($headerRowStart + 1) . ':G' . $headerRowEnd); // 2nd Total
        $sheet->mergeCells('H' . ($headerRowStart + 1) . ':K' . ($headerRowStart + 1)); // 2ND QUARTER under ACCOMPLISHMENTS
        $sheet->mergeCells('L' . ($headerRowStart + 1) . ':L' . $headerRowEnd); // ANNUAL TOTAL



         // Apply the header style to the sheet (this style is already defined in your code)
         $sheet->getStyle('A1:Q' . ($headerRowStart + 2))->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '8B0000']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'FFFFFF']]],
        ]);

        // Set column width for better appearance (optional)
        foreach (range('A', 'Q') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        //DATA
        $orgOutcomes = DB::table('org_otc')->where('status', 'Active')
        ->whereYear('created_at', $year)
        ->orderBy('order','ASC')
        ->get();

        $row = $headerRowEnd + 1; // Start inserting data below the headers

        foreach ($orgOutcomes as $outcome) {
            // Insert the Organizational Outcome (Yellow row)
            $sheet->setCellValue('A' . $row, $outcome->order . '.' .$outcome->organizational_outcome );

            // Style the Organizational Outcome row
            $sheet->getStyle('A' . $row . ':Q' . $row)->applyFromArray([
                'font' => ['bold' => true, 'size' => 14], // Thicker weight
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'fcd654'], // Yellow highlight
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN, // Border style
                        'color' => ['rgb' => 'FFFFFF'], // White color for the border
                    ],
                ],
            ]);

            // Set row height for organizational outcome
            $sheet->getRowDimension($row)->setRowHeight(30); // Increase height

            $row++;

            // Fetch success indicators related to the current organizational outcome
            $successIndicators = DB::table('success_indc')
                ->whereYear('created_at', $year)
                ->where('org_id', $outcome->id)
                ->get();

            foreach ($successIndicators as $indicator) {
                // Insert Success Indicators (Pink row)
                $sheet->setCellValue('A' . $row, $indicator->measures); // Measures under INDICATORS
                $sheet->setCellValue('B' . $row, $indicator->target); // Annual Target

                // Initialize an array to hold the total accomplishments for each month
                $totalAccomplishmentsByMonth = [];
                $monthsForPeriod = $this->getMonthsForPeriod($selectedQuarter);

                foreach ($monthsForPeriod as $month) {
                    $totalAccomplishmentsByMonth[$month] = 0; // Initialize each month with 0
                }

                $sheet->getStyle('A' . $row . ':Q' . $row)->applyFromArray([
                    'font' => ['bold' => true, 'size' => 12], // Thicker weight
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'FFC0CB'], // Pink highlight
                    ],
                    'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN, // Border style
                        'color' => ['rgb' => 'FFFFFF'], // White color for the border
                    ],
                ],
                ]);

                //QTR.TOTAL
                $sheet->setCellValue('K' . $row, '=SUM(H' . $row . ':J' . $row . ')');

                $row++;

                $dvisions = Division::where('division_name', 'like', '%PO%')->get();

                foreach ($dvisions as $division) {
                    $divisionName = str_replace(' PO', '', $division->division_name);

                    $targetField = str_replace(' ', '_', $divisionName) . '_target'; // Convert to lowercase with underscores
                    $targetValue = $indicator->$targetField ?? 0; // Get target value, default to 0 if not set

                    // Insert division name and target value in respective columns
                    $sheet->setCellValue('A' . $row, $divisionName); // Division Name
                    $sheet->setCellValue('B' . $row, $targetValue); // Corresponding Target

                    $entries = Entries::where('indicator_id', $indicator->id)
                    ->whereYear('created_at', $year)
                    ->whereIn(DB::raw('MONTH(created_at)'), $this->getMonthsForPeriod($selectedQuarter))
                    ->get();


                   // Initialize month accomplishments for the current division
                    $accomplishmentsByMonth = [];
                    foreach ($monthsForPeriod as $month) {
                        $accomplishmentsByMonth[$month] = 0; // Initialize each month with 0
                    }

                    // Aggregate the accomplishments for the respective months
                    foreach ($entries as $entry) {
                        $month = date('n', strtotime($entry->created_at)); // Get month as number (1-12)
                        $accomField = str_replace(' ', '_', $divisionName) . '_accomplishment';
                        $accomplishmentsByMonth[$month] += $entry->$accomField ?? 0; // Sum the accomplishments

                        // Sum the accomplishments for the total array
                        $totalAccomplishmentsByMonth[$month] += $entry->$accomField ?? 0;
                    }

                    foreach ($monthsForPeriod as $monthIndex => $month) {
                        $columnLetter = chr(72 + $monthIndex); // Calculate the column letter based on the index
                        $sheet->setCellValue($columnLetter . $row, $accomplishmentsByMonth[$month]); // Set accomplishment in the correct column
                    }

                    $sheet->setCellValue('K' . $row, '=SUM(H' . $row . ':J' . $row . ')');

                    $row++;
                }

                 // Insert total accomplishments for the indicator row
                foreach ($monthsForPeriod as $monthIndex => $month) {
                    $columnLetter = chr(72 + $monthIndex);
                    $sheet->setCellValue($columnLetter . ($row - 7), $totalAccomplishmentsByMonth[$month]);
                }

            }
        }

        // Stream the file
        $callback = function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $file = fopen('php://output', 'w');
            fclose($file);

            // Output the file to the browser
            $writer->save('php://output');
        };

        $headers = [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="export.xlsx"',
        ];

        return new StreamedResponse($callback, 200, $headers);
    }

    public function exportQuarterThree(Request $request)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $selectedQuarter = 'Q3';
        $year = $request->input('year');


        // Dynamically set the name of the quarter
        $quarterNames = [
            'Q1' => '1st QUARTER',
            'Q2' => '2nd QUARTER',
            'Q3' => '3rd QUARTER',
            'Q4' => '4th QUARTER',
        ];

        $quarterName = isset($quarterNames[$selectedQuarter]) ? $quarterNames[$selectedQuarter] : 'QUARTER';

        $previousQuarter = $this->getPreviousQuarter($selectedQuarter);
        $PreviousQuarterName = isset($quarterNames[$previousQuarter]) ? $quarterNames[$previousQuarter] : '';

        // First level headers
        $headerRowStart = 1;
        $headerRowEnd = 3;

        // Add the header rows dynamically
        $sheet->setCellValue('A' . $headerRowStart, 'INDICATORS');
        $sheet->setCellValue('B' . $headerRowStart, 'TARGETS');
        $sheet->setCellValue('F' . $headerRowStart, 'ACCOMPLISHMENTS');
        $sheet->setCellValue('L' . $headerRowStart, 'Percentage');
        $sheet->setCellValue('N' . $headerRowStart, 'Quarter Balance');
        $sheet->setCellValue('O' . $headerRowStart, 'Annual Balance');
        $sheet->setCellValue('P' . $headerRowStart, 'Remark');

        $sheet->setCellValue('B' . ($headerRowStart + 1), 'Annual');

        // Conditional headers for quarters
        if ($selectedQuarter === 'Q1') {
            $sheet->setCellValue('C' . ($headerRowStart + 1), $quarterName);
            $sheet->setCellValue('D' . ($headerRowStart + 1), $PreviousQuarterName);
            $sheet->mergeCells('C' . ($headerRowStart + 1) . ':D' . $headerRowEnd); // Merge for Q1
        } else {
            $sheet->setCellValue('C' . ($headerRowStart + 1), $PreviousQuarterName);
            $sheet->setCellValue('D' . ($headerRowStart + 1), $quarterName);
            $sheet->mergeCells('C' . ($headerRowStart + 1) . ':C' . $headerRowEnd); // Keep separate for other quarters
            $sheet->mergeCells('D' . ($headerRowStart + 1) . ':D' . $headerRowEnd);
        }

        $sheet->setCellValue('E' . ($headerRowStart + 1), 'Total');
        $sheet->setCellValue('F' . ($headerRowStart + 1), 'Total Accomp.');

        $sheet->setCellValue('G' . ($headerRowStart + 1), $quarterName);
        $sheet->setCellValue('K' . ($headerRowStart + 1), 'ANNUAL TOTAL');
        $sheet->setCellValue('L' . ($headerRowStart + 1), 'Qtr');
        $sheet->setCellValue('M' . ($headerRowStart + 1), 'Annual');

         // Merge cells for 1st level headers
        $sheet->mergeCells('A' . $headerRowStart . ':A' . $headerRowEnd); // INDICATORS
        $sheet->mergeCells('B' . $headerRowStart . ':E' . $headerRowStart); // TARGETS
        $sheet->mergeCells('F' . $headerRowStart . ':K' . $headerRowStart); // ACCOMPLISHMENTS
        $sheet->mergeCells('L' . $headerRowStart . ':M' . $headerRowStart); // Percentage
        $sheet->mergeCells('N' . $headerRowStart . ':N' . $headerRowEnd); // Quarter Balance
        $sheet->mergeCells('O' . $headerRowStart . ':O' . $headerRowEnd); // Annual Balance
        $sheet->mergeCells('P' . $headerRowStart . ':P' . $headerRowEnd); // Remark

        // Merge second level headers
        $sheet->mergeCells('B' . ($headerRowStart + 1) . ':B' . $headerRowEnd); // Annual Target
        $sheet->mergeCells('E' . ($headerRowStart + 1) . ':E' . $headerRowEnd); // Total
        $sheet->mergeCells('F' . ($headerRowStart + 1) . ':F' . $headerRowEnd); // Total Accomp.
        $sheet->mergeCells('G' . ($headerRowStart + 1) . ':J' . ($headerRowStart + 1)); // 2ND QUARTER under ACCOMPLISHMENTS
        $sheet->mergeCells('K' . ($headerRowStart + 1) . ':K' . $headerRowEnd); // ANNUAL TOTAL
        $sheet->mergeCells('L' . ($headerRowStart + 1) . ':L' . $headerRowEnd); // ANNUAL TOTAL
        $sheet->mergeCells('M' . ($headerRowStart + 1) . ':M' . $headerRowEnd); // ANNUAL TOTAL

        // // Third level headers
        $months = $this->getMonthsForPeriod($selectedQuarter);
        $monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];

        // Insert dynamic month headers
        if ($months) {
            foreach ($months as $index => $month) {
                $sheet->setCellValue(chr(71 + $index) . ($headerRowStart + 2), $monthNames[$month - 1]); // Row 3 is now headerRowStart + 2
            }
        } else {
            // Handle unexpected input (if no months are found)
            $sheet->setCellValue('G' . ($headerRowStart + 2), 'N/A');
            $sheet->setCellValue('H' . ($headerRowStart + 2), 'N/A');
            $sheet->setCellValue('I' . ($headerRowStart + 2), 'N/A');
        }

        $sheet->setCellValue('J' . ($headerRowStart + 2), 'QTR. TOTAL');
        $sheet->mergeCells('G' . ($headerRowStart + 1) . ':J' . ($headerRowStart + 1));

         // Apply the header style to the sheet (this style is already defined in your code)
         $sheet->getStyle('A1:P' . ($headerRowStart + 2))->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '8B0000']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'FFFFFF']]],
        ]);

        // Set column width for better appearance (optional)
        foreach (range('A', 'P') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        //DATA
        $orgOutcomes = DB::table('org_otc')->where('status', 'Active')
        ->whereYear('created_at', $year)
        ->orderBy('order','ASC')
        ->get();

        $row = $headerRowEnd + 1; // Start inserting data below the headers

        foreach ($orgOutcomes as $outcome) {
            // Insert the Organizational Outcome (Yellow row)
            $sheet->setCellValue('A' . $row, $outcome->order . '.' .$outcome->organizational_outcome );

            // Style the Organizational Outcome row
            $sheet->getStyle('A' . $row . ':P' . $row)->applyFromArray([
                'font' => ['bold' => true, 'size' => 14], // Thicker weight
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'fcd654'], // Yellow highlight
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN, // Border style
                        'color' => ['rgb' => 'FFFFFF'], // White color for the border
                    ],
                ],
            ]);

            // Set row height for organizational outcome
            $sheet->getRowDimension($row)->setRowHeight(30); // Increase height

            $row++;

            // Fetch success indicators related to the current organizational outcome
            $successIndicators = DB::table('success_indc')
                ->whereYear('created_at', $year)
                ->where('org_id', $outcome->id)
                ->get();

            foreach ($successIndicators as $indicator) {
                // Insert Success Indicators (Pink row)
                $sheet->setCellValue('A' . $row, $indicator->measures); // Measures under INDICATORS
                $sheet->setCellValue('B' . $row, $indicator->target); // Annual Target

                // Initialize an array to hold the total accomplishments for each month
                $totalAccomplishmentsByMonth = [];
                $monthsForPeriod = $this->getMonthsForPeriod($selectedQuarter);
                foreach ($monthsForPeriod as $month) {
                    $totalAccomplishmentsByMonth[$month] = 0; // Initialize each month with 0
                }

                $sheet->getStyle('A' . $row . ':P' . $row)->applyFromArray([
                    'font' => ['bold' => true, 'size' => 12], // Thicker weight
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'FFC0CB'], // Pink highlight
                    ],
                    'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN, // Border style
                        'color' => ['rgb' => 'FFFFFF'], // White color for the border
                    ],
                ],
                ]);


                //QTR.TOTAL
                $sheet->setCellValue('J' . $row, '=SUM(G' . $row . ':I' . $row . ')');

                //ACCOMPLISHMENT: ANNUAL TOTAL
                if ($selectedQuarter === 'Q1') {
                    $sheet->mergeCells('C' . $row . ':D' . $row);
                    $sheet->setCellValue('C' . $row, '=SUM(G' . $row . ':I' . $row . ')');
                    $sheet->setCellValue('K' . $row, '=SUM(G' . $row . ':I' . $row . ')');
                    $sheet->setCellValue('O' . $row, '=FG' . $row . '-K' . $row);
                }else{

                    $sheet->setCellValue('K' . $row, '=F' . $row . '+J' . $row);
                }

                $row++;

                $dvisions = Division::where('division_name', 'like', '%PO%')->get();

                foreach ($dvisions as $division) {
                    $divisionName = str_replace(' PO', '', $division->division_name);

                    $targetField = str_replace(' ', '_', $divisionName) . '_target'; // Convert to lowercase with underscores
                    $targetValue = $indicator->$targetField ?? 0; // Get target value, default to 0 if not set

                    // Insert division name and target value in respective columns
                    $sheet->setCellValue('A' . $row, $divisionName); // Division Name
                    $sheet->setCellValue('B' . $row, $targetValue); // Corresponding Target

                    $entries = Entries::where('indicator_id', $indicator->id)
                    ->whereYear('created_at', $year)
                    ->whereIn(DB::raw('MONTH(created_at)'), $this->getMonthsForPeriod($selectedQuarter))
                    ->get();

                   // Initialize month accomplishments for the current division
                    $accomplishmentsByMonth = [];
                    foreach ($monthsForPeriod as $month) {
                        $accomplishmentsByMonth[$month] = 0; // Initialize each month with 0
                    }

                    // Aggregate the accomplishments for the respective months
                    foreach ($entries as $entry) {
                        $month = date('n', strtotime($entry->created_at)); // Get month as number (1-12)
                        $accomField = str_replace(' ', '_', $divisionName) . '_accomplishment';
                        $accomplishmentsByMonth[$month] += $entry->$accomField ?? 0; // Sum the accomplishments

                        // Sum the accomplishments for the total array
                        $totalAccomplishmentsByMonth[$month] += $entry->$accomField ?? 0;
                    }

                    // Insert accomplishments into respective month columns
                    foreach ($monthsForPeriod as $monthIndex => $month) {
                        $columnLetter = chr(71 + $monthIndex); // Calculate the column letter based on the index
                        $sheet->setCellValue($columnLetter . $row, $accomplishmentsByMonth[$month]); // Set accomplishment in the correct column
                    }

                    $sheet->setCellValue('J' . $row, '=SUM(G' . $row . ':I' . $row . ')');
                    // //ACCOMPLISHMENT: ANNUAL TOTAL
                    if ($selectedQuarter === 'Q1') {
                        $sheet->mergeCells('C' . $row . ':D' . $row);
                        $sheet->setCellValue('C' . $row, '=SUM(G' . $row . ':I' . $row . ')');
                        $sheet->setCellValue('K' . $row, '=SUM(G' . $row . ':I' . $row . ')');
                        $sheet->setCellValue('O' . $row, '=FG' . $row . '-K' . $row);
                    }else{

                    $sheet->setCellValue('K' . $row, '=F' . $row . '+J' . $row);
                    }

                    $row++;
                }

                 // Insert total accomplishments for the indicator row
                foreach ($monthsForPeriod as $monthIndex => $month) {
                    $columnLetter = chr(71 + $monthIndex);
                    $sheet->setCellValue($columnLetter . ($row - 7), $totalAccomplishmentsByMonth[$month]);
                }
            }
        }

        // Stream the file
        $callback = function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $file = fopen('php://output', 'w');
            fclose($file);

            // Output the file to the browser
            $writer->save('php://output');
        };

        $headers = [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="export.xlsx"',
        ];

        return new StreamedResponse($callback, 200, $headers);
    }

    public function exportQuarterFour(Request $request)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $selectedQuarter = 'Q4';
        $year = $request->input('year');

        // Dynamically set the name of the quarter
        $quarterNames = [
            'Q1' => '1st QUARTER',
            'Q2' => '2nd QUARTER',
            'Q3' => '3rd QUARTER',
            'Q4' => '4th QUARTER',
        ];

        $quarterName = isset($quarterNames[$selectedQuarter]) ? $quarterNames[$selectedQuarter] : 'QUARTER';

        $previousQuarter = $this->getPreviousQuarter($selectedQuarter);
        $PreviousQuarterName = isset($quarterNames[$previousQuarter]) ? $quarterNames[$previousQuarter] : '';

        // First level headers
        $headerRowStart = 1;
        $headerRowEnd = 3;

        // Add the header rows dynamically
        $sheet->setCellValue('A' . $headerRowStart, 'INDICATORS');
        $sheet->setCellValue('B' . $headerRowStart, 'TARGETS');
        $sheet->setCellValue('F' . $headerRowStart, 'ACCOMPLISHMENTS');
        $sheet->setCellValue('L' . $headerRowStart, 'Percentage');
        $sheet->setCellValue('N' . $headerRowStart, 'Quarter Balance');
        $sheet->setCellValue('O' . $headerRowStart, 'Annual Balance');
        $sheet->setCellValue('P' . $headerRowStart, 'Remark');

        $sheet->setCellValue('B' . ($headerRowStart + 1), 'Annual');

        // Conditional headers for quarters
        if ($selectedQuarter === 'Q1') {
            $sheet->setCellValue('C' . ($headerRowStart + 1), $quarterName);
            $sheet->setCellValue('D' . ($headerRowStart + 1), $PreviousQuarterName);
            $sheet->mergeCells('C' . ($headerRowStart + 1) . ':D' . $headerRowEnd); // Merge for Q1
        } else {
            $sheet->setCellValue('C' . ($headerRowStart + 1), $PreviousQuarterName);
            $sheet->setCellValue('D' . ($headerRowStart + 1), $quarterName);
            $sheet->mergeCells('C' . ($headerRowStart + 1) . ':C' . $headerRowEnd); // Keep separate for other quarters
            $sheet->mergeCells('D' . ($headerRowStart + 1) . ':D' . $headerRowEnd);
        }

        $sheet->setCellValue('E' . ($headerRowStart + 1), 'Total');
        $sheet->setCellValue('F' . ($headerRowStart + 1), 'Total Accomp.');

        $sheet->setCellValue('G' . ($headerRowStart + 1), $quarterName);
        $sheet->setCellValue('K' . ($headerRowStart + 1), 'ANNUAL TOTAL');
        $sheet->setCellValue('L' . ($headerRowStart + 1), 'Qtr');
        $sheet->setCellValue('M' . ($headerRowStart + 1), 'Annual');

         // Merge cells for 1st level headers
        $sheet->mergeCells('A' . $headerRowStart . ':A' . $headerRowEnd); // INDICATORS
        $sheet->mergeCells('B' . $headerRowStart . ':E' . $headerRowStart); // TARGETS
        $sheet->mergeCells('F' . $headerRowStart . ':K' . $headerRowStart); // ACCOMPLISHMENTS
        $sheet->mergeCells('L' . $headerRowStart . ':M' . $headerRowStart); // Percentage
        $sheet->mergeCells('N' . $headerRowStart . ':N' . $headerRowEnd); // Quarter Balance
        $sheet->mergeCells('O' . $headerRowStart . ':O' . $headerRowEnd); // Annual Balance
        $sheet->mergeCells('P' . $headerRowStart . ':P' . $headerRowEnd); // Remark

        // Merge second level headers
        $sheet->mergeCells('B' . ($headerRowStart + 1) . ':B' . $headerRowEnd); // Annual Target
        $sheet->mergeCells('E' . ($headerRowStart + 1) . ':E' . $headerRowEnd); // Total
        $sheet->mergeCells('F' . ($headerRowStart + 1) . ':F' . $headerRowEnd); // Total Accomp.
        $sheet->mergeCells('G' . ($headerRowStart + 1) . ':J' . ($headerRowStart + 1)); // 2ND QUARTER under ACCOMPLISHMENTS
        $sheet->mergeCells('K' . ($headerRowStart + 1) . ':K' . $headerRowEnd); // ANNUAL TOTAL
        $sheet->mergeCells('L' . ($headerRowStart + 1) . ':L' . $headerRowEnd); // ANNUAL TOTAL
        $sheet->mergeCells('M' . ($headerRowStart + 1) . ':M' . $headerRowEnd); // ANNUAL TOTAL

        // // Third level headers
        $months = $this->getMonthsForPeriod($selectedQuarter);
        $monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];

        // Insert dynamic month headers
        if ($months) {
            foreach ($months as $index => $month) {
                $sheet->setCellValue(chr(71 + $index) . ($headerRowStart + 2), $monthNames[$month - 1]); // Row 3 is now headerRowStart + 2
            }
        } else {
            // Handle unexpected input (if no months are found)
            $sheet->setCellValue('G' . ($headerRowStart + 2), 'N/A');
            $sheet->setCellValue('H' . ($headerRowStart + 2), 'N/A');
            $sheet->setCellValue('I' . ($headerRowStart + 2), 'N/A');
        }

        $sheet->setCellValue('J' . ($headerRowStart + 2), 'QTR. TOTAL');
        $sheet->mergeCells('G' . ($headerRowStart + 1) . ':J' . ($headerRowStart + 1));

         // Apply the header style to the sheet (this style is already defined in your code)
         $sheet->getStyle('A1:P' . ($headerRowStart + 2))->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '8B0000']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'FFFFFF']]],
        ]);

        // Set column width for better appearance (optional)
        foreach (range('A', 'P') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        //DATA
        $orgOutcomes = DB::table('org_otc')->where('status', 'Active')
        ->whereYear('created_at', $year)
        ->orderBy('order','ASC')
        ->get();

        $row = $headerRowEnd + 1; // Start inserting data below the headers

        foreach ($orgOutcomes as $outcome) {
            // Insert the Organizational Outcome (Yellow row)
            $sheet->setCellValue('A' . $row, $outcome->order . '.' .$outcome->organizational_outcome );

            // Style the Organizational Outcome row
            $sheet->getStyle('A' . $row . ':P' . $row)->applyFromArray([
                'font' => ['bold' => true, 'size' => 14], // Thicker weight
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'fcd654'], // Yellow highlight
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN, // Border style
                        'color' => ['rgb' => 'FFFFFF'], // White color for the border
                    ],
                ],
            ]);

            // Set row height for organizational outcome
            $sheet->getRowDimension($row)->setRowHeight(30); // Increase height

            $row++;

            // Fetch success indicators related to the current organizational outcome
            $successIndicators = DB::table('success_indc')
                ->whereYear('created_at', $year)
                ->where('org_id', $outcome->id)
                ->get();

            foreach ($successIndicators as $indicator) {
                // Insert Success Indicators (Pink row)
                $sheet->setCellValue('A' . $row, $indicator->measures); // Measures under INDICATORS
                $sheet->setCellValue('B' . $row, $indicator->target); // Annual Target

                // Initialize an array to hold the total accomplishments for each month
                $totalAccomplishmentsByMonth = [];
                $monthsForPeriod = $this->getMonthsForPeriod($selectedQuarter);
                foreach ($monthsForPeriod as $month) {
                    $totalAccomplishmentsByMonth[$month] = 0; // Initialize each month with 0
                }

                $sheet->getStyle('A' . $row . ':P' . $row)->applyFromArray([
                    'font' => ['bold' => true, 'size' => 12], // Thicker weight
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'FFC0CB'], // Pink highlight
                    ],
                    'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN, // Border style
                        'color' => ['rgb' => 'FFFFFF'], // White color for the border
                    ],
                ],
                ]);


                //QTR.TOTAL
                $sheet->setCellValue('J' . $row, '=SUM(G' . $row . ':I' . $row . ')');

                //ACCOMPLISHMENT: ANNUAL TOTAL
                if ($selectedQuarter === 'Q1') {
                    $sheet->mergeCells('C' . $row . ':D' . $row);
                    $sheet->setCellValue('C' . $row, '=SUM(G' . $row . ':I' . $row . ')');
                    $sheet->setCellValue('K' . $row, '=SUM(G' . $row . ':I' . $row . ')');
                    $sheet->setCellValue('O' . $row, '=FG' . $row . '-K' . $row);
                }else{

                    $sheet->setCellValue('K' . $row, '=F' . $row . '+J' . $row);
                }

                $row++;

                $dvisions = Division::where('division_name', 'like', '%PO%')->get();

                foreach ($dvisions as $division) {
                    $divisionName = str_replace(' PO', '', $division->division_name);

                    $targetField = str_replace(' ', '_', $divisionName) . '_target'; // Convert to lowercase with underscores
                    $targetValue = $indicator->$targetField ?? 0; // Get target value, default to 0 if not set

                    // Insert division name and target value in respective columns
                    $sheet->setCellValue('A' . $row, $divisionName); // Division Name
                    $sheet->setCellValue('B' . $row, $targetValue); // Corresponding Target

                    $entries = Entries::where('indicator_id', $indicator->id)
                    ->whereYear('created_at', $year)
                    ->whereIn(DB::raw('MONTH(created_at)'), $this->getMonthsForPeriod($selectedQuarter))
                    ->get();

                   // Initialize month accomplishments for the current division
                    $accomplishmentsByMonth = [];
                    foreach ($monthsForPeriod as $month) {
                        $accomplishmentsByMonth[$month] = 0; // Initialize each month with 0
                    }

                    // Aggregate the accomplishments for the respective months
                    foreach ($entries as $entry) {
                        $month = date('n', strtotime($entry->created_at)); // Get month as number (1-12)
                        $accomField = str_replace(' ', '_', $divisionName) . '_accomplishment';
                        $accomplishmentsByMonth[$month] += $entry->$accomField ?? 0; // Sum the accomplishments

                        // Sum the accomplishments for the total array
                        $totalAccomplishmentsByMonth[$month] += $entry->$accomField ?? 0;
                    }

                    // Insert accomplishments into respective month columns
                    foreach ($monthsForPeriod as $monthIndex => $month) {
                        $columnLetter = chr(71 + $monthIndex); // Calculate the column letter based on the index
                        $sheet->setCellValue($columnLetter . $row, $accomplishmentsByMonth[$month]); // Set accomplishment in the correct column
                    }

                    $sheet->setCellValue('J' . $row, '=SUM(G' . $row . ':I' . $row . ')');
                    // //ACCOMPLISHMENT: ANNUAL TOTAL
                    if ($selectedQuarter === 'Q1') {
                        $sheet->mergeCells('C' . $row . ':D' . $row);
                        $sheet->setCellValue('C' . $row, '=SUM(G' . $row . ':I' . $row . ')');
                        $sheet->setCellValue('K' . $row, '=SUM(G' . $row . ':I' . $row . ')');
                        $sheet->setCellValue('O' . $row, '=FG' . $row . '-K' . $row);
                    }else{

                    $sheet->setCellValue('K' . $row, '=F' . $row . '+J' . $row);
                    }

                    $row++;
                }

                 // Insert total accomplishments for the indicator row
                foreach ($monthsForPeriod as $monthIndex => $month) {
                    $columnLetter = chr(71 + $monthIndex);
                    $sheet->setCellValue($columnLetter . ($row - 7), $totalAccomplishmentsByMonth[$month]);
                }
            }
        }

        // Stream the file
        $callback = function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $file = fopen('php://output', 'w');
            fclose($file);

            // Output the file to the browser
            $writer->save('php://output');
        };

        $headers = [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="export.xlsx"',
        ];

        return new StreamedResponse($callback, 200, $headers);
    }

    public function export(Request $request){

        $selectedQuarter = $request->input('period');

        switch ($selectedQuarter) {
            case 'Q1':
                return $this->exportQuarterOne($request);
            case 'Q2':
                return $this->exportQuarterTwo($request);
            case 'Q3':
                return $this->exportQuarterThree($request);
            case 'Q4':
                return $this->exportQuarterFour($request);
            default:
                return '';
        }



    }

    public function exportMultipleSheets(Request $request)
    {
        $spreadsheet = new Spreadsheet();
        $year = $request->input('year');


        //START 1st Quarter

            $quarterOne = 'Q1';
            $quarterOneName =  '1ST QUARTER';

            // First level headers
            $headerRowStart = 1;
            $headerRowEnd = 3;

            // Create a new Spreadsheet object
            $sheet1 = $spreadsheet->setActiveSheetIndex(0);
            $sheet1->setTitle('Q1');

            // Add the header rows dynamically
            $sheet1->setCellValue('A' . $headerRowStart, 'INDICATORS');
            $sheet1->setCellValue('B' . $headerRowStart, 'TARGETS');
            $sheet1->setCellValue('D' . $headerRowStart, 'ACCOMPLISHMENTS');
            $sheet1->setCellValue('I' . $headerRowStart, 'Percentage');
            $sheet1->setCellValue('K' . $headerRowStart, 'Quarter Balance');
            $sheet1->setCellValue('L' . $headerRowStart, 'Annual Balance');
            $sheet1->setCellValue('M' . $headerRowStart, 'Remark');

            $sheet1->setCellValue('B' . ($headerRowStart + 1), 'Annual');
            $sheet1->setCellValue('C' . ($headerRowStart + 1), $quarterOneName);

            $sheet1->setCellValue('D' . ($headerRowStart + 1), $quarterOneName);
            $sheet1->setCellValue('G' . ($headerRowStart + 1), 'QTR. TOTAL');
            $sheet1->setCellValue('H' . ($headerRowStart + 1), 'ANNUAL TOTAL');
            $sheet1->setCellValue('I' . ($headerRowStart + 1), 'Qtr');
            $sheet1->setCellValue('J' . ($headerRowStart + 1), 'Annual');

            // Merge cells for 1st level headers
            $sheet1->mergeCells('A' . $headerRowStart . ':A' . $headerRowEnd); // INDICATORS
            $sheet1->mergeCells('B' . $headerRowStart . ':C' . $headerRowStart); // TARGETS
            $sheet1->mergeCells('D' . $headerRowStart . ':H' . $headerRowStart); // ACCOMPLISHMENTS
            $sheet1->mergeCells('I' . $headerRowStart . ':J' . $headerRowStart); // Percentage
            $sheet1->mergeCells('K' . $headerRowStart . ':K' . $headerRowEnd); // Quarter Balance
            $sheet1->mergeCells('L' . $headerRowStart . ':L' . $headerRowEnd); // Annual Balance
            $sheet1->mergeCells('M' . $headerRowStart . ':M' . $headerRowEnd); // Remark

            // Merge second level headers
            $sheet1->mergeCells('C' . ($headerRowStart + 1) . ':C' . $headerRowEnd);
            $sheet1->mergeCells('B' . ($headerRowStart + 1) . ':B' . $headerRowEnd);
            $sheet1->mergeCells('D' . ($headerRowStart + 1) . ':F' . ($headerRowStart + 1));
            $sheet1->mergeCells('G' . ($headerRowStart + 1) . ':G' . $headerRowEnd);
            $sheet1->mergeCells('H' . ($headerRowStart + 1) . ':H' . $headerRowEnd);
            $sheet1->mergeCells('I' . ($headerRowStart + 1) . ':I' . $headerRowEnd);
            $sheet1->mergeCells('J' . ($headerRowStart + 1) . ':J' . $headerRowEnd);


            // // Third level headers
            $months = $this->getMonthsForPeriod($quarterOne);
            $monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];

            // Insert dynamic month headers
            if ($months) {
                foreach ($months as $index => $month) {
                    $sheet1->setCellValue(chr(68 + $index) . ($headerRowStart + 2), $monthNames[$month - 1]); // Row 3 is now headerRowStart + 2
                }
            }


            // Apply the header style to the sheet (this style is already defined in your code)
            $sheet1->getStyle('A1:M' . ($headerRowStart + 2))->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '8B0000']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'FFFFFF']]],
            ]);

            // Set column width for better appearance (optional)
            foreach (range('A', 'M') as $columnID) {
                $sheet1->getColumnDimension($columnID)->setAutoSize(true);
            }

            //DATA
            $orgOutcomes = DB::table('org_otc')->where('status', 'Active')
            ->whereYear('created_at', $year)
            ->orderBy('order','ASC')
            ->get();

            $row = $headerRowEnd + 1; // Start inserting data below the headers

                foreach ($orgOutcomes as $outcome) {
                    // Insert the Organizational Outcome (Yellow row)
                    $sheet1->setCellValue('A' . $row, $outcome->order . '.' .$outcome->organizational_outcome );

                    // Style the Organizational Outcome row
                    $sheet1->getStyle('A' . $row . ':M' . $row)->applyFromArray([
                        'font' => ['bold' => true, 'size' => 14], // Thicker weight
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => 'fcd654'], // Yellow highlight
                        ],
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN, // Border style
                                'color' => ['rgb' => 'FFFFFF'], // White color for the border
                            ],
                        ],
                    ]);

                    // Set row height for organizational outcome
                    $sheet1->getRowDimension($row)->setRowHeight(30); // Increase height

                    $row++;

                    // Fetch success indicators related to the current organizational outcome
                    $successIndicators = DB::table('success_indc')
                        ->whereYear('created_at', $year)
                        ->where('org_id', $outcome->id)
                        ->get();

                    foreach ($successIndicators as $indicator) {
                        // Insert Success Indicators (Pink row)
                        $sheet1->setCellValue('A' . $row, $indicator->measures); // Measures under INDICATORS
                        $sheet1->setCellValue('B' . $row, $indicator->target); // Annual Target
                        $sheet1->setCellValue('C' . $row, '=SUM(D' . $row . ':F' . $row . ')'); //1st QUARTER
                        $sheet1->setCellValue('G' . $row, '=SUM(D' . $row . ':F' . $row . ')');  //QTR.TOTAL
                        $sheet1->setCellValue('H' . $row, '=SUM(D' . $row . ':F' . $row . ')'); //ACCOMPLISHMENT: ANNUAL TOTAL
                        $sheet1->setCellValue('I' . $row, '=(G' . $row . '/C' . $row . ')'); // percenatge QTR
                        $sheet1->setCellValue('J' . $row, '=(G' . $row . '/B' . $row . ')'); //Percenatge Annual
                        $sheet1->setCellValue('K' . $row, '=(C' . $row . '-G' . $row . ')'); //  QTR BALANCE
                        $sheet1->setCellValue('L' . $row, '=(B' . $row . '-H' . $row . ')'); //  Annual BALANCE
                        $sheet1->getStyle('I' . $row)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_PERCENTAGE);
                        $sheet1->getStyle('J' . $row)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_PERCENTAGE);



                        // Initialize an array to hold the total accomplishments for each month
                        $totalAccomplishmentsByMonth = [];
                        $monthsForPeriod = $this->getMonthsForPeriod($quarterOne);
                        foreach ($monthsForPeriod as $month) {
                            $totalAccomplishmentsByMonth[$month] = 0; // Initialize each month with 0
                        }

                        $sheet1->getStyle('A' . $row . ':M' . $row)->applyFromArray([
                            'font' => ['bold' => true, 'size' => 12], // Thicker weight
                            'fill' => [
                                'fillType' => Fill::FILL_SOLID,
                                'startColor' => ['rgb' => 'FFC0CB'], // Pink highlight
                            ],
                            'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN, // Border style
                                'color' => ['rgb' => 'FFFFFF'], // White color for the border
                            ],
                        ],
                        ]);


                        $row++;

                        $dvisions = Division::where('division_name', 'like', '%PO%')->get();

                        foreach ($dvisions as $division) {
                            $divisionName = str_replace(' PO', '', $division->division_name);

                            $targetField = str_replace(' ', '_', $divisionName) . '_target'; // Convert to lowercase with underscores
                            $targetValue = $indicator->$targetField ?? 0; // Get target value, default to 0 if not set

                            // Insert division name and target value in respective columns
                            $sheet1->setCellValue('A' . $row, $divisionName . ' NFO'); //Division Name
                            $sheet1->setCellValue('B' . $row, $targetValue); // Corresponding Target

                            $entries = Entries::where('indicator_id', $indicator->id)
                            ->whereYear('created_at', $year)
                            ->whereIn(DB::raw('MONTH(created_at)'), $this->getMonthsForPeriod($quarterOne))
                            ->get();

                            // Initialize month accomplishments for the current division
                            $accomplishmentsByMonth = [];
                            foreach ($monthsForPeriod as $month) {
                                $accomplishmentsByMonth[$month] = 0; // Initialize each month with 0
                            }

                            // Aggregate the accomplishments for the respective months
                            foreach ($entries as $entry) {
                                $month = date('n', strtotime($entry->created_at)); // Get month as number (1-12)
                                $accomField = str_replace(' ', '_', $divisionName) . '_accomplishment';
                                $accomplishmentsByMonth[$month] += $entry->$accomField ?? 0; // Sum the accomplishments

                                // Sum the accomplishments for the total array
                                $totalAccomplishmentsByMonth[$month] += $entry->$accomField ?? 0;
                            }

                            // Insert accomplishments into respective month columns
                            foreach ($monthsForPeriod as $monthIndex => $month) {
                                $columnLetter = chr(68 + $monthIndex); // Calculate the column letter based on the index
                                $sheet1->setCellValue($columnLetter . $row, $accomplishmentsByMonth[$month]); // Set accomplishment in the correct column
                            }

                            $sheet1->setCellValue('G' . $row, '=SUM(D' . $row . ':F' . $row . ')'); //QTR.TOTAL
                            $sheet1->setCellValue('H' . $row, '=SUM(D' . $row . ':F' . $row . ')'); // //ACCOMPLISHMENT: ANNUAL TOTAL
                            $sheet1->setCellValue('I' . $row, '=(G' . $row . '/C' . $row . ')'); // percenatge QTR
                            $sheet1->setCellValue('J' . $row, '=(G' . $row . '/B' . $row . ')'); //Percenatge Annual
                            $sheet1->setCellValue('K' . $row, '=(C' . $row . '-G' . $row . ')'); //  QTR BALANCE
                            $sheet1->setCellValue('L' . $row, '=(B' . $row . '-H' . $row . ')'); //  Annual BALANCE
                            $sheet1->getStyle('I' . $row)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_PERCENTAGE);
                            $sheet1->getStyle('J' . $row)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_PERCENTAGE);


                            $row++;
                        }

                        // Insert total accomplishments for the indicator row
                        foreach ($monthsForPeriod as $monthIndex => $month) {
                            $columnLetter = chr(68 + $monthIndex);
                            $sheet1->setCellValue($columnLetter . ($row - 7), $totalAccomplishmentsByMonth[$month]);
                        }
                    }
                }

        //END 1st QUARTER


        //START 2ND QUARTER
            $sheet2 = $spreadsheet->createSheet();
            $sheet2->setTitle('Q2');
            $QuarterTwo = 'Q2';

            $quarterTwoName = '2ND QUARTER';

            $previousQuarter = $this->getPreviousQuarter($QuarterTwo);
            $PreviousQuarterName = '1ST QUARTER';

            // First level headers
            $headerRowStart = 1;
            $headerRowEnd = 3;

            // Add the header rows dynamically
            $sheet2->setCellValue('A' . $headerRowStart, 'INDICATORS');
            $sheet2->setCellValue('B' . $headerRowStart, 'TARGETS');
            $sheet2->setCellValue('G' . $headerRowStart, 'ACCOMPLISHMENTS');
            $sheet2->setCellValue('M' . $headerRowStart, 'Percentage');
            $sheet2->setCellValue('O' . $headerRowStart, 'Quarter Balance');
            $sheet2->setCellValue('P' . $headerRowStart, 'Annual Balance');
            $sheet2->setCellValue('Q' . $headerRowStart, 'Remark');

            //Second Level

            //TARGET
            $sheet2->setCellValue('B' . ($headerRowStart + 1), 'Annual Target');
            $sheet2->setCellValue('C' . ($headerRowStart + 1), 'Annual Balance');
            $sheet2->setCellValue('D' . ($headerRowStart + 1), $PreviousQuarterName);
            $sheet2->setCellValue('E' . ($headerRowStart + 1), $quarterTwoName);
            $sheet2->setCellValue('F' . ($headerRowStart + 1), '2nd Total.');

            //ACCOMPLISHMEMNT
            $sheet2->setCellValue('G' . ($headerRowStart + 1), 'Total Accomp');
            $sheet2->setCellValue('H' . ($headerRowStart + 1), $quarterTwoName);
            $months = $this->getMonthsForPeriod($QuarterTwo);
            $monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];

            // Insert dynamic month headers
            if ($months) {
                foreach ($months as $index => $month) {
                    $sheet2->setCellValue(chr(72 + $index) . ($headerRowStart + 2), $monthNames[$month - 1]); // Row 3 is now headerRowStart + 2
                }
            }

            $sheet2->setCellValue('K' . $headerRowEnd, 'QTR. TOTAL');
            $sheet2->setCellValue('L' . ($headerRowStart + 1), 'ANNUAL TOTAL');

            //PERCENTAGE
            $sheet2->setCellValue('M' . ($headerRowStart + 1), 'Qtr');
            $sheet2->setCellValue('N' . ($headerRowStart + 1), 'Annual');


            // Merge cells for 1st level headers
            $sheet2->mergeCells('A' . $headerRowStart . ':A' . $headerRowEnd); // INDICATORS
            $sheet2->mergeCells('B' . $headerRowStart . ':F' . $headerRowStart); // TARGETS
            $sheet2->mergeCells('G' . $headerRowStart . ':L' . $headerRowStart); // ACCOMPLISHMENTS
            $sheet2->mergeCells('M' . $headerRowStart . ':N' . $headerRowStart); // Percentage
            $sheet2->mergeCells('O' . $headerRowStart . ':O' . $headerRowEnd); // Quarter Balance
            $sheet2->mergeCells('P' . $headerRowStart . ':P' . $headerRowEnd); // Annual Balance
            $sheet2->mergeCells('Q' . $headerRowStart . ':Q' . $headerRowEnd); // Remark

            // Merge second level headers
            $sheet2->mergeCells('B' . ($headerRowStart + 1) . ':B' . $headerRowEnd); // Annual Target
            $sheet2->mergeCells('C' . ($headerRowStart + 1) . ':C' . $headerRowEnd); // Annual Balance
            $sheet2->mergeCells('D' . ($headerRowStart + 1) . ':D' . $headerRowEnd); // 1st quarter
            $sheet2->mergeCells('E' . ($headerRowStart + 1) . ':E' . $headerRowEnd); // 2nd quarter
            $sheet2->mergeCells('F' . ($headerRowStart + 1) . ':F' . $headerRowEnd); // 2nd Total
            $sheet2->mergeCells('G' . ($headerRowStart + 1) . ':G' . $headerRowEnd); // 2nd Total
            $sheet2->mergeCells('H' . ($headerRowStart + 1) . ':K' . ($headerRowStart + 1)); // 2ND QUARTER under ACCOMPLISHMENTS
            $sheet2->mergeCells('L' . ($headerRowStart + 1) . ':L' . $headerRowEnd); // ANNUAL TOTAL



            // Apply the header style to the sheet2 (this style is already defined in your code)
            $sheet2->getStyle('A1:Q' . ($headerRowStart + 2))->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '8B0000']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'FFFFFF']]],
            ]);

            // Set column width for better appearance (optional)
            foreach (range('A', 'Q') as $columnID) {
                $sheet2->getColumnDimension($columnID)->setAutoSize(true);
            }

            //DATA
            $orgOutcomes = DB::table('org_otc')->where('status', 'Active')
            ->whereYear('created_at', $year)
            ->orderBy('order','ASC')
            ->get();

            $row = $headerRowEnd + 1; // Start inserting data below the headers

            foreach ($orgOutcomes as $outcome) {
                // Insert the Organizational Outcome (Yellow row)
                $sheet2->setCellValue('A' . $row, $outcome->order . '.' .$outcome->organizational_outcome );

                // Style the Organizational Outcome row
                $sheet2->getStyle('A' . $row . ':Q' . $row)->applyFromArray([
                    'font' => ['bold' => true, 'size' => 14], // Thicker weight
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'fcd654'], // Yellow highlight
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN, // Border style
                            'color' => ['rgb' => 'FFFFFF'], // White color for the border
                        ],
                    ],
                ]);

                // Set row height for organizational outcome
                $sheet2->getRowDimension($row)->setRowHeight(30); // Increase height

                $row++;

                // Fetch success indicators related to the current organizational outcome
                $successIndicators = DB::table('success_indc')
                    ->whereYear('created_at', $year)
                    ->where('org_id', $outcome->id)
                    ->get();

                foreach ($successIndicators as $indicator) {
                    // Insert Success Indicators (Pink row)
                    $sheet2->setCellValue('A' . $row, $indicator->measures); // Measures under INDICATORS
                    $sheet2->setCellValue('B' . $row, $indicator->target); // Annual Target

                    // Initialize an array to hold the total accomplishments for each month
                    $totalAccomplishmentsByMonth = [];
                    $monthsForPeriod = $this->getMonthsForPeriod($QuarterTwo);

                    foreach ($monthsForPeriod as $month) {
                        $totalAccomplishmentsByMonth[$month] = 0; // Initialize each month with 0
                    }

                    $sheet2->getStyle('A' . $row . ':Q' . $row)->applyFromArray([
                        'font' => ['bold' => true, 'size' => 12], // Thicker weight
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => 'FFC0CB'], // Pink highlight
                        ],
                        'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN, // Border style
                            'color' => ['rgb' => 'FFFFFF'], // White color for the border
                        ],
                    ],
                    ]);


                    $sheet2->setCellValue('K' . $row, '=SUM(H' . $row . ':J' . $row . ')'); //QTR.TOTAL
                    $sheet2->setCellValue('C' . $row, "='Q1'!L" . $row); //TARGET: ANNUAL BALANCE
                    $sheet2->setCellValue('F' . $row, '=(D' . $row . '+E' . $row . ')'); //2ND TOTAL
                    $sheet2->setCellValue('G' . $row, "='Q1'!G" . $row); //ACCOMPLISHMENT: TOTAL ACCOMP
                    $sheet2->setCellValue('L' . $row, '=(G' . $row . '+K' . $row . ')'); //ANNUAL TOTAL
                    $sheet2->setCellValue('M' . $row, '=(K' . $row . '/G' . $row . ')'); //PERCENTAGE QTR
                    $sheet2->setCellValue('N' . $row, '=(L' . $row . '/B' . $row . ')'); //PERCENTAGE ANNUAL
                    $sheet2->getStyle('M' . $row)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_PERCENTAGE);
                    $sheet2->getStyle('N' . $row)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_PERCENTAGE);
                    $sheet2->setCellValue('O' . $row, '=(G' . $row . '-K' . $row . ')'); //QUARTER BALANCE
                    $sheet2->setCellValue('P' . $row, '=(C' . $row . '-L' . $row . ')'); //ANNUAL BALANCE


                    $row++;

                    $dvisions = Division::where('division_name', 'like', '%PO%')->get();

                    foreach ($dvisions as $division) {
                        $divisionName = str_replace(' PO', '', $division->division_name);

                        $targetField = str_replace(' ', '_', $divisionName) . '_target'; // Convert to lowercase with underscores
                        $targetValue = $indicator->$targetField ?? 0; // Get target value, default to 0 if not set

                        // Insert division name and target value in respective columns
                        $sheet2->setCellValue('A' . $row, $divisionName); // Division Name
                        $sheet2->setCellValue('B' . $row, $targetValue); // Corresponding Target

                        $entries = Entries::where('indicator_id', $indicator->id)
                        ->whereYear('created_at', $year)
                        ->whereIn(DB::raw('MONTH(created_at)'), $this->getMonthsForPeriod($QuarterTwo))
                        ->get();


                    // Initialize month accomplishments for the current division
                        $accomplishmentsByMonth = [];
                        foreach ($monthsForPeriod as $month) {
                            $accomplishmentsByMonth[$month] = 0; // Initialize each month with 0
                        }

                        // Aggregate the accomplishments for the respective months
                        foreach ($entries as $entry) {
                            $month = date('n', strtotime($entry->created_at)); // Get month as number (1-12)
                            $accomField = str_replace(' ', '_', $divisionName) . '_accomplishment';
                            $accomplishmentsByMonth[$month] += $entry->$accomField ?? 0; // Sum the accomplishments

                            // Sum the accomplishments for the total array
                            $totalAccomplishmentsByMonth[$month] += $entry->$accomField ?? 0;
                        }

                        foreach ($monthsForPeriod as $monthIndex => $month) {
                            $columnLetter = chr(72 + $monthIndex); // Calculate the column letter based on the index
                            $sheet2->setCellValue($columnLetter . $row, $accomplishmentsByMonth[$month]); // Set accomplishment in the correct column
                        }

                        $sheet2->setCellValue('K' . $row, '=SUM(H' . $row . ':J' . $row . ')'); //QTR.TOTAL
                        $sheet2->setCellValue('C' . $row, "='Q1'!L" . $row); //TARGET: ANNUAL BALANCE
                        $sheet2->setCellValue('F' . $row, '=(D' . $row . '+E' . $row . ')'); //2ND TOTAL
                        $sheet2->setCellValue('G' . $row, "='Q1'!G" . $row); //ACCOMPLISHMENT: TOTAL ACCOMP
                        $sheet2->setCellValue('L' . $row, '=(G' . $row . '+K' . $row . ')'); //ANNUAL TOTAL
                        $sheet2->setCellValue('M' . $row, '=(K' . $row . '/G' . $row . ')'); //PERCENTAGE QTR
                        $sheet2->setCellValue('N' . $row, '=(L' . $row . '/B' . $row . ')'); //PERCENTAGE ANNUAL
                        $sheet2->getStyle('M' . $row)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_PERCENTAGE);
                        $sheet2->getStyle('N' . $row)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_PERCENTAGE);
                        $sheet2->setCellValue('O' . $row, '=(G' . $row . '-K' . $row . ')'); //QUARTER BALANCE
                        $sheet2->setCellValue('P' . $row, '=(C' . $row . '-L' . $row . ')'); //ANNUAL BALANCE

                        $row++;
                    }

                    // Insert total accomplishments for the indicator row
                    foreach ($monthsForPeriod as $monthIndex => $month) {
                        $columnLetter = chr(72 + $monthIndex);
                        $sheet2->setCellValue($columnLetter . ($row - 7), $totalAccomplishmentsByMonth[$month]);
                    }

                }
            }



        //END 2ND QUARTER

        //START 3RD QUARTER
            $sheet3 = $spreadsheet->createSheet();
            $sheet3->setTitle('Q3');
            $QuarterThree = 'Q3';

            $quarterthreeName = '3RD QUARTER';

            $previousQuarter = $this->getPreviousQuarter($QuarterThree);
            $PreviousQuarterName = '2ND QUARTER';

            // First level headers
            $headerRowStart = 1;
            $headerRowEnd = 3;

            // Add the header rows dynamically
            $sheet3->setCellValue('A' . $headerRowStart, 'INDICATORS');
            $sheet3->setCellValue('B' . $headerRowStart, 'TARGETS');
            $sheet3->setCellValue('F' . $headerRowStart, 'ACCOMPLISHMENTS');
            $sheet3->setCellValue('L' . $headerRowStart, 'Percentage');
            $sheet3->setCellValue('N' . $headerRowStart, 'Quarter Balance');
            $sheet3->setCellValue('O' . $headerRowStart, 'Annual Balance');
            $sheet3->setCellValue('P' . $headerRowStart, 'Remark');

            //Second Level

            //TARGET
            $sheet3->setCellValue('B' . ($headerRowStart + 1), 'Annual');

            $sheet3->setCellValue('C' . ($headerRowStart + 1), $PreviousQuarterName);
            $sheet3->setCellValue('D' . ($headerRowStart + 1), $quarterthreeName);
            $sheet3->setCellValue('E' . ($headerRowStart + 1), '3RD Total.');

            //ACCOMPLISHMEMNT
            $sheet3->setCellValue('F' . ($headerRowStart + 1), 'Total Accomp');
            $sheet3->setCellValue('G' . ($headerRowStart + 1), $quarterthreeName);
            $months = $this->getMonthsForPeriod($QuarterThree);
            $monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];

            // Insert dynamic month headers
            if ($months) {
                foreach ($months as $index => $month) {
                    $sheet3->setCellValue(chr(71 + $index) . ($headerRowStart + 2), $monthNames[$month - 1]); // Row 3 is now headerRowStart + 2
                }
            }

            $sheet3->setCellValue('J' . $headerRowEnd, 'QTR. TOTAL');
            $sheet3->setCellValue('K' . ($headerRowStart + 1), 'ANNUAL TOTAL');

            //PERCENTAGE
            $sheet3->setCellValue('L' . ($headerRowStart + 1), 'Qtr');
            $sheet3->setCellValue('M' . ($headerRowStart + 1), 'Annual');


            // Merge cells for 1st level headers
            $sheet3->mergeCells('A' . $headerRowStart . ':A' . $headerRowEnd); // INDICATORS
            $sheet3->mergeCells('B' . $headerRowStart . ':E' . $headerRowStart); // TARGETS
            $sheet3->mergeCells('F' . $headerRowStart . ':K' . $headerRowStart); // ACCOMPLISHMENTS
            $sheet3->mergeCells('L' . $headerRowStart . ':M' . $headerRowStart); // Percentage
            $sheet3->mergeCells('N' . $headerRowStart . ':N' . $headerRowEnd); // Quarter Balance
            $sheet3->mergeCells('O' . $headerRowStart . ':O' . $headerRowEnd); // Annual Balance
            $sheet3->mergeCells('P' . $headerRowStart . ':P' . $headerRowEnd); // Remark

            // Merge second level headers
            $sheet3->mergeCells('B' . ($headerRowStart + 1) . ':B' . $headerRowEnd); // Annual Target
            $sheet3->mergeCells('C' . ($headerRowStart + 1) . ':C' . $headerRowEnd); // Annual Balance
            $sheet3->mergeCells('D' . ($headerRowStart + 1) . ':D' . $headerRowEnd); // 1st quarter
            $sheet3->mergeCells('E' . ($headerRowStart + 1) . ':E' . $headerRowEnd); // 2nd quarter
            $sheet3->mergeCells('F' . ($headerRowStart + 1) . ':F' . $headerRowEnd); // 2nd Total
            $sheet3->mergeCells('G' . ($headerRowStart + 1) . ':J' . ($headerRowStart + 1)); // 2nd Total
            // $sheet3->mergeCells('H' . ($headerRowStart + 1) . ':K' . ($headerRowStart + 1)); // 2ND QUARTER under ACCOMPLISHMENTS
            $sheet3->mergeCells('K' . ($headerRowStart + 1) . ':K' . $headerRowEnd); // ANNUAL TOTAL
            $sheet3->mergeCells('L' . ($headerRowStart + 1) . ':L' . $headerRowEnd); // ANNUAL TOTAL
            $sheet3->mergeCells('M' . ($headerRowStart + 1) . ':M' . $headerRowEnd); // ANNUAL TOTAL



            // Apply the header style to the sheet3 (this style is already defined in your code)
            $sheet3->getStyle('A1:P' . ($headerRowStart + 2))->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '8B0000']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'FFFFFF']]],
            ]);

            // Set column width for better appearance (optional)
            foreach (range('A', 'P') as $columnID) {
                $sheet3->getColumnDimension($columnID)->setAutoSize(true);
            }

            //DATA
            $orgOutcomes = DB::table('org_otc')->where('status', 'Active')
            ->whereYear('created_at', $year)
            ->orderBy('order','ASC')
            ->get();

            $row = $headerRowEnd + 1; // Start inserting data below the headers

            foreach ($orgOutcomes as $outcome) {
                // Insert the Organizational Outcome (Yellow row)
                $sheet3->setCellValue('A' . $row, $outcome->order . '.' .$outcome->organizational_outcome );

                // Style the Organizational Outcome row
                $sheet3->getStyle('A' . $row . ':P' . $row)->applyFromArray([
                    'font' => ['bold' => true, 'size' => 14], // Thicker weight
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'fcd654'], // Yellow highlight
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN, // Border style
                            'color' => ['rgb' => 'FFFFFF'], // White color for the border
                        ],
                    ],
                ]);

                // Set row height for organizational outcome
                $sheet3->getRowDimension($row)->setRowHeight(30); // Increase height
                $sheet3->setCellValue('F' . $row, "='Q2'!L" . $row); //ACCOMPLISHMENT: TOTAL ACCOMP

                $row++;

                // Fetch success indicators related to the current organizational outcome
                $successIndicators = DB::table('success_indc')
                    ->whereYear('created_at', $year)
                    ->where('org_id', $outcome->id)
                    ->get();

                foreach ($successIndicators as $indicator) {
                    // Insert Success Indicators (Pink row)
                    $sheet3->setCellValue('A' . $row, $indicator->measures); // Measures under INDICATORS
                    $sheet3->setCellValue('B' . $row, $indicator->target); // Annual Target

                    // Initialize an array to hold the total accomplishments for each month
                    $totalAccomplishmentsByMonth = [];
                    $monthsForPeriod = $this->getMonthsForPeriod($QuarterThree);

                    foreach ($monthsForPeriod as $month) {
                        $totalAccomplishmentsByMonth[$month] = 0; // Initialize each month with 0
                    }

                    $sheet3->getStyle('A' . $row . ':P' . $row)->applyFromArray([
                        'font' => ['bold' => true, 'size' => 12], // Thicker weight
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => 'FFC0CB'], // Pink highlight
                        ],
                        'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN, // Border style
                            'color' => ['rgb' => 'FFFFFF'], // White color for the border
                        ],
                    ],
                    ]);


                    $sheet3->setCellValue('C' . $row, '=(G' . $row .')'); //2ND QUARTER
                    $sheet3->setCellValue('J' . $row, '=SUM(G' . $row . ':I' . $row . ')'); //QTR.TOTAL
                    $sheet3->setCellValue('F' . $row, "='Q2'!L" . $row); //ACCOMPLISHMENT: TOTAL ACCOMP
                    $sheet3->setCellValue('K' . $row, '=(F' . $row . '+J' . $row . ')'); //ACCOMPLISHMENT: ANNUAL TOTAL

                    $row++;

                    $dvisions = Division::where('division_name', 'like', '%PO%')->get();

                    foreach ($dvisions as $division) {
                        $divisionName = str_replace(' PO', '', $division->division_name);

                        $targetField = str_replace(' ', '_', $divisionName) . '_target'; // Convert to lowercase with underscores
                        $targetValue = $indicator->$targetField ?? 0; // Get target value, default to 0 if not set

                        // Insert division name and target value in respective columns
                        $sheet3->setCellValue('A' . $row, $divisionName); // Division Name
                        $sheet3->setCellValue('B' . $row, $targetValue); // Corresponding Target

                        $entries = Entries::where('indicator_id', $indicator->id)
                        ->whereYear('created_at', $year)
                        ->whereIn(DB::raw('MONTH(created_at)'), $this->getMonthsForPeriod($QuarterThree))
                        ->get();


                    // Initialize month accomplishments for the current division
                        $accomplishmentsByMonth = [];
                        foreach ($monthsForPeriod as $month) {
                            $accomplishmentsByMonth[$month] = 0; // Initialize each month with 0
                        }

                        // Aggregate the accomplishments for the respective months
                        foreach ($entries as $entry) {
                            $month = date('n', strtotime($entry->created_at)); // Get month as number (1-12)
                            $accomField = str_replace(' ', '_', $divisionName) . '_accomplishment';
                            $accomplishmentsByMonth[$month] += $entry->$accomField ?? 0; // Sum the accomplishments

                            // Sum the accomplishments for the total array
                            $totalAccomplishmentsByMonth[$month] += $entry->$accomField ?? 0;
                        }

                        foreach ($monthsForPeriod as $monthIndex => $month) {
                            $columnLetter = chr(71 + $monthIndex); // Calculate the column letter based on the index
                            $sheet3->setCellValue($columnLetter . $row, $accomplishmentsByMonth[$month]); // Set accomplishment in the correct column
                        }

                        $sheet3->setCellValue('C' . $row, '=(G' . $row .')'); //2ND QUARTER
                        $sheet3->setCellValue('J' . $row, '=SUM(G' . $row . ':I' . $row . ')'); //QTR.TOTAL
                        $sheet3->setCellValue('F' . $row, "='Q2'!L" . $row); //ACCOMPLISHMENT: TOTAL ACCOMP
                        $sheet3->setCellValue('K' . $row, '=(F' . $row . '+J' . $row . ')'); //ACCOMPLISHMENT: ANNUAL TOTAL


                        $row++;
                    }

                    // Insert total accomplishments for the indicator row
                    foreach ($monthsForPeriod as $monthIndex => $month) {
                        $columnLetter = chr(71 + $monthIndex);
                        $sheet3->setCellValue($columnLetter . ($row - 7), $totalAccomplishmentsByMonth[$month]);
                    }

                }
            }



        //END 3RD QUARTER

        //START 4TH QUARTER
            $sheet4 = $spreadsheet->createSheet();
            $sheet4->setTitle('Q4');
            $QuarterFour = 'Q4';

            $quarterFourName = '4TH QUARTER';

            $previousQuarter = $this->getPreviousQuarter($QuarterFour);
            $PreviousQuarterName = '3RD QUARTER';

            // First level headers
            $headerRowStart = 1;
            $headerRowEnd = 3;

            // Add the header rows dynamically
            $sheet4->setCellValue('A' . $headerRowStart, 'INDICATORS');
            $sheet4->setCellValue('B' . $headerRowStart, 'TARGETS');
            $sheet4->setCellValue('F' . $headerRowStart, 'ACCOMPLISHMENTS');
            $sheet4->setCellValue('L' . $headerRowStart, 'Percentage');
            $sheet4->setCellValue('N' . $headerRowStart, 'Quarter Balance');
            $sheet4->setCellValue('O' . $headerRowStart, 'Annual Balance');
            $sheet4->setCellValue('P' . $headerRowStart, 'Remark');

            //Second Level

            //TARGET
            $sheet4->setCellValue('B' . ($headerRowStart + 1), 'Annual');

            $sheet4->setCellValue('C' . ($headerRowStart + 1), $PreviousQuarterName);
            $sheet4->setCellValue('D' . ($headerRowStart + 1), $quarterFourName);
            $sheet4->setCellValue('E' . ($headerRowStart + 1), '4TH Total.');

            //ACCOMPLISHMEMNT
            $sheet4->setCellValue('F' . ($headerRowStart + 1), 'Total Accomp');
            $sheet4->setCellValue('G' . ($headerRowStart + 1), $quarterFourName);
            $months = $this->getMonthsForPeriod($QuarterFour);
            $monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];

            // Insert dynamic month headers
            if ($months) {
                foreach ($months as $index => $month) {
                    $sheet4->setCellValue(chr(71 + $index) . ($headerRowStart + 2), $monthNames[$month - 1]); // Row 3 is now headerRowStart + 2
                }
            }

            $sheet4->setCellValue('J' . $headerRowEnd, 'QTR. TOTAL');
            $sheet4->setCellValue('K' . ($headerRowStart + 1), 'ANNUAL TOTAL');

            //PERCENTAGE
            $sheet4->setCellValue('L' . ($headerRowStart + 1), 'Qtr');
            $sheet4->setCellValue('M' . ($headerRowStart + 1), 'Annual');


            // Merge cells for 1st level headers
            $sheet4->mergeCells('A' . $headerRowStart . ':A' . $headerRowEnd); // INDICATORS
            $sheet4->mergeCells('B' . $headerRowStart . ':E' . $headerRowStart); // TARGETS
            $sheet4->mergeCells('F' . $headerRowStart . ':K' . $headerRowStart); // ACCOMPLISHMENTS
            $sheet4->mergeCells('L' . $headerRowStart . ':M' . $headerRowStart); // Percentage
            $sheet4->mergeCells('N' . $headerRowStart . ':N' . $headerRowEnd); // Quarter Balance
            $sheet4->mergeCells('O' . $headerRowStart . ':O' . $headerRowEnd); // Annual Balance
            $sheet4->mergeCells('P' . $headerRowStart . ':P' . $headerRowEnd); // Remark

            // Merge second level headers
            $sheet4->mergeCells('B' . ($headerRowStart + 1) . ':B' . $headerRowEnd); // Annual Target
            $sheet4->mergeCells('C' . ($headerRowStart + 1) . ':C' . $headerRowEnd); // Annual Balance
            $sheet4->mergeCells('D' . ($headerRowStart + 1) . ':D' . $headerRowEnd); // 1st quarter
            $sheet4->mergeCells('E' . ($headerRowStart + 1) . ':E' . $headerRowEnd); // 2nd quarter
            $sheet4->mergeCells('F' . ($headerRowStart + 1) . ':F' . $headerRowEnd); // 2nd Total
            $sheet4->mergeCells('G' . ($headerRowStart + 1) . ':J' . ($headerRowStart + 1)); // 2nd Total
            // $sheet4->mergeCells('H' . ($headerRowStart + 1) . ':K' . ($headerRowStart + 1)); // 2ND QUARTER under ACCOMPLISHMENTS
            $sheet4->mergeCells('K' . ($headerRowStart + 1) . ':K' . $headerRowEnd); // ANNUAL TOTAL
            $sheet4->mergeCells('L' . ($headerRowStart + 1) . ':L' . $headerRowEnd); // ANNUAL TOTAL
            $sheet4->mergeCells('M' . ($headerRowStart + 1) . ':M' . $headerRowEnd); // ANNUAL TOTAL



            // Apply the header style to the sheet4 (this style is already defined in your code)
            $sheet4->getStyle('A1:P' . ($headerRowStart + 2))->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '8B0000']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'FFFFFF']]],
            ]);

            // Set column width for better appearance (optional)
            foreach (range('A', 'P') as $columnID) {
                $sheet4->getColumnDimension($columnID)->setAutoSize(true);
            }

            //DATA
            $orgOutcomes = DB::table('org_otc')->where('status', 'Active')
            ->whereYear('created_at', $year)
            ->orderBy('order','ASC')
            ->get();

            $row = $headerRowEnd + 1; // Start inserting data below the headers

            foreach ($orgOutcomes as $outcome) {
                // Insert the Organizational Outcome (Yellow row)
                $sheet4->setCellValue('A' . $row, $outcome->order . '.' .$outcome->organizational_outcome );

                // Style the Organizational Outcome row
                $sheet4->getStyle('A' . $row . ':P' . $row)->applyFromArray([
                    'font' => ['bold' => true, 'size' => 14], // Thicker weight
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'fcd654'], // Yellow highlight
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN, // Border style
                            'color' => ['rgb' => 'FFFFFF'], // White color for the border
                        ],
                    ],
                ]);

                // Set row height for organizational outcome
                $sheet4->getRowDimension($row)->setRowHeight(30); // Increase height
                // $sheet4->setCellValue('F' . $row, "='Q2'!L" . $row); //ACCOMPLISHMENT: TOTAL ACCOMP

                $row++;

                // Fetch success indicators related to the current organizational outcome
                $successIndicators = DB::table('success_indc')
                    ->whereYear('created_at', $year)
                    ->where('org_id', $outcome->id)
                    ->get();

                foreach ($successIndicators as $indicator) {
                    // Insert Success Indicators (Pink row)
                    $sheet4->setCellValue('A' . $row, $indicator->measures); // Measures under INDICATORS
                    $sheet4->setCellValue('B' . $row, $indicator->target); // Annual Target

                    // Initialize an array to hold the total accomplishments for each month
                    $totalAccomplishmentsByMonth = [];
                    $monthsForPeriod = $this->getMonthsForPeriod($QuarterFour);

                    foreach ($monthsForPeriod as $month) {
                        $totalAccomplishmentsByMonth[$month] = 0; // Initialize each month with 0
                    }

                    $sheet4->getStyle('A' . $row . ':P' . $row)->applyFromArray([
                        'font' => ['bold' => true, 'size' => 12], // Thicker weight
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => 'FFC0CB'], // Pink highlight
                        ],
                        'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN, // Border style
                            'color' => ['rgb' => 'FFFFFF'], // White color for the border
                        ],
                    ],
                    ]);


                    $sheet4->setCellValue('C' . $row, '=(G' . $row .')'); //2ND QUARTER
                    $sheet4->setCellValue('J' . $row, '=SUM(G' . $row . ':I' . $row . ')'); //QTR.TOTAL
                    $sheet4->setCellValue('F' . $row, "='Q2'!L" . $row); //ACCOMPLISHMENT: TOTAL ACCOMP
                    $sheet4->setCellValue('K' . $row, '=(F' . $row . '+J' . $row . ')'); //ACCOMPLISHMENT: ANNUAL TOTAL

                    $row++;

                    $dvisions = Division::where('division_name', 'like', '%PO%')->get();

                    foreach ($dvisions as $division) {
                        $divisionName = str_replace(' PO', '', $division->division_name);

                        $targetField = str_replace(' ', '_', $divisionName) . '_target'; // Convert to lowercase with underscores
                        $targetValue = $indicator->$targetField ?? 0; // Get target value, default to 0 if not set

                        // Insert division name and target value in respective columns
                        $sheet4->setCellValue('A' . $row, $divisionName); // Division Name
                        $sheet4->setCellValue('B' . $row, $targetValue); // Corresponding Target

                        $entries = Entries::where('indicator_id', $indicator->id)
                        ->whereYear('created_at', $year)
                        ->whereIn(DB::raw('MONTH(created_at)'), $this->getMonthsForPeriod($QuarterFour))
                        ->get();


                    // Initialize month accomplishments for the current division
                        $accomplishmentsByMonth = [];
                        foreach ($monthsForPeriod as $month) {
                            $accomplishmentsByMonth[$month] = 0; // Initialize each month with 0
                        }

                        // Aggregate the accomplishments for the respective months
                        foreach ($entries as $entry) {
                            $month = date('n', strtotime($entry->created_at)); // Get month as number (1-12)
                            $accomField = str_replace(' ', '_', $divisionName) . '_accomplishment';
                            $accomplishmentsByMonth[$month] += $entry->$accomField ?? 0; // Sum the accomplishments

                            // Sum the accomplishments for the total array
                            $totalAccomplishmentsByMonth[$month] += $entry->$accomField ?? 0;
                        }

                        foreach ($monthsForPeriod as $monthIndex => $month) {
                            $columnLetter = chr(71 + $monthIndex); // Calculate the column letter based on the index
                            $sheet4->setCellValue($columnLetter . $row, $accomplishmentsByMonth[$month]); // Set accomplishment in the correct column
                        }

                        $sheet4->setCellValue('C' . $row, '=(G' . $row .')'); //2ND QUARTER
                        $sheet4->setCellValue('J' . $row, '=SUM(G' . $row . ':I' . $row . ')'); //QTR.TOTAL
                        // $sheet4->setCellValue('F' . $row, "='Q2'!L" . $row); //ACCOMPLISHMENT: TOTAL ACCOMP
                        // $sheet4->setCellValue('K' . $row, '=(F' . $row . '+J' . $row . ')'); //ACCOMPLISHMENT: ANNUAL TOTAL


                        $row++;
                    }

                    // Insert total accomplishments for the indicator row
                    foreach ($monthsForPeriod as $monthIndex => $month) {
                        $columnLetter = chr(71 + $monthIndex);
                        $sheet4->setCellValue($columnLetter . ($row - 7), $totalAccomplishmentsByMonth[$month]);
                    }

                }
            }



        //END 4TH QUARTER



        $spreadsheet->setActiveSheetIndex(0);
        // Set headers for file download
        $fileName = 'multiple-sheets.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        header('Cache-Control: max-age=0');

        // Write the file
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');

        // Exit to prevent any additional output
        exit;


    }















}
