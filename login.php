<?php
require 'eloquent.php';

use Illuminate\Database\Capsule\Manager as DB;
?>

<!DOCTYPE html>
<html>
<head>
<title>Login</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <h1>Login</h1>
    <div class="link-container">
        <a href="index.php" class="search-link">Home</a>
    </div>
    
    <form action="login.php" method="post">
    <div style='display: flex; flex-direction: column; gap: 10px; justify-content: center; align-items: baseline; margin-top: 20px;'>
        <label for="user_id">ID</label>
        <input type="text" name="user_id" id="user_id">
        
        <label for="password">PASSWORD</label>
        <input type="text" name="password" id="password">
        
        <input type="submit" value="Log In" 
               style='display: inline-start;
                      margin: 0 10px;
                      padding: 10px 20px;
                      background-color: #555555;
                      color: white;
                      text-decoration: none;
                      border-radius: 4px;
                      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
                      transition: background-color 0.3s;'
               onmouseover="this.style.backgroundColor='#4d73b1';" 
               onmouseout="this.style.backgroundColor='#555555';">
    </form>

    <?php
        session_start();

        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            // get the posted id and password
            $userID = $_POST['user_id'];
            $password = $_POST['password'];
            
            if ($userID === '' || $password === '') {
                $error = "Invalid username or password";
                echo "{$error}";
                exit();
            }

            // authentic the user

            // check students table
            $results = DB::table('student')
            ->where('stuname', "{$userID}")
            ->where('icl_id', "{$password}")
            ->get();

            if ($results->isNotEmpty()) {
                // user is a student
                $_SESSION['user_id'] = $userID;
                $_SESSION['password'] = $password;
                $_SESSION['identity'] = 'student';
                header("Location: index.php");
                exit();
            }
            
            // check schools table
            $results = DB::table('school')
            ->where('schname', "{$userID}")
            ->where('school_id', "{$password}")
            ->get();

            if ($results->isNotEmpty()) {
                // user is a student
                $_SESSION['user_id'] = $userID;
                $_SESSION['password'] = $password;
                $_SESSION['identity'] = 'school';
                header("Location: index.php");
                exit();
            }

            // user is an admin
            if ($userID === 'admin' && $password === '123') {
                $_SESSION['user_id'] = $userID;
                $_SESSION['password'] = $password;
                $_SESSION['identity'] = 'admin';
                header("Location: index.php");
                exit();
            }

            $error = "Invalid username or password";
            echo "{$error}";
            exit();
        }
    ?>
</div>
</body>
</html>
