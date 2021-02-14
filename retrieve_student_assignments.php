<?php
include 'sql_functions.php';

$studentCampusID = $_POST["user"];

// Create connection
$conn = new mysqli($sql_host, $sql_username, $sql_password, $sql_dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$assignment_div_list = "SELECT assignment_name, max_points, extra_credit, date_assigned, date_due, document_link FROM Assignments";

$current_result_list = $conn->query($assignment_div_list);
$past_result_list = $conn->query($assignment_div_list);
$currentDate = null;
try {
    $currentDate = new DateTime();
} catch(Exception $e) {
    echo "Date Time Error: " . $e;
}

echo "<p style='font-size: xx-large;margin: 0px;text-align: center'>Current Assignments</p>";
while($row = $current_result_list->fetch_assoc()) {
    $studentDueDate = getStudentDueDateForAssignment($studentCampusID, $row['assignment_name'], $conn);
    if($studentDueDate > $currentDate) {
        $divID = "currAssignmentDiv_" . htmlspecialchars(str_replace(" ", "~", $row['assignment_name']), ENT_QUOTES);
        echo "<div id=$divID>";
        echo "<p style='text-align: center;font-size: x-large;margin: 0px 3px;'>" . $row['assignment_name'] . "</p><br>";
        echo "<p style='text-align: center;font-size: large;margin: 5px;'><b>Due Date: </b>" . $studentDueDate->format("l, F jS, Y, g:i:sA") . "</p><br>";
        $viewAssignmentID = "view_" . htmlspecialchars(str_replace(" ", "~", $row['assignment_name']), ENT_QUOTES) . "_" . $studentCampusID;
        $submitAssignmentID = "submit_" . htmlspecialchars(str_replace(" ", "~", $row['assignment_name']), ENT_QUOTES) . "_" . $studentCampusID;
        $testAssignmentID = "test_" . htmlspecialchars(str_replace(" ", "~", $row['assignment_name']), ENT_QUOTES) . "_" . $studentCampusID;
        $submissionStatus = getAssignmentSubmissionStatus($studentCampusID, $row['assignment_name'], $conn);
        echo "<p style='display: inline;margin-left: 5px'><b>Status: </b></p>";
        if($submissionStatus === 2) {
            echo "<p style='color: #0d6b0d;display: inline;'><b>All Parts Submitted</b></p>";
        } else if($submissionStatus === 1) {
            echo "<p style='color: blue;display: inline;'><b>Some Parts Submitted</b></p>";
        } else {
            echo "<p style='color: red;display: inline;'><b>Nothing Submitted</b></p>";
        }
        echo "<br>";
        echo "<br>";
        $viewButtonOnClick = $row['document_link'];
        $get_string = "?assignment=" . htmlspecialchars(str_replace(" ", "~", $row['assignment_name']), ENT_QUOTES);
        $submit_query_string = "submissions.php" . $get_string;
        $test_query_string = "view_test_code.php" . $get_string;
        echo "<button class='viewAssignment' id=$viewAssignmentID onclick='window.open(\"$viewButtonOnClick\", \"_blank\")'>View Assignment</button>";
        echo "<button class='submitCode' id=$submitAssignmentID onclick='location.href = \"$submit_query_string\"'>Submit Code</button>";
        echo "<button class='testProgram' id=$testAssignmentID onclick='location.href = \"$test_query_string\"'>View/Test Program</button>";
        echo "</div>";
    }
}

echo "<p style='font-size: xx-large;margin: 0px;text-align: center'>Past Assignments</p>";
while($row = $past_result_list->fetch_assoc()) {
    $studentDueDate = getStudentDueDateForAssignment($studentCampusID, $row['assignment_name'], $conn);
    if($studentDueDate < $currentDate) {
        $divID = "pastAssignmentDiv_" . htmlspecialchars(str_replace(" ", "~", $row['assignment_name']), ENT_QUOTES);
        echo "<div id=$divID>";
        echo "<p style='text-align: center;font-size: x-large;margin: 0px 3px;'>" . $row['assignment_name'] . "</p><br>";
        echo "<p style='text-align: center;font-size: large;margin: 5px;'><b>Due Date: </b>" . $studentDueDate->format("l, F jS, Y, g:i:sA") . "</p><br>";
        $viewAssignmentID = "view_" . htmlspecialchars(str_replace(" ", "~", $row['assignment_name']), ENT_QUOTES) . "_" . $studentCampusID;
        $submitAssignmentID = "submit_" . htmlspecialchars(str_replace(" ", "~", $row['assignment_name']), ENT_QUOTES) . "_" . $studentCampusID;
        $testAssignmentID = "test_" . htmlspecialchars(str_replace(" ", "~", $row['assignment_name']), ENT_QUOTES) . "_" . $studentCampusID;
        $submissionStatus = getAssignmentSubmissionStatus($studentCampusID, $row['assignment_name'], $conn);
        echo "<p style='display: inline;margin-left: 5px'><b>Status: </b></p>";
        if($submissionStatus === 2) {
            echo "<p style='color: #0d6b0d;display: inline;'><b>All Parts Submitted</b></p>";
        } else if($submissionStatus === 1) {
            echo "<p style='color: blue;display: inline;'><b>Some Parts Submitted</b></p>";
        } else {
            echo "<p style='color: red;display: inline;'><b>Nothing Submitted</b></p>";
        }
        echo "<br>";
        echo "<br>";
        $viewButtonOnClick = $row['document_link'];
        $get_string = "?assignment=" . htmlspecialchars(str_replace(" ", "~", $row['assignment_name']), ENT_QUOTES);
        $submit_query_string = "submissions.php" . $get_string;
        $test_query_string = "view_test_code.php" . $get_string;
        echo "<button class='viewAssignment' id=$viewAssignmentID onclick='window.open(\"$viewButtonOnClick\", \"_blank\")'>View Assignment</button>";
        echo "<button class='submitCode' id=$submitAssignmentID onclick='location.href = \"$submit_query_string\"'>Submit Code</button>";
        echo "<button class='testProgram' id=$testAssignmentID onclick='location.href = \"$test_query_string\"'>View/Test Program</button>";
        echo "</div>";
    }
}

