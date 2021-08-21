<?php

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

$assignment_name = $_POST['assignmentName'];
$short_id = $_POST['shortID'];
$column_id = $_POST['columnID'];

include '../sql_functions.php';

// Create connection
$conn = new mysqli($sql_host, $sql_username, $sql_password, $sql_dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$assignment_name_sql = $conn->real_escape_string($assignment_name);

chmod(".", 0755);


$file_path = $short_id . ".csv";
$grade_file = fopen($file_path, "w");

$header_line = "Username," . $short_id . "|" . $column_id . "\n";
fwrite($grade_file, $header_line);

$get_student_list_sql = "SELECT umbc_id, umbc_name_id FROM Users WHERE role = 'Student'";
$students_list = $conn->query($get_student_list_sql);
while($row = $students_list->fetch_assoc()) {
    $grade_array = getStudentScoreForAssignment($row['umbc_id'], $assignment_name, $conn);
    $grade_line = $row['umbc_name_id'] . "," . $grade_array[0] . "\n";
    fwrite($grade_file, $grade_line);
}

fclose($grade_file);
echo realpath($file_path);