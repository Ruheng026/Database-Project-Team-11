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
<meta charset="UTF-8">
<!DOCTYPE html>
<html>
<head>
<title>Edit School</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <?php
    echo "<h1>Edit Schools Information</h1>";
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $selectedGroupID = $_POST['group_id'];
        $selectedLoc = $_POST['local_id'];
        $selectedIntl = $_POST['intl_id'];
        $selectedGroupSemester = $_POST['group_semester'];
        if ($selectedGroupID === "") {
            echo "<div style='text-align: center;'>";
            echo "No group found.";
            echo "</div>";
            exit();
        }
    ?>
    <div class="link-container">
        <a href="index.php" class="search-link">Home</a>
        <a href="adminSchools.php" class="search-link">Back to Search</a>
    </div>
    
    <form action="editSchool.php" method="post">
        <?php
            
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

            $studentLoc = DB::table('student')
            ->where('student.icl_id',$selectedLoc)
            ->select('student.*')
            ->get();
            foreach($studentLoc as $row){
                $selectedLocName = $row->stuname;
            }
            $studentIntl = DB::table('student')
            ->where('student.icl_id',$selectedIntl)
            ->select('student.*')
            ->get();
            foreach($studentLoc as $row){
                $selectedIntlName = $row->stuname;
            }
            if ($local_session->isNotEmpty() || $intl_session->isNotEmpty()) {
                echo "<h3 style='text-align: left;'>Session Info </h3>";

                echo"<table>";
                
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

    </form>
    

</div>
</body>
</html>
