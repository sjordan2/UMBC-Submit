<?php
include '../sql_functions.php';

$assignment = $_POST['assignment'];
$student_id = $_POST['student_id'];

// Create connection
$conn = new mysqli($sql_host, $sql_username, $sql_password, $sql_dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$assignment_sql = $conn->real_escape_string($assignment);

$delextension_sql_check = "SELECT * FROM Extensions WHERE assignment = '$assignment_sql' AND student_id = '$student_id'";
$result = $conn->query($delextension_sql_check);
if($result->num_rows > 1) {
    echo "ERROR: There are multiple rows matching the same Extension!";
} else {
    $delextension_sql = "DELETE FROM Extensions WHERE assignment = '$assignment_sql' AND student_id = '$student_id'";
    $result = $conn->query($delextension_sql);
    if($result === TRUE) {
        $successMessage = "SUCCESS: " . $assignment . " extension for " . getFullNameFromCampusID($student_id, $conn) . " has been deleted!";
        echo $successMessage;
    } else {
        $errorMessage = "ERROR: " . $conn->error;
        echo $errorMessage;
    }
}