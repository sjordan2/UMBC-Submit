<?php
include '../sql_functions.php';

$student_id = $_POST['student'];
$assignment = $_POST['assignment'];
$new_due_date = $_POST['newDueDate'];

// Create connection
$conn = new mysqli($sql_host, $sql_username, $sql_password, $sql_dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$getCurrentAssignmentDueDate_sql = "SELECT assignment_name, date_due FROM Assignments WHERE assignment_name = '$assignment'";
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
    if ($date_proposedDue < $date_currAssignment) {
        echo "ERROR: The new extension must be after the current course-wide due date for " . $assignment . "!";
    } else {
        $edit_extension_sql = "UPDATE Extensions 
                        SET new_due_date = '$new_due_date' 
                        WHERE assignment = '$assignment' AND student_id = '$student_id'";
        $result = $conn->query($edit_extension_sql);

        if ($result === TRUE) {
            $successMessage = "SUCCESS: " . $assignment . " extension for " . getFullNameFromCampusID($student_id, $conn) . " was successfully edited!";
            echo $successMessage;
        } else {
            $errorMessage = "ERROR: " . $conn->error;
            echo $errorMessage;
        }
    }
}