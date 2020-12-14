<?php

include '../sql_functions.php';

// Create connection
$conn = new mysqli($sql_host, $sql_username, $sql_password, $sql_dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$STUDENT_LIST = [];

$student_sql = "SELECT firstname, lastname, umbc_name_id, umbc_id FROM Users WHERE status = 'Active'";
$student_list = $conn->query($student_sql);
if ($student_list->num_rows > 0) {
    // output data of each row
    while($row = $student_list->fetch_assoc()) {
        $student_string = $row["firstname"] . " " . $row["lastname"] . " (" . $row["umbc_name_id"] . ")";
        array_push($STUDENT_LIST, $student_string);
    }
}
echo json_encode($STUDENT_LIST);