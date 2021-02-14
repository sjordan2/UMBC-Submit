<?php

$assignment_name = $_POST['assignment'];
$date_granted = $_POST['dateCreated'];
$student_name_id = substr(explode("(", $_POST['student'])[1], 0, -1);
$new_due_date = $_POST['dateDue'];

require_once '../sql_functions.php';
$conn = new mysqli($sql_host, $sql_username, $sql_password, $sql_dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$assignment_name_sql = $conn->real_escape_string($assignment_name);

$getCurrentAssignmentDueDate_sql = "SELECT assignment_name, date_due FROM Assignments WHERE assignment_name = '$assignment_name_sql'";
$currAssignmentDueDate = $conn->query($getCurrentAssignmentDueDate_sql)->fetch_assoc()['date_due'];

$date_currAssignment = null;
$date_proposedDue = null;
$date_current = null;
try {
    $date_currAssignment = new DateTime($currAssignmentDueDate);
    $date_proposedDue = new DateTime($new_due_date);
    $date_current = new DateTime();
} catch (Exception $e) {
    echo "ERROR: Date Time Screw Up!";
}

if($date_proposedDue < $date_current) {
    echo "ERROR: The new extension must be in the future!";
} else {
    if($date_proposedDue < $date_currAssignment) {
        echo "ERROR: The new extension must be after the current course-wide due date for " . $assignment_name . "!";
    } else {
        $getCampusID_sql = "SELECT umbc_id FROM Users WHERE umbc_name_id = '$student_name_id'";
        $queried_studentID = $conn->query($getCampusID_sql)->fetch_assoc()['umbc_id'];

        $checkIfStudentHasExtension_sql = "SELECT student_id FROM Extensions WHERE assignment = '$assignment_name_sql' AND student_id = '$queried_studentID'";
        $studentResult = $conn->query($checkIfStudentHasExtension_sql);
        if ($studentResult->num_rows > 0) {
            echo "ERROR: That student already has an extension for " . $assignment_name . "!";
        } else {
            $newextension_sql = "INSERT INTO Extensions (assignment, student_id, date_granted, new_due_date)
                        VALUES ('$assignment_name_sql', '$queried_studentID', '$date_granted', '$new_due_date')";
            if ($conn->query($newextension_sql) === TRUE) {
                $success_message = "SUCCESS: " . $_POST['student'] . " has been granted an extension for " . $assignment_name . "!";
                echo $success_message;
            } else {
                $error_message = "ERROR: " . $conn->error;
                echo $error_message;
            }
        }
    }
}