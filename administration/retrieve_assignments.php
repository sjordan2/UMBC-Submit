<?php

include '../sql_functions.php';

// Create connection
$conn = new mysqli($sql_host, $sql_username, $sql_password, $sql_dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

ensureAssignmentsTableCreation($conn);

$sql_list = "SELECT assignment_name, max_points, extra_credit, date_assigned, date_due FROM Assignments";
$result_list = $conn->query($sql_list);
echo "<h id='currNumUsers'>Total Number of Assignments: $result_list->num_rows</h>";
echo "<input type='text' id='searchAssignments' onkeyup='updateAssignmentsTable()' placeholder='Enter search term here'><br>";
echo "<p id='messageFeedback' class='errorMessage'></p>";
echo "<table id='assignments_table'>";
$id_list = ["assignment_name", "max_points", "extra_credit"];
echo "<tr id='header_row'><th>Assignment</th><th>Total Points</th><th>Extra Credit Points</th><th>Date Created</th><th>Date Due</th><th>Status</th><th>Actions</th></tr>";
if ($result_list->num_rows > 0) {
    // output data of each row
    while($row = $result_list->fetch_assoc()) {
        $assignment_name = str_replace(" ", "~", $row['assignment_name']);
        $row_id = 'assignment_row_' . $assignment_name;
        echo "<tr id=$row_id>";
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
            echo "Date Time Error!";
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
            echo "Past Due";
        }
        echo "</td>";

        $deleteid = "del_" . $assignment_name; // Removes space due to HTML constraints
        $editid = "edit_" . $assignment_name;
        echo "<td><button class='edit_button' id=$editid onclick='editAssignment(this)'>Edit Assignment</button>
                <button class='delete_button' id=$deleteid onclick='deleteAssignment(this)'>Remove from Database</button></td>";
        echo "</tr>";
    }
    echo "</table>";
}