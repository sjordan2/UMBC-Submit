<?php

require_once '../includes/db_sql.php';
require_once 'user_functions.php';

$conn = new mysqli($sql_host, $sql_username, $sql_password, $sql_dbname);
if($conn->connect_error) {
    echo "ERROR: " . $conn->connect_error;
} else {
    $action = $_POST["action"]; // Required action variable
    if ($action === "newUser") {

        $new_user_array = [];

        $umbc_id = $_POST["userID"];
        $umbc_name_id = $_POST["nameID"];
        $firstname = $conn->real_escape_string($_POST["firstname"]);
        $lastname = $conn->real_escape_string($_POST["lastname"]);
        $section = $_POST["discussion"];
        $role = $_POST["role"];
        $alpha_num_key = getToken(64);
        $new_user_sql = null;
        if($section === "NULL") {
            $new_user_sql = "INSERT INTO Users (umbc_id, umbc_name_id, firstname, lastname, role, status, alpha_num_key, email_sent)
                    VALUES ('$umbc_id', '$umbc_name_id', '$firstname',
                   '$lastname', '$role', 'Active', '$alpha_num_key', '0')";
        } else {
            $new_user_sql = "INSERT INTO Users (umbc_id, umbc_name_id, firstname, lastname, section, role, status, alpha_num_key, email_sent)
                        VALUES ('$umbc_id', '$umbc_name_id', '$firstname',
                       '$lastname', '$section', '$role', 'Active', '$alpha_num_key', '0')";
        }
        $new_user_result = $conn->query($new_user_sql);
        if($new_user_result === true) {
            $new_user_array["message"] = "SUCCESS: " . $firstname . " " . $lastname . " (" . $umbc_name_id . ") has been added to the database!";
        } else {
            $new_user_array["message"] = "ERROR: " . $conn->error;
        }

        $users_table_sql = "SELECT umbc_id, umbc_name_id, firstname, lastname, section, role, status FROM Users";
        $users_table_result = $conn->query($users_table_sql);
        $new_user_array["userCount"] = $users_table_result->num_rows;

        $non_emailed_users_sql = "SELECT umbc_id FROM Users WHERE email_sent = '0'";
        $non_emailed_users_result = $conn->query($non_emailed_users_sql);
        $new_user_array["emailCount"] = $non_emailed_users_result->num_rows;

        $page_size = intval($_POST["pageSize"]);

        $sql_row_num_query = "WITH num_map AS (SELECT ROW_NUMBER() OVER (ORDER BY umbc_id) row_num, umbc_id FROM Users ORDER BY umbc_id) SELECT row_num FROM num_map WHERE umbc_id = '$umbc_id'";
        $row_num_result = $conn->query($sql_row_num_query);
        $position = intval($row_num_result->fetch_assoc()['row_num']);

        $page = intdiv($position, $page_size);
        $new_user_array['page'] = $page + 1;
        $row_index = $position % $page_size;
        $new_user_array['row'] = $row_index - 1;

        $users_table_sql = "SELECT umbc_id, umbc_name_id, firstname, lastname, section, role, status FROM Users";
        $users_table_result = $conn->query($users_table_sql);
        $new_user_array['table'] = constructUserTableData($users_table_result);
        $new_user_array["columns"] = constructUserTableHeadings();

        echo json_encode($new_user_array);
    } else if($action === "send_email") {
        $campus_id = $_POST['umbc_id'];
        $email_student_sql = "SELECT umbc_name_id, role, firstname, lastname, section FROM Users WHERE umbc_id = '$campus_id'"; // The list of students to email
        $email_student_result = $conn->query($email_student_sql);
        $student_row = $email_student_result->fetch_assoc();

        $get_instructors_sql = "SELECT firstname, lastname, umbc_name_id FROM Users WHERE role = 'Instructor'";
        $get_instructors_result = $conn->query($get_instructors_sql);
        $instructor_array = [];
        while ($instructor_row = $get_instructors_result->fetch_assoc()) {
            $signature_string = $instructor_row['firstname'] . " " . $instructor_row['lastname'] . " <" . $instructor_row['umbc_name_id'] . "@umbc.edu>";
            array_push($instructor_array, $signature_string);
        }

        $return_array = [];
        if($email_student_result->num_rows > 1) {
            array_push($return_array, "ERROR: Duplicate entries in database for campus ID:" . $campus_id . "!");
        } else {
            $update_user_email_sql = "UPDATE Users SET email_sent = '1' WHERE umbc_id = '$campus_id'";
            $sql_result = $conn->query($update_user_email_sql);
            array_push($return_array, email_one_user($campus_id, $student_row["umbc_name_id"], $student_row["role"], $student_row["firstname"], $student_row["lastname"], $student_row["section"], $instructor_array, $submit_system_admin, true));
        }

        $non_emailed_users_sql = "SELECT umbc_id FROM Users WHERE email_sent = '0'";
        $non_emailed_users_result = $conn->query($non_emailed_users_sql);
        array_push($return_array, $non_emailed_users_result->num_rows);

        echo json_encode($return_array);
    } else if($action === "email_all") {
        $email_results = [];
        $students_to_email_sql = "SELECT umbc_id, umbc_name_id, role, firstname, lastname, section FROM Users WHERE email_sent = '0'";
        $students_to_email_result = $conn->query($students_to_email_sql);

        $get_instructors_sql = "SELECT firstname, lastname, umbc_name_id FROM Users WHERE role = 'Instructor'";
        $get_instructors_result = $conn->query($get_instructors_sql);
        $instructor_array = [];
        while ($instructor_row = $get_instructors_result->fetch_assoc()) {
            $signature_string = $instructor_row['firstname'] . " " . $instructor_row['lastname'] . " <" . $instructor_row['umbc_name_id'] . "@umbc.edu>";
            array_push($instructor_array, $signature_string);
        }

        while($user_row = $students_to_email_result->fetch_assoc()) {
            $campus_id = $user_row["umbc_id"];
            $update_user_email_sql = "UPDATE Users SET email_sent = '1' WHERE umbc_id = '$campus_id'";
            $sql_result = $conn->query($update_user_email_sql);
            $curr_result = email_one_user($campus_id, $user_row["umbc_name_id"], $user_row["role"], $user_row["firstname"], $user_row["lastname"], $user_row["section"], $instructor_array, $submit_system_admin, false);
            if($curr_result !== false) { // There was an error with the mail sending for this user
                array_push($email_results, $curr_result);
            }
        }

        echo json_encode($email_results);
    } else if($action === "editUser") {
        $edit_user_array = [];
        $umbc_id = $_POST["userID"];
        $umbc_name_id = $_POST["nameID"];
        $firstname = $conn->real_escape_string($_POST["firstname"]);
        $lastname = $conn->real_escape_string($_POST["lastname"]);
        $section = $_POST["discussion"];
        $role = $_POST["role"];
        $status = $_POST["status"];

        $edit_user_sql = null;
        if ($section === "NULL") {
            $edit_user_sql = "UPDATE Users SET firstname = '$firstname', lastname = '$lastname', section = NULL,
                 umbc_name_id = '$umbc_name_id', role = '$role', status = '$status' WHERE umbc_id = '$umbc_id'";
        } else {
            $edit_user_sql = "UPDATE Users SET firstname = '$firstname', lastname = '$lastname', section = '$section',
                 umbc_name_id = '$umbc_name_id', role = '$role', status = '$status' WHERE umbc_id = '$umbc_id'";
        }
        $edit_user_result = $conn->query($edit_user_sql);
        if ($edit_user_result === true) {
            $edit_user_array["message"] = "SUCCESS: " . $umbc_id . " has been updated in the database!";
        } else {
            $edit_user_array["message"] = "ERROR: " . $conn->error;
        }
        $users_table_sql = "SELECT umbc_id, umbc_name_id, firstname, lastname, section, role, status FROM Users";
        $users_table_result = $conn->query($users_table_sql);
        $edit_user_array["userCount"] = $users_table_result->num_rows;

        $non_emailed_users_sql = "SELECT umbc_id FROM Users WHERE email_sent = '0'";
        $non_emailed_users_result = $conn->query($non_emailed_users_sql);
        $edit_user_array["emailCount"] = $non_emailed_users_result->num_rows;

        $page_size = intval($_POST["pageSize"]);

        $sql_row_num_query = "WITH num_map AS (SELECT ROW_NUMBER() OVER (ORDER BY umbc_id) row_num, umbc_id FROM Users ORDER BY umbc_id) SELECT row_num FROM num_map WHERE umbc_id = '$umbc_id'";
        $row_num_result = $conn->query($sql_row_num_query);
        $position = intval($row_num_result->fetch_assoc()['row_num']);

        $page = intdiv($position, $page_size);
        $edit_user_array['page'] = $page + 1;
        $row_index = $position % $page_size;
        $edit_user_array['row'] = $row_index - 1;

        $users_table_sql = "SELECT umbc_id, umbc_name_id, firstname, lastname, section, role, status FROM Users";
        $users_table_result = $conn->query($users_table_sql);
        $edit_user_array['table'] = constructUserTableData($users_table_result);
        $edit_user_array["columns"] = constructUserTableHeadings();

        echo json_encode($edit_user_array);
    } else if($action === "delete") {
        $data_array = [];
        $user_to_delete = $_POST["user"];
        // Get the position of the user we want to delete - this is so when the user is deleted, we go to the page/location that the user was in (instead of returning to the first page).

        $delete_user_sql = "DELETE FROM Users WHERE umbc_id = '$user_to_delete'";
        $delete_user_result = $conn->query($delete_user_sql);
        if($delete_user_result === true) {
            $data_array['message'] = "SUCCESS: " . $user_to_delete . " has been removed from the database!";
        } else {
            $data_array['message'] = "ERROR: " . $conn->error;
        }

        $users_table_sql = "SELECT umbc_id, umbc_name_id, firstname, lastname, section, role, status FROM Users";
        $users_table_result = $conn->query($users_table_sql);
        $data_array['userCount'] = $users_table_result->num_rows;

        $non_emailed_users_sql = "SELECT umbc_id FROM Users WHERE email_sent = '0'";
        $non_emailed_users_result = $conn->query($non_emailed_users_sql);
        $data_array["emailCount"] = $non_emailed_users_result->num_rows;

        $data_array['table'] = constructUserTableData($users_table_result);
        $data_array["columns"] = constructUserTableHeadings();

        echo json_encode($data_array);
    } else if($action === "upload_students") {
        ignore_user_abort();

        $REX_LASTNAME = "StudentLastName"; // Last name column header in Rex
        $REX_FIRSTNAME = "StudentFirstName"; // First name column header in Rex
        $REX_CAMPUSID = "StudentCampusID"; // Campus ID column header in Rex
        $REX_NAMEID = "StudentMyUMBCId"; // Name ID column header in Rex
        $REX_DISCUSSION = "ClassNumberClassSectionSourceKey"; // Lecture/Discussion Section column header in Rex

        $students_file = fopen($_FILES['rexFile']['tmp_name'], 'r');
        $headerLine = fgets($students_file);
        $fileDelimiter = null;
        if(strpos($headerLine, ",") !== false) { // If there is a comma in the header line
            $fileDelimiter = ",";
        } else {
            $fileDelimiter = "\t";
        }

        $lastNameColumn = null;
        $firstNameColumn = null;
        $campusIDColumn = null;
        $nameIDColumn = null;
        $discussionColumn = null;

        $headerArray = explode($fileDelimiter, $headerLine);
        $lastNameColumn = array_search($REX_LASTNAME, $headerArray);
        $firstNameColumn = array_search($REX_FIRSTNAME, $headerArray);
        $campusIDColumn = array_search($REX_CAMPUSID, $headerArray);
        $nameIDColumn = array_search($REX_NAMEID, $headerArray);
        $discussionColumn = array_search($REX_DISCUSSION, $headerArray);

        $success_counter = 0;
        $error_occurred = false;
        $new_user_sql = "INSERT IGNORE INTO Users (umbc_id, umbc_name_id, firstname, lastname, section, role, status, alpha_num_key, email_sent) VALUES";
        while (($line = fgetcsv($students_file, 0, $fileDelimiter)) !== false) {
            // This is assuming that lecture sections are "10, 20, 30", etc. and discussion sections are "11, 34, 56" etc.
            if(intval($line[$discussionColumn]) % 10 !== 0) { // If the row denotes a discussion section, not a lecture section
                $umbc_id = $line[$campusIDColumn];
                $umbc_name_id = $line[$nameIDColumn];
                $firstname = $conn->real_escape_string($line[$firstNameColumn]);
                $lastname = $conn->real_escape_string($line[$lastNameColumn]);
                $section = $line[$discussionColumn];
                $alpha_num_key = getToken(64);
                $new_user_sql .= "('$umbc_id', '$umbc_name_id', '$firstname',
                       '$lastname', '$section', 'Student', 'Active', '$alpha_num_key', '0'),";
            }
        }
        $new_user_sql = substr($new_user_sql, 0, -1); // Strip the comma from the end
        $new_user_result = $conn->query($new_user_sql);

        $error_list = [];
        if($conn->warning_count > 0) { // Then there was an error with some student's data

            $warning_list = $conn->get_warnings();
            do {
                $error_occurred = true;
                array_push($error_list, "ERROR " . $warning_list->errno . ": " . $warning_list->message);
            } while($warning_list->next());
        }

        $return_array = [];
        if($error_occurred === true) {
            $return_array["message"] = "ERROR";
            $return_array["error_list"] = $error_list;
        } else {
            $return_array["message"] = "SUCCESS";
        }
        $users_table_sql = "SELECT umbc_id, umbc_name_id, firstname, lastname, section, role, status FROM Users";
        $users_table_result = $conn->query($users_table_sql);

        $return_array["userCount"] = $users_table_result->num_rows;

        $non_emailed_users_sql = "SELECT umbc_id FROM Users WHERE email_sent = '0'";
        $non_emailed_users_result = $conn->query($non_emailed_users_sql);
        $return_array["emailCount"] = $non_emailed_users_result->num_rows;

        $return_array["table"] = constructUserTableData($users_table_result);
        $return_array["columns"] = constructUserTableHeadings();

        echo json_encode($return_array);
    } else if($action === "refresh_table") {
        $users_table_sql = "SELECT umbc_id, umbc_name_id, firstname, lastname, section, role, status FROM Users";
        $users_table_result = $conn->query($users_table_sql);
        $data_array = [];
        $data_array["table"] = constructUserTableData($users_table_result);
        $data_array["columns"] = constructUserTableHeadings();
        echo json_encode($data_array);
    }
}