<?php
require_once '../sql_functions.php';
require_once '../config.php';
require_once $php_nested_cas_path . 'CAS.php';
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
    header('Location: ../home.php');
    exit();
}

if(getEnrollment(phpCAS::getUser(), $conn) === "Student") {
    header('Location: ../home.php');
    exit();
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

    button.gradeSubmissions {
        background-color: white;
        color: #0073ca;
        display: inline-block;
        padding: 10px 15px;
        margin-left: 5px;
        margin-bottom: 5px;
        cursor: pointer;
        border: 2px solid;
        align-content: center;
    }

    button.gradeSubmissions:hover {
        background-color: #0073ca;
        color: white;
    }

</style>
<ul>
    <li><a href="../home.php">Home</a></li>
    <?php
    if(getEnrollment(phpCAS::getUser(), $conn) === "TA") {
        echo "<li><a href='../assignments.php'>View Assignments</a></li>
                <li><a href='../grades.php'>View Grades</a></li>
                <li><a href='' style='color: white' class='current'>Grade Submissions</a></li>";
    } else if(getEnrollment(phpCAS::getUser(), $conn) === "Instructor") {
        echo "<li><a href='../assignments.php'>View Assignments</a></li>
                <li><a href='../grades.php'>View Grades</a></li>
                <li><a href='' style='color: white' class='current'>Grade Submissions</a></li>
                <li><a href='../administration/user_management.php' style='color: red'>User Management</a></li>
                <li><a href='../administration/assignment_management.php' style='color: red'>Assignment Management</a></li>";
    }
    if(getEnrollment(phpCAS::getUser(), $conn) !== false) {
        echo '<li style="float:right"><a id="userButton" onclick="logoutDropdown()">';
        echo getFullNameFromCampusID(phpCAS::getUser(), $conn) . " (" . phpCAS::getUser() . ")";
        echo '
        </a>
                <div id="logOutDrop" class="user_content">
                    <a href="?logout=True">Log Out</a>
                </div>
            </li>';
    } else {
        $UNENROLLED_STUDENT = true; // This is a word, right?
    }
echo "</ul>";

$grading_sql = "SELECT assignment_name, date_due, grading_due_date FROM Assignments";
$current_grading = $conn->query($grading_sql);
$upcoming_grading = $conn->query($grading_sql);
$past_grading = $conn->query($grading_sql);
echo "<p style='font-size: xx-large;margin: 0px;text-align: center'>Current Grading Assignments</p>";
while($row_current = $current_grading->fetch_assoc()) {
    $current_date = null;
    $assignment_date = null;
    $grading_date = null;
    try {
        $current_date = new DateTime();
        $assignment_date = new DateTime($row_current['date_due']);
        $grading_date = new DateTime($row_current['grading_due_date']);
    } catch(Exception $e) {
        echo "Date Time Error! Message: " . $e;
    }
    if($assignment_date < $current_date and $current_date < $grading_date) {
        $divID = "currAssignmentDiv_" . htmlspecialchars(str_replace(" ", "~", $row_current['assignment_name']), ENT_QUOTES);
        echo "<div id=$divID>";
        echo "<p style='text-align: center;font-size: x-large;margin: 0px 3px;'>" . $row_current['assignment_name'] . "</p><br>";
        echo "<p style='text-align: center;font-size: large;margin: 5px;'><b>Grading Due Date: </b>" . $grading_date->format("l, F jS, Y, g:i:sA") . "</p><br>";
        $gradeAssignmentID = "grade_" . htmlspecialchars(str_replace(" ", "~", $row_current['assignment_name']), ENT_QUOTES);
        $submissionStatusArray = getAssignmentGradingStatus(phpCAS::getUser(), $row_current['assignment_name'], $conn);
        $partsDone = $submissionStatusArray[0];
        $partsTotal = $submissionStatusArray[1];
        echo "<p style='display: inline;margin-left: 5px'><b>Status: </b></p>";
        if($partsDone == $partsTotal) {
            echo "<p style='color: #3f9b42;display: inline;'><b>$partsDone / $partsTotal parts completed! Congratulations!</b></p>";
        } else {
            echo "<p style='color: red;display: inline;'><b>$partsDone / $partsTotal parts completed. Get back to work!</b></p>";
        }
        echo "<br>";
        echo "<br>";
        $grade_page = "grade_assignment.php";
        $get_string = "?assignment=" . htmlspecialchars(str_replace(" ", "~", $row_current['assignment_name']), ENT_QUOTES) . "&section=" . getUserSectionNumber(phpCAS::getUser(), $conn);
        $final_query = $grade_page . $get_string;
        echo "<button class='gradeSubmissions' id=$gradeAssignmentID onclick='location.href = \"$final_query\"'>Grade!</button>";
        echo "</div>";
    }
}
echo "<p style='font-size: xx-large;margin: 0px;text-align: center'>Upcoming Grading Assignments</p>";
while($row_upcoming = $upcoming_grading->fetch_assoc()) {
    $current_date = null;
    $assignment_date = null;
    $grading_date = null;
    try {
        $current_date = new DateTime();
        $assignment_date = new DateTime($row_upcoming['date_due']);
        $grading_date = new DateTime($row_upcoming['grading_due_date']);
    } catch(Exception $e) {
        echo "Date Time Error! Message: " . $e;
    }
    if($current_date < $assignment_date) {
        $divID = "currAssignmentDiv_" . htmlspecialchars(str_replace(" ", "~", $row_upcoming['assignment_name']), ENT_QUOTES);
        echo "<div id=$divID>";
        echo "<p style='text-align: center;font-size: x-large;margin: 0px 3px;'>" . $row_upcoming['assignment_name'] . "</p><br>";
        echo "<p style='text-align: center;font-size: large;margin: 5px;'><b>Grading Due Date: </b>" . $grading_date->format("l, F jS, Y, g:i:sA") . "</p><br>";
        $gradeAssignmentID = "grade_" . htmlspecialchars(str_replace(" ", "~", $row_upcoming['assignment_name']), ENT_QUOTES);
        $submissionStatusArray = getAssignmentGradingStatus(phpCAS::getUser(), $row_upcoming['assignment_name'], $conn);
        $partsDone = $submissionStatusArray[0];
        $partsTotal = $submissionStatusArray[1];
        echo "<p style='display: inline;margin-left: 5px'><b>Status: </b></p>";
        if($partsDone == $partsTotal) {
            echo "<p style='color: #3f9b42;display: inline;'><b>$partsDone / $partsTotal parts completed! Congratulations!</b></p>";
        } else {
            echo "<p style='color: red;display: inline;'><b>$partsDone / $partsTotal parts completed. Get back to work!</b></p>";
        }
        echo "<br>";
        echo "<br>";
        $grade_page = "grade_assignment.php";
        $get_string = "?assignment=" . htmlspecialchars(str_replace(" ", "~", $row_upcoming['assignment_name']), ENT_QUOTES) . "&section=" . getUserSectionNumber(phpCAS::getUser(), $conn);
        $final_query = $grade_page . $get_string;
        echo "<button class='gradeSubmissions' id=$gradeAssignmentID onclick='location.href = \"$final_query\"'>Grade!</button>";
        echo "</div>";
    }
}
echo "<p style='font-size: xx-large;margin: 0px;text-align: center'>Past Grading Assignments</p>";
while($row_past = $past_grading->fetch_assoc()) {
    $current_date = null;
    $grading_date = null;
    try {
        $current_date = new DateTime();
        $grading_date = new DateTime($row_past['grading_due_date']);
    } catch(Exception $e) {
        echo "Date Time Error! Message: " . $e;
    }
    if($current_date > $grading_date) {
        $divID = "currAssignmentDiv_" . htmlspecialchars(str_replace(" ", "~", $row_past['assignment_name']), ENT_QUOTES);
        echo "<div id=$divID>";
        echo "<p style='text-align: center;font-size: x-large;margin: 0px 3px;'>" . $row_past['assignment_name'] . "</p><br>";
        echo "<p style='text-align: center;font-size: large;margin: 5px;'><b>Grading Due Date: </b>" . $grading_date->format("l, F jS, Y, g:i:sA") . "</p><br>";
        $gradeAssignmentID = "grade_" . htmlspecialchars(str_replace(" ", "~", $row_past['assignment_name']), ENT_QUOTES);
        $submissionStatusArray = getAssignmentGradingStatus(phpCAS::getUser(), $row_past['assignment_name'], $conn);
        $partsDone = $submissionStatusArray[0];
        $partsTotal = $submissionStatusArray[1];
        echo "<p style='display: inline;margin-left: 5px'><b>Status: </b></p>";
        if($partsDone == $partsTotal) {
            echo "<p style='color: #3f9b42;display: inline;'><b>$partsDone / $partsTotal parts completed! Congratulations!</b></p>";
        } else {
            echo "<p style='color: red;display: inline;'><b>$partsDone / $partsTotal parts completed. Get back to work!</b></p>";
        }
        echo "<br>";
        echo "<br>";
        $grade_page = "grade_assignment.php";
        $get_string = "?assignment=" . htmlspecialchars(str_replace(" ", "~", $row_past['assignment_name']), ENT_QUOTES) . "&section=" . getUserSectionNumber(phpCAS::getUser(), $conn);
        $final_query = $grade_page . $get_string;
        echo "<button class='gradeSubmissions' id=$gradeAssignmentID onclick='location.href = \"$final_query\"'>Grade!</button>";
        echo "</div>";
    }
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