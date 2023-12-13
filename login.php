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
    <form action="login.php" method="post">
        <div>
            <label for="user_id">ID</label>
            <input type="text" name="user_id" id="user_id">
        </div>
        <div>
            <label for="password">PASSWORD</label>
            <input type="password" name="password" id="password">
        </div>
        <section>
            <button type="submit">Log in</button>
        </section>
    </form>

    <?php
        session_start();

        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            // get the posted id and password
            $userID = $_POST['user_id'];
            $password = $_POST['password'];
            // echo "{$userID} {$password}";

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
        }
    ?>
</div>
</body>
</html>
