<?php
require_once '../includes/db_sql.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

function constructAssignmentTableHeadings() {
    $data = [];

    $assignment_name = [];
    $assignment_name["title"] = "Assignment Name";
    $assignment_name["field"] = "assignment_name";
    $assignment_name["sortable"] = true;
    $assignment_name["width"] = "20";
    $assignment_name["widthUnit"] = "%";
    array_push($data, $assignment_name);

    $point_value = [];
    $point_value["title"] = "Point Value";
    $point_value["field"] = "point_value";
    $point_value["sortable"] = true;
    $point_value["width"] = "10";
    $point_value["widthUnit"] = "%";
    $point_value["searchable"] = false;
    array_push($data, $point_value);

    $date_due = [];
    $date_due["title"] = "Date Due";
    $date_due["field"] = "date_due";
    $date_due["sortable"] = true;
    $date_due["searchable"] = false;
    $date_due["width"] = "20";
    $date_due["widthUnit"] = "%";
    $date_due["sorter"] = "dateSorter";
    array_push($data, $date_due);

    $class_type = [];
    $class_type["title"] = "Class Type";
    $class_type["field"] = "class_type";
    $class_type["sortable"] = true;
    $class_type["width"] = "10";
    $class_type["widthUnit"] = "%";
    $class_type["searchable"] = false;
    array_push($data, $class_type);

    $status = [];
    $status["title"] = "Status";
    $status["field"] = "status";
    $status["searchable"] = false;
    $status["width"] = "20";
    $status["widthUnit"] = "%";
    $status["formatter"] = "assignmentStatusFormatter";
    array_push($data, $status);

    $actions = [];
    $actions["title"] = "Actions";
    $actions["field"] = "actions";
    $actions["formatter"] = "loadActions";
    $actions["width"] = "20";
    $actions["widthUnit"] = "%";
    $actions["searchable"] = false;
    array_push($data, $actions);

    return json_encode($data);
}

function constructAssignmentTableData($conn, $assignment_result) {
    $data = [];
    while ($row = $assignment_result->fetch_assoc()) {
        $curr_assignment = [];
        $curr_assignment["assignment_name"] = $row['assignment_name'];
        $curr_assignment["point_value"] = $row['point_value'];
        try {
            $nice_date_time = new DateTime($row['date_due']);
            $curr_assignment["date_due"] = $nice_date_time->format("l, F jS, Y, g:i:s A");
        } catch(Exception $e) {
            $curr_assignment["date_due"] = $row['date_due'];
        }

        $curr_assignment["class_type"] = $row['class_type'];
        $curr_assignment["status"] = verifyAssignmentForVisibility($row["assignment_name"], $conn);
        if(intval($row["is_visible"]) === 1) {
            $curr_assignment["actions"] = "visible";
        } else {
            $curr_assignment["actions"] = "hidden";
        }
        array_push($data, $curr_assignment);
    }
    return json_encode($data);
}

function constructSubmissionPartsTableData($submission_parts_result) {
    $data = [];
    while($row = $submission_parts_result->fetch_assoc()) {
        $curr_part = [];
        $curr_part["part_name"] = $row["part_name"];
        $curr_part["point_value"] = $row["point_value"];
        $curr_part["extra_credit"] = $row["extra_credit"];
        $curr_part["actions"] = "Actions";
        array_push($data, $curr_part);
    }
    return json_encode($data);
}

function constructSubmissionPartsTableHeadings() {
    $data = [];

    $part_name = [];
    $part_name["title"] = "Part Name";
    $part_name["field"] = "part_name";
    array_push($data, $part_name);

    $point_value = [];
    $point_value["title"] = "Point Value";
    $point_value["field"] = "point_value";
    array_push($data, $point_value);

    $extra_credit = [];
    $extra_credit["title"] = "Extra Credit";
    $extra_credit["field"] = "extra_credit";
    array_push($data, $extra_credit);

    $actions = [];
    $actions["title"] = "Actions";
    $actions["field"] = "actions";
    $actions["formatter"] = "loadSubmissionPartActions";
    array_push($data, $actions);

    return json_encode($data);
}

