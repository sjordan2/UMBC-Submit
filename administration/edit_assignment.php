<?php
include '../sql_functions.php';

$new_max_points = $_POST["maxPoints"];
$new_extra_credit = $_POST["ecPoints"];
$new_due_date = $_POST["dueDate"];
$assignment_name = str_replace("~", " ", $_POST["assignmentName"]);

if(!ctype_digit($new_max_points) or (int)$new_max_points < 0) {
    echo "ERROR: Total Points must be a positive integer!";
} else {
    if(!ctype_digit($new_extra_credit) or (int)$new_extra_credit < 0) {
        echo "ERROR: Extra Credit Points must be a positive integer!";
    } else {
        // Create connection
        $conn = new mysqli($sql_host, $sql_username, $sql_password, $sql_dbname);

        // Check connection
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        $assignment_name_sql = $conn->real_escape_string($assignment_name);

        $edit_assignment_sql = "UPDATE Assignments 
                        SET max_points = '$new_max_points', extra_credit = '$new_extra_credit', date_due = '$new_due_date' 
                        WHERE assignment_name = '$assignment_name_sql'";
        $result = $conn->query($edit_assignment_sql);

        if ($result === TRUE) {
            $successMessage = "SUCCESS: '" . $assignment_name . "' successfully edited!";
            echo $successMessage;
        } else {
            $errorMessage = "ERROR: " . $conn->error;
            echo $errorMessage;
        }
    }
}
