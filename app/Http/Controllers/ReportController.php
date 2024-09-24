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
            ->groupBy(function($entry) {
                return [$entry->indicator_id, $entry->created_at->format('m')]; // Group by indicator and month
            });
            // dd($entry->sum('Albay_accomplishment'));



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
            ->groupBy(function($entry) {
                return [$entry->indicator_id, $entry->created_at->format('m')]; // Group by indicator and month
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

    public function generateCSV(Request $request)
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
    $indicatorIds = collect();

    if (!empty($divisionIds)) {
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

    if ($indicatorIds->isEmpty()) {
        return response()->json(['error' => 'No matching indicators found.'], 404);
    }

    $orgOutcomes = Organizational::with(['successIndicators' => function($query) use ($year, $indicatorIds) {
        if ($indicatorIds->isNotEmpty()) {
            $query->whereIn('id', $indicatorIds);
        }
        if ($year) {
            $query->whereYear('created_at', $year);
        }
    }])
    ->where(function($query) use ($year, $indicatorIds) {
        $query->whereHas('successIndicators', function($query) use ($year, $indicatorIds) {
            if ($indicatorIds->isNotEmpty()) {
                $query->whereIn('id', $indicatorIds);
            }
            if ($year) {
                $query->whereYear('created_at', $year);
            }
        });
    })
    ->orderBy('order', 'ASC')
    ->get();

    // Create a CSV file
    $fileName = 'OPCR_Report_' . date('Y-m-d_H-i-s') . '.csv';
    $headers = [
        "Content-Type" => "text/csv",
        "Content-Disposition" => "attachment; filename=\"$fileName\"",
    ];

    // Open output stream
    $callback = function() use ($orgOutcomes, $divisionIds, $entries) {
        $file = fopen('php://output', 'w');

        // Write the CSV header
        fputcsv($file, ['INDICATOR', 'TARGET', 'ACCOMPLISHMENT', 'PERCENTAGE', 'Actual Accomplishment', 'Quarter Balance', 'Annual Balance', 'Remarks']);

        // Write the data rows
        foreach ($orgOutcomes as $outcome) {
            $rowSpan = count($outcome->successIndicators) > 0 ? count($outcome->successIndicators) : 1;
            $firstRow = true;

            foreach ($outcome->successIndicators as $indicator) {
                $row = [];

                if ($firstRow) {
                    $row[] = $outcome->organizational_outcome; // Organizational Outcome/PAP
                    $firstRow = false;
                } else {
                    $row[] = ''; // Skip Organizational Outcome/PAP for subsequent rows
                }



                // Success Indicator
                $row[] = $indicator->measures;

                // Allotted Budget (This needs to be calculated based on logic)
                $row[] = $indicator->allotted_budget ?? 'N/A';

                // Division/Individuals Accountable
                $division_ids = json_decode($indicator->division_id);
                $row[] = implode(', ', $division_ids);

                // Actual Accomplishment (example value)
                $row[] = $entries[$indicator->id] ?? 'N/A';

                // Quarterly ratings (example values, replace with actual data)
                $row[] = rand(1, 5); // Q1 rating
                $row[] = rand(1, 5); // Q2 rating
                $row[] = rand(1, 5); // Q3 rating
                $row[] = rand(1, 5); // Q4 rating

                // Remarks
                $row[] = 'Some remarks';

                // Write the row to the CSV
                fputcsv($file, $row);
            }
        }

        fclose($file);
    };

    // Return the CSV download
    return response()->stream($callback, 200, $headers);
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

    public function exportToCSV()
    {
        // Replace this with your data-fetching logic
        $data = [
            ['Organizational Outcome/PAP', 'Success Indicator', 'Allotted Budget', 'Division', 'Q1', 'Q2', 'Q3', 'Q4', 'Remarks'],
            ['Outcome 1', 'Indicator 1', '1000', 'Division A', 'Complete', '', '', '', 'Good progress'],
            ['Outcome 2', 'Indicator 2', '2000', 'Division B', '', 'Complete', '', '', 'Needs Improvement'],
            // Add more rows as needed
        ];

        // Define the CSV filename
        $fileName = 'report_' . date('Y_m_d_H_i_s') . '.csv';

        // Set headers to trigger download
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ];

        // Use a callback to stream the CSV content
        $callback = function() use ($data) {
            $file = fopen('php://output', 'w');

            // Write each row to the CSV file
            foreach ($data as $row) {
                fputcsv($file, $row);
            }

            fclose($file);
        };

        // Return the streamed CSV response
        return response()->stream($callback, 200, $headers);
    }

    public function export()
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="export.csv"',
        ];

        $callback = function () {
            $file = fopen('php://output', 'w');

            // First level headers
            fputcsv($file, [
                'INDICATORS', 'TARGETS', '', '', '', '',
                'ACCOMPLISHMENTS', '', '', '', '', '', '',
                'Percentage', '', 'Balance', '', 'REMARKS'
            ]);

            // Second level headers
            fputcsv($file, [
                '', '', 'Annual', '2nd Qtr', '3rd', 'Total 3rd',
                'Total Accomp.', '3RD QUARTER', '', '', 'ANNUAL TOTAL', '',
                'Qtr', 'Annual', 'Quarter Balance', 'Annual Balance', ''
            ]);

            // Third level headers (if applicable)
            fputcsv($file, [
                '', '', '', '', '', '',
                '', 'Oct', 'Nov', 'Dec', 'QTR.', '',
                '', '', '', '', ''
            ]);

            fclose($file);
        };

        return new StreamedResponse($callback, 200, $headers);
    }














}
