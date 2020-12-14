<?php

$assignment_name = $_POST['name'];
$date_assigned = $_POST['dateCreated'];
$date_due = $_POST['dateDue'];
$max_points = $_POST['maxPoints'];
$extra_credit = $_POST['extraCredit'];

require_once '../sql_functions.php';
$conn = new mysqli($sql_host, $sql_username, $sql_password, $sql_dbname);

$newassignment_sql = "INSERT INTO Assignments (assignment_name, date_assigned, date_due, max_points, extra_credit)
                    VALUES ('$assignment_name', '$date_assigned', '$date_due',
                   '$max_points', '$extra_credit')";
if ($conn->query($newassignment_sql) === TRUE) {
    $success_message = "SUCCESS: " . $assignment_name . " has been added to the database!";
    echo $success_message;
} else {
    $error_message = "ERROR: " . $conn->error;
    echo $error_message;
}