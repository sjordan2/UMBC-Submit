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

$link_id = "link_input_" . htmlspecialchars(str_replace(" ", "~", $assignment_name), ENT_QUOTES);
$link_button = "link_button_" . htmlspecialchars(str_replace(" ", "~", $assignment_name), ENT_QUOTES);
$linkMessage_id = "link_message_" . htmlspecialchars(str_replace(" ", "~", $assignment_name), ENT_QUOTES);

echo "<p id=$linkMessage_id style='display: none;color: red;margin-bottom: 0px'></p>";
echo "<p style='display: inline'>Set Document Link: </p>";
echo "<input type='text' id=$link_id placeholder='Enter document link here...' style='display: inline;height: 25px;font-size: 18px;' size='30'>";
echo "<button id=$link_button style='margin-left: 5px' class='edit_button' onclick='setDocumentLink(this)'>Set Link</button><br>";
echo "<p style='font-size: x-large;margin-top: 0px;display: inline'>Configure Submission Parts and Files</p><br>";

$assignment_part_list = "SELECT DISTINCT part_name, point_value FROM SubmissionParts WHERE assignment = '$assignment_name_sql'";
$part_list = $conn->query($assignment_part_list);
$div_left = "subPartsDiv_" . htmlspecialchars(str_replace(" ", "~", $assignment_name), ENT_QUOTES);
echo "<div id=$div_left style='display: inline-block;border: 1px solid black;padding: 5px;margin: 5px'>";
$create_part_text = "createPartName_" . htmlspecialchars(str_replace(" ", "~", $assignment_name), ENT_QUOTES);
$create_part_value = "createPartValue_" . htmlspecialchars(str_replace(" ", "~", $assignment_name), ENT_QUOTES);
$create_part_id = "createPartButton_" . htmlspecialchars(str_replace(" ", "~", $assignment_name), ENT_QUOTES);
$part_message = "partMessage_" . htmlspecialchars(str_replace(" ", "~", $assignment_name), ENT_QUOTES);
echo "<p class='errorMessage' id=$part_message></p>";
echo "<input type='text' id=$create_part_text placeholder='Enter part name here...' style='display: inline;height: 25px;font-size: 18px;' size='30'>";
echo "<input type='number' id=$create_part_value placeholder='Point Value' style='display: inline;height: 25px;font-size: 18px;margin-left: 2px' size='5'>";
echo "<button class='edit_button' id='$create_part_id' onclick='addNewSubmissionPart(this)' style='margin-left: 5px'>Add New Part</button><br>";
$select_id = "subParts_" . htmlspecialchars(str_replace(" ", "~", $assignment_name), ENT_QUOTES);
echo "<label for=$select_id style='display: inline-block'>Select a Part: </label><br>";
echo "<select name=$select_id id=$select_id onchange='updatePartSelection(this)' style='display: inline-block; margin-right: 5px;width: 300px;font-size: medium;'>";
echo "<option disabled selected value='Select a Part...' style='width: 200px'>Select a Part...</option>";
while ($row = $part_list->fetch_assoc()) {
    $part_name = $row['part_name'];
    $point_value = $row['point_value'];
    echo "<option value='$part_name' style='width: 200px'>$part_name - $point_value points</option>";
}
echo "</select>";
$delete_part_id = "deletePart_" . htmlspecialchars(str_replace(" ", "~", $assignment_name), ENT_QUOTES);
echo "<button class='delete_button' id='$delete_part_id' onclick='deleteSubmissionPart(this)' style='margin-left: 5px'>Delete Selected Part</button><br>";

echo "</div>";

$div_right = "subFilesDiv_" . htmlspecialchars(str_replace(" ", "~", $assignment_name), ENT_QUOTES);
echo "<div id='$div_right' style='display: inline-block;border: 1px solid black;padding: 5px'>";
echo "<p style='margin: 5px'>You must select a submission part first!</p>";
echo "</div>";

echo "<p style='font-size: x-large;margin-top: 5px;margin-bottom: 0px; display: block'>Configure Sample Testing Procedure</p><br>";

$testing_div = "sampleTestingDiv_" . htmlspecialchars(str_replace(" ", "~", $assignment_name), ENT_QUOTES);
echo "<div id='$testing_div'>";
echo "<p style='margin: 5px'>You must select a submission part first!</p>";
echo "</div>";


echo "</table>";