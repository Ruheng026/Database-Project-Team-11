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
<title>Admin Search Schools</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <?php
    echo "<h1>Schools Information</h1>";
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $selectedSchoolID = $_POST['school_id'];
        if ($selectedSchoolID === "") {
            echo "<div style='text-align: center;'>";
            echo "No school found.";
            echo "</div>";
            exit();
        }
    
    ?>
    <div class="link-container">
        <a href="index.php" class="search-link">Home</a>
        <a href="adminSchools.php" class="search-link">Back to Search</a>
    </div>
    <form action="adminSearchSchools.php" method="post">
    <?php
        //school basic info
        $school_basic_info = DB::table('school')
            ->where('school_id',$selectedSchoolID)
            ->select(
                'school.school_id',
                'school.schname',
                'school.schcounty',
                'school.schaddress'
            )
            ->get();
        if($school_basic_info->isNotEmpty()){
            echo "<div style='text-align: right;'>
            <h3 style='text-align: center;'></h3>
            </div>";
            echo "<table>
            <tr><th>Name</th><th>Address</th></tr>";
            foreach ($school_basic_info as $row){
                $selectedSchoolName = $row->schname;
                echo "<tr><td>{$row->schname}</td><td>{$row->schaddress}</td></tr>";
            }
            echo "</table>";
        }else{
            echo"<div style='text-align: center;'>No school found.</div>";
            exit();
        }   
    }
    ?>
    
    <!-- search contact, trip, trip participant -->
    <h3 style='text-align: left;'>Search for Contact, Trips, Trip Participants Information</h3>
    <form action="adminSearchSchools.php" method="post">
    <div style='display: flex; flex-direction: row; gap: 10px; justify-content: center; align-items: baseline; margin-top: 20px;'>
    <?php
        $semesters=DB::table('semester_contact')
            ->where('school_id',$selectedSchoolID)
            ->distinct()
            ->select(
                'semester_contact.school_id',
                'semester_contact.semester',
                'semester_contact.conname'
            )
            ->get();
    ?>
    <label for="selectSemester">Semester:</label>
    <select name="selectSemester" id="selectSemester">
        <?php
            foreach($semesters as $row){
                $selected = (($_POST['selectSemester']??"")==$row->semester)?'selected':'';
                echo "<option value='{$row->semester}'{$selected}>{$row->semester}</option>";
            }
            echo "<input type=\"hidden\" name=\"school_id\" value=\"$selectedSchoolID\">";
        ?>  
    </select>
    <input type="submit" name="firstForm" value="Search">
    </div>
    </form>
    <?php
        if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['firstForm'])) {
            $selectedSemester = $_POST['selectSemester']??"";
            if($selectedSemester === ""){
                echo"<div style='text-align: center;'>No semester selected.</div>";
                exit();
            }
            //semester contact
            $semester_contact = DB::table('semester_contact')
            ->join('contact_person', function($join) use($selectedSemester){
                $join->on('semester_contact.conname','=','contact_person.conname');
            })
            ->where('semester_contact.semester', $selectedSemester)
            ->where('semester_contact.school_id', $selectedSchoolID)
            ->select(
                'contact_person.conname',
                'contact_person.conphone',
                'contact_person.conemail'
            )
            ->get();

            if ($semester_contact->isNotEmpty()) {
                echo "<table>";
                echo "<tr><th>Name</th><th>Phone</th><th>Email</th></tr>";
                foreach ($semester_contact as $row) {
                    echo "<tr><td>{$row->conname}</td><td>{$row->conphone}</td><td>{$row->conemail}</td></tr>";
                }
                echo "</table>";
            } else {
                echo "<div style='text-align: center;'>";
                echo "No contact person found.";
                echo "</div>";
            }

            $school_trip = DB::table('trip_school')
            ->join('trip', function ($join) use ($selectedSemester){
                $join->on('trip.semester','=','trip_school.semester')
                ->on('trip.trip_no','=','trip_school.trip_no');
            })
            ->where('trip.semester',$selectedSemester)
            ->where('trip_school.school_id',$selectedSchoolID)
            ->select('trip.trip_no', 'trip.startdate', 'trip.enddate')
            ->get();

            if ($school_trip->isNotEmpty()){
                echo"<table>
                <tr><th>Trip No</th><th>Start Date</th><th>End Date</th></tr>";
                foreach ($school_trip as $row) {
                    $selectedTripNo = $row->trip_no;
                    echo "<tr><td>{$row->trip_no}</td><td>{$row->startdate}</td><td>{$row->enddate}</td></tr>";
                }
                echo"</table>";
            } else {
                echo "<div style='text-align: center;'>";
                echo "No trip found.";
                echo "</div>";
            }
            
            $other_school_trip = DB::table('trip_school')
            ->join('school', function($join)use($selectedSemester){
                $join->on('trip_school.school_id','=','school.school_id');
            })
            ->where('trip_school.school_id','!=',$selectedSchoolID)
            ->where('trip_school.trip_no',$selectedTripNo)
            ->select('school.schname')
            ->get();
            
            if ($other_school_trip->isNotEmpty()){
                echo"<table>
                <tr><th>School</th></tr>";
                foreach ($other_school_trip as $row) {
                    echo "<tr><td>{$row->schname}</td></tr>";
                }
                echo"</table>";
            } else {
                echo "<div style='text-align: center;'>";
                echo "No other school found.";
                echo "</div>";
            }

            $trip_participant = DB::table('trip_student')
            ->join('student',function($join)use($selectedSemester){
                $join->on('student.icl_id','=','trip_student.icl_id');
            })
            ->where('trip_student.trip_no',$selectedTripNo)
            ->select('student.stuname','student.stunationality')
            ->get();

            if($trip_participant->isNotEmpty()){
                echo"<table>
                <tr><th>Name</th><th>Nationality</th></tr>";
                foreach ($trip_participant as $row) {
                    echo "<tr><td>{$row->stuname}</td><td>{$row->stunationality}</td></tr>";
                }
                echo"</table>";
            }else {
                echo "<div style='text-align: center;'>";
                echo "No student found in the trip.";
                echo "</div>";
            }
        }
    ?>

    <!-- second form: semester, group id -->
    <h3 style='text-align: left;'>Search for Group's Information </h3>
    <form action="adminSearchSchools.php" method="post">
    <div style='display: flex; flex-direction: row; gap: 10px; justify-content: center; align-items: baseline; margin-top: 20px;'>
        <label for="groupSemester">Semester:</label>
        <select name="groupSemester" id="groupSemester">
            <?php
                $group_semesters = DB::table('group_')
                ->where('group_.school_id', $selectedSchoolID)
                ->distinct()
                ->select(
                    'group_.semester',
                    'group_.group_id')
                ->get();
                foreach($group_semesters as $row){
                    $selected_semester = ($_POST['groupSemester']??'')==$row->semester?'semester':'';
                    echo "<option value='{$row->semester}'{$selected_semester}>{$row->semester}</option>";
                }
                echo "<input type=\"hidden\" name=\"school_id\" value=\"$selectedSchoolID\">";
            ?>
        </select>
        <label for="selectGroup">Group ID:</label>
        <select name="selectGroup" id="selectGroup">
            <?php
                foreach($group_semesters as $row){
                    $selected_group_id = ($_POST['selectGroup']??'')==$row->group_id?'group_id':'';
                    echo "<option value='{$row->group_id}'{$selected_group_id}>{$row->group_id}</option>";
                }
                echo "<input type=\"hidden\" name=\"school_id\" value=\"$selectedSchoolID\">";
            ?>  
        </select>
        <input type="submit" name="secondForm" value="Search">
    </div>
    </form>
    <?php
        if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['secondForm'])) {
            $selectedGroupSemester = ($_POST['groupSemester']) ?? $selectedSemester;
            $selectedGroupID = $_POST['selectGroup'] ?? '';
            if($selectedGroupSemester === ""){
                echo"<div style='text-align: center;'>No semester selected.</div>";
                exit();
            }
            if($selectedGroupID === ""){
                echo"<div style='text-align: center;'>No group selected.</div>";
                exit();
            }
            $group_local = DB::table('group_')
            ->join('student',function($join){
                $join->on('group_.locicl_id','=','student.icl_id');
            })
            ->where('group_.group_id', $selectedGroupID)
            ->where('group_.semester', $selectedGroupSemester)
            ->select('student.*','group_.*')
            ->get();

            if($group_local->isNotEmpty()){
                echo "<h3 style='text-align: left;'>Partner's Information </h3>";
                
                echo "<table>";
                echo "<tr><th>Name</th><th>Group ID</th><th>ICL ID</th><th>Nationality</th><th>University</th><th>Phone</th><th>Email</th></tr>";
                foreach ($group_local as $row) {
                    $selectedLoc = $row->icl_id;
                    $selectedLocName = $row->stuname;
                    echo "<tr><td>{$row->stuname}</td><td>{$row->group_id}</td><td>{$row->icl_id}</td><td>{$row->stunationality}</td><td>{$row->stuuniversity}</td><td>{$row->stuphone}</td><td>{$row->stuemail}</td></tr>";
                }
            }else{

            }

            $group_international = DB::table('group_')
            ->join('student',function($join){
                $join->on('group_.intlicl_id','=','student.icl_id');
            })
            ->where('group_.group_id', $selectedGroupID)
            ->where('group_.semester', $selectedGroupSemester)
            ->select('student.*','group_.*')
            ->get();

            if($group_international->isNotEmpty()){
                foreach ($group_international as $row) {
                    $selectedIntl = $row->icl_id;
                    $selectedIntlName = $row->stuname;
                    echo "<tr><td>{$row->stuname}</td><td>{$row->group_id}</td><td>{$row->icl_id}</td><td>{$row->stunationality}</td><td>{$row->stuuniversity}</td><td>{$row->stuphone}</td><td>{$row->stuemail}</td></tr>";
                }
                echo "</table>";
            }else{

            }
                
            $local_session = DB::table('session')
            ->join('group_',function($join){
                $join->on('session.group_id','=','group_.group_id');
            })
            ->join('session_attendance',function($join){
                $join->on('session.session_id','=','session_attendance.session_id');
            })
            ->join('attend_status',function($join){
                $join->on('session_attendance.attend_no','attend_status.attend_no');
            })
            ->where('group_.group_id',$selectedGroupID)
            ->where('group_.semester', $selectedGroupSemester)
            ->where('session_attendance.icl_id',$selectedLoc)
            ->select('session.date','group_.starttime','group_.endtime','attend_status.attendtype as local')
            ->get();

            $intl_session = DB::table('session')
            ->join('group_',function($join){
                $join->on('session.group_id','=','group_.group_id');
            })
            ->join('session_attendance',function($join){
                $join->on('session.session_id','=','session_attendance.session_id');
            })
            ->join('attend_status',function($join){
                $join->on('session_attendance.attend_no','attend_status.attend_no');
            })
            ->where('group_.group_id',$selectedGroupID)
            ->where('group_.semester', $selectedGroupSemester)
            ->where('session_attendance.icl_id',$selectedIntl)
            ->select('session.date','group_.starttime','group_.endtime','attend_status.attendtype as intl')
            ->get();
            
            
            if ($local_session->isNotEmpty() || $intl_session->isNotEmpty()) {
                echo "<h3 style='text-align: left;'>Session Info </h3>";
                echo"<form action=\"editSchool.php\" method=\"post\">
                <input type=\"hidden\" name=\"group_id\" value=\"$selectedGroupID\">
                <input type=\"hidden\" name=\"local_id\" value=\"$selectedLoc\">
                <input type=\"hidden\" name=\"intl_id\" value=\"$selectedIntl\">
                <input type=\"hidden\" name=\"group_semester\" value=\"$selectedGroupSemester\">
                <input type=\"submit\" value=\"Edit\">
                </form>
                <table>";
                
                echo "<tr><th>Date</th><th>Time</th><th>Attendance of {$selectedIntlName}(International Student)</th><th>Attendance of {$selectedLocName}(Local Student)</th></tr>";
                // Iterate over the international sessions
                foreach ($intl_session as $intl_row) {
                    $local_row = $local_session->firstWhere('date', $intl_row->date);
                    echo "<tr><th>{$intl_row->date}</th><th>{$intl_row->starttime}~{$intl_row->endtime}</th><th>{$intl_row->intl}</th>";
                    
                    // Check if corresponding international session exists
                    if ($intl_row) {
                        echo "<th>{$intl_row->intl}</th></tr>";
                    } else {
                        echo "<th>N/A</th></tr>";
                    }
                }
                // If there are local sessions without corresponding international sessions
                foreach ($local_session as $local_row) {
                    $intl_row = $intl_session->firstWhere('date', $local_row->date);
            
                    // Check if corresponding local session already printed
                    if (!$intl_row) {
                        echo "<tr><th>{$local_row->date}</th><th>N/A</th><th>N/A</th><th>{$local_row->local}</th></tr>";
                    }
                }
            
                echo "</table>";
            } else {
                // Handle the case where both $local_session and $intl_session are empty
                echo "No data available.";
            }
        }
    
    ?>
</div>
</body>
</html>

