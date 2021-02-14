<?php

include '../sql_functions.php';

// Create connection
$conn = new mysqli($sql_host, $sql_username, $sql_password, $sql_dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$assignment_name = $_POST['assignmentName'];
$part_to_delete = $_POST['partName'];

$assignment_name_sql = $conn->real_escape_string($assignment_name);
$part_name_sql = $conn->real_escape_string($part_to_delete);

$delete_part_sql = "DELETE FROM SubmissionParts WHERE assignment = '$assignment_name_sql' AND part_name = '$part_name_sql'";
$delete_result = $conn->query($delete_part_sql);

if($delete_result === TRUE) {
    $successMessage = "SUCCESS: " . $part_to_delete . " was successfully deleted from " . $assignment_name . "!";
    echo $successMessage;
} else {
    $errorMessage = "ERROR: " . $conn->error;
    echo $errorMessage;
}