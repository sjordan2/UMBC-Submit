<?php
include '../sql_functions.php';

$assignment_to_delete = str_replace("~", " ", $_POST['assignment_name']);

// Create connection
$conn = new mysqli($sql_host, $sql_username, $sql_password, $sql_dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$assignment_to_delete_sql = $conn->real_escape_string($assignment_to_delete);

$delassignment_sql_check = "SELECT * FROM Assignments WHERE assignment_name = '$assignment_to_delete_sql';";
$result = $conn->query($delassignment_sql_check);
if($result->num_rows > 1) {
    echo "ERROR: There are multiple rows matching the same Assignment!";
} else {
    $delassignment_sql = "DELETE FROM Assignments WHERE assignment_name = '$assignment_to_delete_sql';";
    $result = $conn->query($delassignment_sql);
    if($result === TRUE) {
        $successMessage = "SUCCESS: Assignment '" . $assignment_to_delete . "' successfully deleted!";
        echo $successMessage;
    } else {
        $errorMessage = "ERROR: " . $conn->error;
        echo $errorMessage;
    }
}