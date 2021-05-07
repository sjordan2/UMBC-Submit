<?php

include '../sql_functions.php';

// Create connection
$conn = new mysqli($sql_host, $sql_username, $sql_password, $sql_dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$uploaded_input_file = fopen($_FILES['input_file']['tmp_name'], 'r');
$assignment_name = $_POST['assignmentName'];
$part_name = $_POST['partName'];
$type_name = $_POST['type'];
$file_name = $_POST['fileName'];

$file_contents = fread($uploaded_input_file, filesize($_FILES['input_file']['tmp_name']));
fclose($uploaded_input_file);

$assignment_sql = $conn->real_escape_string($assignment_name);
$part_sql = $conn->real_escape_string($part_name);
$file_contents_sql = $conn->real_escape_string($file_contents);

// Check if the file already exists
$check_input_file_sql = "SELECT id_number FROM Testing WHERE assignment = '$assignment_sql' AND part = '$part_sql' AND file_type = 'SAMPLE_IO' AND file_name = '$file_name'";
$check_input_file_result = $conn->query($check_input_file_sql);
$new_input_file_sql = null;
if($check_input_file_result->num_rows === 0) {
    // If the file doesn't exist, add it to the database
    $new_input_file_sql = "INSERT INTO Testing (assignment, part, file_type, file_name, file_contents)
                         VALUES ('$assignment_sql', '$part_sql', 'SAMPLE_IO', '$file_name', '$file_contents_sql')";
} else {
    // Otherwise, update the existing file
    $new_input_file_sql = "UPDATE Testing SET file_contents = '$file_contents_sql' 
                         WHERE assignment = '$assignment_sql' AND part = '$part_sql' AND file_type = 'SAMPLE_IO' AND file_name = '$file_name'";
}
$new_input_file_result = $conn->query($new_input_file_sql);
if($new_input_file_result === TRUE) {
    echo "Success!";
} else {
    echo "Error: " . $conn->error;
}
