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
    if($action === "load_panel_for_part") {
        $return_json = [];
        $sub_files_list = [];
        $assignment_name = $conn->real_escape_string($_POST["assignment"]);
        $part_name = $conn->real_escape_string($_POST["part"]);
        $get_sub_files_sql = "SELECT submission_file_name FROM SubmissionFiles WHERE assignment_name = '$assignment_name' AND part_name = '$part_name'";
        $get_sub_files_result = $conn->query($get_sub_files_sql);
        $get_student_code = null;
        while($row = $get_sub_files_result->fetch_assoc()) {
            array_push($sub_files_list, $row["submission_file_name"]);
            if($get_student_code === null) {
                $get_student_code = $row["submission_file_name"];
            }
        }
        array_push($return_json, $sub_files_list);
        $user_id = $_SERVER["umbccampusid"];
        $latest_sub_number = getCurrentSubmissionNumber($user_id, $assignment_name, $part_name, $conn) - 1;
        $get_first_code_sql = "SELECT submission_contents FROM Submissions WHERE student_id = '$user_id' AND assignment_name = '$assignment_name' AND part_name = '$part_name' AND submission_number = '$latest_sub_number' AND submission_file_name = '$get_student_code'";
        $get_first_code_results = $conn->query($get_first_code_sql);
        array_push($return_json, $get_first_code_results->fetch_assoc()["submission_contents"]);

        echo json_encode($return_json);
    } else if($action === "load_file") {
        $assignment_name = $conn->real_escape_string($_POST["assignment"]);
        $part_name = $conn->real_escape_string($_POST["part"]);
        $file_name = $_POST["file_to_load"];
        $user_id = $_SERVER["umbccampusid"];
        $latest_sub_number = getCurrentSubmissionNumber($user_id, $assignment_name, $part_name, $conn) - 1;
        $get_submitted_file_sql = "SELECT submission_contents FROM Submissions WHERE student_id = '$user_id' AND assignment_name = '$assignment_name' AND part_name = '$part_name' AND submission_number = '$latest_sub_number' AND submission_file_name = '$file_name'";
        $get_submitted_file_result = $conn->query($get_submitted_file_sql);
        echo $get_submitted_file_result->fetch_assoc()["submission_contents"];
    }
}