function constructSubmissionFilesTableData($submission_files_result) {
    $data = [];
    while($row = $submission_files_result->fetch_assoc()) {
        $curr_file = [];
        $curr_file["file_name"] = $row["submission_file_name"];
        $curr_file["actions"] = "Actions";
        array_push($data, $curr_file);
    }
    return json_encode($data);
}

function constructSubmissionFilesTableHeadings() {
    $data = [];

    $file_name = [];
    $file_name["title"] = "File Name";
    $file_name["field"] = "file_name";
    $file_name["formatter"] = "loadSubmissionFileTextBoxes";
    array_push($data, $file_name);

    $actions = [];
    $actions["title"] = "Actions";
    $actions["field"] = "actions";
    $actions["formatter"] = "loadSubmissionFileActions";
    array_push($data, $actions);

    return json_encode($data);
}

function constructGradingRubricTableData($load_rubric_result) {
    $data = [];
    while($row = $load_rubric_result->fetch_assoc()) {
        $curr_line = [];
        $curr_line["line_type"] = $row["line_type"];
        $curr_line["line_item"] = $row["line_item"];
        $curr_line["line_value"] = $row["line_value"];
        array_push($data, $curr_line);
    }

    return json_encode($data);
}

function constructGradingRubricTableHeadings() {
    $data = [];

    $line_type = [];
    $line_type["title"] = "Line Type";
    $line_type["field"] = "line_type";
    $line_type["formatter"] = "loadGradingRubricLineType";
    $line_type["width"] = "15";
    $line_type["widthUnit"] = "%";
    array_push($data, $line_type);

    $line_item = [];
    $line_item["title"] = "Line Item";
    $line_item["field"] = "line_item";
    $line_item["formatter"] = "loadGradingRubricLineItem";
    $line_item["width"] = "60";
    $line_item["widthUnit"] = "%";
    array_push($data, $line_item);

    $line_value = [];
    $line_value["title"] = "Point Value";
    $line_value["field"] = "line_value";
    $line_value["formatter"] = "loadGradingRubricLineValue";
    $line_value["width"] = "5";
    $line_value["widthUnit"] = "%";
    array_push($data, $line_value);

    $actions = [];
    $actions["title"] = "Actions";
    $actions["field"] = "actions";
    $actions["formatter"] = "loadGradingRubricActions";
    $line_value["width"] = "20";
    $line_value["widthUnit"] = "%";
    array_push($data, $actions);

    return json_encode($data);
}

function constructMakefileTableData($get_makefile_table_result) {
    $data = [];
    while($row = $get_makefile_table_result->fetch_assoc()) {
        $curr_file = [];
        $curr_file["file_name"] = "Makefile";
        array_push($data, $curr_file);
    }

    return json_encode($data);
}

function constructMakefileTableHeadings() {
    $data = [];

    $file_name = [];
    $file_name["title"] = "File Name";
    $file_name["field"] = "file_name";
    $file_name["width"] = "30";
    $file_name["widthUnit"] = "%";
    array_push($data, $file_name);

    $actions = [];
    $actions["title"] = "Actions";
    $actions["field"] = "actions";
    $actions["width"] = "70";
    $actions["widthUnit"] = "%";
    $actions["formatter"] = "loadAuxiliaryFileActions";
    array_push($data, $actions);

    return json_encode($data);
}

function constructIOFileTableData($get_io_file_table_result) {
    $data = [];
    while($row = $get_io_file_table_result->fetch_assoc()) {
        $curr_file = [];
        $curr_file["file_type"] = explode("_", $row["file_type"])[1];
        $curr_file["file_name"] = $row["file_name"];
        array_push($data, $curr_file);
    }
    return json_encode($data);
}

