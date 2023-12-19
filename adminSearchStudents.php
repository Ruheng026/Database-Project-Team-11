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
<title>Admin Search Students</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <?php
    echo "<h1>Students Information</h1>";
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $selectedIclID = $_POST['icl_id'];
        if ($selectedIclID === "") {
            echo "<div style='text-align: center;'>";
            echo "No student name found.";
            echo "</div>";
            exit();
        }
    ?>
    <div class="link-container">
        <a href="index.php" class="search-link">Home</a>
        <a href="adminStudents.php" class="search-link">Back to Search</a>
    </div>
    <script>
    function toggleEditArea() {
        var editArea = document.getElementById('edit_area');
        if (editArea.style.display === 'none' || editArea.style.display === '') {
            editArea.style.display = 'block';
        } else {
            editArea.style.display = 'none';
        }
    }
    </script>
    <form action="adminSearchStudents.php" method="post">
    <?php
        // 1.1 student basic info
        $student_basic_info = DB::table('student')
            ->where('icl_id', $selectedIclID)
            ->select(
                'student.icl_id' ,
                'student.stunationality',
                'student.stutype',
                'student.studegree',
                'student.stuname',
                'student.stusex',
                'student.stuphone',
                'student.stuuniversity',
                'student.stuemail'
            )
            ->get();

        if ($student_basic_info->isNotEmpty()) {
            
            echo "<div style='text-align: right;'>
                <h3 style='text-align: center;'>Student Basic Information</h3>
                </div>";
            
            echo "<table>";
            
            echo "<tr><th>Name</th><th>ICL ID</th><th>Nationality</th><th>University</th><th>Phone</th><th>Email</th></tr>";
            foreach ($student_basic_info as $row) {
                $selectedName = $row->stuname;

                echo "<tr>
                    <td>{$row->stuname}</td>
                    <td>{$row->icl_id}</td>
                    <td>{$row->stunationality}</td>
                    <td>{$row->stuuniversity}</td>
                    <td>{$row->stuphone}</td>
                    <td>{$row->stuemail}</td>
                
                        <form action=\"editStudent.php\" method=\"post\">
                            <input type=\"hidden\" name=\"icl_id\" value=\"$selectedIclID\">
                        </form>
         
        </tr>";
}
            echo "<input type=\"hidden\" name=\"icl_id\" value= \"$row->icl_id\">";
            echo "<input type=\"hidden\" name=\"display\" value= \"editArea.style.display\">";
            echo "<form action=\"editStudent.php\" method=\"post\">";
            echo "<input type=\"hidden\" name=\"icl_id\" value=\"$selectedIclID\">";
            echo "<div style='display: flex; flex-direction: row; gap: 10px; justify-content: center; align-items: baseline; margin-top: 20px;'>";
            echo "<input type=\"submit\" value=\"Edit\">";
            echo "</div>";
            echo "</form>";
            echo "</table>";
            
            
        } else {
            echo "<div style='text-align: center;'>";
            echo "No student found.";
            echo "</div>";
            exit();
        }    
        
    }
    ?>
    
    <!-- 2. search course and trip -->
    <form action="adminSearchStudents.php" method="post">
    <div style='display: flex; flex-direction: row; gap: 10px; justify-content: center; align-items: baseline; margin-top: 20px;'>
        <?php
        $semesters = DB::table('student_course')
            ->where('icl_id', $selectedIclID)
            ->distinct()
            ->select(
                'student_course.icl_id' ,
                'student_course.semester' ,
                'student_course.course_id'
            )
            ->get();
        ?>
        <label for="studentSemester">Semester:</label>
        <select name="studentSemester" id="studentSemester">
        <?php
            foreach ($semesters as $row) {
                $selected = (($_POST['studentSemester'] ?? "") == $row->semester) ? 'selected' : '';
                echo "<option value='{$row->semester}' {$selected}>{$row->semester}</option>";
            }
            echo "<input type=\"hidden\" name=\"icl_id\" value= \"$row->icl_id\">";
        ?>
        </select>
        <input type="submit" value="Search">
    </div>
    </form>
    <?php
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $selectedSemester = $_POST['studentSemester']??"";
            if ($selectedSemester === "") {
                echo "<div style='text-align: center;'>";
                echo "No semester selected.";
                echo "</div>";
                exit();
            }
            //2.1 course
            $semester_course = DB::table('student_course')
            ->where('icl_id', $selectedIclID)
            ->where('semester', $selectedSemester)
            ->distinct()
            ->select(
                'student_course.icl_id' ,
                'student_course.semester' ,
                'student_course.course_id'
            )
            ->first();
            
            if ($semester_course) {
                $semesterCourseID = $semester_course->course_id;
            } else {
                $semesterCourseID = "null"; 
            }

            $semesterCourse= DB::table('course')
            ->where('course_id', $semesterCourseID)
            ->distinct()
            ->select(
                'course.coursename' ,
                'course.coursesch' ,
                'course.credits' ,
                'course.course_id',
            )
            ->first();

            
            if ($semesterCourse) {
                $semesterCourseName = $semesterCourse->coursename;
                echo "<h3 style='text-align: left;'>Your Course Information</h3>";
                echo "<h3 style='text-align: center;'> $semesterCourseName</h3>";
            } else {
                echo "<div style='text-align: center;'>";
                echo "No semester selected.";
                echo "</div>";
                exit();
            }
            //2.2 search trip
            $semester_trip_info = DB::table('trip')
            ->join('trip_student', function ($join) use ($selectedSemester) {
                $join->on('trip.semester', '=', 'trip_student.semester')
                    ->on('trip.trip_no', '=', 'trip_student.trip_no');
            })
            ->join('trip_school', function ($join) use ($selectedSemester) {
                $join->on('trip.semester', '=', 'trip_school.semester')
                    ->on('trip.trip_no', '=', 'trip_school.trip_no');
            })
            ->join('school', 'trip_school.school_id', '=', 'school.school_id')
            ->where('trip_student.icl_id', $selectedIclID)
            ->where('trip.semester', $selectedSemester)
            ->select('trip.trip_no as trip_no', 'trip.startdate', 'trip.enddate', 'school.schcounty as schoolcounty', 'school.schname as schoolname', 'trip_student.showup')
            ->get();
            echo "<h3 style='text-align: left;'>Your Trip Information </h3>";

            if ($semester_trip_info->isNotEmpty()) {
                echo "<table>";
                echo "<tr><th>Trip No</th><th>Date</th><th>County</th><th>Schools to be visited</th><th>Show Up</th></tr>";
                foreach ($semester_trip_info as $row) {
                    $date = ($row->startdate == $row->enddate) ? $row->startdate : "{$row->startdate} ~ {$row->enddate}";
                    echo "<tr><td>{$row->trip_no}</td><td>{$date}</td><td>{$row->schoolcounty}</td><td>{$row->schoolname}</td><td>{$row->showup}</td></tr>";
                }
                echo "</table>";
            } else {
                echo "<div style='text-align: center;'>";
                echo "No trip found.";
                echo "</div>";
            }
        }
        else{
            echo "<h4 style='text-align: center;'> select a semester</h4>";
        }
    ?>
    <!-- 3. semester, school -->
    <form action="adminSearchStudents.php" method="post">
    <div style='display: flex; flex-direction: row; gap: 10px; justify-content: center; align-items: baseline; margin-top: 20px;'>
        <label for="studentSemester">Semester:</label>
        <select name="studentSemester" id="studentSemester">
        <?php
            $semesters = DB::table('group_')
            ->where(function ($query) use ($selectedIclID) {
                $query->where('group_.locicl_id', $selectedIclID)
                    ->orWhere('group_.intlicl_id', $selectedIclID);
            })
            ->distinct()
            ->select(
                'group_.semester'
            )
            ->get();
            foreach ($semesters as $row) {
                $selected = (($_POST['studentSemester'] ?? '') == $row->semester) ? 'selected' : '';
                echo "<option value='{$row->semester}' {$selected}>{$row->semester}</option>";
            }
            
            $selectedGroupSemester = $_POST['studentSemester'] ?? '1111';
            $groups = DB::table('group_')
                ->join('school', 'group_.school_id', '=', 'school.school_id')
                ->where(function ($query) use ($selectedIclID) {
                    $query->where('group_.locicl_id', $selectedIclID)
                        ->orWhere('group_.intlicl_id', $selectedIclID);
                })
                ->where('group_.semester', $selectedGroupSemester)
                ->select('group_.group_id', 'school.school_id', 'school.schname')
                ->get();
            echo "<input type=\"hidden\" name=\"icl_id\" value= \"$selectedIclID\">";
        ?>
        </select>

        <label for="studentGroupSchool">School:</label>
        <select name="studentGroupSchool" id="studentGroupSchool">
        <?php
            foreach ($groups as $row) {
                $selected = (($_POST['studentGroupSchool'] ?? '') == $row->schname) ? 'selected' : '';
                $selectedGroupId = $row->group_id;
                echo "<option value='{$row->schname}' {$selected}>{$row->schname}</option>";
                echo "<input type=\"hidden\" name=\"group_id\" value= \"$selectedGroupId\">";
            }
        ?>
        </select>
        <input type="submit" value="Search">
        </div>
    </form>
    <?php
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $selectedGroupSemester = $_POST['studentSemester'] ?? "1111";
            $selectedGroupSchool = $_POST['studentGroupSchool'] ?? " ";
            $selectedGroupId = $_POST['group_id'] ?? " ";
            if ($selectedGroupSemester === "" || $selectedGroupSchool === "" ) {
                echo "<div style='text-align: center;'>";
                echo "No info found.";
                echo "</div>";
                exit();
            }
            //3.1 partner
            
            $partner_basic_info = DB::table('group_')
            ->join('student', function ($join) {
                $join->on('group_.locicl_id', '=', 'student.icl_id')
                    ->orOn('group_.intlicl_id', '=', 'student.icl_id');
            })
            ->leftJoin('student as local', 'group_.locicl_id', '=', 'local.icl_id')
            ->leftJoin('student as intl', 'group_.intlicl_id', '=', 'intl.icl_id')
            ->where('group_.group_id', $selectedGroupId)
            ->where('group_.semester', $selectedGroupSemester)
            ->where('student.icl_id', $selectedIclID)
            ->selectRaw("
                CASE WHEN student.stutype = 'Local' THEN intl.stuname ELSE local.stuname END AS partnername,
                CASE WHEN student.stutype = 'Local' THEN intl.stunationality ELSE local.stunationality END AS partnernationality,
                CASE WHEN student.stutype = 'Local' THEN intl.stuemail ELSE local.stuemail END AS partneremail,
                CASE WHEN student.stutype = 'Local' THEN intl.stuphone ELSE local.stuphone END AS partnerphone,
                CASE WHEN student.stutype = 'Local' THEN intl.stuuniversity ELSE local.stuuniversity END AS partneruniversity,
                CASE WHEN student.stutype = 'Local' THEN intl.icl_id ELSE local.icl_id END AS partnerid
            ")
            ->get();
        
            if ($partner_basic_info->isNotEmpty()) {
                echo "<h3 style='text-align: left;'>Partner's Information </h3>";
                echo "<table>";
                echo "<tr><th>Name</th><th>ICL ID</th><th>Nationality</th><th>University</th><th>Phone</th><th>Email</th></tr>";
                foreach ($partner_basic_info as $row) {
                    echo "<tr><td>{$row->partnername}</td><td>{$row->partnerid}</td><td>{$row->partnernationality}</td><td>{$row->partneruniversity}</td><td>{$row->partnerphone}</td><td>{$row->partneremail}</td></tr>";
                }
                echo "</table>";
            } else {
                echo "<div style='text-align: center;'>";
                echo "</div>";
                exit();
            }
            //3.2 school
            $school_basic_info = DB::table('group_')
            ->where('group_id', $selectedGroupId)
            ->join('school', 'group_.school_id', '=', 'school.school_id')
            ->join('semester_contact', function ($join) {
                $join->on('school.school_id', '=', 'semester_contact.school_id')
                    ->whereColumn('group_.semester', '=', 'semester_contact.semester');
            })
            ->join('contact_person as contact', function ($join) {
                $join->on('semester_contact.conname', '=', 'contact.conname')
                    ->whereColumn('semester_contact.school_id', '=', 'contact.school_id');
            })
            ->select(
                'school.schname as schoolname',
                'school.schaddress as schooladdress',
                'school.schcounty as schoolcounty',
                'contact.conname as contactname',
                'contact.conemail as contactemail',
                'contact.conphone as contactphone'
            )
            ->first();

        if ($school_basic_info) {
            $semesterSchoolCounty = $school_basic_info->schoolcounty;
            $semesterSchoolName = $school_basic_info->schoolname;
            $semesterSchoolAddress = $school_basic_info->schooladdress;
            $semesterSchoolContactName = $school_basic_info->contactname;
            $semesterSchoolContactEmail = $school_basic_info->contactemail;
            $semesterSchoolContactPhone = $school_basic_info->contactphone;
            echo "<h3 style='text-align: left;'>School's Information </h3>";
            echo "<h3 style='text-align: center;'>$semesterSchoolCounty $semesterSchoolName</h3>";

            echo "<h4 style='text-align: center;'>   $semesterSchoolAddress</h4>";
            echo "<h4 style='text-align: center;'>   $semesterSchoolContactName $semesterSchoolContactPhone $semesterSchoolContactEmail</h4>";

        } else {
            echo "<div style='text-align: center;'>";
            echo "No school found.";
            echo "</div>";
            exit();
        }
        //3.3 session
        $session_info = DB::table('group_')
            ->where('group_.group_id', $selectedGroupId)
            ->join('session', 'group_.group_id', '=', 'session.group_id')
            ->join('session_attendance', 'session.session_id', '=', 'session_attendance.session_id')
            ->join('attend_status', 'session_attendance.attend_no', '=', 'attend_status.attend_no')
            ->where('session_attendance.icl_id', $selectedIclID)
            ->select(
                'session.date as sessiondate',
                'group_.starttime as starttime',
                'group_.endtime as endtime',
                'attend_status.attendtype as attendancetype',
                'attend_status.deduction as deductionpoints'
            )
            ->get();

        if ($session_info) {
            echo "<h3 style='text-align: left;'>Sessoion's Information </h3>";
            echo "<table>";
            echo "<tr><th>Date</th><th>Time</th><th>Attendance</th><th>Deduction</th></tr>";
            foreach ($session_info as $row) {
                $time = ($row->starttime == $row->endtime) ? $row->starttime : "{$row->starttime} ~ {$row->endtime}";
                echo "<tr><td>{$row->sessiondate}</td><td>{$time}</td><td>{$row->attendancetype}</td><td>{$row->deductionpoints}</td></tr>";
            }
            echo "</table>";
        } else {
            echo "<div style='text-align: center;'>";
            echo "No session found.";
            echo "</div>";
            exit();
        }
        //3.4 total deduction
        $deduction = DB::table('session')
        ->where('group_.group_id', $selectedGroupId)
        ->join('group_', 'session.group_id', '=', 'group_.group_id')
        ->join('session_attendance', 'session.session_id', '=', 'session_attendance.session_id')
        ->join('attend_status', 'session_attendance.attend_no', '=', 'attend_status.attend_no')
        ->where('session_attendance.icl_id', $selectedIclID)
        ->sum('attend_status.deduction');
        echo "<h3 style='text-align: left;'>Total Deduction Point:</h3>";
        echo "<h3 style='text-align: center;'>$deduction </h3>";

        //3.5 attendance
        $attendanceRate = DB::table('session')
        ->where('session.group_id', $selectedGroupId)
        ->join('session_attendance', 'session.session_id', '=', 'session_attendance.session_id')
        ->where('session_attendance.icl_id', $selectedIclID)
        ->selectRaw('
            ROUND(
                CASE
                    WHEN COUNT(CASE WHEN session_attendance.attend_no = ? THEN 1 ELSE 0 END) = 0 THEN 0
                    ELSE (COUNT(CASE WHEN session_attendance.attend_no = ? THEN 1 ELSE 0 END) * 100.0 / NULLIF(COUNT(CASE WHEN session_attendance.attend_no = ? THEN 0 ELSE 1 END), 0))
                END,
                2
            ) AS attendance_rate', ['A', 'A', 'J'])
        ->value('attendance_rate');



        echo "<h3 style='text-align: left;'>Session Attendance Rate:</h3>";
        echo "<h3 style='text-align: center;'>$attendanceRate %</h3>";
        }
        else{
            echo "<h4 style='text-align: center;'>Select a semester</h4>";
        }
        
    ?>  

</div>
</body>
</html>
