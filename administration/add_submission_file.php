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
if($check_result->num_rows == 0) {
    echo "ERROR: The number of rows should never be zero!";
} else {
    if($check_result->num_rows == 1) {
        // Check if the submission file name is empty or not
        $check_result_name = $check_result->fetch_assoc()['submission_file_name'];
        if (empty($check_result_name)) {
            // If it is empty, then we want to update the row so that it has the new submission file name.
            $update_row_sql = "UPDATE SubmissionParts
                               SET submission_file_name = '$file_name'
                               WHERE assignment = '$assignment_name_sql'
                               AND part_name = '$part_name_sql'";
            $update_row_result = $conn->query($update_row_sql);
            if($update_row_result == True) {
                echo "SUCCESS: '" . $file_name . "' was added to '" . $part_name . "'!";
            } else {
                echo "ERROR: " . $conn->error;
            }
        } else {
            //If it is NOT empty, then we want to simply add a new row.
            $new_file_sql = "INSERT INTO SubmissionParts (assignment, part_name, submission_file_name) 
                VALUES ('$assignment_name_sql', '$part_name_sql', '$file_name')";
            $new_file_result = $conn->query($new_file_sql);
            if($new_file_result == True) {
                echo "SUCCESS: '" . $file_name . "' was added to '" . $part_name . "'!";
            } else {
                echo "ERROR: " . $conn->error;
            }
        }
    } else {
        $new_file_sql = "INSERT INTO SubmissionParts (assignment, part_name, submission_file_name) 
                VALUES ('$assignment_name_sql', '$part_name_sql', '$file_name')";
        $new_file_result = $conn->query($new_file_sql);
        if($new_file_result == True) {
            echo "SUCCESS: '" . $file_name . "' was added to '" . $part_name . "'!";
        } else {
            echo "ERROR: " . $conn->error;
        }
    }
}