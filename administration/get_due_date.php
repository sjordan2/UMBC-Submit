<?php
include '../sql_functions.php';

$assignment = $_POST["assignmentName"];

// Create connection
$conn = new mysqli($sql_host, $sql_username, $sql_password, $sql_dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$get_assignment_due_date_sql = "SELECT date_due FROM Assignments WHERE assignment_name = '$assignment'";
$result = $conn->query($get_assignment_due_date_sql)->fetch_assoc()['date_due'];

try {
    $due_date = new DateTime($result);
    echo $due_date->format("l, F jS, Y, g:i:sA");
} catch (Exception $e) {
    echo "ERROR: Date Time Screw Up!";
}