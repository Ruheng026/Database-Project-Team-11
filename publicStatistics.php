<?php
require 'eloquent.php';

use Illuminate\Database\Capsule\Manager as DB;
?>

<!DOCTYPE html>
<html>
<head>
<title>ICL Public Statistics</title>
<link rel="stylesheet" href="style.css">
<!-- Include jQuery -->
<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<!-- Include AJAX script -->
<script>
    $(document).ready(function () {
        $('#schcounty').on('change', function () {
            var selectedCounty = $(this).val();
            
            // Make an AJAX request to fetch schools based on the selected county
            $.ajax({
                url: 'getSchoolsByCounty.php', // Create this file to handle the AJAX request
                method: 'POST',
                data: { schcounty: selectedCounty },
                success: function (data) {
                    // Update the schname dropdown with the fetched schools
                    $('#schname').html(data);
                }
            });
        });
    });
</script>
</head>
<body>
<div class="container">
    <h1>ICL Public Statistics</h1>
    <div class="link-container">
        <a href="index.php" class="search-link">Home</a>
    </div>
    <?php
        // 1. the top 10 countries with the most participants so far
        $studentsByNationality = DB::table('student')
            ->select('stunationality', DB::raw('COUNT(*) as TotalStudentsByNationality'))
            ->groupBy('stunationality')
            ->orderByDesc(DB::raw('COUNT(*)'))
            ->limit(10)
            ->get();
        // 2. the total number of participating students
        $totalStudents = DB::table('student')->count();

        $index = 1;
        echo "<h3 style='text-align: center;'>Top 10 Participants by Nationality</h3>";
        echo "<table>";
        echo "<tr><th>Rank</th><th>Nationality</th><th>Participants Count</th></tr>";
        foreach ($studentsByNationality as $nationality) {
            echo "<tr><td>{$index}</td><td>{$nationality->stunationality}</td><td>{$nationality->totalstudentsbynationality}</td></tr>";
            $index++;
        }
        echo "<tr><td>Total</td><td></td><td>{$totalStudents}</td></tr>";
        echo "</table>";

        // 3. the number of schools from each region that have participated in the ICL
        $schoolsByCounty = DB::table('school')
            ->select('schcounty', DB::raw('COUNT(DISTINCT school_id) as TotalSchoolsByCounty'))
            ->groupBy('schcounty')
            ->orderByDesc(DB::raw('COUNT(DISTINCT school_id)'))
            ->orderBy('schcounty')
            ->get();
        $totalSchools = DB::table('school')->count();
        
        echo "<h3 style='text-align: center;'>Partner Schools by County</h3>";
        echo "<table>";
        echo "<tr><th>County</th><th>Schools Count</th></tr>";
        foreach ($schoolsByCounty as $county) {
            echo "<tr><td>{$county->schcounty}</td><td>{$county->totalschoolsbycounty}</td></tr>";
        }
        echo "<tr><td>Total</td><td>{$totalSchools}</td></tr>";
        echo "</table>";

        // 4. a list of schools that have participated
        $schools = DB::table('school')
            ->select('schname', 'schcounty')
            ->orderBy('schcounty')
            ->get();

            $schools = DB::table('school')
            ->select('schname', 'schcounty')
            ->orderBy('schcounty')
            ->get()
            ->toArray();
        
        echo "<h3 style='text-align: center;'>Partner Schools List</h3>";
        
        // Split the schools into three groups
        $schoolsChunks = array_chunk($schools, ceil(count($schools) / 4));

        echo "<div style='text-align: center;'>";
        
        for ($tableIndex = 0; $tableIndex < 4; $tableIndex++) {
            // echo "<div style='float: left; margin-right: 20px;'>";
            echo "<div style='display: inline-block; margin-right: 20px; vertical-align: top;'>";
            echo "<table>";
            echo "<tr><th>County</th><th>Name</th></tr>";
        
            foreach ($schoolsChunks[$tableIndex] as $school) {
                echo "<tr><td>{$school->schcounty}</td><td>{$school->schname}</td></tr>";
            }
        
            echo "</table>";
            echo "</div>";
        }

        echo "</div>";
        
        echo "<div style='clear: both;'></div>";
        
        // echo "<h3 style='text-align: center;'>Partner Schools List</h3>";
        // echo "<table>";
        // echo "<tr><th>County</th><th>Name</th></tr>";
        // foreach ($schools as $school) {
        //     echo "<tr><td>{$school->schcounty}</td><td>{$school->schname}</td></tr>";
        // }
        // echo "</table>";
    ?>
    <!-- 5. search a school -->
    <h3 style='text-align: center;'>Search a School</h3>
    <form action="publicStatistics.php" method="post">
    <div style='display: flex; flex-direction: row; gap: 10px; justify-content: center; align-items: baseline; margin-top: 20px;'>
        <label for="schcounty">County:</label>
        <select name="schcounty" id="schcounty">
            <?php
                $schCounties = DB::table('school')->distinct()->orderBy('schcounty')->get(['schcounty']);
                foreach ($schCounties as $schcounty) {
                    $selected = ($_POST['schcounty'] ?? '') == $schcounty->schcounty ? 'selected' : '';
                    echo "<option value='{$schcounty->schcounty}' {$selected}>{$schcounty->schcounty}</option>";
                }
            ?>
        </select>

        <label for="schname">School:</label>
        <select name="schname" id="schname">
            <?php
                $selectedCounty = $_POST['schcounty'] ?? '南投縣';
                $schoolsForCounty = DB::table('school')
                    ->where('schcounty', $selectedCounty)
                    ->get(['school_id', 'schname']);

                foreach ($schoolsForCounty as $school) {
                    $selected = ($_POST['schname'] ?? '') == $school->school_id ? 'selected' : '';
                    echo "<option value='{$school->school_id}' {$selected}>{$school->schname}</option>";
                }
            ?>
        </select>

        <input type="submit" value="Search">
    </div>
    </form>

    <?php
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $selectedSchID = $_POST['schname'];

            $results = DB::table('school as s')
                ->select('s.*', 'g.group_id', 'g.semester', 'g.dayofweek', 'g.starttime', 'g.endtime')
                ->join('group_ as g', function($join) {
                    $join->on('s.school_id', '=', 'g.school_id');
                })
                ->where('s.school_id', $selectedSchID)
                ->orderBy('g.semester', 'desc')
                ->orderBy('g.starttime', 'asc')
                ->get();

            if ($results->isNotEmpty()) {
                $groupedResults = $results->groupBy('school_id');
                foreach ($groupedResults as $schoolId => $schoolGroups) {
                    echo "<div style='text-align: center;'>";

                    echo "<h4>School Info</h4>";
                    echo "{$schoolGroups->first()->schcounty} {$schoolGroups->first()->schname}<br>";
                    echo "{$schoolGroups->first()->schaddress}<br>";
                    
                    echo "<h4>Participating Semesters and Session Times</h4>";
                    // foreach ($schoolGroups as $row) {
                    //     $dayOfWeekString = date('l', strtotime("Sunday +{$row->dayofweek} days"));
                    //     echo "{$row->semester} {$dayOfWeekString} {$row->starttime}-{$row->endtime}<br>";
                    // }
                    $uniqueDayOfWeeks = [];
                    foreach ($schoolGroups as $row) {
                        $dayOfWeekString = date('l', strtotime("Sunday +{$row->dayofweek} days"));
                        if (!in_array($dayOfWeekString, $uniqueDayOfWeeks)) {
                            $uniqueDayOfWeeks[] = $dayOfWeekString;
                            echo "{$row->semester} {$dayOfWeekString} {$row->starttime}-{$row->endtime}<br>";
                        }
                    }

                    echo "</div>";
                }
            } else {
                echo "<div style='text-align: center;'>";
                echo "No schools found.";
                echo "</div>";
            }
        }
    ?>
</div>
</body>
</html>
