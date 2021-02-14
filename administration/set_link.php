<?php

include '../sql_functions.php';

// Create connection
$conn = new mysqli($sql_host, $sql_username, $sql_password, $sql_dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$assignment_name = $_POST['assignmentName'];
$assignment_tilde = str_replace(" ", "~", $assignment_name);
$new_link = $_POST['newLink'];
$assignment_name_sql = $conn->real_escape_string($assignment_name);

$link_sql = "UPDATE Assignments 
            SET document_link = '$new_link'
            WHERE assignment_name = '$assignment_name_sql'";
$link_result = $conn->query($link_sql);

if ($link_result === TRUE) {
    $successMessage = "SUCCESS: Document Link for " . $assignment_name . " successfully set!";
    echo $successMessage;
} else {
    $errorMessage = "ERROR: " . $conn->error;
    echo $errorMessage;
}