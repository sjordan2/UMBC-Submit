<?php

require_once 'db_sql.php';

function getFullNameFromCampusID($campusID, $conn): string {
    $campusID_sql = "SELECT lastname, firstname FROM Users WHERE umbc_id = '$campusID'";
    $result = $conn->query($campusID_sql);
    if($result->num_rows != 1) {
        return "ERROR";
    } else {
        $row = $result->fetch_assoc();
        return $row['firstname'] . " " . $row['lastname'];
    }
}
function getEnrollment($campusID, $conn) {
    $campusID_sql = "SELECT role FROM Users WHERE umbc_id = '$campusID'";
    $result = $conn->query($campusID_sql);
    if($result->num_rows < 1) {
        return false;
    } else {
        $row = $result->fetch_assoc();
        return $row['role'];
    }
}

function outputInstructors($conn): String {
    $get_instructors_sql = "SELECT firstname, lastname, umbc_name_id FROM Users WHERE role = 'Instructor'";
    $get_instructors_result = $conn->query($get_instructors_sql);
    $response_text = "";
    while($row = $get_instructors_result->fetch_assoc()) {
        $response_text .= "<b>" . $row["firstname"] . " " . $row["lastname"] . " &lt;" . $row["umbc_name_id"] . "@umbc.edu&gt; </b><br>";
    }
    $response_text .= "<hr>";
    return htmlspecialchars($response_text);
}

function getCurrentSubmissionNumber($user_id, $assignment, $part, $conn): int {
    // The below query gets all the student's submissions for this part
    $submission_check_sql = "SELECT submission_number FROM Submissions WHERE student_id = '$user_id' AND assignment_name = '$assignment' AND part_name = '$part'";
    $submission_check_result = $conn->query($submission_check_sql);
    if($submission_check_result->num_rows === 0) {
        return 1;
    } else {
        // The below query selects the one submission that has the highest submission number and returns that number
        $number_parts_submission = "SELECT MAX(submission_number) AS SubmissionMax FROM Submissions WHERE student_id = '$user_id' AND assignment_name = '$assignment' AND part_name = '$part' LIMIT 1";
        return $conn->query($number_parts_submission)->fetch_assoc()['SubmissionMax'] + 1;
    }
}

function getPartSubmissionStatus($user_id, $assignment, $part, $conn) {
    $part_submission_sql = "SELECT date_submitted FROM Submissions WHERE student_id = '$user_id' AND assignment_name = '$assignment' AND part_name = '$part' ORDER BY date_submitted DESC";
    $part_check_result = $conn->query($part_submission_sql);
    if($part_check_result->num_rows === 0) {
        return null;
    } else {
        $returned_date_object = null;
        try {
            $returned_date_object = new DateTime($part_check_result->fetch_assoc()["date_submitted"]);
        } catch(Exception $e) {
            echo "Date Time Error when getting part submission status: " . $e;
            exit();
        }
        return $returned_date_object->format("l, F jS, Y, g:i:s A");
    }
}

////////
////////

function ensureUsersTableCreation($conn) {
    $sql = "SHOW TABLES LIKE 'Users';";
    $result = $conn->query($sql);
    if($result->num_rows == 0) {
        $newtable_sql = "CREATE TABLE Users (
                        umbc_id VARCHAR(30) PRIMARY KEY,
                        umbc_name_id VARCHAR(30) NOT NULL,
                        firstname VARCHAR(30) NOT NULL,
                        lastname VARCHAR(30) NOT NULL,
                        section INT,
                        role VARCHAR(30) NOT NULL,
                        status VARCHAR(15) NOT NULL,
                        alpha_num_key VARCHAR(70) NOT NULL,
                        email_sent INT NOT NULL
                        )";
        if ($conn->query($newtable_sql) !== TRUE) {
            echo "ERROR: " . $conn->error;
        }
    }
}