function constructIOFileTableHeadings() {
    $data = [];

    $file_type = [];
    $file_type["title"] = "File Type";
    $file_type["field"] = "file_type";
    $file_type["width"] = "15";
    $file_type["widthUnit"] = "%";
    array_push($data, $file_type);

    $file_name = [];
    $file_name["title"] = "File Name";
    $file_name["field"] = "file_name";
    $file_name["width"] = "25";
    $file_name["widthUnit"] = "%";
    array_push($data, $file_name);

    $actions = [];
    $actions["title"] = "Actions";
    $actions["field"] = "actions";
    $actions["width"] = "60";
    $actions["widthUnit"] = "%";
    $actions["formatter"] = "loadAuxiliaryFileActions";
    array_push($data, $actions);

    return json_encode($data);
}

function constructExtensionsTableData($extensions_result, $conn) {
    $data = [];
    while($row = $extensions_result->fetch_assoc()) {
        $curr_extension = [];
        $curr_extension["name_string"] = getNameStringFromCampusID($row["student_id"], $conn);
        $new_due_date = null;
        try {
            $new_due_date = new DateTime($row["new_due_date"]);
        } catch(Exception $e) {
            echo "ERROR: Date Time screw up when constructing extensions table!";
        }
        $curr_extension["new_due_date"] = $new_due_date->format("l, F jS, Y, g:i:s A");

        array_push($data, $curr_extension);
    }
    return json_encode($data);
}

function constructExtensionsTableHeadings() {
    $data = [];

    $name_string = [];
    $name_string["title"] = "Student";
    $name_string["field"] = "name_string";
    $name_string["width"] = "35";
    $name_string["widthUnit"] = "%";
    array_push($data, $name_string);

    $new_due_date = [];
    $new_due_date["title"] = "New Due Date";
    $new_due_date["field"] = "new_due_date";
    $new_due_date["width"] = "35";
    $new_due_date["widthUnit"] = "%";
    array_push($data, $new_due_date);

    $actions = [];
    $actions["title"] = "Actions";
    $actions["field"] = "actions";
    $actions["width"] = "30";
    $actions["widthUnit"] = "%";
    $actions["formatter"] = "loadExtensionsTableActions";
    array_push($data, $actions);

    return json_encode($data);
}

function getNameStringFromCampusID($campus_id, $conn) {
    $get_full_name_sql = "SELECT firstname, lastname, umbc_name_id FROM Users WHERE umbc_id = '$campus_id'";
    $name_result = $conn->query($get_full_name_sql)->fetch_assoc();
    $full_name = $name_result["firstname"] . " " . $name_result["lastname"];
    $return_string = $full_name . " (" . $name_result["umbc_name_id"] . ")";
    return $return_string;
}

function constructUserTableData($user_result) {
    $data = [];
    while ($row = $user_result->fetch_assoc()) {
        $curr_user = [];
        $curr_user["campus_id"] = $row['umbc_id'];
        $curr_user["last_name"] = $row['lastname'];
        $curr_user["first_name"] = $row['firstname'];
        $curr_user["name_id"] = $row['umbc_name_id'];
        array_push($data, $curr_user);
    }
    return json_encode($data);
}
function constructUserTableHeadings() {
    $data = [];

    $campus_id = [];
    $campus_id["title"] = "Campus ID";
    $campus_id["field"] = "campus_id";
    $campus_id["sortable"] = true;
    array_push($data, $campus_id);

    $last_name = [];
    $last_name["title"] = "Last Name";
    $last_name["field"] = "last_name";
    $last_name["sortable"] = true;
    array_push($data, $last_name);

    $first_name = [];
    $first_name["title"] = "First Name";
    $first_name["field"] = "first_name";
    $first_name["sortable"] = true;
    array_push($data, $first_name);

    $name_id = [];
    $name_id["title"] = "Name ID";
    $name_id["field"] = "name_id";
    $name_id["sortable"] = true;
    array_push($data, $name_id);

    $actions = [];
    $actions["title"] = "Actions";
    $actions["field"] = "actions";
    $actions["formatter"] = "loadUserTableForExtensionsActions";
    $actions["searchable"] = false;
    array_push($data, $actions);

    return json_encode($data);
}

function getCampusIDFromExtensionString($student_string, $conn) {
    $umbc_name_id = substr(explode("(", $student_string)[1], 0, -1);
    $get_campus_id_sql = "SELECT umbc_id FROM Users WHERE umbc_name_id = '$umbc_name_id'";
    $get_campus_id_result = $conn->query($get_campus_id_sql);
    return $get_campus_id_result->fetch_assoc()["umbc_id"];
}

