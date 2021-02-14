<?php
include 'sql_functions.php';

$studentCampusID = $_POST["user"];
$given_alphanumeric = $_POST["alpha_num_key"];

// Create connection
$conn = new mysqli($sql_host, $sql_username, $sql_password, $sql_dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$student_ank = getAlphaNumKey($studentCampusID, $conn);

if($student_ank !== $given_alphanumeric) {
    echo "ERROR: Authentication keys do not match!\nThis incident has been reported to the course administrators.";
    exit();
}

echo "<p style='font-size: xx-large;margin: 0px;text-align: center'>List of Past Assignments</p>";
$assignment_list_sql = "SELECT assignment_name, grades_released FROM Assignments";
$assignment_result_list = $conn->query($assignment_list_sql);

$current_date = null;
try {
    $current_date = new DateTime();
} catch(Exception $e) {
    echo "Date Time Error - Retrieve Student Grades: " . $e;
}

while($row = $assignment_result_list->fetch_assoc()) {
    $assignment_due_date = getStudentDueDateForAssignment($studentCampusID, $row["assignment_name"], $conn);
    if($assignment_due_date < $current_date) { // If it is past the student's due date for this assignment, then show the box
        echo "<div style='display: block;'>";
        if($row["grades_released"] === "1") {
            $total_score_array = getStudentScoreForAssignment($studentCampusID, $row["assignment_name"], $conn);
            echo "<div style='width: 54%;display: inline-block;border: none'>";
            echo "<p style='text-align: center;font-size: xx-large;margin: 5px;display: inline-block'>" . $row['assignment_name'] . "</p>";
            echo "</div>";
            echo "<div style='width: 31.7%;display: inline-block;border: none'>";
            echo "<p style='text-align: center;font-size: xx-large;margin: 5px;display: inline-block'>Total Score: <b>$total_score_array[0]</b> / $total_score_array[1]</p>";
            echo "</div>";
            echo "<div style='width: 12%;display: inline-block;border: none;margin-left: 1.5%'>";
            $buttonID = "completedAssignment_" . htmlspecialchars(str_replace(" ", "~", $row['assignment_name']), ENT_QUOTES);
            echo "<button id='$buttonID' class='viewAssignment' style='margin: 5px' onclick='viewRubricAsStudent(this)'>View Grading Rubric</button>";
            echo "</div>";
        } else {
            echo "<div style='width: 54%;display: inline-block;border: none'>";
            echo "<p style='text-align: center;font-size: xx-large;margin: 5px 20px 5px 5px;display: inline-block'>" . $row['assignment_name'] . "</p>";
            echo "</div>";
            echo "<div style='width: 44.5%;display: inline-block;border: none'>";
            echo "<p style='font-size: x-large;margin: 5px;display: inline-block;color: #be0000;font-weight: bold'>Grades have not been released yet! Please check back later!</p>";
            echo "</div>";
        }
        echo "</div>";
    }
}
$assignment_div_list = "SELECT assignment_name, max_points, extra_credit, date_assigned, date_due, document_link FROM Assignments";

$result_list = $conn->query($assignment_div_list);
$currentDate = null;
try {
    $currentDate = new DateTime();
} catch(Exception $e) {
    echo "Date Time Error: " . $e;
}

exit();
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

