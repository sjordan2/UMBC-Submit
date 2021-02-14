<?php
include 'sql_functions.php';

$assignment = $_POST['assignment_name'];
$encoded_assignment = htmlspecialchars($assignment, ENT_QUOTES);
$currUser = $_POST['campus_id'];

// Create connection
$conn = new mysqli($sql_host, $sql_username, $sql_password, $sql_dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$assignment_sql = $conn->real_escape_string($assignment);

$submission_parts_sql = "SELECT DISTINCT part_name, point_value FROM SubmissionParts WHERE assignment = '$assignment_sql'";
$submission_parts_result = $conn->query($submission_parts_sql);
$counter = 1;
while($row_part = $submission_parts_result->fetch_assoc()) {
    $part_name = $row_part["part_name"];
    $part_name_sql = $conn->real_escape_string($part_name);
    $point_value = $row_part["point_value"];
    $part_name_tilde = htmlspecialchars(str_replace(" ", "~", $row_part["part_name"]), ENT_QUOTES);
    echo "<div id=$part_name_tilde style='padding: 5px'>";
    echo "<p style='font-size: x-large;margin-bottom: 0px;margin-top: 5px;display: inline-block'><b>Part $counter: $part_name ($point_value points) -&nbsp</b></p>";
    if(getPartSubmissionStatus($currUser, $assignment, $part_name, $conn) === true) {
        echo "<p style='font-size: x-large;margin-bottom: 0px;margin-top: 5px;display: inline-block;color: #0d6b0d'><b> Submitted</b></p>";
    } else {
        echo "<p style='font-size: x-large;margin-bottom: 0px;margin-top: 5px;display: inline-block;color: red'><b> Not Submitted</b></p>";
    }
    echo "<br>";
    $submission_files_sql = "SELECT submission_file_name FROM SubmissionParts WHERE assignment = '$assignment_sql' AND part_name = '$part_name_sql'";
    $submission_result = $conn->query($submission_files_sql);
    while($row_file = $submission_result->fetch_assoc()) {
        $subFile = $row_file['submission_file_name'];
        echo "<p style='font-size: large;display: inline-block;margin-bottom: 2px'>File to Submit: $subFile</p><br>";
        echo "<input type='file' id=$subFile style='margin-left: 5px;display: inline-block'><br>";
        $errorID = "errorFile_" . $subFile;
        echo "<p class='errorMessage' id=$errorID></p>";
    }
    $submitButtonID = "submitPart_" . $part_name_tilde;
    $viewTestButtonID = "viewTest_" . htmlspecialchars(str_replace(" ", "~", $assignment), ENT_QUOTES) . "_" . $part_name_tilde;
    echo "<button id=$submitButtonID class='submitPart' style='margin-top: 5px;display: inline-block' onclick='submitPart(this, \"$currUser\", \"$encoded_assignment\")'>Submit Part</button>";
    echo "<button id=$viewTestButtonID class='testCode' style='margin-top: 5px;float: right;display: inline-block' onclick='viewTestPartFromSubmission(this)'>View/Test Submission >></button><br>";
    $part_response_id = "partResponse_" . $part_name_tilde;
    echo "<p class='errorMessage' id=$part_response_id></p>";
    echo "</div>";
    $counter++;
}