function ensureAssignmentsTableCreation($conn) {
    $sql = "SHOW TABLES LIKE 'Assignments';";
    $result = $conn->query($sql);
    if($result->num_rows == 0) {
        $newtable_sql = "CREATE TABLE Assignments (
                        assignment_name VARCHAR(50) PRIMARY KEY,
                        date_assigned DATETIME NOT NULL,
                        date_due DATETIME NOT NULL,
                        max_points INT NOT NULL,
                        extra_credit INT NOT NULL,
                        document_link VARCHAR(150),
                        grading_due_date DATETIME NOT NULL,
                        grades_released INT NOT NULL,
                        is_visible INT NOT NULL
                        )";
        if ($conn->query($newtable_sql) !== TRUE) {
            echo "ERROR: " . $conn->error;
        }
    }
}

function ensureExtensionsTableCreation($conn) {
    $sql = "SHOW TABLES LIKE 'Extensions';";
    $result = $conn->query($sql);
    if($result->num_rows == 0) {
        $newtable_sql = "CREATE TABLE Extensions (
                        id_number INT PRIMARY KEY AUTO_INCREMENT,
                        student_id VARCHAR(30) NOT NULL,
                        CONSTRAINT student_in_database
                        FOREIGN KEY (student_id) 
                            REFERENCES Users (umbc_id)
                            ON DELETE CASCADE 
                            ON UPDATE CASCADE,
                        assignment VARCHAR(30) NOT NULL,
                        CONSTRAINT assignment_in_database
                        FOREIGN KEY (assignment) 
                            REFERENCES Assignments (assignment_name)
                            ON DELETE CASCADE 
                            ON UPDATE CASCADE,
                        date_granted DATETIME NOT NULL,
                        new_due_date DATETIME NOT NULL
                          )";
        if ($conn->query($newtable_sql) !== TRUE) {
            echo "ERROR: " . $conn->error;
        }
    }
}

function ensureSubmissionPartsTableCreation($conn) {
    $sql = "SHOW TABLES LIKE 'SubmissionParts';";
    $result = $conn->query($sql);
    if($result->num_rows == 0) {
        $newtable_sql = "CREATE TABLE SubmissionParts (
                        id_number INT PRIMARY KEY AUTO_INCREMENT,
                        assignment VARCHAR(30) NOT NULL,
                        CONSTRAINT assignment_for_part
                        FOREIGN KEY (assignment) 
                            REFERENCES Assignments (assignment_name)
                            ON DELETE CASCADE 
                            ON UPDATE CASCADE,
                        part_name VARCHAR(30) NOT NULL,
                        KEY part_name (part_name),
                        point_value INT NOT NULL,
                        submission_file_name VARCHAR(30),
                        KEY submission_file_name (submission_file_name)
                          )";
        if ($conn->query($newtable_sql) !== TRUE) {
            echo "ERROR: " . $conn->error;
        }
    }
}

function ensureSubmissionsTableCreation($conn) {
    $sql = "SHOW TABLES LIKE 'Submissions';";
    $result = $conn->query($sql);
    if($result->num_rows == 0) {
        $newtable_sql = "CREATE TABLE Submissions (
                        id_number INT PRIMARY KEY AUTO_INCREMENT,
                        student_id VARCHAR(30) NOT NULL,
                        CONSTRAINT student_submitting
                        FOREIGN KEY (student_id) 
                            REFERENCES Users (umbc_id)
                            ON DELETE CASCADE
                            ON UPDATE CASCADE,
                        assignment_name VARCHAR(50) NOT NULL,
                        CONSTRAINT assignment_for_submission
                        FOREIGN KEY (assignment_name) 
                            REFERENCES Assignments (assignment_name)
                            ON DELETE CASCADE
                            ON UPDATE CASCADE,
                        part_name VARCHAR(30) NOT NULL,
                        CONSTRAINT assignment_part_of_submission
                        FOREIGN KEY (part_name)
                            REFERENCES SubmissionParts (part_name)
                            ON DELETE CASCADE
                            ON UPDATE CASCADE,
                        submission_number INT NOT NULL,
                        date_submitted DATETIME NOT NULL,
                        submission_file_name VARCHAR(30) NOT NULL,
                        CONSTRAINT file_name_to_submit
                        FOREIGN KEY (submission_file_name) 
                            REFERENCES SubmissionFiles (submission_file_name)
                            ON DELETE CASCADE
                            ON UPDATE CASCADE,
                        submission_contents LONGTEXT NOT NULL
                          )";
        if ($conn->query($newtable_sql) !== TRUE) {
            echo "ERROR: " . $conn->error;
        }
    }
}

