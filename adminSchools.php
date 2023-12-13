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
    <a href="index.php" class="search-link">Home</a>
</div>
</body>
</html>
