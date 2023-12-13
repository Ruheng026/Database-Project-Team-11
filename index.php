<!DOCTYPE html>
<html>
<head>
<title>Welcome to ICL System</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <h1>ICL System</h1>
    <!-- <h2>NTUIM 112-1 Database Management</h2> -->
    <div class="link-container">
        <?php
            session_start();
            
            // check if logged in
            if (isset($_SESSION['user_id'])) {
                // user is admin
                if ($_SESSION['identity'] == "admin") {
                    echo '<a href="adminStudents.php" class="search-link">Admin Students</a>';
                    echo '<a href="adminSchools.php" class="search-link">Admin Schools</a>';
                    echo '<a href="adminTrips.php" class="search-link">Admin Trips</a>';
                    echo '<a href="publicStatistics.php" class="search-link">Public Statistics</a>';
                    echo '<a href="adminStatistics.php" class="search-link">Admin Statistics</a>';
                }
                // user is student
                else if ($_SESSION['identity'] == "student") {
                    echo '<a href="publicStatistics.php" class="search-link">Public Statistics</a>';
                    echo '<a href="student.php" class="search-link">My Page</a>';
                }
                // user is school
                else if ($_SESSION['identity'] == "school") {
                    echo '<a href="publicStatistics.php" class="search-link">Public Statistics</a>';
                    echo '<a href="school.php" class="search-link">My Page</a>';
                }
                // log out page (button)
                echo '<a href="logout.php" class="search-link">Logout</a>';
            }
            // not logged in
            else {
                echo '<a href="publicStatistics.php" class="search-link">Public Statistics</a>';
                echo '<a href="login.php" class="search-link">Login</a>';
            }
        ?>
    </div>
    
    <?php
        if (isset($_SESSION['user_id'])) {
            // store session info
            $userID = $_SESSION['user_id'];
            $password = $_SESSION['password'];
            $identity = $_SESSION['identity'];
            // logged in succesfully, echo info
            echo '<p style="color: #728FCE;">Status: logged in as ' . $userID . ' (' . $identity . ')</p>';
        }
        if (isset($_GET['logout']) && $_GET['logout'] == 1) {
            // logged out succesfully
            echo '<p style="color: #728FCE;">Status: logged out</p>';
        }
    ?>
</div>
</body>
</html>
