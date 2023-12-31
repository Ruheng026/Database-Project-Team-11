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
if ($identity !== 'admin' && $identity !== 'student') {
    header("Location: index.php");
    exit();
}

DB::beginTransaction();
DB::table('student')->lockForUpdate()->get();

?>
<meta charset="UTF-8">
<!DOCTYPE html>
<html>
<head>
<title>Edit Students</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <?php
    echo "<h1>Edit Students Information</h1>";
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
    
    <form action="editStudent.php" method="post">
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
                echo "<tr><td>{$row->stuname}</td><td>{$row->icl_id}</td><td>{$row->stunationality}</td><td>{$row->stuuniversity}</td><td>{$row->stuphone}</td><td>{$row->stuemail}</td></tr>";
            }
            echo "<input type=\"hidden\" name=\"icl_id\" value= \"$row->icl_id\">";
            echo "<input type=\"hidden\" name=\"display\" value= \"editArea.style.display\">";
            
            echo "</table>";
            
        } else {
            echo "<div style='text-align: center;'>";
            echo "No student found.";
            echo "</div>";
            DB::rollBack();
            exit();
        }
        // echo"<script>
        // function refresh{
        //     $refresh = 1;
        //     setTimeout(function(){ location.reload(); }, 2000);
        // }
        // </script>";
    
        if ($student_basic_info->isNotEmpty()) {
            echo "<div id='edit_area' style='text-align: center;'>
                    <h3 style='text-align: center;'>Edit Student Basic Information</h3>
            <form accept-charset='UTF-8' method='post' action='editStudent.php'>";  // Add your update script file in the action attribute
            echo "<div style='text-align: center;'>";
            echo "<table style='text-align: center;'>";
            // Display table headers
            echo "<tr><th>Edit Information </th>";
            foreach ($student_basic_info as $row) {
                echo "<th>{$row->icl_id},{$row->stuname}</th>";
            }
            echo "</tr>";
        
            // Display table rows
            echo "<tr><th>Name</th>";
            foreach ($student_basic_info as $row) {
                echo "<td>{$row->stuname}</td>";
            }
            echo "</tr>";

            echo "<tr><th>ICL ID</th>";
            foreach ($student_basic_info as $row) {
                echo "<td>{$row->icl_id}</td>";
            }
            echo "</tr>";
        
            echo "<tr><th>Nationality</th>";
            foreach ($student_basic_info as $row) {
                echo "<td>{$row->stunationality}</td>";
            }
            echo "</tr>";
        
            echo "<tr><th>University</th>";
            foreach ($student_basic_info as $row) {
                echo "<td>{$row->stuuniversity}</td>";
            }
            echo "</tr>";
        
            echo "<tr><th>Phone</th>";
            foreach ($student_basic_info as $row) {
                echo "<td><input type='text' name='stuphone' style='width: 200px;' value='{$row->stuphone}'></td>";
            }
            echo "</tr>";
        
            echo "<tr><th>Email</th>";
            foreach ($student_basic_info as $row) {
                echo "<td><input type='text' name='stuemail' style='width: 200px;' value='{$row->stuemail}'></td>";
            }
            echo "</tr>";
        
            echo "</table>";
            echo "</div>";
            echo "<input type='submit' name='saveChanges' value='Save Changes' style='width: 120px;' onclick = refresh()>";
            echo "</form>";
            echo "</div>";
        } else {
            echo "<div style='text-align: center;'>";
            echo "No student found.";
            echo "</div>";
            DB::rollBack();
            exit();
        }
        
    }
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['saveChanges'])) {
        // Handle form submission
        $stuphone = isset($_POST["stuphone"]) ? $_POST["stuphone"] : null;
        $stuemail = isset($_POST["stuemail"]) ? $_POST["stuemail"] : null;

        // Check if phone number is not a 10-digit number
        if (!preg_match('/^\d{10}$/', $stuphone)) {
            echo "Invalid phone number format. Please enter a 10-digit number.";
            // throw new \Exception("Invalid phone number format. Please enter a 10-digit number.");
            DB::rollBack();
            exit();
        }

        // Check if email exceeds 30 characters
        if (strlen($stuemail) > 30) {
            echo "Email length exceeds the maximum allowed characters (30).";
            // throw new \Exception("Email length exceeds the maximum allowed characters (30).");
            DB::rollBack();
            exit();
        }
    
        try {
            // Retrieve the current record for comparison
            $existingRecord = DB::table('student')
                ->where('icl_id', $selectedIclID)
                ->first();
        
            if (!$existingRecord) {
                throw new \Exception("Record not found.");
            }
        
            // Update the record
            $result = DB::table('student')
                ->where('icl_id', $selectedIclID)
                ->update([
                    'stuphone' => $stuphone,
                    'stuemail' => $stuemail,
                ]);
        
            if ($result) {
                DB::commit();
                echo "Update successful!";
                if ($identity === 'student')
                    header("Location: student.php");
                if ($identity === 'admin')
                    header("Location: adminStudents.php");
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
