<?php

require_once '../sql_functions.php';
$conn = new mysqli($sql_host, $sql_username, $sql_password, $sql_dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$assignment_name_to_insert = $conn->real_escape_string($_POST['name']);
$assignment_name = $_POST['name'];
$date_assigned = $_POST['dateCreated'];
$date_due = $_POST['dateDue'];
$max_points = $_POST['maxPoints'];
$extra_credit = $_POST['extraCredit'];

$dateDueObject = null;
try{
    $dateDueObject = new DateTime($date_due);
} catch(Exception $e) {
    echo "Date Time Error! Assignment Creation! Message: " . $e;
}

$dateDueObject->modify("+1 week"); // Default grading due date is one week after assignment due date for students.
$gradingDueDate = $dateDueObject->format("Y-m-d H:i:s");

$newassignment_sql = "INSERT INTO Assignments (assignment_name, date_assigned, date_due, max_points, extra_credit, grading_due_date)
                    VALUES ('$assignment_name_to_insert', '$date_assigned', '$date_due',
                   '$max_points', '$extra_credit', '$gradingDueDate')";
if ($conn->query($newassignment_sql) === TRUE) {
    $success_message = "SUCCESS: " . $assignment_name . " has been added to the database!";
    echo $success_message;
} else {
    $error_message = "ERROR: " . $conn->error;
    echo $error_message;
}