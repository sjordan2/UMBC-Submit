<?php

$assignment_name = $_POST['assignmentName'];
$newPartName = $_POST['partName'];
$pointValue = $_POST['pointValue'];

include '../sql_functions.php';

// Create connection
$conn = new mysqli($sql_host, $sql_username, $sql_password, $sql_dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$assignment_name_sql = $conn->real_escape_string($assignment_name);
$new_part_sql = $conn->real_escape_string($newPartName);

$newPart_sql = "INSERT INTO SubmissionParts (assignment, part_name, point_value) 
                VALUES ('$assignment_name_sql', '$new_part_sql', '$pointValue')";
$newPart_result = $conn->query($newPart_sql);

$newRubricBase = "INSERT INTO RubricParts (assignment, part_name, line_type, line_item, point_value)
                  VALUES ('$assignment_name_sql', '$new_part_sql', '0', 'Example Rubric Item', '0')";
$newRubricBase_result = $conn->query($newRubricBase);

$newRubricComment = "INSERT INTO RubricParts (assignment, part_name, line_type, line_item, point_value)
                     VALUES('$assignment_name_sql', '$new_part_sql', '2', NULL, NULL)";

if($newPart_result === True and $newRubricBase_result === True) {
    echo "SUCCESS: Part '" . $newPartName . "' added to " . $assignment_name . "!";
} else {
    echo "ERROR: " . $conn->error;
}