function ensureRubricPartsTableCreation($conn) {
    $sql = "SHOW TABLES LIKE 'RubricParts';";
    $result = $conn->query($sql);
    if($result->num_rows == 0) {
        $newtable_sql = "CREATE TABLE RubricParts (
                        id_number INT PRIMARY KEY AUTO_INCREMENT,
                        assignment VARCHAR(30) NOT NULL,
                        CONSTRAINT assignment_for_rubric_part
                        FOREIGN KEY (assignment) 
                            REFERENCES Assignments (assignment_name)
                            ON DELETE CASCADE 
                            ON UPDATE CASCADE,
                        part_name VARCHAR(30) NOT NULL,
                        CONSTRAINT assignment_part_of_rubric_part
                        FOREIGN KEY (part_name)
                            REFERENCES SubmissionParts (part_name)
                            ON DELETE CASCADE
                            ON UPDATE CASCADE,
                        line_type INT NOT NULL,
                        line_item VARCHAR(255),
                        point_value INT,
                        KEY TypeItemValueTuple (line_type, line_item, point_value)
                          )"; // line_type: -1 = TA Note (%%% in old rubric - gets hidden from students), 0 = actual point line item, 1 = Student Note (students can see this)
                              // point_value: NULL -> No point value (used for TA/Student Notes), anything >= 0 -> point value, for actual point line item
        if ($conn->query($newtable_sql) !== TRUE) {
            echo "ERROR: " . $conn->error;
        }
    }
}

function ensureRubricsTableCreation($conn) {
    $sql = "SHOW TABLES LIKE 'Rubrics';";
    $result = $conn->query($sql);
    if($result->num_rows == 0) {
        $newtable_sql = "CREATE TABLE Rubrics (
                        id_number INT PRIMARY KEY AUTO_INCREMENT,
                        student_id VARCHAR(30) NOT NULL,
                        CONSTRAINT student_for_rubric
                        FOREIGN KEY (student_id) 
                            REFERENCES Users (umbc_id)
                            ON DELETE CASCADE
                            ON UPDATE CASCADE,
                        assignment VARCHAR(30) NOT NULL,
                        CONSTRAINT assignment_for_rubric
                        FOREIGN KEY (assignment) 
                            REFERENCES Assignments (assignment_name)
                            ON DELETE CASCADE 
                            ON UPDATE CASCADE,
                        part_name VARCHAR(30) NOT NULL,
                        CONSTRAINT assignment_part_of_rubric
                        FOREIGN KEY (part_name)
                            REFERENCES SubmissionParts (part_name)
                            ON DELETE CASCADE
                            ON UPDATE CASCADE,
                        line_type INT NOT NULL, 
                        line_item VARCHAR(255),
                        point_value INT,
                        CONSTRAINT rubric_line_item_points
                        FOREIGN KEY (line_type, line_item, point_value)
                            REFERENCES RubricParts (line_type, line_item, point_value)
                            ON DELETE CASCADE
                            ON UPDATE CASCADE,
                        points_received INT,
                        grader_comments VARCHAR(760)
                          )"; // line_type: -1 = TA Note (%%% in old rubric - gets hidden from students), 0 = actual point line item, 1 = Student Note (students can see this), 2 = TA Comment
        // point_value: -1 = No point value (used for TA/Student Notes). anything greater than 0 = actual point value, for line item
        if ($conn->query($newtable_sql) !== TRUE) {
            echo "ERROR: " . $conn->error;
        }
    }
}

