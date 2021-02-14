<?php

include '../sql_functions.php';

// Create connection
$conn = new mysqli($sql_host, $sql_username, $sql_password, $sql_dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$assignment_name = $_POST['assignmentName'];
$assignment_tilde = str_replace(" ", "~", $assignment_name);
$assignment_name_sql = $conn->real_escape_string($assignment_name);
$part_name = $_POST['partName'];
$part_tilde = str_replace(" ", "~", $part_name);
$part_name_sql = $conn->real_escape_string($part_name);

$get_part_rubric_sql = "SELECT id_number, line_type, line_item, point_value FROM RubricParts WHERE assignment = '$assignment_name_sql' AND part_name = '$part_name_sql'";
$check_rubric_result = $conn->query($get_part_rubric_sql);
if($check_rubric_result->num_rows == 0) {
    echo "<p style='margin: 0px'>This should never have zero rows!</p>";
} else {
    $get_part_rubric = $conn->query($get_part_rubric_sql);
    $rubric_table_id = "rubricTable_" . $assignment_tilde . "_" . $part_tilde;
    $rubricMessageID = "rubricMessage_" . $assignment_tilde;
    echo "<p class='errorMessage' id=$rubricMessageID></p>";
    echo "<table id=$rubric_table_id>";
    echo "<tr id='header_row'><th>Line Type</th><th>Line Item</th><th>Point Value</th><th>Actions</th></tr>";
    $counter = 1;
    while($row = $get_part_rubric->fetch_assoc()) { // Rubric Line ID numbers are guaranteed unique across the whole platform.
        $line_type = $row['line_type'];
        if($line_type != 2) {
            echo "<tr>";
            echo "<td>";
            $select_id = "rubricLine_" . $row['id_number'] . "_type_" . $assignment_tilde . "_" . $part_tilde;
            echo "<select name=$select_id id=$select_id style='display: inline-block; margin-right: 5px;width: 120px;font-size: medium;' onchange='checkDisabledPointValue(this)'>";
            if ($row['line_type'] == 0) {
                echo "<option selected value=0 style='width: 120px'>Graded Item</option>";
                echo "<option value=-1 style='width: 120px'>TA Note</option>";
                echo "<option value=1 style='width: 120px'>Student Note</option>";
            } else if ($row['line_type'] == 1) {
                echo "<option selected value=1 style='width: 120px'>Student Note</option>";
                echo "<option value=0 style='width: 120px'>Graded Item</option>";
                echo "<option value=-1 style='width: 120px'>TA Note</option>";
            } else {
                echo "<option selected value=-1 style='width: 120px'>TA Note</option>";
                echo "<option value=1 style='width: 120px'>Student Note</option>";
                echo "<option value=0 style='width: 120px'>Graded Item</option>";
            }
            echo "</select>";
            echo "</td>";

            echo "<td>";
            $item_id = "rubricLine_" . $row['id_number'] . "_item_" . $assignment_tilde . "_" . $part_tilde;
            $item_value = htmlspecialchars($row['line_item'], ENT_QUOTES);
            echo "<input type='text' id=$item_id value='$item_value'>";
            echo "</td>";

            echo "<td>";
            $points_id = "rubricLine_" . $row['id_number'] . "_value_" . $assignment_tilde . "_" . $part_tilde;
            $points_value = $row['point_value'];
            if ($row['line_type'] == 0) {
                echo "<input type='number' id=$points_id style='width: 75px' value=$points_value>";
            } else {
                echo "<input type='number' id=$points_id style='width: 75px' disabled>";
            }
            echo "</td>";

            echo "<td>";
            $add_id = "rubricLine_" . $row['id_number'] . "_add_" . $assignment_tilde . "_" . $part_tilde . "_" . $counter;
            $id = $row['id_number'];
            echo "<button id=$add_id class='edit_button' style='margin-right: 2px;padding: 6px 15px;font-size: large;font-weight: bold' onclick='addRubricRow(this)'>+</button>";
            $del_id = "rubricLine_" . $row['id_number'] . "_del_" . $assignment_tilde . "_" . $part_tilde . "_" . $counter;
            if ($counter == 1) {
                echo "<button id=$del_id class='delete_button_disabled' style='padding: 6px 15px;font-size: large;font-weight: bold'>-</button>";
            } else {
                echo "<button id=$del_id class='delete_button' style='padding: 6px 15px;font-size: large;font-weight: bold' onclick='deleteRubricRow(this)'>-</button>";
            }
            echo "</td>";
            echo "</tr>";
            $counter++;
        }
    }
    echo "</table>";
    $saveButtonID = "saveRubric_" . $assignment_tilde . "_" . $part_tilde;
    echo "<button id=$saveButtonID class='utility' style='float: right;margin-top: 5px;margin-bottom: 5px' onclick='saveRubric(this)'>Save Rubric</button>";
}
