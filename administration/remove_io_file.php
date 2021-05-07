<?php

include '../sql_functions.php';

// Create connection
$conn = new mysqli($sql_host, $sql_username, $sql_password, $sql_dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// MAKE THIS WORK WITH TEST INPUT/OUTPUT AS WELL!

$assignment_name = $_POST['assignmentName'];
$part_name = $_POST['partName'];
$file_name = $_POST['fileName'];
$method = $_POST['method'];

$assignment_sql = $conn->real_escape_string($assignment_name);
$part_sql = $conn->real_escape_string($part_name);

$delete_io_file_sql = "DELETE FROM Testing WHERE assignment = '$assignment_sql' AND part = '$part_sql' AND file_type = 'SAMPLE_IO' AND file_name = '$file_name'";
$delete_io_file_result = $conn->query($delete_io_file_sql);

if($delete_io_file_result === TRUE) {
    echo "Success!";
} else {
    echo "ERROR: " . $conn->error;
}
