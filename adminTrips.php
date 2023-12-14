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
<title>Admin Trips</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <h1>Admin Trips</h1>
    <div class="link-container">
        <a href="index.php" class="search-link">Home</a>
    </div>
    <!-- 1. search a trip -->
    <h3 style='text-align: center;'>Search a Trip</h3>
    <form action="adminTrips.php" method="post">
    <div style='display: flex; flex-direction: row; gap: 10px; justify-content: center; align-items: baseline; margin-top: 20px;'>
        <label for="tripSemester">Semester:</label>
        <select name="tripSemester" id="tripSemester">
            <?php
                $tripSemesters = DB::table('trip')->distinct()->orderBy('semester', 'desc')->get(['semester']);
                foreach ($tripSemesters as $semester) {
                    $selected = ($_POST['tripSemester'] ?? '') == $semester->semester ? 'selected' : '';
                    echo "<option value='{$semester->semester}' {$selected}>{$semester->semester}</option>";
                }
            ?>
        </select>

        <label for="tripNo">Trip No:</label>
        <input type="text" name="tripNo" value="<?php echo isset($_POST['tripNo']) ? htmlspecialchars($_POST['tripNo']) : ''; ?>">

        <input type="submit" value="Search">
    </div>
    </form>

    <?php
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $selectedSemester = $_POST['tripSemester'];
            $selectedTripNo = $_POST['tripNo'];
            if ($selectedTripNo === "") {
                echo "<div style='text-align: center;'>";
                echo "No trips found.";
                echo "</div>";
                exit();
            }

            // 1.1 trip basic info
            $trip_basic_info = DB::table('trip')
                ->where('semester', $selectedSemester)
                ->where('trip_no', $selectedTripNo)
                ->select(
                    'trip.trip_no',
                    'trip.startdate',
                    'trip.enddate'
                )
                ->get();

            if ($trip_basic_info->isNotEmpty()) {
                echo "<h3 style='text-align: center;'>Trip Basic Info</h3>";
                echo "<table>";
                echo "<tr><th>Semester</th><th>Trip No</th><th>Start Date</th><th>End Date</th></tr>";
                foreach ($trip_basic_info as $row) {
                    echo "<tr><td>{$selectedSemester}</td><td>{$row->trip_no}</td><td>{$row->startdate}</td><td>{$row->enddate}</td></tr>";
                }
                echo "</table>";
            } else {
                echo "<div style='text-align: center;'>";
                echo "No trips found.";
                echo "</div>";
                exit();
            }

            // 1.2 trip schools info
            $trip_schools_info = DB::table('trip')
                ->join('trip_school', function ($join) {
                    $join->on('trip.semester', '=', 'trip_school.semester')
                        ->on('trip.trip_no', '=', 'trip_school.trip_no');
                })
                ->join('school', 'trip_school.school_id', '=', 'school.school_id')
                ->join('semester_contact', function ($join) {
                    $join->on('school.school_id', '=', 'semester_contact.school_id')
                        ->on('trip.semester', '=', 'semester_contact.semester');
                })
                ->join('contact_person', function ($join) {
                    $join->on('semester_contact.conname', '=', 'contact_person.conname')
                        ->on('semester_contact.school_id', '=', 'contact_person.school_id');
                })
                ->where('trip.semester', $selectedSemester)
                ->where('trip.trip_no', $selectedTripNo)
                ->select(
                    'school.schcounty AS schoolcounty',
                    'school.schname AS schoolname',
                    'school.schaddress AS schooladdress',
                    'contact_person.conname AS contactname',
                    'contact_person.conemail AS contactemail',
                    'contact_person.conphone AS contactphone'
                )
                ->get();
            
            if ($trip_schools_info->isNotEmpty()) {
                echo "<h3 style='text-align: center;'>Trip Schools Info</h3>";
                echo "<table>";
                echo "<tr><th>School County</th><th>School Name</th><th>Contact Name</th><th>Contact Email</th><th>Contact Phone</th></tr>";
                foreach ($trip_schools_info as $row) {
                    echo "<tr><td>{$row->schoolcounty}</td><td>{$row->schoolname}</td><td>{$row->contactname}</td><td>{$row->contactemail}</td><td>{$row->contactphone}</td></tr>";
                }
                echo "</table>";
            } else {
                echo "<div style='text-align: center;'>";
                echo "No schools found.";
                echo "</div>";
                exit();
            }

            // 1.3 trip participants list
            $trip_participants_info = DB::table('trip_student')
                ->join('student', function($join) {
                    $join->on('trip_student.icl_id', '=', 'student.icl_id');
                })
                ->where('trip_student.semester', $selectedSemester)
                ->where('trip_student.trip_no', $selectedTripNo)
                ->select(
                    'student.icl_id AS icl_id',
                    'student.stuname AS stu_name',
                    'student.stunationality AS stu_nationality',
                    'student.stusex AS stu_sex',
                    'student.stuphone AS stu_phone',
                    'student.stuemail AS stu_email',
                    'trip_student.showup AS showup'
                )
                ->get();
            
            if ($trip_participants_info->isNotEmpty()) {
                echo "<h3 style='text-align: center;'>Trip Participants Info</h3>";
                echo "<table>";
                echo "<tr><th>ICL ID</th><th>Name</th><th>Nationality</th><th>Sex</th><th>Phone</th><th>Email</th><th>Show Up</th></tr>";
                foreach ($trip_participants_info as $row) {
                    echo "<tr><td>{$row->icl_id}</td><td>{$row->stu_name}</td><td>{$row->stu_nationality}</td><td>{$row->stu_sex}</td><td>{$row->stu_phone}</td><td>{$row->stu_email}</td><td>{$row->showup}</td></tr>";
                }
                echo "</table>";
            } else {
                echo "<div style='text-align: center;'>";
                echo "No participants found.";
                echo "</div>";
                exit();
            }
        }
    ?>
</div>
</body>
</html>
