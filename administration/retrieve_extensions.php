<?php

$assignment_name = $_POST['assignmentName'];

$assignment_tilde = str_replace(" ", "~", $assignment_name);

include '../sql_functions.php';

// Create connection
$conn = new mysqli($sql_host, $sql_username, $sql_password, $sql_dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$assignment_name_sql = $conn->real_escape_string($assignment_name);

$assignment_ext_list = "SELECT student_id, date_granted, new_due_date FROM Extensions WHERE assignment = '$assignment_name_sql'";
$extension_list = $conn->query($assignment_ext_list);

echo "<h id='currProject'>Extensions for Assignment: $assignment_name</h>";
$editId = "edited_" . $assignment_tilde;
echo "<p id=$editId class='errorMessage'></p>";
$button_showForm_id = "button_" . $assignment_tilde;
echo "<button class='utility' style='float: right;' onclick='showNewExtensionForm(this)' id=$button_showForm_id>Grant New Extension</button><br>";
$newext_id = "extform_" . $assignment_tilde;
$search_id = "searchStudents_" . $assignment_tilde;
$inputError_id = "inputError_" . $assignment_tilde;
$newExtension_id = "newExtensionMessage_" . $assignment_tilde;
$duedate_id = "dueDate_" . $assignment_tilde;
echo "<form method='post' onsubmit='return validateExtensionsForm(this)' id=$newext_id>
        <label for=$search_id>Student (Type to Search)</label><br>
        <input list='studentsList' id=$search_id name=$search_id onkeyup='showSearchedStudents(this)'><br>
        <datalist id='studentsList'>
        </datalist>
        <label for=$duedate_id>New Due Date</label><br>
        <input type='datetime-local' id=$duedate_id name=$duedate_id step='1'><br>

        <input type='submit' class='submitClass'>
        <p id=$inputError_id class=errorMessage></p>
        <p id=$newExtension_id class=errorMessage></p>
    </form>";
echo "<table id='extensions_table' style='table-layout: fixed'>";
echo "<tr id='header_row' class='regular'><th>Student Name</th><th>Date Granted</th><th>Extended Due Date</th><th>Status</th><th>Actions</th></tr>";
if ($extension_list->num_rows > 0) {
    // output data of each row
    while ($row = $extension_list->fetch_assoc()) {
        $student_id = $row['student_id'] . "_" . $assignment_tilde;
        $row_id = 'extension_row_' . $student_id;
        echo "<tr id=$row_id class='regular'>";

        $element_id = "student_name_" . $student_id;
        $text_id = $element_id . "_element";
        echo "<td id=$element_id>";
        echo "<p id=$text_id>";
        $formatted_student = getFullNameFromCampusID($row['student_id'], $conn) . " (" . $row['student_id'] . ")";
        echo $formatted_student;
        echo "</p>";
        echo "</td>";

        $date_granted = null;
        $new_due_date = null;
        $current_datetime = null;
        try {
            $date_granted = new DateTime($row['date_granted']);
            $new_due_date = new DateTime($row['new_due_date']);
            $current_datetime = new DateTime();
        } catch (Exception $e) {
            echo "Date Time Error!";
        }
        $element_id = "date_granted_" . $student_id;
        $text_id = $element_id . "_element";
        echo "<td id=$element_id>";
        echo "<p id=$text_id>";
        echo $date_granted->format("l, F jS, Y");
        echo "</p>";
        echo "</td>";

        $element_id = "new_due_date_" . $student_id;
        $text_id = $element_id . "_element";
        echo "<td id=$element_id>";
        echo "<p id=$text_id>";
        echo $new_due_date->format("l, F jS, Y, g:i:sA");
        echo "</p>";
        echo "</td>";


        $statusid = "status_" . $student_id;
        if($new_due_date > $current_datetime === true) { // If the current date/time is not greater than the due date (i.e. before)
            echo "<td bgcolor='#006400' id=$statusid>";
            echo "Open";
        } else {
            echo "<td bgcolor='red' id=$statusid>";
            echo "Closed";
        }
        echo "</td>";

        $deleteid = "del_" . $student_id;
        $editid = "edit_" . $student_id;
        echo "<td><button class='edit_button' id=$editid onclick='editExtension(this)'>Edit Extension</button>
                <button class='delete_button' id=$deleteid onclick='deleteExtension(this)'>Remove from Database</button></td>";

        echo "</tr>";
    }
}
echo "</table>";
