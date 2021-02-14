<?php
require_once 'sql_functions.php';
require_once 'config.php';
require_once $phpcas_path . 'CAS.php';
phpCAS::setDebug();
phpCAS::setVerbose(true);
phpCAS::client(CAS_VERSION_2_0, $cas_host, $cas_port, $cas_context);
phpCAS::setNoCasServerValidation(); // FIX THIS BUCKO
phpCAS::forceAuthentication();

$UNENROLLED_STUDENT = false;

$conn = new mysqli($sql_host, $sql_username, $sql_password, $sql_dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_REQUEST['logout'])) {
    header('Location: https://www.csee.umbc.edu');
//    phpCAS::logout();
}
?>

<html lang="en">
<style>
    body {background-color: #F1C04B}

    ul {
        list-style-type: none;
        margin: 0;
        padding: 0;
        overflow: hidden;
        background-color: #000000;
    }

    li {
        float: left;
    }

    li a {
        display: block;
        color: white;
        text-align: center;
        padding: 14px 20px;
        text-decoration: none;
        cursor: pointer;
    }

    li a:hover:not(.current) {
        background-color: #2d2e2f;
    }

    .current {
        background-color: #4CAF50;
    }


    .userDropdown {
        position: relative;
        display: block;
    }

    .user_content {
        display: none;
        position: absolute;
        background-color: #ff0000;
        min-width: 150px;
        overflow: auto;
        z-index: 1;
    }

    .user_content a {
        color: white;
        font-weight: bolder;
        padding: 12px 16px;
        text-align: center;
        text-decoration: none;
        display: block;
    }

</style>

<ul>
    <li><a class="current" href="">Home</a></li>
    <?php
    if(getEnrollment(phpCAS::getUser(), $conn) === "Student") {
        echo "<li><a href='assignments.php'>View Assignments</a></li>
                <li><a href='grades.php'>View Grades</a></li>";
    } else if(getEnrollment(phpCAS::getUser(), $conn) === "TA") {
        echo "<li><a href='assignments.php'>View Assignments</a></li>
                <li><a href='grades.php'>View Grades</a></li>
                <li><a href='ta/grade_submissions.php' style='color: deepskyblue'>Grade Submissions</a></li>";
    } else if(getEnrollment(phpCAS::getUser(), $conn) === "Instructor") {
        echo "<li><a href='assignments.php'>View Assignments</a></li>
                <li><a href='grades.php'>View Grades</a></li>
            <li><a href='ta/grade.php' style='color: deepskyblue'>Grade Submissions</a></li>
            <li><a href='administration/user_management.php' style='color: red'>User Management</a></li>
            <li><a href='administration/assignment_management.php' style='color: red'>Assignment Management</a></li>";
    }
    if(getEnrollment(phpCAS::getUser(), $conn) !== false) {
        echo '<li style="float:right"><a id="userButton" onclick="logoutDropdown()">';
        echo getFullNameFromCampusID(phpCAS::getUser(), $conn) . " (" . phpCAS::getUser() . ")";
        echo '
        </a>
                <div id="logOutDrop" class="user_content">
                    <a href="?logout=True">Log Out</a>
                </div>
            </li>;';
    } else {
        $UNENROLLED_STUDENT = true; // This is a word, right?
    }
    ?>
</ul>

<?php
if($UNENROLLED_STUDENT === true) {
    echo "<p style='font-size: xx-large;text-align: center'>Oh no! It appears that you are not registered for this course!</p>";
} else {
    echo "<p style='font-size: x-large;text-align: center'>Welcome to the UMBC Submit System! Please click 'View Assignments' in the top navigation bar to get started.</p><br><p style='font-size: x-large;text-align: center'>idk what to put here... a logo? announcement board? course information?</p>";
}
?>

</html>

<script>
    function logoutDropdown() {
        if(document.getElementById('logOutDrop').style.display === 'block') {
            document.getElementById('logOutDrop').style.display = '';
        } else {
            document.getElementById('logOutDrop').style.display = 'block';
        }
    }
</script>