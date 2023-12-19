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
<title>Admin Schools</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <h1>Admin Schools</h1>
    <div class="link-container">
        <a href="index.php" class="search-link">Home</a>
    </div>
    <h3 style='text-align:center;'>Search School</h3>
    <form action="adminSchools.php" method="post">
    <div style='display: flex; flex-direction: row; gap: 10px; justify-content: center; align-items: baseline; margin-top: 20px;'>
        <label for="search">School name or ID</label>
        <!-- <select name="search" id="search">
            <option>Name</option>
            <option>School ID</option>
        </select> -->
        <label for="school"></label>
        <input type="text" name="school" value="<?php echo isset($_POST['school']) ? htmlspecialchars($_POST['school']): ''; ?>">
        <input type="submit" value="Search">
    </div>
    </form>    

<?php
    if ($_SERVER["REQUEST_METHOD"] == "POST"){
        $selectedSchool = $_POST['school'];
        if ($selectedSchool === ""){
            echo "<div style = 'text-align: center;'>";
            echo "No school found.";
            echo "</div>";
            exit();
        }
    
    // school basic info
    $school_basic_info = DB::table('school')
    ->where(function ($query) use ($selectedSchool){
        $query->where('school.school_id', $selectedSchool)
                    ->orWhere('school.schname', $selectedSchool)
                    ->orWhere('school.schaddress', $selectedSchool)
                    ->orWhere('school.schcounty', $selectedSchool);
    })
        ->select(
            'school.school_id',
            'school.schname',
            'school.schaddress',
            'school.schcounty'
        )
        ->get();

    if ($school_basic_info->isNotEmpty()){
        echo "<h3 style='text-align: center;'>School Basic Info</h3>";
        echo "<table>";
        echo "<tr><th>Name</th><th>School ID</th></tr>";
        foreach ($school_basic_info as $row) {
            $schoolId = $row->school_id;
            echo "<tr><td>{$row->schname}</td><td>{$row->schaddress}</td></tr>";
        }
        echo 
        "</table>
        <form action=\"adminSearchSchools.php\" method=\"post\">
        <input type=\"hidden\" name=\"school_id\" value=\"$schoolId\">
        <div style='display: flex; flex-direction: row; gap: 10px; justify-content: center; align-items: baseline; margin-top: 20px;'>
        <input type=\"submit\" value=\"View\">
        </div>
        </form>";
    }
    else{
        echo 
        "<div style='text-align: center;'>
        No school found.
        </div>";
        exit();
    }
    }
?>

</div>
</body>
</html>