function ensureTestingTableCreation($conn) {
    $sql = "SHOW TABLES LIKE 'Testing';";
    $result = $conn->query($sql);
    if($result->num_rows == 0) {
        $newtable_sql = "CREATE TABLE Testing (
                        id_number INT PRIMARY KEY AUTO_INCREMENT,
                        assignment VARCHAR(30) NOT NULL,
                        CONSTRAINT assignment_for_test
                        FOREIGN KEY (assignment) 
                            REFERENCES Assignments (assignment_name)
                            ON DELETE CASCADE 
                            ON UPDATE CASCADE,
                        part VARCHAR(30) NOT NULL,
                        CONSTRAINT part_for_test
                        FOREIGN KEY (part)
                            REFERENCES SubmissionParts (part_name)
                            ON DELETE CASCADE
                            ON UPDATE CASCADE,
                        file_type VARCHAR(20) NOT NULL,
                        file_name VARCHAR(30) NOT NULL,
                        file_contents LONGTEXT NOT NULL
                        )";
        if ($conn->query($newtable_sql) !== TRUE) {
            echo "ERROR: " . $conn->error;
        }
    }
}

function getStudentDueDateForAssignment($campus_id, $assignment, $conn): ?DateTime {
    $assignment_name_sql = $conn->real_escape_string($assignment);
    // First, check if they have any extensions for the given assignment
    $extension_sql = "SELECT new_due_date FROM Extensions WHERE assignment = '$assignment_name_sql' AND student_id = '$campus_id'";
    $result_extension = $conn->query($extension_sql);
    if($result_extension->num_rows > 0) { // The student does have an extension - thus, since the extension due date is always greater than the assignment due date, use this one.
        $retrieved_extended_due_date = $result_extension->fetch_assoc()['new_due_date'];
        $student_due_date = null;
        try {
            $student_due_date = new DateTime($retrieved_extended_due_date);
        } catch(Exception $e) {
            echo "Date Time Error: " . $e;
        }
        return $student_due_date;
    } else { // The student does not have an extension = thus, return the course-wide due date for the assignment.
        $assignment_sql = "SELECT date_due FROM Assignments WHERE assignment_name = '$assignment_name_sql'";
        $result_assignment = $conn->query($assignment_sql); // We can assume that the number of rows in this query will always be greater than 0.
        $retrieved_due_date = $result_assignment->fetch_assoc()['date_due'];
        $student_due_date = null;
        try {
            $student_due_date = new DateTime($retrieved_due_date);
        } catch(Exception $e) {
            echo "Date Time Error: " . $e;
        }
        return $student_due_date;
    }
}

function getAssignmentSubmissionStatus($campus_id, $assignment, $conn): int {
    $assignment_name_sql = $conn->real_escape_string($assignment);
    $student_submission_sql = "SELECT DiSTINCT assignment_part FROM Submissions WHERE assignment = '$assignment_name_sql' AND student_id = '$campus_id'";
    $num_parts_submitted = $conn->query($student_submission_sql)->num_rows;
    $numparts_sql = "SELECT DISTINCT part_name FROM SubmissionParts WHERE assignment = '$assignment_name_sql'";
    $num_parts_total = $conn->query($numparts_sql)->num_rows;
    if($num_parts_submitted == 0) {
        return 0;
    } else if($num_parts_submitted > 0 and $num_parts_submitted < $num_parts_total){
        return 1;
    } else {
        return 2;
    }
}

function getAssignmentGradingStatus($ta_campus_id, $assignment, $conn): array {
    $assignment_name_sql = $conn->real_escape_string($assignment);
    $get_assignment_parts_sql = "SELECT DISTINCT part_name from SubmissionParts WHERE assignment = '$assignment_name_sql'";
    $get_assignment_parts_result = $conn->query($get_assignment_parts_sql);
    $students_to_check = getStudentsInSection(getUserSectionNumber($ta_campus_id, $conn), $conn);
    $num_parts_done = 0;
    $num_parts_total = $get_assignment_parts_result->num_rows;
    while($row = $get_assignment_parts_result->fetch_assoc()) {
        $this_part_done = true;
        foreach($students_to_check as $student) {
            if(getStudentGradingStatus($student, $assignment, $row['part_name'], $conn) === false) {
                $this_part_done = false;
            }
        }
        if($this_part_done === true) {
            $num_parts_done++;
        }
    }
    return array($num_parts_done, $num_parts_total);
}

function getStudentsInSection($section_num, $conn): array {
    $students_in_section_sql = "SELECT umbc_id FROM Users WHERE role = 'Student' AND section = '$section_num'";
    $students_in_section_result = $conn->query($students_in_section_sql);
    $student_array = [];
    while($row = $students_in_section_result->fetch_assoc()) {
        array_push($student_array, $row['umbc_id']);
    }
    return $student_array;
}

