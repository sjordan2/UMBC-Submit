<?php

include '../sql_functions.php';

// Create connection
$conn = new mysqli($sql_host, $sql_username, $sql_password, $sql_dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$assignment_name = $_POST['assignmentName'];
$assignment_name_sql = $conn->real_escape_string($assignment_name);

$grading_due_date_sql = "SELECT grading_due_date FROM Assignments WHERE assignment_name = '$assignment_name_sql'";
$assignment_part_list = "SELECT DISTINCT part_name, point_value FROM SubmissionParts WHERE assignment = '$assignment_name_sql'";
$grading_result = $conn->query($grading_due_date_sql)->fetch_assoc()['grading_due_date'];
$part_list = $conn->query($assignment_part_list);
$grading_date_object = null;
try {
    $grading_date_object = new DateTime($grading_result);
} catch(Exception $e) {
    echo "Date Time Mess Up! Message: " . $e;
}
$grading_due_date = $grading_date_object->format("l, F jS, Y, g:i:sA");

$grading_message_id = "gradingMessage_" . str_replace(" ", "~", $assignment_name);
echo "<p id=$grading_message_id style='display: none;color: red;margin-bottom: 0px'></p>";
echo "<p style='display: inline'>Current Grading Due Date for TAs: </p>";
echo "<p style='display: inline'><b>$grading_due_date</b></p><br>";
$grading_date_input = "grading_input_" . str_replace(" ", "~", $assignment_name);
echo "<input type='datetime-local' id=$grading_date_input step='1'>";
$grading_date_button = "grading_button_" . str_replace(" ", "~", $assignment_name);
echo "<button id=$grading_date_button style='margin-left: 5px' class='edit_button' onclick='setGradingDueDate(this)'>Set Grading Due Date</button><br>";
echo "<h>Set up Rubric for:&nbsp</h>";
$select_id = "gradingRubric_" . str_replace(" ", "~", $assignment_name);
echo "<select name=$select_id id=$select_id onchange='getRubricForPart(this)' style='display: inline-block; margin-right: 5px;width: 300px;font-size: medium;'>";
echo "<option disabled selected value='Select a Part...' style='width: 200px'>Select a Part...</option>";
while ($row = $part_list->fetch_assoc()) {
    $part_name = $row['part_name'];
    $point_value = $row['point_value'];
    echo "<option value='$part_name' style='width: 200px'>$part_name - $point_value points</option>";
}
echo "</select><br>";
$div_rubric = "rubricDiv_" . str_replace(" ", "~", $assignment_name);
echo "<div id=$div_rubric style='display: inline-block;border: 1px solid black;padding: 5px'>";
echo "<p style='margin: 5px'>You must select an assignment part first!</p>";
echo "</div>";
$encoded_assignment_name = htmlspecialchars(str_replace(" ", "~", $assignment_name), ENT_QUOTES);
echo "<br><button class='utility' onclick='releaseGrading(\"$encoded_assignment_name\")' style='text-align: center;margin-top: 5px'>Release Grading</button>";
echo "<button class='utility' onclick='downloadGrades(\"$encoded_assignment_name\")' style='text-align: center;margin-top: 5px;margin-left: 10px'>Download Grades</button>";
$grade_release_id = "gradeRelease_" . $encoded_assignment_name;
echo "<p class='errorMessage' id='$grade_release_id'></p>";