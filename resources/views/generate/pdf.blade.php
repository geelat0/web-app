<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>OPCR</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
        }

        .container {
            width: 100%;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .header_page {
            font-size: 12px;
        }

        .subheader {
            font-weight: bold;
        }

        @page {
            /* margin: 100px 25px; */
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin: 0 auto; /* Center the table */
        }

        .table th, .table td {
            border: 1px solid black;
            padding: 8px;
            text-align: left;
        }

        .table th {
            background-color: #8ccdf0;
            font-size: 14px;
            text-align: center;
        }

        .table td {
            vertical-align: top;
            font-size: 12px;
        }

        .no-indicators td {
            border: 1px solid  black; /* Special border for rows without indicators */
            padding: 8px;
            text-align: left;
        }

        .footer {
            text-align: right;
            font-size: 12px;
            position: fixed;
            bottom: 20px;
            width: 100%;
        }

        .page-number:before {
            content: counter(page);
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header_page">
            Republic of the Philippines
        </div> 
        <div class="subheader">
            Department of Labor and Employment
        </div>
        <div class="header_page">
            Intramuros, Manila
        </div>
    </div>
    <div class="container">
        <table class="table">
            <thead>
                <tr>
                    <th rowspan="2">Organizational Outcome/PAP</th>
                    <th rowspan="2">Success Indicator</th>
                    <th rowspan="2">Allotted Budget</th>
                    <th rowspan="2">Division/Individuals Accountable</th>
                    <th rowspan="2">Actual Accomplishment</th>
                    <th colspan="4">Rating</th>
                    <th rowspan="2">Remarks</th>
                </tr>
                <tr class="rating-header">
                    <th>Q1</th>
                    <th>Q2</th>
                    <th>T3</th>
                    <th>A4</th>
                </tr>
            </thead>
            <tbody>
                @foreach($orgOutcomes as $outcome)
                    @php
                        $hasIndicators = count($outcome->successIndicators) > 0;
                    @endphp
                    @if(!$hasIndicators)
                        <tr class="no-indicators">
                            <td colspan="1">
                                {{ $outcome->organizational_outcome }}
                            </td>
                            <td colspan="9"></td>
                        </tr>

                    @else
                        <tr>
                            <td rowspan="{{ count($outcome->successIndicators) }}">
                                {{ $outcome->organizational_outcome }}
                            </td>
                            @foreach($outcome->successIndicators as $index => $indicator)
                                @php
                                    $division_ids = json_decode($indicator->division_id);
                                    $filteredDivisionIds = $divisionIds ?? []; // The filtered division IDs from the request
                                @endphp
                                @if($index > 0)
                                    <tr>
                                @endif
                                <td>
                                    @if(empty($filteredDivisionIds))
                                        <!-- If no division is filtered, show the target and measures -->
                                        {{ '(' . $indicator->target . ')' . ' ' . $indicator->measures }}
                                    @else
                                        <!-- If division is filtered, show the specific budget based on division -->
                                        @foreach($division_ids as $divisionId)
                                            @php
                                                $division = \App\Models\Division::find($divisionId);
                                                $showDivision = in_array($divisionId, $filteredDivisionIds);
                                            @endphp

                                            @if($showDivision && $division)
                                                @if($division->division_name === 'Albay PO')
                                                {{ '(' . $indicator->Albay_target . ')' . ' ' . $indicator->measures }}
                                               
                                                @elseif($division->division_name === 'Camarines Norte PO')
                                                {{ '(' . $indicator->Camarines_Norte_target . ')' . ' ' . $indicator->measures }}

                                                @elseif($division->division_name === 'Camarines Sur PO')
                                                {{ '(' . $indicator->Camarines_Sur_target . ')' . ' ' . $indicator->measures }}
                                                
                                                @elseif($division->division_name === 'Catanduanes PO')
                                                {{ '(' . $indicator->Catanduanes_target . ')' . ' ' . $indicator->measures }}
                                                
                                                @elseif($division->division_name === 'Masbate PO')
                                                {{ '(' . $indicator->Masbate_target . ')' . ' ' . $indicator->measures }}
                                                
                                                @elseif($division->division_name === 'Sorsogon PO')
                                                {{ '(' . $indicator->Sorsogon_target . ')' . ' ' . $indicator->measures }}
                                                
                                                @else
                                                {{ '(' . $indicator->target . ')' . ' ' . $indicator->measures }}
                                                @endif
                                            @endif
                                        @endforeach
                                    @endif
                                </td>
                                <td>
                                    @if(empty($filteredDivisionIds))
                                        <!-- If no division is filtered, show the target and measures -->
                                        {{ number_format($indicator->alloted_budget, 2) }}
                                    @else
                                        <!-- If division is filtered, show the specific budget based on division -->
                                        @foreach($division_ids as $divisionId)
                                            @php
                                                $division = \App\Models\Division::find($divisionId);
                                                $showDivision = in_array($divisionId, $filteredDivisionIds);
                                            @endphp

                                            @if($showDivision && $division)
                                                @if($division->division_name === 'Albay PO')
                                                {{ number_format($indicator->Albay_budget, 2) }}
                                               
                                                @elseif($division->division_name === 'Camarines Norte PO')
                                                {{ number_format($indicator->Camarines_Norte_budget, 2) }}

                                                @elseif($division->division_name === 'Camarines Sur PO')
                                                {{ number_format($indicator->Camarines_Sur_budget, 2) }}
                                                
                                                @elseif($division->division_name === 'Catanduanes PO')
                                                {{ number_format($indicator->Catanduanes_budget, 2) }}
                                                
                                                @elseif($division->division_name === 'Masbate PO')
                                                {{ number_format($indicator->Masbate_budget, 2) }}
                                                
                                                @elseif($division->division_name === 'Sorsogon PO')
                                                {{ number_format($indicator->Sorsogon_budget, 2) }}
                                                
                                                @else
                                                {{ number_format($indicator->alloted_budget, 2) }}
                                                @endif
                                            @endif
                                        @endforeach
                                    @endif
                                </td>
                                <td>
                                    @foreach($division_ids as $divisionId)
                                        @php
                                            $division = \App\Models\Division::find($divisionId);
                                            $showDivision = empty($filteredDivisionIds) || in_array($divisionId, $filteredDivisionIds);
                                        @endphp

                                        @if($showDivision && $division)
                                            {{ $division->division_name }}<br>
                                        @endif
                                    @endforeach
                                </td>
                                <td>
                                    @if(isset($entries[$indicator->id]))
                                        @foreach($entries[$indicator->id] as $entry)
                                            {{ $entry->accomplishment }}<br>
                                        @endforeach
                                    @endif
                                </td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                            @endforeach
                        </tr>
                    @endif
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="footer">
        <span class="page-number"></span>
    </div>
</body>
</html>
