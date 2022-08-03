<?php
require_once './includes/sql_functions.php';

//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);

$conn = new mysqli($sql_host, $sql_username, $sql_password, $sql_dbname);
if($conn->connect_error) {
    echo "ERROR: " . $conn->connect_error;
} else {
    $action = $_POST["action"]; // Required action variable
    if($action === "retrieve_sub_files_for_part") {
        $return_json = [];
        $assignment_name = $conn->real_escape_string($_POST["assignment"]);
        $part_name = $conn->real_escape_string($_POST["part"]);
        $get_sub_files_sql = "SELECT submission_file_name FROM SubmissionFiles WHERE assignment_name = '$assignment_name' AND part_name = '$part_name'";
        $get_sub_files_result = $conn->query($get_sub_files_sql);
        while($row = $get_sub_files_result->fetch_assoc()) {
            array_push($return_json, $row["submission_file_name"]);
        }

        echo json_encode($return_json);
    } else if($action === "submit_part") {
        $return_json = [];


        // I do this first to ensure that the time is logged server side as soon as possible, before any SQL queries
        $current_date_obj = null;
        try {
            $current_date_obj = new DateTime();
        } catch (Exception $e) {
            echo "ERROR: Date Time Screw-Up! Contact a Course Administrator! Message: " . $e;
        }
        $current_date = $current_date_obj->format('Y-m-d H:i:s');

        $user_submitting = $_SERVER["umbccampusid"]; // Have I mentioned that I love Shibboleth?
        $assignment_name = $conn->real_escape_string($_POST["assignment_name"]);
        $part_name = $conn->real_escape_string($_POST["part_name"]);

        $curr_sub_number = getCurrentSubmissionNumber($user_submitting, $assignment_name, $part_name, $conn);

        $query_sub_files_sql = "SELECT submission_file_name FROM SubmissionFiles WHERE assignment_name = '$assignment_name' AND part_name = '$part_name'";
        $query_sub_files_result = $conn->query($query_sub_files_sql);
        $good_submission = true;
        while($row = $query_sub_files_result->fetch_assoc()) {
            $true_file_name = $row["submission_file_name"];
            $weird_file_name = str_replace(".", "_", $true_file_name);
            $submitted_file = fopen($_FILES[$weird_file_name]['tmp_name'], 'r');
            $read_in_file = fread($submitted_file, filesize($_FILES[$weird_file_name]['tmp_name'])) . "\r\n";
            $file_contents = $conn->real_escape_string($read_in_file);
            $submit_file_sql = "INSERT INTO Submissions (student_id, assignment_name, part_name, submission_number, date_submitted, submission_file_name, submission_contents) 
                                VALUES ('$user_submitting', '$assignment_name', '$part_name', '$curr_sub_number', '$current_date', '$true_file_name', '$file_contents')";
            $submit_file_result = $conn->query($submit_file_sql);
            if($submit_file_result === false) {
                $good_submission = false;
            }
        }
        if($good_submission === true) {
            array_push($return_json, "SUCCESS: '" . $part_name . "' was submitted on " . $current_date_obj->format('n/j/o') . " at " . $current_date_obj->format('g:i:s A') . "!");
            array_push($return_json, "Submitted - " . $current_date_obj->format("l, F jS, Y, g:i:s A"));
        } else {
            array_push($return_json, "ERROR: There was a problem submitting your assignment! Please contact a course administrator: " . $conn->error);
        }
        echo json_encode($return_json);
    }
}
