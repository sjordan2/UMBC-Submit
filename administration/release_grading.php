<?php

include '../sql_functions.php';

// Create connection
$conn = new mysqli($sql_host, $sql_username, $sql_password, $sql_dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$assignment_name = $_POST['assignment'];

$assignment_sql = $conn->real_escape_string($assignment_name);
$check_if_released_sql = "SELECT grades_released FROM Assignments WHERE assignment_name = '$assignment_sql'";
if($conn->query($check_if_released_sql)->fetch_assoc()['grades_released'] === "1") {
    echo "ERROR: Grades have already been released for this assignment!";
    exit();
}


$get_non_students_sql = "SELECT umbc_id, umbc_name_id, section FROM Users WHERE role != 'Student'"; // Gets instructors and tas
$get_non_students_result = $conn->query($get_non_students_sql);
$good_to_release = true;
$incomplete_tas = "";
while($row = $get_non_students_result->fetch_assoc()) {
    $ta_status_array = getAssignmentGradingStatus($row["umbc_id"], $assignment_name, $conn);
    if($ta_status_array[0] != $ta_status_array[1]) {
        $good_to_release = false;
        if($incomplete_tas === "") {
            $incomplete_tas = $row["umbc_name_id"];
        } else {
            $incomplete_tas .= ", " . $row["umbc_name_id"];
        }
    }
}

if($good_to_release === false) {
    echo "ERROR: The following TAs have not finished grading: " . $incomplete_tas;
} else {
    $get_max_points_sql = "SELECT max_points FROM Assignments WHERE assignment_name = '$assignment_sql'";
    $assignment_max_points = $conn->query($get_max_points_sql)->fetch_assoc()["max_points"];

    $total_num_students_sql = "SELECT umbc_id FROM Users WHERE role = 'Student'";
    $total_num_students = $conn->query($total_num_students_sql)->num_rows;

    $list_of_tas_instructors = $conn->query($get_non_students_sql);
    $ta_file = fopen("ta_grade_release_email.txt", "r");
    $ta_email_base = fread($ta_file, filesize("ta_grade_release_email.txt"));

    $instructor_file = fopen("instructor_grade_release_email.txt", "r");
    $instructor_email_base = fread($instructor_file, filesize("instructor_grade_release_email.txt"));

    $student_file = fopen("student_grade_release_email.txt", "r");
    $student_email_base = fread($student_file, filesize("student_grade_release_email.txt"));

    $class_stats = getCourseStats($assignment_name, $conn);

    $get_instructors_sql = "SELECT firstname, lastname, umbc_name_id FROM Users WHERE role = 'Instructor'";
    $get_instructors_result = $conn->query($get_instructors_sql);
    $instructor_array = [];
    while($row = $get_instructors_result->fetch_assoc()) {
        $signature_string = $row['firstname'] . " " . $row['lastname'] . " <" . $row['umbc_name_id'] . "@umbc.edu>";
        array_push($instructor_array, $signature_string);
    }

    while($row = $list_of_tas_instructors->fetch_assoc()) {
        $num_students = getNumStudentsInSection($row["section"], $conn);
        if($num_students !== 0) {
            $ta_campus_id = $row["umbc_id"];
            $section_stats = getSectionStats($row["section"], $assignment_name, $conn);
            $curr_ta_email = $ta_email_base;
            $curr_ta_email = str_replace("{{assignment_name}}", $assignment_name, $curr_ta_email);

            $curr_ta_email = str_replace("{{umbc_name_id}}", $row["umbc_name_id"], $curr_ta_email);
            $curr_ta_email = str_replace("{{section}}", $row["section"], $curr_ta_email);
            $curr_ta_email = str_replace("{{max_points}}", $assignment_max_points, $curr_ta_email);

            $curr_ta_email = str_replace("{{num_students}}", $num_students, $curr_ta_email);
            $curr_ta_email = str_replace("{{section_average}}", $section_stats[0], $curr_ta_email);
            $curr_ta_email = str_replace("{{section_median}}", $section_stats[1], $curr_ta_email);
            $curr_ta_email = str_replace("{{section_sd}}", $section_stats[2], $curr_ta_email);

            $curr_ta_email = str_replace("{{total_num_students}}", $total_num_students, $curr_ta_email);
            $curr_ta_email = str_replace("{{total_average}}", $class_stats[0], $curr_ta_email);
            $curr_ta_email = str_replace("{{total_median}}", $class_stats[1], $curr_ta_email);
            $curr_ta_email = str_replace("{{total_sd}}", $class_stats[2], $curr_ta_email);

            $curr_ta_email .= "\n\n";
            foreach($instructor_array as $signature) {
                $curr_ta_email .= $signature;
                $curr_ta_email .= "\n";
            }
            $curr_ta_email .= "\n";

            $email_recipient = $row["umbc_name_id"] . "@umbc.edu";
            $email_subject = "Grade Release and Section Statistics for " . $assignment_name;
            $email_headers = "From: UMBC Submit System <umbc-submit@csee.umbc.edu>" . "\r\n" . 'X-Mailer: PHP/' . phpversion();

            $error_host = "-f" . $submit_system_admin . "@umbc.edu";
            $mail_status = mail($email_recipient, $email_subject, $curr_ta_email, $email_headers, $error_host);
        }
    }

    $get_instructor_emails_sql = "SELECT umbc_name_id FROM Users WHERE role = 'Instructor'";
    $get_instructor_emails_results = $conn->query($get_instructor_emails_sql);
    while($row = $get_instructor_emails_results->fetch_assoc()) {
        $curr_instructor_email = $instructor_email_base;
        $curr_instructor_email = str_replace("{{assignment_name}}", $assignment_name, $curr_instructor_email);

        $curr_instructor_email = str_replace("{{max_points}}", $assignment_max_points, $curr_instructor_email);

        $curr_instructor_email = str_replace("{{total_num_students}}", $total_num_students, $curr_instructor_email);
        $curr_instructor_email = str_replace("{{total_average}}", $class_stats[0], $curr_instructor_email);
        $curr_instructor_email = str_replace("{{total_median}}", $class_stats[1], $curr_instructor_email);
        $curr_instructor_email = str_replace("{{total_sd}}", $class_stats[2], $curr_instructor_email);

        $curr_instructor_email .= "\n\n";
        foreach($instructor_array as $signature) {
            $curr_instructor_email .= $signature;
            $curr_instructor_email .= "\n";
        }
        $curr_instructor_email .= "\n";

        $email_recipient = $row["umbc_name_id"] . "@umbc.edu";
        $email_subject = "Grade Release and Course Statistics for " . $assignment_name;
        $email_headers = "From: UMBC Submit System <umbc-submit@csee.umbc.edu>" . "\r\n" . 'X-Mailer: PHP/' . phpversion();

        $error_host = "-f" . $submit_system_admin . "@umbc.edu";
        $mail_status = mail($email_recipient, $email_subject, $curr_instructor_email, $email_headers, $error_host);
    }

    $get_student_emails_sql = "SELECT umbc_name_id, firstname, lastname FROM Users WHERE role = 'Student'";
    $get_student_emails_results = $conn->query($get_student_emails_sql);
    while($row = $get_student_emails_results->fetch_assoc()) {
        $curr_student_email = $student_email_base;
        $curr_student_email = str_replace("{{firstname}}", $row["firstname"], $curr_student_email);
        $curr_student_email = str_replace("{{lastname}}", $row["lastname"], $curr_student_email);

        $curr_student_email = str_replace("{{assignment_name}}", $assignment_name, $curr_student_email);

        $curr_student_email = str_replace("{{web_grade_link}}", "https://www.csee.umbc.edu/~sjordan2/grades.php", $curr_student_email);

        $curr_student_email .= "\n\n";
        foreach($instructor_array as $signature) {
            $curr_student_email .= $signature;
            $curr_student_email .= "\n";
        }
        $curr_student_email .= "\n";

        $email_recipient = $row["umbc_name_id"] . "@umbc.edu";
        $email_subject = "Grade Release for " . $assignment_name;
        $email_headers = "From: UMBC Submit System <umbc-submit@csee.umbc.edu>" . "\r\n" . 'X-Mailer: PHP/' . phpversion();

        $error_host = "-f" . $submit_system_admin . "@umbc.edu";
        $mail_status = mail($email_recipient, $email_subject, $curr_student_email, $email_headers, $error_host);
    }

    $update_grade_release_sql = "UPDATE Assignments SET grades_released = '1' WHERE assignment_name = '$assignment_sql'";
    $conn->query($update_grade_release_sql);
    echo "SUCCESS: Grades have been released for this assignment!";
}