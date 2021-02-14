<?php

include '../sql_functions.php';

// Create connection
$conn = new mysqli($sql_host, $sql_username, $sql_password, $sql_dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$student_id = $_POST['student'];
$action = $_POST['action'];

$student_section = getUserSectionNumber($student_id, $conn);

$student_list_sql = "SELECT umbc_id FROM Users WHERE section = '$student_section' AND role = 'Student'";
$student_list_result = $conn->query($student_list_sql);
$student_array = [];
while($row = $student_list_result->fetch_assoc()) {
    array_push($student_array, $row['umbc_id']);
}
$index_of_current_student = array_search($student_id, $student_array);
$new_index = null;
$end_of_cycle = false;
if($action === "previous") {
    if($index_of_current_student === 0) {
        $end_of_cycle = true;
    } else {
        $new_index = $index_of_current_student - 1;
    }
} else {
    if($index_of_current_student === (count($student_array) - 1)) {
        $end_of_cycle = true;
    } else {
        $new_index = $index_of_current_student + 1;
    }
}
if($end_of_cycle === false) {
    echo $student_array[$new_index];
} else {
    echo "ENDOFCYCLE_" . $student_section;
}