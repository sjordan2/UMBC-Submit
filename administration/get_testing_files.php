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
$method = $_POST['method'];

$assignment_name_sql = $conn->real_escape_string($assignment_name);
$part_name_sql = $conn->real_escape_string($part_name);

if($method == "SAMPLE") {
    echo "<div style='display: inline-block;border: 1px solid black;text-align: left;width: 49.5%;float: left'>";
    echo "<p style='margin: 5px;font-weight: bold;text-align: center'>Submit Makefile (Required)</p>";
    echo "<p style='margin: 5px;text-align: center'>Current Makefile: </p>";
    $current_makefile_sql = "SELECT file_contents FROM Testing WHERE assignment = '$assignment_name_sql' AND part = '$part_name_sql' AND file_type = 'SAMPLE_MAKEFILE'";
    $current_makefile_result = $conn->query($current_makefile_sql);
    echo "<div id='makefile_contents_div' style='overflow-x: scroll;background-color: black;color: white;width: 100%;max-height: 250px;overflow-y: scroll;'>";
    if($current_makefile_result->num_rows === 0) {
        echo "<p style='margin: 5px;color: red;font-weight: bold;text-align: center'>No Makefile uploaded! You need this! </p>";
    } else {
        echo "<pre style='width: 5px'>" . $current_makefile_result->fetch_assoc()['file_contents'] . "</pre>";
    }
    echo "</div>";
    $assignment_html = htmlspecialchars($assignment_name, ENT_QUOTES);
    $part_html = htmlspecialchars($part_name, ENT_QUOTES);
    echo "<form id='makefile_upload' action='javascript:uploadMakefile(\"$assignment_html\", \"$part_html\")' style='text-align: center'>
        <input type='file' name='makefile_file' id='makefile_file'><br>
        <input class='submitClass' type='submit' value='Upload New Makefile'>
        <p class='errorMessage' id='makefileMessage'></p>
        </form>";
    echo "</div>";


    echo "<div style='display: inline-block;border: 1px solid black;width: 49.5%;float: right;text-align: center'>";
    echo "<p style='margin: 5px;font-weight: bold;text-align: center'>Submit Input Files (Optional)</p>";
    echo "<p style='margin: 5px;text-align: center'>Current Input Files: </p>";
    $current_input_sql = "SELECT id_number, file_name FROM Testing WHERE assignment = '$assignment_name_sql' AND part = '$part_name_sql' AND file_type = 'SAMPLE_IO'";
    $current_input_result = $conn->query($current_input_sql);
    if($current_input_result->num_rows === 0) {
        echo "<p style='margin: 5px;color: blue;text-align: center'>None Found.</p>";
    } else {
        $html_id = "inputTable_" . $assignment_html;
        echo "<table id='$html_id' style='text-align: center;width: 100%'>";
        echo "<tr><th>File Name</th><th>Actions</th></tr>";
        $counter = 0;
        while($row = $current_input_result->fetch_assoc()) {
            $id_row = "input_" . $assignment_html . "_" . $counter;
            $file_name = $row['file_name'];
            echo "<tr>";
            echo "<td style='overflow-x: scroll; overflow-y: hidden'>";
            echo "<p id='$id_row'>$file_name</p>";
            echo "</td>";
            $prev_id = "previewInput_" . $assignment_html . "_" . $part_html . "_" . $counter;
            $del_id = "deleteInput_" . $assignment_html . "_" . $part_html . "_SAMPLE_" . $counter;
            echo "<td><button id='$prev_id' class='edit_button' style='margin-right: 5px' onclick='previewInput(this)'>Preview ⇩</button><button id='$del_id' class='delete_button' onclick='removeInput(this)'>Remove from Database</button></td>";
            echo "</tr>";
            $counter++;
        }
        echo "</table>";

    }

    echo "<form id='sample_input_upload' action='javascript:uploadSampleInput(\"$assignment_html\", \"$part_html\")' style='text-align: center'>
          <input type='file' name='sample_input_file' id='sample_input_file'><br>
          <input class='submitClass' type='submit' value='Upload Input File'>
          <p class='errorMessage' id='sampleInputMessage'></p>
          </form>";
    echo "</div>";
    echo "</div>";
}