<?php

include '../sql_functions.php';

// Create connection
$conn = new mysqli($sql_host, $sql_username, $sql_password, $sql_dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$assignment_name = $_POST['assignment'];
$part_name = $_POST['part'];
$action = $_POST['action'];
$student_id = $_POST['student'];

$student_section = getUserSectionNumber($student_id, $conn);

$assignment_sql = $conn->real_escape_string(str_replace("~", " ", $assignment_name));

$part_list_sql = "SELECT DISTINCT part_name FROM SubmissionParts WHERE assignment = '$assignment_sql'";
$part_list_result = $conn->query($part_list_sql);
$part_array = [];
while($row = $part_list_result->fetch_assoc()) {
    array_push($part_array, $row['part_name']);
}
$index_of_current_part = array_search(str_replace("~", " ", $part_name), $part_array);
//echo $index_of_current_part;
$new_index = null;
$end_of_cycle = false;
if($action === "previous") {
    if($index_of_current_part === 0) {
        $end_of_cycle = true;
    } else {
        $new_index = $index_of_current_part - 1;
    }
} else {
    if($index_of_current_part === (count($part_array) - 1)) {
        $end_of_cycle = true;
    } else {
        $new_index = $index_of_current_part + 1;
    }
}
//echo $new_index;
if($end_of_cycle === false) {
    echo htmlspecialchars(str_replace(" ", "~", $part_array[$new_index]));
} else {
    echo "ENDOFCYCLE_" . $student_section;
}