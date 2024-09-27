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

    public function export(Request $request)
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



        // Get the appropriate quarter name based on the selectedQuarter
        $quarterName = isset($quarterNames[$selectedQuarter]) ? $quarterNames[$selectedQuarter] : 'QUARTER';

        $previousQuarter = $this->getPreviousQuarter($selectedQuarter);
        $PreviousQuarterName = isset($quarterNames[$previousQuarter]) ? $quarterNames[$previousQuarter] : '';

        // Set the headers based on your structure
        // First level headers

        // Row number for headers
        $headerRowStart = 1; // Starting point for the first header row
        $headerRowEnd = 3;   // Default end point

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
                    $columnLetter = chr(71 + $monthIndex); // Calculate the column letter based on the index
                    $sheet->setCellValue($columnLetter . ($row - 7), $totalAccomplishmentsByMonth[$month]); // Set total accomplishment in the previous row
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














}
