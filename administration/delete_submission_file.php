<?php

$assignment_name = $_POST['assignmentName'];
$part_name = $_POST['partName'];
$file_name = $_POST['fileName'];

include '../sql_functions.php';

// Create connection
$conn = new mysqli($sql_host, $sql_username, $sql_password, $sql_dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$assignment_name_sql = $conn->real_escape_string($assignment_name);
$part_name_sql = $conn->real_escape_string($part_name);

$check_files_sql = "SELECT submission_file_name FROM SubmissionParts WHERE assignment = '$assignment_name_sql' AND part_name = '$part_name_sql'";
$check_result = $conn->query($check_files_sql);

if($check_result->num_rows === 1) {
    // If this is the case, then we want to just set the submission file name equal to null, indicating no entries.
    $update_row_sql = "UPDATE SubmissionParts
                               SET submission_file_name = NULL
                               WHERE assignment = '$assignment_name_sql'
                               AND part_name = '$part_name_sql'";
    $update_row_result = $conn->query($update_row_sql);
    if($update_row_result === True) {
        echo "SUCCESS: '" . $file_name . "' successfully removed from '" . $part_name . "'!";
    } else {
        echo "ERROR: " . $conn->error;
    }
} else {
    // Otherwise, we want to delete the whole row from the table.
    $delete_row_sql = "DELETE FROM SubmissionParts
                       WHERE assignment = '$assignment_name_sql'
                       AND part_name = '$part_name_sql'
                       AND submission_file_name = '$file_name'";
    $delete_row_result = $conn->query($delete_row_sql);
    if($delete_row_result === True) {
        echo "SUCCESS: '" . $file_name . "' successfully removed from '" . $part_name . "'!";
    } else {
        echo "ERROR: " . $conn->error;
    }
}