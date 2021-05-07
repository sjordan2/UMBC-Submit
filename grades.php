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

if(getEnrollment(phpCAS::getUser(), $conn) === false) {
    header('Location: home.php');
}
?>

<html lang="en">
<style>
    body {background-color: #ABABAB}

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

    div {
        border: 2px solid black;
        margin: 2px;
        display: inline-block;
    }

    div.outerDiv {
        border: none;
        margin-top: 10px;
        width: 100%;
    }

    button.viewAssignment {
        background-color: white;
        color: #5e00ca;
        display: inline-block;
        padding: 15px 15px;
        cursor: pointer;
        border: 2px solid;
        text-align: left;
    }

    button.viewAssignment:hover {
        background-color: #5e00ca;
        color: white;
    }

    button.submitCode {
        background-color: white;
        color: #0d6b0d;
        display: inline-block;
        padding: 10px 15px;
        margin-left: 5px;
        margin-bottom: 5px;
        cursor: pointer;
        border: 2px solid;
        text-align: center;
    }

    button.submitCode:hover {
        background-color: #0d6b0d;
        color: white;
    }

    button.testProgram {
        background-color: white;
        color: #0073ca;
        display: inline-block;
        padding: 10px 15px;
        margin-left: 5px;
        margin-bottom: 5px;
        margin-right: 5px;
        cursor: pointer;
        border: 2px solid;
        text-align: right;
    }

    button.testProgram:hover {
        background-color: #0073ca;
        color: white;
    }

</style>
<?php
$currUser = phpCAS::getUser();
echo "<body onload='loadGradesForUser(\"$currUser\")'>";
?>
<ul>
    <li><a href="home.php">Home</a></li>
    <?php
    if(getEnrollment(phpCAS::getUser(), $conn) === "Student") {
        echo "<li><a href='assignments.php'>View Assignments</a></li>
                <li><a href='' class='current'>View Grades</a></li>";
    } else if(getEnrollment(phpCAS::getUser(), $conn) === "TA") {
        echo "<li><a href='assignments.php' class='current'>View Assignments</a></li>
                <li><a href='' class='current'>View Grades</a></li>
                <li><a href='ta/grade_submissions.php' style='color: deepskyblue'>Grade Submissions</a></li>";
    } else if(getEnrollment(phpCAS::getUser(), $conn) === "Instructor") {
        echo "<li><a href='assignments.php'>View Assignments</a></li>
                <li><a href='' class='current'>View Grades</a></li>
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
    }
    echo "<!-- Unique alphanumeric key for security. DO NOT SHARE! -->";
    $alpha_num_key = getAlphaNumKey(phpCAS::getUser(), $conn);
    echo "<p hidden id='alpha_num_key'>$alpha_num_key</p>";
    ?>
</ul>
<div id='divHTML' class="outerDiv">
    <!--   Placeholder for AJAX Query   -->
</div>
</html>

<script>
    function loadGradesForUser(user_campus_id) {
        let ajaxQuery = new XMLHttpRequest();
        ajaxQuery.onreadystatechange = function () {
            if (this.readyState === 4 && this.status === 200) {
                document.getElementById("divHTML").innerHTML = this.responseText;
            }
        };
        ajaxQuery.open("POST", "retrieve_student_grades.php", true);
        ajaxQuery.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        ajaxQuery.send("user=" + user_campus_id + "&alpha_num_key=" + document.getElementById("alpha_num_key").innerText);
    }

    function viewRubricAsStudent(button) {
        let assignmentName = button.id.split("_")[1];
        window.location.href = "view_grade.php?assignment=" + assignmentName;
    }
</script>