<?php
require 'eloquent.php';

use Illuminate\Database\Capsule\Manager as DB;
?>

<!DOCTYPE html>
<html>
<head>
<title>ICL Public Statistics</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <h1>ICL Public Statistics</h1>
    <div class="link-container">
        <a href="index.php" class="search-link">Home</a>
    </div>
    <div>
        <?php
            $students = DB::table('student')->get();
            $totalStudents = $students->count();
            $schools = DB::table('school')->get();
            $totalSchools = $schools->count();
        ?>
        <h3>Total Students: <?php echo $totalStudents; ?></h3>
        <h3>Total Schools: <?php echo $totalSchools; ?></h3>
    </div>
</div>
</body>
</html>
