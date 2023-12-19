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

// user is not school, redirect to index.php
if ($identity !== 'school') {
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
<title>My Page</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <h1>My Page</h1>
    <div class="link-container">
        <a href="index.php" class="search-link">Home</a>
    </div>
    <form action="school.php" method="post">
        <?php
            $selectedSchoolID=$password;
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
        ?>
        <form action="school.php" method="post">
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
            echo "<input type=\"hidden\" name=\"school_id\" value=\"$row->school_id\">";
        ?>  
        </select>
        <input type="submit" value="Search">
        </div>
        <?php
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
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
        </form>
        <form action="school.php" method="post">
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
                    $semester = ($_POST['groupSemester']??'')==$row->semester?'semester':'';
                    echo "<option value='{$row->semester}'{$semester}>{$row->semester}</option>";
                }
                echo "<input type=\"hidden\" name=\"school_id\" value=\"$row->school_id\">";
            ?>
        </select>
        <label for="selectGroup">Group ID:</label>
        <select name="selectGroup" id="selectGroup">
            <?php
                foreach($group_semesters as $row){
                    $group_id = ($_POST['selectGroup']??'')==$row->group_id?'group_id':'';
                    echo "<option value='{$row->group_id}'{$group_id}>{$row->group_id}</option>";
                }
                echo "<input type=\"hidden\" name=\"school_id\" value=\"$row->school_id\">";
            ?>  
        </select>
        <input type="submit" value="Search">
        </div> 
        <?php
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $selectedGroupSemester = $_POST['groupSemester']??'';
            $selectedGroupID = $_POST['selectGroup']??'';
            // if($selectedGroupSemester === ""){
            //     echo"<div style='text-align: center;'>No semester selected.</div>";
            //     exit();
            // }
            // if($selectedGroupID === ""){
            //     echo"<div style='text-align: center;'>No group selected.</div>";
            //     exit();
            // }
            $group_local = DB::table('group_')
            ->join('student',function($join){
                $join->on('group_.locicl_id','=','student.icl_id');
            })
            ->where('group_.group_id', $group_id)
            ->where('group_.semester', $semester)
            ->select('student.*','group_.*')
            ->get();

            if($group_local->isNotEmpty()){
                echo "<h3 style='text-align: left;'>Partner's Information </h3>";
                echo "<table>";
                echo "<tr><th>Name</th><th>ICL ID</th><th>Nationality</th><th>University</th><th>Phone</th><th>Email</th></tr>";
                foreach ($group_local as $row) {
                    echo "<tr><td>{$row->stuname}</td><td>{$row->group_id}</td><td>{$row->icl_id}</td><td>{$row->stunationality}</td><td>{$row->stuuniversity}</td><td>{$row->stuphone}</td><td>{$row->stuemail}</td></tr>";
                }
                echo "</table>";
            }else{

            }
        }
    
    ?>
        </form>
</div>
</body>
</html>
