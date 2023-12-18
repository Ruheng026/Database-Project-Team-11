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
<title>Search Students</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <h1>Search Students</h1>
    <div class="link-container">
        <a href="index.php" class="search-link">Home</a>
    </div>
    <!-- 1. search student with name -->
    <h3 style='text-align: center;'>Search student with name, ICL ID, phone or email</h3>
    <form action="adminStudents.php" method="post">
    <div style='display: flex; flex-direction: row; gap: 10px; justify-content: center; align-items: baseline; margin-top: 20px;'>
        <input type="text" name="student_name" value="<?php echo isset($_POST['student_name']) ? htmlspecialchars($_POST['student_name']) : ''; ?>">
        <input type="submit" value="Search">
    </div>
    </form>
    <?php
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $selectedStudentName = $_POST['student_name'];
            if ($selectedStudentName === "") {
                echo "<div style='text-align: center;'>";
                echo "No student name found.";
                echo "</div>";
                exit();
            }
            // 1.1 student basic info
            $student_basic_info = DB::table('student')
            ->where(function ($query) use ($selectedStudentName) {
                $query->where('student.icl_id', $selectedStudentName)
                    ->orWhere('student.stuname', $selectedStudentName)
                    ->orWhere('student.stuphone', $selectedStudentName)
                    ->orWhere('student.stuemail', $selectedStudentName);
            })
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
                echo "<h3 style='text-align: center;'>Student Basic Info</h3>";
                echo "<table>";
                echo "<tr><th>Name</th><th>ICL ID</th><th>Nationality</th><th>University</th><th>Phone</th><th>Email</th></tr>";
                foreach ($student_basic_info as $row) {
                    $ICLId = $row->icl_id;
                    echo "<tr><td>{$row->stuname}</td><td>{$row->icl_id}</td><td>{$row->stunationality}</td><td>{$row->stuuniversity}</td><td>{$row->stuphone}</td><td>{$row->stuemail}</td></tr>";
                }
                echo "</table>";
               
                echo "<form action=\"adminSearchStudents.php\" method=\"post\">";
                echo "<input type=\"hidden\" name=\"icl_id\" value=\"$ICLId\">";
                echo "<div style='display: flex; flex-direction: row; gap: 10px; justify-content: center; align-items: baseline; margin-top: 20px;'>";
                echo "<input type=\"submit\" value=\"View\">";
                echo "</div>";
                echo "</form>";
            } else {
                echo "<div style='text-align: center;'>";
                echo "No student found.";
                echo "</div>";
                exit();
            }
        }
    ?>
    
    
    
</div>

</body>
</html>
