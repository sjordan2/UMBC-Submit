<?php
include 'sql_functions.php';

$student_to_delete = $_POST['student_id'];

// Create connection
$conn = new mysqli($sql_host, $sql_username, $sql_password, $sql_dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$delstudent_sql_check = "SELECT * FROM Students WHERE umbc_id = '$student_to_delete';";
$result = $conn->query($delstudent_sql_check);
if($result->num_rows > 1) {
    echo "ERROR: There are multiple rows matching the same UMBC Name ID!";
} else {
    $delstudent_sql = "DELETE FROM Students WHERE umbc_id = '$student_to_delete';";
    $result = $conn->query($delstudent_sql);
    if($result === TRUE) {
        $successMessage = "SUCCESS: Student '" . $student_to_delete . "' successfully deleted!";
        echo $successMessage;
    } else {
        $errorMessage = "ERROR: " . $conn->error;
        echo $errorMessage;
    }
}

