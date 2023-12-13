<?php
require 'eloquent.php';

use Illuminate\Database\Capsule\Manager as DB;
?>

<?php
session_start();

// check if the user is authenticated
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// user is authenticated
$userID = $_SESSION['user_id'];
$password = $_SESSION['password'];
$identity = $_SESSION['identity'];

// user is not admin, redirect to index.php
if ($identity !== 'admin') {
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
<title>ICL Admin Statistics</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <h1>ICL Admin Statistics</h1>
    <div class="link-container">
        <a href="index.php" class="search-link">Home</a>
    </div>
    
    <form action="adminStatistics.php" method="post">
    <div style='display: flex; flex-direction: row; gap: 10px; justify-content: center; align-items: baseline; margin-top: 20px;'>
        <label for="semester">Semester:</label>
        <select name="semester" id="semester">
            <?php
                $semesters = DB::table('student_course')->distinct()->orderBy('semester', 'desc')->get(['semester']);
                foreach ($semesters as $semester) {
                    $selected = ($_POST['semester'] ?? '') == $semester->semester ? 'selected' : '';
                    echo "<option value='{$semester->semester}' {$selected}>{$semester->semester}</option>";
                }
            ?>
        </select>

        <input type="submit" value="Search">
    </div>
    </form>

    <?php
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $selectedSemester = $_POST['semester'];

            // 1.1 overall attendance rate
            $attendanceRate = DB::table('session_attendance as sa')
                ->join('session as s', 'sa.session_id', '=', 's.session_id')
                ->join('group_ as g', 's.group_id', '=', 'g.group_id')
                ->where('g.semester', $selectedSemester)
                ->selectRaw('
                    COUNT(CASE WHEN sa.attend_no = \'A\' THEN 1 ELSE NULL END) * 100.0 / 
                    NULLIF(COUNT(CASE WHEN sa.attend_no != \'J\' THEN 1 ELSE NULL END), 0) 
                    AS attendance_rate
                ')
                ->first();
            
            echo "<h3 style='text-align: center;'>Overall Attendance Rate</h3>";
            echo "<div style='text-align: center;'>";
            echo "{$attendanceRate->attendance_rate}%";
            echo "</div>";

            // 1.2 10 students with the lowest attendance rate
            $studentsAttendance = DB::table('student as st')
                ->join('session_attendance as sa', 'st.icl_id', '=', 'sa.icl_id')
                ->join('session as s', 'sa.session_id', '=', 's.session_id')
                ->join('group_ as g', 's.group_id', '=', 'g.group_id')
                ->where('g.semester', $selectedSemester)
                ->groupBy('st.icl_id', 'st.stuname')
                ->selectRaw('
                    st.icl_id AS icl_id,
                    st.stuname AS stuname,
                    ROUND(
                        (COUNT(CASE WHEN sa.attend_no = \'A\' THEN 1 ELSE NULL END) * 100.0) /
                        NULLIF(COUNT(CASE WHEN sa.attend_no != \'J\' THEN 1 ELSE NULL END), 0),
                        2
                    ) AS attendance_rate
                ')
                ->orderBy('attendance_rate', 'ASC')
                ->limit(10)
                ->get();

            $index = 1;
            echo "<h3 style='text-align: center;'>Top 10 Students with the Lowest Attendance Rate</h3>";
            echo "<table>";
            echo "<tr><th>Rank</th><th>ICL ID</th><th>Name</th><th>Attendance Rate</th></tr>";
            foreach ($studentsAttendance as $row) {
                echo "<tr><td>{$index}</td><td>{$row->icl_id}</td><td>{$row->stuname}</td><td>{$row->attendance_rate}</td></tr>";
                $index++;
            }
            echo "</table>";
            
            // 1.3 10 trips with the highest no-show rate
            $tripStatistics = DB::table('trip as t')
                ->join('trip_student as ts', function ($join) {
                    $join->on('t.semester', '=', 'ts.semester')
                         ->on('t.trip_no', '=', 'ts.trip_no');
                })
                ->where('t.semester', $selectedSemester)
                ->groupBy('t.trip_no')
                ->selectRaw('
                    t.trip_no AS trip_no,
                    COUNT(*) AS total_students,
                    SUM(CASE WHEN ts.showup = \'F\' THEN 1 ELSE 0 END) AS no_show_count,
                    (SUM(CASE WHEN ts.showup = \'F\' THEN 1 ELSE 0 END) * 100.0 / COUNT(*)) AS no_show_rate
                ')
                ->orderBy('no_show_rate', 'DESC')
                ->limit(10)
                ->get();

            $index = 1;
            echo "<h3 style='text-align: center;'>Top 10 Trips with the Highest No-show Rate</h3>";
            echo "<table>";
            echo "<tr><th>Rank</th><th>Trip No</th><th>Total Students</th><th>No-show Count</th><th>No-show Rate</th></tr>";
            foreach ($tripStatistics as $row) {
                echo "<tr><td>{$index}</td><td>{$row->trip_no}</td><td>{$row->total_students}</td><td>{$row->no_show_count}</td><td>{$row->no_show_rate}</td></tr>";
                $index++;
            }
            echo "</table>";

            // 1.4 course enrollment info
            $enrolledCourses = DB::table('student_course as sc')
                ->join('course as c', 'sc.course_id', '=', 'c.course_id')
                ->where('sc.semester', $selectedSemester)
                ->groupBy('c.course_id', 'c.coursename', 'c.coursesch', 'c.credits')
                ->selectRaw('
                    c.course_id AS course_id,
                    c.coursename AS coursename,
                    c.coursesch AS coursesch,
                    c.credits AS credits,
                    COUNT(*) AS enrolled_students
                ')
                ->orderBy('course_id', 'asc')
                ->get();

            echo "<h3 style='text-align: center;'>Course Enrollment Info</h3>";
            echo "<table>";
            echo "<tr><th>Course ID</th><th>Course Name</th><th>Course School</th><th>Credits</th><th>Enrolled Students Count</th></tr>";
            foreach ($enrolledCourses as $row) {
                echo "<tr><td>{$row->course_id}</td><td>{$row->coursename}</td><td>{$row->coursesch}</td><td>{$row->credits}</td><td>{$row->enrolled_students}</td></tr>";
                $index++;
            }
            echo "</table>";
        }
    ?>
</div>
</body>
</html>
