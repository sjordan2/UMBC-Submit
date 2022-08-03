<?php
require_once '../includes/db_sql.php';
require_once 'assignment_functions.php';

//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);

$conn = new mysqli($sql_host, $sql_username, $sql_password, $sql_dbname);
if($conn->connect_error) {
    echo "ERROR: " . $conn->connect_error;
} else {
    $action = $_POST["action"]; // Required action variable
    if($action === "refresh_table") {
        $assignment_table_sql = "SELECT assignment_name, point_value, date_due, class_type, is_visible, document_link FROM Assignments";
        $assignment_table_result = $conn->query($assignment_table_sql);
        $data_array = [];
        $data_array["table"] = constructAssignmentTableData($conn, $assignment_table_result);
        $data_array["columns"] = constructAssignmentTableHeadings();
        echo json_encode($data_array);
    } else if($action === "newAssignment") {
        $create_array = [];
        $assignment_name = $conn->real_escape_string($_POST["assignment"]);
        $point_value = $_POST["pointValue"];
        $current_date_time = null;
        $date_time_due = null;
        try {
            $date_time_due = new DateTime($_POST["dateDue"]);
            $current_date_time = new DateTime();
        } catch(Exception $e) {
            echo "ERROR: Date Time Error: " . $e->getMessage();
            exit();
        }
        if($current_date_time > $date_time_due) {
            $create_array["message"] = "ERROR: Given due date is not after the current date and time!";
        } else {
            $date_time_sql = $date_time_due->format("Y-m-d H:i:s");
            $class_type = $_POST["classSelection"];
            $create_assignment_sql = "INSERT INTO Assignments (assignment_name, point_value, date_due, class_type, grades_released, is_visible)
                                  VALUES ('$assignment_name', '$point_value', '$date_time_sql', '$class_type', '0', '0')";
            $create_assignment_result = $conn->query($create_assignment_sql);
            if($create_assignment_result === true) {
                $create_array["message"] = "SUCCESS: " . $assignment_name . " has been added to the database!";
            } else {
                $create_array["message"] = "ERROR: " . $conn->error;
            }
        }

        $assignment_table_sql = "SELECT assignment_name, point_value, date_due, class_type, is_visible FROM Assignments";
        $assignment_table_result = $conn->query($assignment_table_sql);
        $create_array["table"] = constructAssignmentTableData($conn, $assignment_table_result);
        $create_array["columns"] = constructAssignmentTableHeadings();

        echo json_encode($create_array);

    } else if($action === "delete_assignment") {
        $delete_array = [];
        $assignment_name = $conn->real_escape_string($_POST["assignment"]);
        $delete_assignment_sql = "DELETE FROM Assignments WHERE assignment_name = '$assignment_name'";
        $delete_assignment_result = $conn->query($delete_assignment_sql);
        if($delete_assignment_result === true) {
            $delete_array["message"] = "SUCCESS: " . $assignment_name . " has been removed from the database!";
        } else {
            $delete_array["message"] = "ERROR: " . $conn->error;
        }
        $assignment_table_sql = "SELECT assignment_name, point_value, date_due, class_type, is_visible FROM Assignments";
        $assignment_table_result = $conn->query($assignment_table_sql);
        $delete_array["table"] = constructAssignmentTableData($conn, $assignment_table_result);
        $delete_array["columns"] = constructAssignmentTableHeadings();

        echo json_encode($delete_array);

    } else if($action === "load_panel") {
        $assignment_name_sql = $conn->real_escape_string($_POST["name"]);
        if($_POST["panel"] === "details") {
            $return_json = [];
            $details_panel_sql = "SELECT date_due, class_type, point_value, document_link FROM Assignments WHERE assignment_name = '$assignment_name_sql'";
            $details_panel_result = $conn->query($details_panel_sql);
            $row = $details_panel_result->fetch_assoc();
            array_push($return_json, $row["point_value"]);
            $date_time_list = explode(" ", $row["date_due"]);
            array_push($return_json, $date_time_list[0]);
            array_push($return_json, $date_time_list[1]);
            array_push($return_json, $row["class_type"]);
            array_push($return_json, $row["document_link"]);
            echo json_encode($return_json);
        } else if($_POST["panel"] === "submissions") {
            $return_json = [];
            $submissions_panel_sql = "SELECT part_name, point_value, extra_credit FROM SubmissionParts WHERE assignment_name = '$assignment_name_sql'";
            $submissions_panel_result = $conn->query($submissions_panel_sql);
            $return_json["table"] = constructSubmissionPartsTableData($submissions_panel_result);
            $return_json["columns"] = constructSubmissionPartsTableHeadings();
            echo json_encode($return_json);
        } else if($_POST["panel"] === "grading") {
            $sub_parts_sql = "SELECT part_name FROM SubmissionParts WHERE assignment_name = '$assignment_name_sql'";
            $sub_parts_result = $conn->query($sub_parts_sql);
            if($sub_parts_result === false) {
                echo "ERROR: " . $conn->error;
            } else {
                $list_of_parts = [];
                while($row = $sub_parts_result->fetch_assoc()) {
                    array_push($list_of_parts, $row["part_name"]);
                }
                echo json_encode($list_of_parts);
            }
        } else {
            $return_json = [];
            $extensions_sql = "SELECT student_id, new_due_date FROM Extensions WHERE assignment_name = '$assignment_name_sql'";
            $extensions_result = $conn->query($extensions_sql);
            $return_json["table"] = constructExtensionsTableData($extensions_result, $conn);
            $return_json["columns"] = constructExtensionsTableHeadings();

            echo json_encode($return_json);
        }
    } else if($action === "configureAssignment") {
        $assignment_name = $conn->real_escape_string($_POST["assignment"]);
        $point_value = $_POST["pointValue"];
        $edit_array = [];
        $current_date_time = null;
        $date_time_due = null;
        try {
            $date_time_due = new DateTime($_POST["dateDue"]);
            $current_date_time = new DateTime();
        } catch(Exception $e) {
            echo "Date Time Error: " . $e->getMessage();
            exit();
        }
        if($current_date_time > $date_time_due) {
            $edit_array["message"] = "ERROR: Given due date is not after the current date and time!";
        } else {
            $date_time_sql = $date_time_due->format("Y-m-d H:i:s");
            $class_type = $_POST["classSelection"];
            $edit_assignment_sql = null;
            $document_link = $_POST["documentLink"];
            if($document_link === "NULL") {
                $edit_assignment_sql = "UPDATE Assignments SET point_value = '$point_value', date_due = '$date_time_sql', class_type = '$class_type' WHERE assignment_name = '$assignment_name'";
            } else {
                $edit_assignment_sql = "UPDATE Assignments SET point_value = '$point_value', date_due = '$date_time_sql', class_type = '$class_type', document_link = '$document_link' WHERE assignment_name = '$assignment_name'";
            }
            $edit_assignment_result = $conn->query($edit_assignment_sql);
            if($edit_assignment_result === true) {
                $edit_array["message"] = "SUCCESS: " . $assignment_name . " has been updated in the database!";
            } else {
                $edit_array["message"] = "ERROR: " . $conn->error;
            }
            $assignment_table_sql = "SELECT assignment_name, point_value, date_due, class_type, is_visible FROM Assignments";
            $assignment_table_result = $conn->query($assignment_table_sql);
            $edit_array["table"] = constructAssignmentTableData($conn, $assignment_table_result);
            $edit_array["columns"] = constructAssignmentTableHeadings();
        }
        echo json_encode($edit_array);
    } else if($action === "add_submission_part") {
        $return_json = [];
        $assignment_name = $conn->real_escape_string($_POST["assignment_name"]);
        $new_part_name = $conn->real_escape_string($_POST["part_name"]);
        $new_point_value = $_POST["point_value"];
        $new_extra_credit = $_POST["extra_credit"];
        $curr_sub_parts_sql = "SELECT id_number FROM SubmissionParts WHERE assignment_name = '$assignment_name' AND part_name = '$new_part_name'";
        $curr_sub_parts_result = $conn->query($curr_sub_parts_sql);
        if($curr_sub_parts_result->num_rows > 0) { // If the part already exists in the database, reject the request
            $return_json["message"] = "ERROR: That part name already exists in the database for this assignment!";
        } else {
            $get_assignment_point_sql = "SELECT point_value FROM Assignments WHERE assignment_name = '$assignment_name'";
            $get_assignment_point_result = $conn->query($get_assignment_point_sql)->fetch_assoc()["point_value"];

            $get_current_parts_sql = "SELECT point_value FROM SubmissionParts WHERE assignment_name = '$assignment_name'";
            $get_current_parts_result = $conn->query($get_current_parts_sql);
            $curr_total = 0;
            while($row = $get_current_parts_result->fetch_assoc()) {
                $curr_total += $row["point_value"];
            }

            if(($curr_total += intval($new_point_value)) > $get_assignment_point_result) {
                $return_json["message"] = "ERROR: Adding this part will exceed the specified overall point total for " . $assignment_name . "!";
            } else {
                $new_sub_part_sql = "INSERT INTO SubmissionParts (assignment_name, part_name, point_value, extra_credit)
                             VALUES ('$assignment_name', '$new_part_name', '$new_point_value', '$new_extra_credit')";
                $new_sub_part_result = $conn->query($new_sub_part_sql);

                $starting_file_sql = "INSERT INTO SubmissionFiles (assignment_name, part_name, submission_file_name)
                                VALUES ('$assignment_name', '$new_part_name', 'example_file.py')";
                $starting_file_result = $conn->query($starting_file_sql);

                $starting_rubric_sql = "INSERT INTO RubricParts (assignment_name, part_name, line_type, line_item, line_value) 
                                VALUES ('$assignment_name', '$new_part_name', '0', 'Example Rubric Item', '0')";
                $starting_rubric_result = $conn->query($starting_rubric_sql);


                if($new_sub_part_result === true and $starting_file_result === true and $starting_rubric_result === true) {
                    $return_json["message"] = "SUCCESS: " . $new_part_name . " was successfully added to " . $assignment_name . "!";
                } else {
                    $return_json["message"] = "ERROR: " . $conn->error;
                }
            }
        }

        $sub_part_table_sql = "SELECT part_name, point_value, extra_credit FROM SubmissionParts WHERE assignment_name = '$assignment_name'";
        $sub_part_table_result = $conn->query($sub_part_table_sql);
        $return_json["table"] = constructSubmissionPartsTableData($sub_part_table_result);
        $return_json["columns"] = constructSubmissionPartsTableHeadings();

        echo json_encode($return_json);
    } else if($action === "edit_submission_part") {
        $return_json = [];
        $assignment_name = $conn->real_escape_string($_POST["assignment_name"]);
        $part_name = $conn->real_escape_string($_POST["part_name"]);
        $new_point_value = $_POST["point_value"];
        $new_extra_credit = $_POST["extra_credit"];

        $get_assignment_point_sql = "SELECT point_value FROM Assignments WHERE assignment_name = '$assignment_name'";
        $get_assignment_point_result = $conn->query($get_assignment_point_sql)->fetch_assoc()["point_value"];

        $get_current_parts_sql = "SELECT point_value FROM SubmissionParts WHERE assignment_name = '$assignment_name' AND part_name != '$part_name'";
        $get_current_parts_result = $conn->query($get_current_parts_sql);
        $curr_total = 0;
        while($row = $get_current_parts_result->fetch_assoc()) {
            $curr_total += $row["point_value"];
        }

        if(($curr_total += intval($new_point_value)) > $get_assignment_point_result) {
            $return_json["message"] = "ERROR: The new point value exceeds the specified overall point total for " . $assignment_name . "!";
        } else {
            $edit_sub_part_sql = "UPDATE SubmissionParts SET point_value = '$new_point_value', extra_credit = '$new_extra_credit' WHERE assignment_name = '$assignment_name' AND part_name = '$part_name'";
            $edit_sub_part_result = $conn->query($edit_sub_part_sql);
            if($edit_sub_part_result === true) {
                $return_json["message"] = "SUCCESS: " . $part_name . " was successfully updated for " . $assignment_name . "!";
            } else {
                $return_json["message"] = "ERROR: " . $conn->error;
            }
        }

        $sub_part_table_sql = "SELECT part_name, point_value, extra_credit FROM SubmissionParts WHERE assignment_name = '$assignment_name'";
        $sub_part_table_result = $conn->query($sub_part_table_sql);
        $return_json["table"] = constructSubmissionPartsTableData($sub_part_table_result);
        $return_json["columns"] = constructSubmissionPartsTableHeadings();

        echo json_encode($return_json);
    } else if($action === "delete_submission_part") {
        $return_json = [];
        $assignment_name = $conn->real_escape_string($_POST["assignment_name"]);
        $part_name = $conn->real_escape_string($_POST["part_name"]);

        $delete_sub_part_sql = "DELETE FROM SubmissionParts WHERE assignment_name = '$assignment_name' AND part_name = '$part_name'";
        $delete_sub_part_result = $conn->query($delete_sub_part_sql);

        if($delete_sub_part_result === true) {
            $return_json["message"] = "SUCCESS: " . $part_name . " has been successfully removed from " . $assignment_name . "!";
        } else {
            $return_json["message"] = "ERROR: " . $conn->error;
        }

        $sub_part_table_sql = "SELECT part_name, point_value, extra_credit FROM SubmissionParts WHERE assignment_name = '$assignment_name'";
        $sub_part_table_result = $conn->query($sub_part_table_sql);
        $return_json["table"] = constructSubmissionPartsTableData($sub_part_table_result);
        $return_json["columns"] = constructSubmissionPartsTableHeadings();

        echo json_encode($return_json);
    } else if($action === "load_submission_files") {
        $return_json = [];
        $assignment_name = $conn->real_escape_string($_POST["assignment_name"]);
        $part_name = $conn->real_escape_string($_POST["part_name"]);

        $sub_files_table_sql = "SELECT submission_file_name FROM SubmissionFiles WHERE assignment_name = '$assignment_name' AND part_name = '$part_name'";
        $sub_files_table_result = $conn->query($sub_files_table_sql);

        $return_json["table"] = constructSubmissionFilesTableData($sub_files_table_result);
        $return_json["columns"] = constructSubmissionFilesTableHeadings();

        echo json_encode($return_json);
    } else if($action === "update_submission_files") {
        $assignment_name = $conn->real_escape_string($_POST["name"]);
        $part_name = $conn->real_escape_string($_POST["part"]);

        // I do it this way so that I can avoid cross-referencing each individual file with what is in the database currently
        // I hope this is a more efficient way (also, it helps maintain order based on the primary key, which is a bonus)
        $delete_curr_files_sql = "DELETE FROM SubmissionFiles WHERE assignment_name = '$assignment_name' AND part_name = '$part_name'";
        $delete_curr_files_result = $conn->query($delete_curr_files_sql);

        if($delete_curr_files_result === true) {
            $new_sub_files = json_decode($_POST["newFiles"]);
            $error_occurred = false;
            foreach($new_sub_files as $file) {
                $insert_curr_sub_sql = "INSERT INTO SubmissionFiles (assignment_name, part_name, submission_file_name)
                                VALUES ('$assignment_name', '$part_name', '$file')";
                $insert_curr_sub_result = $conn->query($insert_curr_sub_sql);
                if($insert_curr_sub_result === false) {
                    $error_occurred = true;
                }
            }
            if($error_occurred === false) {
                echo "SUCCESS: Submission files successfully updated for " . $part_name . "!";
            } else {
                echo "ERROR: One submission file threw an error upon insertion!";
            }
        } else {
            echo "ERROR: Wiping previous submission files failed!";
        }
    } else if($action === "load_grading_rubric") {
        $return_json = [];
        $assignment_name = $conn->real_escape_string($_POST["assignment_name"]);
        $part_name = $conn->real_escape_string($_POST["part_name"]);

        $get_part_points_sql = "SELECT point_value, extra_credit FROM SubmissionParts WHERE assignment_name = '$assignment_name' AND part_name = '$part_name'";
        $row = $conn->query($get_part_points_sql)->fetch_assoc();
        $base_points = $row["point_value"];
        $extra_credit_points = $row["extra_credit"];

        $return_json["pointTotal"] = intval($base_points) + intval($extra_credit_points);
        $return_json["extraCredit"] = intval($extra_credit_points);

        $load_rubric_sql = "SELECT line_type, line_item, line_value FROM RubricParts WHERE assignment_name = '$assignment_name' AND part_name = '$part_name' ORDER BY id_number";
        $load_rubric_result = $conn->query($load_rubric_sql);

        $return_json["table"] = constructGradingRubricTableData($load_rubric_result);
        $return_json["columns"] = constructGradingRubricTableHeadings();

        echo json_encode($return_json);
    } else if($action === "update_grading_rubric") {
        $assignment_name = $conn->real_escape_string($_POST["name"]);
        $part_name = $conn->real_escape_string($_POST["part"]);

        $get_part_points_sql = "SELECT point_value, extra_credit FROM SubmissionParts WHERE assignment_name = '$assignment_name' AND part_name = '$part_name'";
        $row = $conn->query($get_part_points_sql)->fetch_assoc();
        $part_points = $row["point_value"];
        $extra_credit_points = $row["extra_credit"];

        // Looping through to make sure the rubric points don't exceed the part point total
        $curr_rubric_total = 0;
        $new_rubric_json = json_decode($_POST["newRubric"], true);
        foreach($new_rubric_json as $line) {
            if($line["type"] === "0") {
                $curr_rubric_total += $line["value"];
            }
        }
        if($curr_rubric_total > ($part_points + $extra_credit_points)) {
            echo "ERROR: Rubric points cannot exceed the sum of the base and extra credit points for this part!";
        } else {
            $delete_curr_rubric_sql = "DELETE FROM RubricParts WHERE assignment_name = '$assignment_name' AND part_name = '$part_name'";
            $delete_curr_rubric_result = $conn->query($delete_curr_rubric_sql);

            if($delete_curr_rubric_result === true) {
                $error_occurred = false;
                foreach($new_rubric_json as $line) {
                    $line_type = $line["type"];
                    $line_item = $line["item"];
                    $line_value = $line["value"];
                    $insert_curr_line_sql = null;
                    if($line_value === "") {
                        $insert_curr_line_sql = "INSERT INTO RubricParts (assignment_name, part_name, line_type, line_item)
                                    VALUES ('$assignment_name', '$part_name', '$line_type', '$line_item')";
                    } else {
                        $insert_curr_line_sql = "INSERT INTO RubricParts (assignment_name, part_name, line_type, line_item, line_value)
                                    VALUES ('$assignment_name', '$part_name', '$line_type', '$line_item', '$line_value')";
                    }
                    $insert_curr_line_result = $conn->query($insert_curr_line_sql);
                    if($insert_curr_line_result === false) {
                        $error_occurred = true;
                    }
                }
                if($error_occurred === true) {
                    echo "ERROR: One rubric line threw an error upon insertion: " . $conn->error;
                } else {
                    echo "SUCCESS: Grading rubric successfully updated for " . $part_name . "!";
                }
            } else {
                echo "ERROR: Wiping previous grading rubric failed!";
            }
        }
    } else if($action === "load_makefile_table") {
        $return_json = [];
        $type = $_POST["type"];
        $assignment_name = $conn->real_escape_string($_POST["assignment_name"]);
        $part_name = $conn->real_escape_string($_POST["part_name"]);

        $get_makefile_table_sql = null;
        if($type === "Sample") {
            $get_makefile_table_sql = "SELECT id_number FROM AuxiliaryFiles WHERE assignment_name = '$assignment_name' AND part_name = '$part_name' AND file_type = 'SAMPLE_MAKEFILE'";
        } else {
            $get_makefile_table_sql = "SELECT id_number FROM AuxiliaryFiles WHERE assignment_name = '$assignment_name' AND part_name = '$part_name' AND file_type = 'TESTING_MAKEFILE'";
        }
        $get_makefile_table_result = $conn->query($get_makefile_table_sql);

        $return_json["table"] = constructMakefileTableData($get_makefile_table_result);
        $return_json["columns"] = constructMakefileTableHeadings();

        echo json_encode($return_json);
    } else if($action === "upload_makefile") {
        $return_json = [];
        $assignment_name = $conn->real_escape_string($_POST["assignment"]);
        $part_name = $conn->real_escape_string($_POST["part"]);
        $type = $_POST["type"];
        $uploaded_makefile = fopen($_FILES['makefile']['tmp_name'], 'r');
        $file_contents = fread($uploaded_makefile, filesize($_FILES['makefile']['tmp_name']));
        fclose($uploaded_makefile);
        $file_contents_sql = $conn->real_escape_string($file_contents);
        $file_type = null;
        if($type === "Sample") {
            $file_type = "SAMPLE_MAKEFILE";
        } else {
            $file_type = "TESTING_MAKEFILE";
        }

        $check_for_makefile_sql = "SELECT id_number FROM AuxiliaryFiles WHERE assignment_name = '$assignment_name' AND part_name = '$part_name' AND file_type = '$file_type'";
        $check_for_makefile_result = $conn->query($check_for_makefile_sql);
        $new_makefile_sql = null;
        if($check_for_makefile_result->num_rows === 0) {
            $new_makefile_sql = "INSERT INTO AuxiliaryFiles (assignment_name, part_name, file_type, file_name, file_contents) 
                            VALUES ('$assignment_name', '$part_name', '$file_type', 'Makefile', '$file_contents_sql')";
        } else {
            $new_makefile_sql = "UPDATE AuxiliaryFiles SET file_contents = '$file_contents_sql' WHERE assignment_name = '$assignment_name' AND part_name = '$part_name' AND file_type = '$file_type'";
        }
        $new_makefile_result = $conn->query($new_makefile_sql);
        if($new_makefile_result === true) {
            $return_json["message"] = "SUCCESS: New " . $type . " Makefile was successfully uploaded!";
        } else {
            $return_json["message"] = "ERROR: " . $conn->error;
        }

        $get_makefile_table_sql = "SELECT id_number FROM AuxiliaryFiles WHERE assignment_name = '$assignment_name' AND part_name = '$part_name' AND file_type = '$file_type'";
        $get_makefile_table_result = $conn->query($get_makefile_table_sql);
        $return_json["table"] = constructMakefileTableData($get_makefile_table_result);
        $return_json["columns"] = constructMakefileTableHeadings();

        echo json_encode($return_json);
    } else if($action === "preview_file") {
        $assignment_name = $conn->real_escape_string($_POST["name"]);
        $part_name = $conn->real_escape_string($_POST["part"]);
        $type = $_POST["type"];
        $file_name = $conn->real_escape_string($_POST["fileName"]);
        $json_array = [];
        $get_file_contents_sql = "SELECT file_contents FROM AuxiliaryFiles WHERE assignment_name = '$assignment_name' AND part_name = '$part_name' AND file_type = '$type' AND file_name = '$file_name'";
        $get_file_contents_result = $conn->query($get_file_contents_sql);
        if($get_file_contents_result === false) {
            echo "ERROR: " . $conn->error;
        } else {
            $file_preview = $get_file_contents_result->fetch_assoc()["file_contents"];
            array_push($json_array, $file_preview);
            echo json_encode($json_array);
        }
    } else if($action === "delete_file") {
        $return_json = [];
        $assignment_name = $conn->real_escape_string($_POST["assignment_name"]);
        $part_name = $conn->real_escape_string($_POST["part_name"]);
        $type = $_POST["type"];
        $file_name = $conn->real_escape_string($_POST["file_name"]);

        $delete_file_sql = "DELETE FROM AuxiliaryFiles WHERE assignment_name = '$assignment_name' AND part_name = '$part_name' AND file_type = '$type' AND file_name = '$file_name'";
        $delete_file_result = $conn->query($delete_file_sql);
        if($delete_file_result === true) {
            $return_json["message"] = "SUCCESS: " . $file_name . " was successfully removed from " . $part_name . "!";
        } else {
            $return_json["message"] = "ERROR: " . $conn->error;
        }

        if(explode("_", $type)[1] === "MAKEFILE") {
            $get_makefile_table_sql = "SELECT id_number FROM AuxiliaryFiles WHERE assignment_name = '$assignment_name' AND part_name = '$part_name' AND file_type = '$type'";
            $get_makefile_table_result = $conn->query($get_makefile_table_sql);
            $return_json["table"] = constructMakefileTableData($get_makefile_table_result);
            $return_json["columns"] = constructMakefileTableHeadings();
        } else {
            $get_io_file_table_sql = null;
            if(explode("_", $type)[0] === "SAMPLE") {
                $get_io_file_table_sql = "SELECT file_name, file_type FROM AuxiliaryFiles WHERE assignment_name = '$assignment_name' AND part_name = '$part_name' AND (file_type = 'SAMPLE_INPUT' OR file_type = 'SAMPLE_OUTPUT')";
            } else {
                $get_io_file_table_sql = "SELECT file_name, file_type FROM AuxiliaryFiles WHERE assignment_name = '$assignment_name' AND part_name = '$part_name' AND (file_type = 'TESTING_INPUT' OR file_type = 'TESTING_OUTPUT')";
            }
            $get_io_file_table_result = $conn->query($get_io_file_table_sql);
            $return_json["table"] = constructIOFileTableData($get_io_file_table_result);
            $return_json["columns"] = constructIOFileTableHeadings();
        }
        echo json_encode($return_json);
    } else if($action === "load_io_file_table") {
        $return_json = [];
        $type = $_POST["type"];
        $assignment_name = $conn->real_escape_string($_POST["assignment_name"]);
        $part_name = $conn->real_escape_string($_POST["part_name"]);

        $get_io_file_table_sql = null;
        if ($type === "Sample") {
            $get_io_file_table_sql = "SELECT file_name, file_type FROM AuxiliaryFiles WHERE assignment_name = '$assignment_name' AND part_name = '$part_name' AND (file_type = 'SAMPLE_INPUT' OR file_type = 'SAMPLE_OUTPUT')";
        } else {
            $get_io_file_table_sql = "SELECT file_name, file_type FROM AuxiliaryFiles WHERE assignment_name = '$assignment_name' AND part_name = '$part_name' AND (file_type = 'TESTING_INPUT' OR file_type = 'TESTING_OUTPUT')";
        }
        $get_io_file_table_result = $conn->query($get_io_file_table_sql);

        $return_json["table"] = constructIOFileTableData($get_io_file_table_result);
        $return_json["columns"] = constructIOFileTableHeadings();

        echo json_encode($return_json);
    } else if($action === "upload_io_file") {
        $return_json = [];
        $assignment_name = $conn->real_escape_string($_POST["assignment"]);
        $part_name = $conn->real_escape_string($_POST["part"]);
        $file_type = $_POST["type"];
        $file_name = $_POST["file_name"];
        $uploaded_io_file = fopen($_FILES['io_file']['tmp_name'], 'r');
        $file_contents = fread($uploaded_io_file, filesize($_FILES['io_file']['tmp_name']));
        fclose($uploaded_io_file);
        $file_contents_sql = $conn->real_escape_string($file_contents);

        $new_io_file_sql = "INSERT INTO AuxiliaryFiles (assignment_name, part_name, file_type, file_name, file_contents) 
                        VALUES ('$assignment_name', '$part_name', '$file_type', '$file_name', '$file_contents_sql')";
        $new_io_file_result = $conn->query($new_io_file_sql);
        if($new_io_file_result === true) {
            $return_json["message"] = "SUCCESS: " . $file_name . " was successfully uploaded!";
        } else {
            $return_json["message"] = "ERROR: " . $conn->error;
        }
        $get_io_file_table_sql = null;
        if(explode("_", $file_type)[0] === "SAMPLE") {
            $get_io_file_table_sql = "SELECT file_name, file_type FROM AuxiliaryFiles WHERE assignment_name = '$assignment_name' AND part_name = '$part_name' AND (file_type = 'SAMPLE_INPUT' OR file_type = 'SAMPLE_OUTPUT')";
        } else {
            $get_io_file_table_sql = "SELECT file_name, file_type FROM AuxiliaryFiles WHERE assignment_name = '$assignment_name' AND part_name = '$part_name' AND (file_type = 'TESTING_INPUT' OR file_type = 'TESTING_OUTPUT')";
        }
        $get_io_file_table_result = $conn->query($get_io_file_table_sql);

        $return_json["table"] = constructIOFileTableData($get_io_file_table_result);
        $return_json["columns"] = constructIOFileTableHeadings();

        echo json_encode($return_json);
    } else if($action === "get_users_table") {
        $users_table_sql = "SELECT umbc_id, umbc_name_id, firstname, lastname FROM Users WHERE role = 'Student' AND status = 'Active'";
        $users_table_result = $conn->query($users_table_sql);
        $data_array = [];
        $data_array["table"] = constructUserTableData($users_table_result);
        $data_array["columns"] = constructUserTableHeadings();
        echo json_encode($data_array);
    } else if($action === "get_assignment_due_date") {
        $assignment_name = $conn->real_escape_string($_POST["name"]);
        $return_json = [];
        $get_due_date_sql = "SELECT date_due FROM Assignments WHERE assignment_name = '$assignment_name'";
        $get_due_date_result = $conn->query($get_due_date_sql);
        $due_date_object = null;
        try {
            $due_date_object = new DateTime($get_due_date_result->fetch_assoc()["date_due"]);
        } catch(Exception $e) {
            echo "Date Time Screw Up when getting assignment due date for extension requests!";
        }
        array_push($return_json, $due_date_object->format("l, F jS, Y, g:i:s A"));

        echo json_encode($return_json);
    } else if($action === "grant_new_extension") {
        $return_json = [];
        $assignment_name = $conn->real_escape_string($_POST["assignment_name"]);
        $student_string = $_POST["student_string"];
        $student_campus_id = getCampusIDFromExtensionString($student_string, $conn);
        $new_due_date = null;
        $current_date = null;
        $get_assignment_date_sql = "SELECT date_due FROM Assignments WHERE assignment_name = '$assignment_name'";
        $assignment_due_date = $conn->query($get_assignment_date_sql)->fetch_assoc()["date_due"];
        try {
            $new_due_date = new DateTime($_POST["new_due_date"]);
            $current_date = new DateTime();
            $assignment_due_date = new DateTime($assignment_due_date);
        } catch(Exception $e) {
            $return_json["message"] = "ERROR: Date Time Screw Up while inserting new extension into database: " . $e->getMessage();
        }
        if(($current_date > $assignment_due_date AND $new_due_date > $current_date)
           OR ($current_date < $assignment_due_date AND $new_due_date > $assignment_due_date)) {
            $date_time_sql = $new_due_date->format("Y-m-d H:i:s");
            $insert_extension_sql = "INSERT INTO Extensions (student_id, assignment_name, new_due_date)
                                     VALUES ('$student_campus_id', '$assignment_name', '$date_time_sql')";
            $insert_extension_result = $conn->query($insert_extension_sql);
            if($insert_extension_result === true) {
                $return_json["message"] = "SUCCESS: Extension successfully granted for " . $student_string . "!";
            } else {
                $return_json["message"] = "ERROR: " . $conn->error;
            }
        } else {
            $return_json["message"] = "ERROR: New due date must be both after the current course-wide due date as well as in the future!";
        }
        $extensions_sql = "SELECT student_id, new_due_date FROM Extensions WHERE assignment_name = '$assignment_name'";
        $extensions_result = $conn->query($extensions_sql);
        $return_json["table"] = constructExtensionsTableData($extensions_result, $conn);
        $return_json["columns"] = constructExtensionsTableHeadings();

        echo json_encode($return_json);
    } else if($action === "grab_edit_extension_data") {
        $return_json = [];
        $assignment_name = $conn->real_escape_string($_POST["assignment_name"]);
        $student_campus_id = getCampusIDFromExtensionString($_POST["student_string"], $conn);
        $get_coursewide_due_date_sql = "SELECT date_due FROM Assignments WHERE assignment_name = '$assignment_name'";
        $coursewide_due_date = $conn->query($get_coursewide_due_date_sql)->fetch_assoc()["date_due"];
        $coursewide_due_date_obj = null;
        try {
            $coursewide_due_date_obj = new DateTime($coursewide_due_date);
        } catch (Exception $e) {
            echo "ERROR: Date Time screw-up while grabbing data for edit extension request!";
        }
        array_push($return_json, $coursewide_due_date_obj->format("l, F jS, Y, g:i:s A"));

        $current_extension_sql = "SELECT new_due_date FROM Extensions WHERE assignment_name = '$assignment_name' AND student_id = '$student_campus_id'";
        $current_extension_date_time = $conn->query($current_extension_sql)->fetch_assoc()["new_due_date"];
        $exploded_date_time = explode(" ", $current_extension_date_time);
        array_push($return_json, $exploded_date_time[0]);
        array_push($return_json, $exploded_date_time[1]);

        echo json_encode($return_json);
    } else if($action === "edit_extension") {
        $return_json = [];
        $assignment_name = $conn->real_escape_string($_POST["assignment_name"]);
        $student_campus_id = getCampusIDFromExtensionString($_POST["student_string"], $conn);
        $get_coursewide_due_date_sql = "SELECT date_due FROM Assignments WHERE assignment_name = '$assignment_name'";
        $coursewide_due_date = $conn->query($get_coursewide_due_date_sql)->fetch_assoc()["date_due"];
        $coursewide_due_date_obj = null;
        $current_date_obj = null;
        $proposed_new_due_date_obj = null;
        try {
            $coursewide_due_date_obj = new DateTime($coursewide_due_date);
            $current_date_obj = new DateTime();
            $proposed_new_due_date_obj = new DateTime($_POST["new_due_date"]);
        } catch (Exception $e) {
            echo "ERROR: Date Time screw-up while editing extension!";
        }
        if($proposed_new_due_date_obj <= $current_date_obj OR $proposed_new_due_date_obj <= $coursewide_due_date_obj) {
            $return_json["message"] = "ERROR: New due date must be both after the current course-wide due date as well as in the future!";
        } else {
            $date_time_sql = $proposed_new_due_date_obj->format("Y-m-d H:i:s");
            $update_extension_sql = "UPDATE Extensions SET new_due_date = '$date_time_sql' WHERE assignment_name = '$assignment_name' AND student_id = '$student_campus_id'";
            $update_extension_result = $conn->query($update_extension_sql);
            if($update_extension_result === true) {
                $return_json["message"] = "SUCCESS: The " . $assignment_name . " extension for " . $_POST["student_string"] . " has been successfully updated!";
            } else {
                $return_json["message"] = "ERROR: " . $conn->error;
            }

            $extensions_sql = "SELECT student_id, new_due_date FROM Extensions WHERE assignment_name = '$assignment_name'";
            $extensions_result = $conn->query($extensions_sql);
            $return_json["table"] = constructExtensionsTableData($extensions_result, $conn);
            $return_json["columns"] = constructExtensionsTableHeadings();
        }
        echo json_encode($return_json);
    } else if($action === "delete_extension") {
        $return_json = [];
        $assignment_name = $conn->real_escape_string($_POST["assignment_name"]);
        $student_campus_id = getCampusIDFromExtensionString($_POST["student_string"], $conn);

        $delete_extension_sql = "DELETE FROM Extensions WHERE assignment_name = '$assignment_name' AND student_id = '$student_campus_id'";
        $delete_extension_result = $conn->query($delete_extension_sql);

        if($delete_extension_result === true) {
            $return_json["message"] = "SUCCESS: " . $assignment_name . " extension for " . $_POST["student_string"] . " was successfully deleted!";
        } else {
            $return_json["message"] = "ERROR: " . $conn->error;
        }

        $extensions_sql = "SELECT student_id, new_due_date FROM Extensions WHERE assignment_name = '$assignment_name'";
        $extensions_result = $conn->query($extensions_sql);
        $return_json["table"] = constructExtensionsTableData($extensions_result, $conn);
        $return_json["columns"] = constructExtensionsTableHeadings();

        echo json_encode($return_json);
    } else if($action === "toggle_visibility") {
        $assignment_name = $conn->real_escape_string($_POST["name"]);
        $visibility_result = verifyAssignmentForVisibility($assignment_name, $conn);
        if($visibility_result[0] === "E") { // There was an error that needs to be fixed before the assignment can be made visible
            echo $visibility_result;
        } else if($visibility_result[0] === "H") { // The assignment is ready to be made visible, so we will do just that
            $make_assignment_visible_sql = "UPDATE Assignments SET is_visible = '1' WHERE assignment_name = '$assignment_name'";
            $make_assignment_visible_result = $conn->query($make_assignment_visible_sql);
            if($make_assignment_visible_result === true) {
                echo "SUCCESS: " . $assignment_name . " has been made visible to students!";
            } else {
                echo "ERROR: " . $conn->error;
            }
        } else { // The assignment is already made visible, so we want to hide it
            $hide_assignment_sql = "UPDATE Assignments SET is_visible = '0' WHERE assignment_name = '$assignment_name'";
            $hide_assignment_result = $conn->query($hide_assignment_sql);
            if($hide_assignment_result === true) {
                echo "SUCCESS: " . $assignment_name . " has been hidden from students!";
            } else {
                echo "ERROR: " . $conn->error;
            }
        }
    }
}