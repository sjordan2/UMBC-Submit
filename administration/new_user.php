<?php
include '../sql_functions.php';

$student_first_name = $_POST['fname'];
$student_last_name = $_POST['lname'];
$student_campus_id = $_POST['cID'];
$student_name_id = $_POST['nID'];
$student_role = $_POST['role'];
$student_discussion = $_POST['disc'];


// Create connection
$conn = new mysqli($sql_host, $sql_username, $sql_password, $sql_dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$newstudent_sql = "INSERT INTO Users (umbc_name_id, umbc_id, firstname, lastname, section, role)
                    VALUES ('$student_name_id', '$student_campus_id', '$student_first_name',
                   '$student_last_name', '$student_discussion', '$student_role')";

if ($conn->query($newstudent_sql) === TRUE) {
    $success_message = "SUCCESS: " . $student_first_name . " " . $student_last_name
        . " (" . $student_name_id . ") has been added to the database!";
    echo $success_message;
} else {
    $error_message = "ERROR: " . $conn->error;
    echo $error_message;
}