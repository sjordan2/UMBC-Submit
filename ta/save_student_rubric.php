<?php

include '../sql_functions.php';

// Create connection
$conn = new mysqli($sql_host, $sql_username, $sql_password, $sql_dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$assignment_name = str_replace("~", " ", $_POST["assignment"]);
$part_name = str_replace("~", " ", $_POST["part"]);
$student_id = $_POST['student'];
$rubric_data = json_decode($_POST['rubricData'], true);
$grader_comments = $_POST['comments'];
$assignment_sql = $conn->real_escape_string($assignment_name);
$part_sql = $conn->real_escape_string($part_name);

// Update the individual line items with the grades received
foreach($rubric_data as $rubric_row) {
    $points_received = $rubric_row['points_received'];
    $max_points = $rubric_row['point_value'];
    $line_item = $conn->real_escape_string($rubric_row['line_item']);
    if($points_received !== "") {
        $update_student_grade_sql = "UPDATE Rubrics SET points_received = '$points_received' WHERE point_value = '$max_points' AND line_item = '$line_item' AND line_type = '0' AND assignment = '$assignment_sql' AND part_name = '$part_sql' AND student_id = '$student_id'";
    } else {
        $update_student_grade_sql = "UPDATE Rubrics SET points_received = NULL WHERE point_value = '$max_points' AND line_item = '$line_item' AND line_type = '0' AND assignment = '$assignment_sql' AND part_name = '$part_sql' AND student_id = '$student_id'";
    }
    $update_student_grade_result = $conn->query($update_student_grade_sql);
    if($update_student_grade_result === false) {
        echo "ERROR: " . $conn->error;
        exit();
    }
}

// Update the comment from the grader to reflect whatever was put in the box
$update_grader_comment_sql = "UPDATE Rubrics SET grader_comments = '$grader_comments' WHERE line_type = '2' AND assignment = '$assignment_sql' AND part_name = '$part_sql' AND student_id = '$student_id'";
$update_grader_comment_result = $conn->query($update_grader_comment_sql);
if($update_grader_comment_result === false) {
    echo "ERROR: " . $conn->error;
    exit();
}