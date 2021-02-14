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

$assignment_name_sql = $conn->real_escape_string($assignment_name);
$part_name_sql = $conn->real_escape_string($part_name);

$sub_file_sql = "SELECT assignment, part_name, submission_file_name FROM SubmissionParts WHERE assignment = '$assignment_name_sql' AND part_name = '$part_name_sql'";
$sub_file_result = $conn->query($sub_file_sql);

if($sub_file_result->num_rows == 0) {
    echo "ERROR: This should never have zero rows!";
} else {
    $subFile_error_id = "subFileError_" . str_replace(" ", "~", $part_name);
    echo "<p style='font-size: x-large;display: inline-block;margin: 0px'>Submission Files for $part_name: </p><br>";
    echo "<p id=$subFile_error_id class=errorMessage></p>";
    $create_file_text = "createFile_" . str_replace(" ", "~", $assignment_name) . "_" . str_replace(" ", "~", $part_name);
    $create_file_id = "createFileButton_" . str_replace(" ", "~", $assignment_name) . "_" . str_replace(" ", "~", $part_name);
    echo "<input type='text' id=$create_file_text placeholder='Enter file name here...' style='display: inline-block;height: 25px;font-size: 18px;' size='30'>";
    echo "<button class='edit_button' id=$create_file_id onclick='addSubmissionFile(this)' style='margin-left: 5px'>Add File</button>";
    if($sub_file_result->num_rows == 1) {
        $currRow = $sub_file_result->fetch_assoc();
        $assignmentName = $currRow['assignment'];
        $partName = $currRow['part_name'];
        $subName = $currRow['submission_file_name'];
        // Then a submission part is already created, but a file may or may not exist.
        if (!empty($subName)) {
            echo "<table id='subFiles_table' style='table-layout: fixed;margin: 5px'>";
            echo "<tr id='header_row'><th>File Name</th><th>Actions</th></tr>";
            // If there *is* a file, then put it in the table.
            $cell_id = "cell_" . $subName;
            echo "<td id=$cell_id>";
            echo $subName;
            echo "</td>";

            $deleteid = "del_" . str_replace(" ", "~", $assignmentName) . "_" . str_replace(" ", "~", $partName) . "_" . $subName;
            echo "<td><button class='delete_button' id=$deleteid onclick='deleteSubmissionFile(this)'>Remove from Part</button></td>";

            echo "</tr>";
        } else {
            echo "<br>No submission files yet!";
        }
    } else {
        echo "<table id='subFiles_table' style='table-layout: fixed;margin: 5px'>";
        echo "<tr id='header_row'><th>File Name</th><th>Actions</th></tr>";
        while($row = $sub_file_result->fetch_assoc()) {
            $cell_id = "cell_" . $row['submission_file_name'];
            echo "<td id=$cell_id>";
            echo $row['submission_file_name'];
            echo "</td>";

            $deleteid = "del_" . str_replace(" ", "~", $row['assignment']) . "_" . str_replace(" ", "~", $row['part_name']) . "_" . $row['submission_file_name'];
            echo "<td><button class='delete_button' id=$deleteid onclick='deleteSubmissionFile(this)'>Remove from Part</button></td>";

            echo "</tr>";
        }
    }
}