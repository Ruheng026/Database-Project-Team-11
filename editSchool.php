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

DB::beginTransaction();
DB::table('session_attendance')->lockForUpdate()->get();

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
    echo "<h1>Edit Session Information</h1>";
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $selectedGroupID = $_POST['group_id'];
        $selectedLoc = $_POST['local_id'];
        $selectedIntl = $_POST['intl_id'];
        $selectedGroupSemester = $_POST['group_semester'];
        if ($selectedGroupID === "") {
            echo "<div style='text-align: center;'>";
            echo "No group found.";
            echo "</div>";
            DB::rollBack();
            exit();
        }

    ?>
    <div class="link-container">
        <a href="index.php" class="search-link">Home</a>
        <a href="adminSchools.php" class="search-link">Back to Search</a>
    </div>
    
    <form action="editSchool.php" method="post">
        <?php
            $the_session= DB::table('session')
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
            ->select('session.date','group_.starttime','group_.endtime','group_.locicl_id','group_.intlicl_id','attend_status.attendtype as local')
            ->distinct()
            ->get();

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

            foreach($studentIntl as $row){
                $selectedIntlName = $row->stuname;
            }

            $attend_status = DB::table('attend_status')
                ->select('attend_status.*')
                ->get();

            if ($local_session->isNotEmpty() || $intl_session->isNotEmpty()) {
                echo "<h3 style='text-align: left;'>Session Info </h3>";
                echo"<table>";
                echo "<tr><th>Date</th><th>Time</th><th>Attendance of {$selectedIntlName}(International Student)</th><th>Attendance of {$selectedLocName} (Local Student)</th></tr>";
                foreach ($the_session as $srow) {
                    echo "<tr><th>{$srow->date}</th><th>{$srow->starttime}~{$srow->endtime}</th>
                    <th>
                    <select name=\"selectedIntlStatus\" id=\"selectedIntlStatus\" style=\"width:260px\" >";
                    foreach($attend_status as $row){
                        $selected =($_POST['selectedIntlStatus']??'')==$row->attendtype?'attendtype':'';
                        echo"<option value='{$row->attendtype}'{$selected}>{$row->attendtype}</option>";
                    }
                    echo"
                    </select>
                    </th>

                    <th>
                    <select name=\"selectedLocStatus\" id=\"selectedLocStatus\" style=\"width:260px\" >";
                    foreach($attend_status as $row){
                        $selected =($_POST['selectedLocStatus']??'')==$row->attendtype?'attendtype':'';
                        echo"<option value='{$row->attendtype}'{$selected}>{$row->attendtype}</option>";
                    }
                    $_POST['selectedSessionId'] = $srow->session_id;
                        $_POST['local_id'] = $srow->locicl_id;
                        $_POST['intl_id'] = $srow->intlicl_id;
                        $_POST['selectedLocDate'] = $srow->date;
                        $_POST['selectedLocStartTime'] = $srow->starttime;
                    echo"
                    </select>
                    </th>";
                }
                echo"</table>";
                
                echo"<table>";
                
                echo "</table>
                <input type=\"hidden\" name=\"group_id\" value=\"$selectedGroupID\">
                <input type=\"hidden\" name=\"local_id\" value=\"$selectedLoc\">
                <input type=\"hidden\" name=\"intl_id\" value=\"$selectedIntl\">
                <input type=\"hidden\" name=\"group_semester\" value=\"$selectedGroupSemester\">
                <input type='submit' name='saveChanges' value='Save Changes'  style='width: 120px;' onclick = refresh()>";
            } else {
                // Handle the case where both $local_session and $intl_session are empty
                echo "No data available.";
            }
        }
        
        ?>
    </form>
    <?php
        if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['saveChanges'])) {
            // Handle form submission
            $selectedLocSessionId = isset($_POST['selectedSessionId']) ? $_POST["selectedSessionId"] : null;
            $selectedIntlSessionId = isset($_POST['selectedSessionId']) ? $_POST["selectedSessionId"] : null;
            $selectedLocID = isset($_POST["local_id"])? $_POST["local_id"] : null;
            $selectedIntlID = isset($_POST["intl_id"])?$_POST["intl_id"] : null;
            $selectedLocStatus = isset($_POST["selectedLocStatus"])? $_POST["selectedLocStatus"] : null;
            $selectedIntlStatus = isset($_POST["selectedIntlStatus"])? $_POST["selectedIntlStatus"] : null;
            echo "<h3 style='text-align: left;'>Session Info $selectedLocSessionId $selectedIntlSessionId</h3>";
            
            try {
                $selectedLocStatusNo = DB::table('attend_status')
                ->where('attendtype', $selectedLocStatus)
                ->select('attend_no')
                ->first();
                $selectedLocStatusNo = $selectedLocStatusNo->attend_no;
                $selectedIntlStatusNo = DB::table('attend_status')
                ->where('attendtype', $selectedIntlStatus)
                ->select('attend_no')
                ->first();
                $selectedIntlStatusNo = $selectedIntlStatusNo->attend_no; 
            
                // Update the record
                $result1 = DB::table('session_attendance')
                ->where('session_id', $selectedLocSessionId)
                ->where('icl_id', $selectedLocID)
                ->update(['attend_no' => $selectedLocStatusNo]);
        
                $result2 = DB::table('session_attendance')
                ->where('session_id', $selectedIntlSessionId)
                ->where('icl_id', $selectedIntlID)
                ->update(['attend_no' => $selectedIntlStatusNo]);
                
                if ($result1 || $result2) {
                    DB::commit();
                    echo "Update successful!";
                    if ($identity === 'school')
                        header("Location: school.php");
                    if ($identity === 'admin')
                        header("Location: adminSchool.php");
                    exit();
    
                } else {
                    throw new \Exception("Error updating record.");
                    DB::rollBack();
                }
            } catch (\Exception $e) {
                echo $e->getMessage();
                DB::rollBack();
            }
        
    
        }
    ?>
    

</div>
</body>
</html>
