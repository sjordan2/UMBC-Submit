<?php
include 'sql_functions.php';

$assignmentName = $_POST["assignment"];
$partName = $_POST["part"];
$studentCampusID = $_POST["campus_id"];
$given_alphanumeric = $_POST["alpha_num_key"];
$submissionNumber = $_POST["submission_number"];
$method = $_POST["method"];

// Create connection
$conn = new mysqli($sql_host, $sql_username, $sql_password, $sql_dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$student_ank = getAlphaNumKey($studentCampusID, $conn);

if($student_ank !== $given_alphanumeric) {
    // If it isn't a TA grading a student's submission
    if(explode("?", explode("/", $_SERVER['HTTP_REFERER'])[5])[0] !== "grade_student_submissions.php") {
        echo "ERROR: Authentication keys do not match! This incident has been reported to the course administrators.";
        exit();
    }
}

$assignmentName_sql = $conn->real_escape_string($assignmentName);
$partName_sql = $conn->real_escape_string($partName);

$right_sub_number = null;
if($submissionNumber === "latest") {
    $right_sub_number = getCurrentSubmissionNumber($studentCampusID, $assignmentName, $partName, $conn) - 1;
} else {
    $right_sub_number = $submissionNumber;
}

// Check if they even have a submission in the first place
$check_submission_sql = "SELECT id_number FROM Submissions WHERE assignment = '$assignmentName_sql' AND assignment_part = '$partName_sql' AND student_id = '$studentCampusID'";
$check_result = $conn->query($check_submission_sql);
if($check_result->num_rows == 0) {
    echo "ERROR: You have not submitted this part yet! You can do so on the Submissions page!";
} else {
    echo "<div id='buttonsDiv' style='overflow-x: scroll;width: 99%;margin-bottom: 0;border: none'>";
    $get_files_sql = "SELECT submission_name FROM Submissions WHERE assignment = '$assignmentName_sql' AND assignment_part = '$partName_sql' AND student_id = '$studentCampusID' AND submission_number = '$right_sub_number'";
    $files_result_buttons = $conn->query($get_files_sql);
    while($row = $files_result_buttons->fetch_assoc()) {
        $file_name = $row['submission_name'];
        $button_id = "nameTab_" . $file_name;
        echo "<button class='sub_file_tab' onclick='selectCodeTab(\"$assignmentName\", \"$partName\", \"$studentCampusID\", \"$file_name\", \"$right_sub_number\")' id=$button_id>$file_name</button>";
    }
    echo "</div>";
    echo "<div style='border: none;display: none;height: 91%;overflow: scroll;margin-top: 0' id='codeContentDiv'>";
    echo "</div>";
}
