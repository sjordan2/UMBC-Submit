<?php

include '../sql_functions.php';

// Create connection
$conn = new mysqli($sql_host, $sql_username, $sql_password, $sql_dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$assignment_name = $_POST['assignmentName'];
$part_name = $_POST['partName'];
$rubric_data = json_decode($_POST['rubricData'], true);
$assignment_name_sql = $conn->real_escape_string($assignment_name);
$part_name_sql = $conn->real_escape_string($part_name);

$part_max_points_sql = "SELECT point_value FROM SubmissionParts WHERE assignment = '$assignment_name_sql' AND part_name = '$part_name_sql'";
$point_value = $conn->query($part_max_points_sql)->fetch_assoc()['point_value'];
$point_total = 0;
$last_id = null;
foreach($rubric_data as $rubric_row) {
    $point_total = $point_total + $rubric_row['line_value'];
    $last_id = $rubric_row['row_id'];
}
if($point_total != $point_value) {
    echo "ERROR: Points must equal the number of points for this part (" . $point_value . ")!";
} else {
    foreach ($rubric_data as $rubric_row) {
        $id_num = $rubric_row['row_id'];
        $line_type = $rubric_row['line_type'];
        $line_item = $conn->real_escape_string($rubric_row['line_item']);
        $point_value = $rubric_row['line_value'];
        $check_row_exists_sql = "SELECT id_number FROM RubricParts WHERE assignment = '$assignment_name_sql' AND part_name = '$part_name_sql' AND id_number = '$id_num' AND line_type != '2'";
        $check_row_result = $conn->query($check_row_exists_sql);
        if ($check_row_result->num_rows == 1) { // Then, this line was simply changed since the last database modification, so we just update it here in place
            $update_row_sql = null;
            if($line_type == '0') {
                $update_row_sql = "UPDATE RubricParts SET line_type = '$line_type', line_item = '$line_item', point_value = '$point_value' WHERE id_number = '$id_num'";
            } else {
                $update_row_sql = "UPDATE RubricParts SET line_type = '$line_type', line_item = '$line_item', point_value = NULL WHERE id_number = '$id_num'";
            }
            $update_row_result = $conn->query($update_row_sql);
            if ($update_row_result === false) {
                echo "ERROR: " . $conn->error;
                exit();
            }
        } else { // Otherwise, this row needs to be created, or needs to be pushed back
            $check_row_id_sql = "SELECT id_number FROM RubricParts WHERE id_number = '$id_num'";
            $check_id_result = $conn->query($check_row_id_sql);
            if ($check_id_result->num_rows == 1) { // Then, the row ID number exists, but it is not for this part or assignment, OR it is the reserved line for grader comments. Thus, we push it back.
                $push_back_rows_sql = "UPDATE RubricParts SET id_number = id_number + 1 WHERE id_number >= '$id_num' ORDER BY id_number DESC";
                $push_back_rows_result = $conn->query($push_back_rows_sql);
                if ($push_back_rows_result === false) {
                    echo "ERROR: " . $conn->error;
                    exit();
                }
            }
            // Regardless, when it gets to this point, there should not be a line with the 'id_num' in the database, so we insert it.
            $insert_row_sql = null;
            if($line_type == '0') {
                $insert_row_sql = "INSERT INTO RubricParts (id_number, assignment, part_name, line_type, line_item, point_value)
                  VALUES ('$id_num', '$assignment_name_sql', '$part_name_sql', '$line_type', '$line_item', '$point_value')";
            } else {
                $insert_row_sql = "INSERT INTO RubricParts (id_number, assignment, part_name, line_type, line_item)
                  VALUES ('$id_num', '$assignment_name_sql', '$part_name_sql', '$line_type', '$line_item')";
            }
            $insert_row_result = $conn->query($insert_row_sql);
            if ($insert_row_result === false) {
                echo "ERROR: " . $conn->error;
                exit();
            }
        }
    }
    $delete_row_sql = "DELETE FROM RubricParts WHERE assignment = '$assignment_name_sql' AND part_name = '$part_name_sql' AND id_number > '$last_id' AND line_type != '2'";
    $delete_row_result = $conn->query($delete_row_sql);
    if ($delete_row_result === false) {
        echo "ERROR: " . $conn->error;
        exit();
    }

    // If it gets here, then it is a successful update :)
    echo "SUCCESS: Rubric successfully updated for " . $part_name . "!";
}