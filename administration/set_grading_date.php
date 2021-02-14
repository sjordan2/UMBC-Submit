<?php

include '../sql_functions.php';

// Create connection
$conn = new mysqli($sql_host, $sql_username, $sql_password, $sql_dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$assignment_name = $_POST['assignmentName'];
$dueDate = $_POST['newDueDate'];
$dateTimeObject = null;
$currAssignmentDateTimeObject = null;

$assignment_name_sql = $conn->real_escape_string($assignment_name);

$check_assignment_date_sql = "SELECT date_due FROM Assignments WHERE assignment_name = '$assignment_name_sql'";
$check_assignment_result = $conn->query($check_assignment_date_sql)->fetch_assoc()['date_due'];

try {
    $dateTimeObject = new DateTime($dueDate);
    $currAssignmentDateTimeObject = new DateTime($check_assignment_result);
} catch(Exception $e) {
    echo "ERROR: Date Time Screw Up! Message: " . $e;
}

if($dateTimeObject < $currAssignmentDateTimeObject) {
    echo "ERROR: The grading due date must be after the current assignment due date!";
} else {

    $formatted_date = $dateTimeObject->format("Y-m-d H:i:s");
    $update_date_sql = "UPDATE Assignments
                        SET grading_due_date = '$formatted_date'
                        WHERE assignment_name = '$assignment_name_sql'";
    $update_date_result = $conn->query($update_date_sql);
    if ($update_date_result === TRUE) {
        $successMessage = "SUCCESS: Grading due date for '" . $assignment_name . "' was successfully edited!";
        echo $successMessage;
    } else {
        $errorMessage = "ERROR: " . $conn->error;
        echo $errorMessage;
    }
}