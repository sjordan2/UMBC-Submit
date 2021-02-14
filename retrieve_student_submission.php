<?php
include 'sql_functions.php';

$assignmentName = $_POST["assignment"];
$partName = $_POST["part"];
$studentCampusID = $_POST["campus_id"];
$fileName = $_POST["fileName"];
$submissionNumber = $_POST["submission_number"];
$given_alphanumeric = $_POST["alpha_num_key"];

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
        echo "ERROR: Authentication keys do not match!\nThis incident has been reported to the course administrators.";
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
$get_file_sql = "SELECT submission_contents FROM Submissions WHERE assignment = '$assignmentName_sql' AND assignment_part = '$partName_sql' AND student_id = '$studentCampusID' AND submission_number = '$right_sub_number' AND submission_name = '$fileName'";
$file_results = $conn->query($get_file_sql);
if($file_results->num_rows != 1) {
    echo "ERROR: Zero or multiple submissions detected! Contact an administrator!";
} else {
    echo $file_results->fetch_assoc()['submission_contents'];
}