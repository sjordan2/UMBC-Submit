<?php

include '../sql_functions.php';

// Create connection
$conn = new mysqli($sql_host, $sql_username, $sql_password, $sql_dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$uploaded_makefile = fopen($_FILES['makefile']['tmp_name'], 'r');
$assignment_name = $_POST['assignmentName'];
$part_name = $_POST['partName'];
$type_name = $_POST['type'];

$file_contents = fread($uploaded_makefile, filesize($_FILES['makefile']['tmp_name']));
fclose($uploaded_makefile);

$assignment_sql = $conn->real_escape_string($assignment_name);
$part_sql = $conn->real_escape_string($part_name);
$file_contents_sql = $conn->real_escape_string($file_contents);

$current_makefile_sql = "SELECT file_contents FROM Testing WHERE assignment = '$assignment_sql' AND part = '$part_sql' AND file_type = 'SAMPLE_MAKEFILE'";
$current_makefile_result = $conn->query($current_makefile_sql);
$new_makefile_sql = null;
if($current_makefile_result->num_rows === 0) {
    // Add a row to the Testing table with this makefile
    $new_makefile_sql = "INSERT INTO Testing (assignment, part, file_type, file_name, file_contents)
                         VALUES ('$assignment_sql', '$part_sql', 'SAMPLE_MAKEFILE', 'Makefile', '$file_contents_sql')";
} else {
    // Modify the existing makefile
    $new_makefile_sql = "UPDATE Testing SET file_contents = '$file_contents_sql' 
                         WHERE assignment = '$assignment_sql' AND part = '$part_sql' AND file_type = 'SAMPLE_MAKEFILE'";
}
$new_makefile_result = $conn->query($new_makefile_sql);
if($new_makefile_result === TRUE) {
    echo "Success!";
} else {
    echo "Error: " . $conn->error;
}
