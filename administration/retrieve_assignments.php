<?php

include '../sql_functions.php';

// Create connection
$conn = new mysqli($sql_host, $sql_username, $sql_password, $sql_dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

ensureUsersTableCreation($conn);
ensureAssignmentsTableCreation($conn);
ensureExtensionsTableCreation($conn);
ensureSubmissionPartsTableCreation($conn);
ensureSubmissionsTableCreation($conn);
ensureRubricPartsTableCreation($conn);
ensureRubricsTableCreation($conn);

$sql_list = "SELECT assignment_name, max_points, extra_credit, date_assigned, date_due FROM Assignments";
$result_list = $conn->query($sql_list);
echo "<h id='currNumUsers'>Total Number of Assignments: $result_list->num_rows</h>";
echo "<input type='text' id='searchAssignments' onkeyup='updateAssignmentsTable()' placeholder='Enter search term here'><br>";
echo "<p id='messageFeedback' class='errorMessage'></p>";
echo "<table id='assignments_table'>";
$id_list = ["assignment_name", "max_points", "extra_credit"];
echo "<tr id='header_row' class='regular'><th>Assignment</th><th>Total Points</th><th>Extra Credit Points</th><th>Date Created</th><th>Date Due</th><th>Status</th><th>Actions</th></tr>";
if ($result_list->num_rows > 0) {
    // output data of each row
    while($row = $result_list->fetch_assoc()) {
        $assignment_name = str_replace(" ", "~", $row['assignment_name']);
        $row_id = 'assignment_row_' . $assignment_name;
        echo "<tr id=$row_id class='regular'>";
        for($colNum = 0; $colNum < count($id_list); $colNum++) {
            $element = $row[$id_list[$colNum]];
            $element_id = $id_list[$colNum] . "_" . $assignment_name;
            $text_id = $element_id . "_element";
            echo "<td id=$element_id>";
            echo "<p id=$text_id>";
            echo $element;
            echo "</p>";
            echo "</td>";
        }

        $date_assigned = null;
        $date_due = null;
        $current_datetime = null;
        try {
            $date_assigned = new DateTime($row['date_assigned']);
            $date_due = new DateTime($row['date_due']);
            $current_datetime = new DateTime();
        } catch (Exception $e) {
            echo "Date Time Error: " . $e;
        }
        $element_id = "date_assigned_" . $assignment_name;
        $text_id = $element_id . "_element";
        echo "<td id=$element_id>";
        echo "<p id=$text_id>";
        echo $date_assigned->format("l, F jS, Y");
        echo "</p>";
        echo "</td>";

        $element_id = "date_due_" . $assignment_name;
        $text_id = $element_id . "_element";
        echo "<td id=$element_id>";
        echo "<p id=$text_id>";
        echo $date_due->format("l, F jS, Y, g:i:sA");
        echo "</p>";
        echo "</td>";


        $statusid = "status_" . $assignment_name;
        if($date_due > $current_datetime === true) { // If the current date/time is not greater than the due date (i.e. before)
            echo "<td bgcolor='#006400' id=$statusid>";
            echo "Open";
        } else {
            echo "<td bgcolor='red' id=$statusid>";
            echo "Closed";
        }
        echo "</td>";

        $deleteid = "del_" . $assignment_name;
        $editid = "edit_" . $assignment_name;
        $extensionid = "extensions_" . $assignment_name;
        $detailsid = "details_" . $assignment_name;
        $gradingID = "grading_" . $assignment_name;
        $buttondata_id = "buttondata_" . $assignment_name;
        echo "<td id=$buttondata_id><button class='edit_button' id=$editid onclick='editAssignment(this)'>Edit Assignment</button>
                <button class='delete_button' id=$deleteid onclick='deleteAssignment(this)'>Remove from Database</button><br>
                <button class='edit_button' id=$extensionid onclick='manageExtensions(this)'>Extensions ⇩</button>
                <button class='edit_button' id=$detailsid onclick='manageDetails(this)'>Assignment Details ⇩</button>
                <button class='edit_button' id=$gradingID onclick='manageGrading(this)'>Manage Grading ⇩</button></td>";
        echo "</tr>";

        $extension_row_id = $extensionid . "_row";
        $extension_cell_id = $extensionid . "_cell";
        echo "<tr id=$extension_row_id class='extension hidden'>";
            echo "<td colspan='42' id=$extension_cell_id>";
            echo "</td>";
        echo "</tr>";

        $details_row_id = $detailsid . "_row";
        $details_cell_id = $detailsid . "_cell";
        echo "<tr id=$details_row_id class='extension hidden'>";
        echo "<td colspan='42' id=$details_cell_id>";
        echo "</td>";
        echo "</tr>";

        $grading_row_id = $gradingID . "_row";
        $grading_cell_id = $gradingID . "_cell";
        echo "<tr id=$grading_row_id class='extension hidden'>";
        echo "<td colspan='42' id=$grading_cell_id>";
        echo "</td>";
        echo "</tr>";


    }
    echo "</table>";
}