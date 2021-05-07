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

$assignment_sql = $conn->real_escape_string($assignment_name);
$part_sql = $conn->real_escape_string($part_name);

$fetch_file_sql = "SELECT file_contents FROM Testing WHERE assignment = '$assignment_sql' AND part = '$part_sql' AND file_name = '$file_name'";
$fetch_file_result = $conn->query($fetch_file_sql);

if($fetch_file_result) {
    if($fetch_file_result->num_rows === 0) {
        echo "ERROR: Query result has no rows!";
    } else {
        $file_contents = $fetch_file_result->fetch_assoc()['file_contents'];
        echo "<div id='io_contents_div' style='overflow-x: scroll;background-color: black;color: white;width: 100%;max-height: 250px;overflow-y: scroll;'>";
        echo "<pre style='width: 5px'>" . $file_contents . "</pre>";
        echo "</div>";
    }
} else {
    echo "ERROR: " . $conn->error;
}