function getUserSectionNumber($user_id, $conn): int {
    $get_user_section_sql = "SELECT section FROM Users WHERE umbc_id = '$user_id'";
    $get_user_section_result = $conn->query($get_user_section_sql);
    return $get_user_section_result->fetch_assoc()['section'];
}

function getNameIDFromCampusID($user_id, $conn): string {
    $get_name_id_sql = "SELECT umbc_name_id FROM Users WHERE umbc_id = '$user_id'";
    $get_name_id_result = $conn->query($get_name_id_sql);
    return $get_name_id_result->fetch_assoc()['umbc_name_id'];
}

function getStudentGradingStatus($student, $assignment_name, $part_name, $conn): bool {
    ensureRubricCreation($assignment_name, $part_name, $student, $conn);
    $assignment_sql = $conn->real_escape_string($assignment_name);
    $part_sql = $conn->real_escape_string($part_name);
    $get_student_grading_sql = "SELECT points_received FROM Rubrics WHERE student_id = '$student' AND assignment = '$assignment_sql' AND part_name = '$part_sql' AND line_type = '0' AND points_received IS NULL";
    $get_student_grading_result = $conn->query($get_student_grading_sql);
    if($get_student_grading_result->num_rows === 0) { // If this is true, then the student does NOT have null values for some line items, which means that they are graded.
        return true;
    } else {
        return false;
    }
}

function ensureRubricCreation($assignment_name, $part_name, $student_id, $conn): bool {
    $assignment_sql = $conn->real_escape_string($assignment_name);
    $part_sql = $conn->real_escape_string($part_name);
    $check_rubric_sql = "SELECT id_number FROM Rubrics WHERE assignment = '$assignment_sql' AND part_name = '$part_sql' AND student_id = '$student_id'";
    $check_rubric_result = $conn->query($check_rubric_sql);
    $status_okay = true;
    if($check_rubric_result->num_rows === 0) {
        // The student doesn't have a rubric, so create one by copying it from the "official" rubric in the RubricParts table
        // Basically just copy it over line by line.. idk if there is a more efficient way to do it lol
        $official_rubric_sql = "SELECT line_type, line_item, point_value FROM RubricParts WHERE assignment = '$assignment_sql' AND part_name = '$part_sql'";
        $official_rubric_result = $conn->query($official_rubric_sql);
        while($row = $official_rubric_result->fetch_assoc()) {
            $type_to_copy = $row['line_type'];
            $item_to_copy = $conn->real_escape_string($row['line_item']);
            $value_to_copy = $row['point_value'];
            $student_rubric_insertion_sql = null;
            if($type_to_copy === "0") { // If the row has a point value, insert it
                $student_rubric_insertion_sql = "INSERT INTO Rubrics (assignment, part_name, student_id, line_type, line_item, point_value)
                                                 VALUES ('$assignment_sql', '$part_sql', '$student_id', '$type_to_copy', '$item_to_copy', '$value_to_copy')";
            } else { // Otherwise, it is a student comment line
                $student_rubric_insertion_sql = "INSERT INTO Rubrics (assignment, part_name, student_id, line_type, line_item, point_value)
                                                 VALUES ('$assignment_sql', '$part_sql', '$student_id', '$type_to_copy', '$item_to_copy', NULL)";
            }
            $rubric_insertion_result = $conn->query($student_rubric_insertion_sql);
            if($rubric_insertion_result === false) {
                $status_okay = false;
            }
        }
        // Insert grading comment box
        $grading_comment_insertion_sql = "INSERT INTO Rubrics (assignment, part_name, student_id, line_type, line_item, point_value, grader_comments)
                                                 VALUES ('$assignment_sql', '$part_sql', '$student_id', '2', NULL, NULL, 'No comments provided.')";
        $grading_comment_result = $conn->query($grading_comment_insertion_sql);
        if($grading_comment_result === false) {
            $status_okay = false;
        }
    }
    // A return value of "True" means that after this function, a full rubric exists. The only way it returns false is if an error occurred with the SQL query.
    return $status_okay;
}

