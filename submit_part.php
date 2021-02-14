<?php
//header("Access-Control-Allow-Origin: *");
require_once 'sql_functions.php';
//require_once 'config.php';
//require_once $phpcas_path . 'CAS.php';
//phpCAS::setDebug();
//phpCAS::setVerbose(true);
//phpCAS::client(CAS_VERSION_2_0, $cas_host, $cas_port, $cas_context);
//phpCAS::setNoCasServerValidation(); // FIX THIS BUCKO
//phpCAS::forceAuthentication();

//$UNENROLLED_STUDENT = false;
//
//$conn = new mysqli($sql_host, $sql_username, $sql_password, $sql_dbname);
//
//// Check connection
//if ($conn->connect_error) {
//    die("Connection failed: " . $conn->connect_error);
//}
//
//echo phpCAS::getUser();

$student_id = $_POST['student_id'];
$assignment_name = $_POST['assignment_name'];
$part_name = $_POST['part_name'];
$given_alpha_num_key = $_POST['alpha_num_key'];

$conn = new mysqli($sql_host, $sql_username, $sql_password, $sql_dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$student_id_ank = getAlphaNumKey($student_id, $conn);

if($given_alpha_num_key !== $student_id_ank) { // Then something is suspicious, so decline the submission
    echo "ERROR: Authentication keys do not match! This incident has been reported to the course administrators.";
    exit();
}

$assignment_name_sql = $conn->real_escape_string($assignment_name);
$part_name_sql = $conn->real_escape_string($part_name);

$current_date = null;
try {
    $current_date = new DateTime();
} catch (Exception $e) {
    echo "ERROR: Date Time Screw-Up! Contact a Course Administrator! Message: " . $e;
}

$current_server_date = $current_date->format('Y-m-d H:i:s');
$currentSubmissionNumber = getCurrentSubmissionNumber($student_id, $assignment_name, $part_name, $conn);

foreach($_FILES as &$submissionFile) {
    $filename_like = "%" . $submissionFile['name'] . "%";
    $filename_verification_sql = "SELECT submission_file_name FROM SubmissionParts WHERE assignment = '$assignment_name_sql' AND part_name = '$part_name_sql' AND submission_file_name LIKE '$filename_like'";
    $verification_result = $conn->query($filename_verification_sql);
    if($verification_result->num_rows == 0) {
        echo "ERROR: Submitted File's Name does not match the assignment configuration! Did you change the Javascript code?";
        exit();
    }
    $submission_name = $submissionFile['name'];
    $submitted_file = fopen($submissionFile['tmp_name'], 'r');
    $file_contents = $conn->real_escape_string(fread($submitted_file, filesize($submissionFile['tmp_name'])));
    $submit_file_sql = "INSERT INTO Submissions (student_id, assignment, assignment_part, submission_number, date_submitted, submission_name, submission_contents)
                    VALUES ('$student_id', '$assignment_name_sql', '$part_name_sql',
                        '$currentSubmissionNumber', '$current_server_date', '$submission_name', '$file_contents')";
    $submit_file_result = $conn->query($submit_file_sql);
    if($submit_file_result === false) {
        echo "ERROR: Submission Screw-Up! Contact a Course Administrator! Message: " . $conn->error;
        exit();
    }
}
// If it gets here, then everything went okily-dokily!
echo "SUCCESS: '" . $part_name . "' was successfully submitted on " . $current_date->format('n/j/o') . " at " . $current_date->format('g:i:s A') . "!\nBe sure to view and test your code!";