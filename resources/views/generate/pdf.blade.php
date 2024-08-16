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
        }

        .table td {
            vertical-align: top;
            font-size: 12px;
        }
    </style>
</head>
<body>
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
                    <tr>
                        <td rowspan="{{ count($outcome->successIndicators) }}">
                            {{ $outcome->organizational_outcome }}
                        </td>
                        @foreach($outcome->successIndicators as $index => $indicator)
                            @if($index > 0)
                                <tr>
                            @endif
                                <td>{{ '(' .$indicator->target. ')' . ' ' . $indicator->measures }}</td>
                                <td>{{ number_format($indicator->alloted_budget, 2) }}</td>
                                <td>
                                    @php
                                    $division_ids = [];
                                    $division_ids = json_decode($indicator->division_id);

                                    @endphp
                                    
                                    @foreach($division_ids as $divisionId)
                                        @php
                                            $division = \App\Models\Division::find($divisionId);
                                        @endphp
                                        {{ $division ? $division->division_name : 'N/A' }}<br>
                                    @endforeach
                                </td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                            </tr>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</body>
</html>