function verifyAssignmentForVisibility($assignment_name, $conn) {
    // First, check if assignment is already visible in the first place
    $check_assignment_visibility_sql = "SELECT is_visible FROM Assignments WHERE assignment_name = '$assignment_name'";
    $assignment_visibility = $conn->query($check_assignment_visibility_sql)->fetch_assoc()["is_visible"];
    if(intval($assignment_visibility) === 1) {
        return "Visible to students!";
    }

    // Visibility Verification
    // 1 - Is there a document link set? - DONE
    // 2 - Do the base part points add up *exactly* to the assignment total? - DONE
    // 3 - Is there a sample makefile uploaded for each part? - DONE
    // 4 - Is there at least one sample input file AND one sample output file uploaded for each part? - DONE

    $doc_link_sql = "SELECT point_value, document_link FROM Assignments WHERE assignment_name = '$assignment_name'";
    $row = $conn->query($doc_link_sql)->fetch_assoc();
    $doc_link_result = $row["document_link"];
    if($doc_link_result === null) {
        return "ERROR: You must fill in a document link in the details panel!";
    }
    $assignment_point_value = $row["point_value"];

    $assignment_parts_sql = "SELECT part_name, point_value, extra_credit FROM SubmissionParts WHERE assignment_name = '$assignment_name'";
    $assignment_parts_result = $conn->query($assignment_parts_sql);
    $part_base_points_total = 0;
    while($row = $assignment_parts_result->fetch_assoc()) {
        $curr_part_name = $row["part_name"];
        $curr_point_value = $row["point_value"];
        $curr_ec_value = $row["extra_credit"];

//        $curr_rubric_total = 0;
//        $rubric_part_points_sql = "SELECT line_value FROM RubricParts WHERE assignment_name = '$assignment_name' AND part_name = '$curr_part_name'";
//        $rubric_part_points_result = $conn->query($rubric_part_points_sql);
//        while($rubric_row = $rubric_part_points_result->fetch_assoc()) {
//            $curr_rubric_total += $rubric_row["line_value"];
//        }
//        if($curr_rubric_total !== ($curr_point_value + $curr_ec_value)) {
//            $good_to_go = false;
//            return "ERROR: The rubric point total for " . $curr_part_name . " must be equal to the sum of the base points and extra credit points for that part!";
//        }

        $check_makefile_sql = "SELECT id_number FROM AuxiliaryFiles WHERE assignment_name = '$assignment_name' AND part_name = '$curr_part_name' AND file_type = 'SAMPLE_MAKEFILE'";
        $check_makefile_result = $conn->query($check_makefile_sql);
        if($check_makefile_result->num_rows === 0) {
            return "ERROR: You must provide a sample makefile for " . $curr_part_name . "!";
        }

        $check_input_file_sql = "SELECT id_number FROM AuxiliaryFiles WHERE assignment_name = '$assignment_name' AND part_name = '$curr_part_name' AND file_type = 'SAMPLE_INPUT'";
        $check_input_file_result = $conn->query($check_input_file_sql);
        if($check_input_file_result->num_rows === 0) {
            return "ERROR: You must provide at least one sample input file for '" . $curr_part_name . "'!";
        }

        $check_output_file_sql = "SELECT id_number FROM AuxiliaryFiles WHERE assignment_name = '$assignment_name' AND part_name = '$curr_part_name' AND file_type = 'SAMPLE_OUTPUT'";
        $check_output_file_result = $conn->query($check_output_file_sql);
        if($check_output_file_result->num_rows === 0) {
            return "ERROR: You must provide at least one sample output file for '" . $curr_part_name . "'!";
        }

        $part_base_points_total += $curr_point_value;
    }
    if(intval($part_base_points_total) !== intval($assignment_point_value)) {
        return "ERROR: The sum of the base points for all the assignment parts must equal the total assignment point value!";
    }

    // If it gets here, then all the verification is finished and passed!
    return "Hidden, but ready to make visible to students!";
}