function getAlphaNumKey($campus_id, $conn) : string {
    $get_alpha_num_sql = "SELECT alpha_num_key FROM Users WHERE umbc_id = '$campus_id'";
    return $conn->query($get_alpha_num_sql)->fetch_assoc()['alpha_num_key'];
}

function getStudentScoreForAssignment($campus_id, $assignment, $conn) : array {
    $assignment_sql = $conn->real_escape_string($assignment);
    $get_parts_sql = "SELECT DISTINCT part_name FROM SubmissionParts WHERE assignment = '$assignment_sql'";
    $get_parts_result = $conn->query($get_parts_sql);
    while($row = $get_parts_result->fetch_assoc()) {
        ensureRubricCreation($assignment, $row['part_name'], $campus_id, $conn);
    }
    $get_student_score_lines_sql = "SELECT points_received, point_value FROM Rubrics WHERE assignment = '$assignment_sql' AND student_id = '$campus_id' AND line_type = '0'";
    $get_student_score_result = $conn->query($get_student_score_lines_sql);
    $total_score = 0;
    $total_possible_points_sql = "SELECT max_points FROM Assignments WHERE assignment_name = '$assignment_sql'";
    $total_points = intval($conn->query($total_possible_points_sql)->fetch_assoc()['max_points']);
    while($row = $get_student_score_result->fetch_assoc()) {
        $total_score += intval($row['points_received']);
    }
    return array($total_score, $total_points);
}

function getUsernameOfGrader($student_id, $conn) : string {
    $student_section = getUserSectionNumber($student_id, $conn);
    $get_grader_sql = "SELECT umbc_name_id FROM Users WHERE section = '$student_section' AND (role = 'TA' OR role = 'Instructor')";
    $get_grader_result = $conn->query($get_grader_sql);
    return $get_grader_result->fetch_assoc()['umbc_name_id'];
}

function getNumStudentsInSection($section_num, $conn) : int {
    $get_num_students_sql = "SELECT umbc_id FROM Users WHERE section = '$section_num' AND role = 'Student'";
    return $conn->query($get_num_students_sql)->num_rows;
}

function getSectionStats($section_num, $assignment_name, $conn) : array {
    $students = getStudentsInSection($section_num, $conn);
    $student_scores = [];
    foreach($students as $student) {
        array_push($student_scores, getStudentScoreForAssignment($student, $assignment_name, $conn)[0]);
    }
    $section_stats = [];
    array_push($section_stats, array_sum($student_scores) / count($student_scores));
    array_push($section_stats, calculate_median($student_scores));
    array_push($section_stats, calculate_sd($student_scores));
    return $section_stats;
}

function getCourseStats($assignment_name, $conn) : array {
    $get_total_student_list_sql = "SELECT umbc_id FROM Users WHERE role = 'Student'";
    $get_student_list_result = $conn->query($get_total_student_list_sql);
    $student_list = [];
    $student_scores = [];

    while($row = $get_student_list_result->fetch_assoc()) {
        array_push($student_list, $row["umbc_id"]);
    }

    foreach($student_list as $student) {
        array_push($student_scores, getStudentScoreForAssignment($student, $assignment_name, $conn)[0]);
    }

    $class_stats = [];
    array_push($class_stats, array_sum($student_scores) / count($student_scores));
    array_push($class_stats, calculate_median($student_scores));
    array_push($class_stats, calculate_sd($student_scores));
    return $class_stats;
}

function calculate_median($arr) : float {
    $count = count($arr); //total numbers in array
    $middle_val = floor(($count - 1) / 2); // find the middle value, or the lowest middle value
    if($count % 2) { // odd number, middle is the median
        $median = $arr[$middle_val];
    } else { // even number, calculate avg of 2 medians
        $low = $arr[$middle_val];
        $high = $arr[$middle_val + 1];
        $median = (($low + $high) / 2);
    }
    return $median;
}

function calculate_sd($arr) : float {
    $num_of_elements = count($arr);
    $variance = 0.0;
    $average = array_sum($arr)/$num_of_elements;
    foreach($arr as $i)
    {
        $variance += pow(($i - $average), 2);
    }
    return (float)sqrt($variance/$num_of_elements);
}