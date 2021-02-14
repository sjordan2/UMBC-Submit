<?php
include 'sql_functions.php';

$assignment = $_POST["assignment"];
$part_name = $_POST["part_name"];
$student_id = $_POST["student"];
$given_alphanumeric = $_POST["alpha_num_key"];

// Create connection
$conn = new mysqli($sql_host, $sql_username, $sql_password, $sql_dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$student_ank = getAlphaNumKey($student_id, $conn);

if($student_ank !== $given_alphanumeric) {
    echo "ERROR: Authentication keys do not match!\nThis incident has been reported to the course administrators.";
    exit();
}


$assignment_sql = $conn->real_escape_string($assignment);
$confirm_grade_release_sql = "SELECT grades_released FROM Assignments WHERE assignment_name = '$assignment_sql'";
if($conn->query($confirm_grade_release_sql)->fetch_assoc()['grades_released'] === "0") { // The grades have not been released yet
    echo "<p style='font-size: x-large;margin: 5px;display: inline-block;color: #be0000;font-weight: bold'>Grades have not been released yet! Please check back later!</p>";
    exit();
} else {
    $part_sql = $conn->real_escape_string($part_name);
    echo "<div style='width: 49.4%;height: 80%;float: left'>"; // Student Code Div
    $select_sub_id = "selectSubmission";
    $get_file_num_for_part_sql = "SELECT id_number FROM SubmissionParts WHERE assignment = '$assignment_sql' AND part_name = '$part_sql'";
    $file_num_for_part = $conn->query($get_file_num_for_part_sql)->num_rows;
    $get_student_part_subs_sql = "SELECT submission_number, date_submitted FROM Submissions WHERE assignment = '$assignment_sql' AND assignment_part = '$part_sql' AND student_id = '$student_id'";
    // We don't need to explicitly order by submission number, since the primary key is already in incremental order.
    $get_student_part_subs_result = $conn->query($get_student_part_subs_sql);
    $number_submissions = $get_student_part_subs_result->num_rows; // Get number of submissions, so we can show which is the latest
    $curr_counter = 1;
    echo "<div style='text-align: center;width: 98.6%;border: none;padding-top: 3px'>";
    if($number_submissions === 0) {
        echo "<select id='$select_sub_id' disabled style='margin-right: 5px;width: 375px;font-size: large;margin-top=5px'>";
        echo "<option selected disabled value=':('>No submissions found :(</option>";
    } else {
        echo "<select name=$select_sub_id id=$select_sub_id onchange='updateSubmission(this, \"$student_id\")' style='margin-right: 5px;width: 375px;font-size: large;margin-top=5px'>";
        while ($row = $get_student_part_subs_result->fetch_assoc()) {
            $curr_sub_number = $row['submission_number'];
            if ($curr_counter % $file_num_for_part === 0) { // If it is a singular submission and not another file from the same submission (idk what this means either lol)
                $date_subbed = null;
                try {
                    $date_subbed = new DateTime($row["date_submitted"]);
                } catch (Exception $e) {
                    echo "Date Time error! Getting submissions in Grade Student Submissions!";
                    exit();
                }
                if ($curr_counter === $number_submissions) { // If this is the latest submission
                    $option_text = "Latest Submission - " . $date_subbed->format("n/j/y") . " at " . $date_subbed->format("g:i:s A");
                    echo "<option selected value='$curr_sub_number'>$option_text</option>";
                } else {
                    $option_text = "Submission " . $curr_sub_number . " - " . $date_subbed->format("n/j/y") . " at " . $date_subbed->format("g:i:s A");
                    echo "<option value='$curr_sub_number'>$option_text</option>";
                }
            }
            $curr_counter++;
        }
    }
    echo "</select>";
    echo "</div>";
    echo "<div id='submissionsView' style='width: 99%;height: 93.5%;border: none'>";
    $check_submission_sql = "SELECT id_number FROM Submissions WHERE assignment = '$assignment_sql' AND assignment_part = '$part_sql' AND student_id = '$student_id'";
    $check_result = $conn->query($check_submission_sql);
    if($check_result->num_rows === 0) {
        echo "<p style='font-size: x-large;text-align: center;color: red;vertical-align: center'>You did not have a submission for this part!</p>";
    } else {
        echo "<div id='buttonsDiv' style='overflow-x: scroll;width: 99%;margin-bottom: 0;border: none'>";
        $encoded_assignment = htmlspecialchars($assignment, ENT_QUOTES);
        $encoded_part = htmlspecialchars($part_name, ENT_QUOTES);

        $check_submission_sql = "SELECT id_number FROM Submissions WHERE assignment = '$assignment_sql' AND assignment_part = '$part_sql' AND student_id = '$student_id'";
        $check_result = $conn->query($check_submission_sql);
        if ($check_result->num_rows === 0) {
            echo "<p style='font-size: x-large;text-align: center;color: red;vertical-align: center'>You did not have a submission for this part!</p>";
        } else {
            $latestSubmissionNumber = getCurrentSubmissionNumber($student_id, $assignment, $part_name, $conn) - 1;
            $get_files_sql = "SELECT submission_name FROM Submissions WHERE assignment = '$assignment_sql' AND assignment_part = '$part_sql' AND student_id = '$student_id' AND submission_number = '$latestSubmissionNumber'";
            $files_result_buttons = $conn->query($get_files_sql);
            $count = 1;
            $first_file_name = null;
            while ($row = $files_result_buttons->fetch_assoc()) {
                $file_name = $row['submission_name'];
                $button_id = "nameTab_" . $file_name;
                if($count === 1) {
                    echo "<button class='sub_file_tab active' onclick='selectCodeTab(\"$encoded_assignment\", \"$encoded_part\", \"$student_id\", \"$file_name\", \"notLatest\")' id=$button_id>$file_name</button>";
                    $first_file_name = $file_name;
                } else {
                    echo "<button class='sub_file_tab' onclick='selectCodeTab(\"$encoded_assignment\", \"$encoded_part\", \"$student_id\", \"$file_name\", \"notLatest\")' id=$button_id>$file_name</button>";
                }
                $count++;
            }
            echo "</div>";
            echo "<div style='border: none;display: none;height: 91.5%;overflow: scroll;' id='codeContentDiv'>";
            $get_file_sql = "SELECT submission_contents FROM Submissions WHERE assignment = '$assignment_sql' AND assignment_part = '$part_sql' AND student_id = '$student_id' AND submission_number = '$latestSubmissionNumber' AND submission_name = '$first_file_name'";
            $file_results = $conn->query($get_file_sql);
            if($file_results->num_rows != 1) {
                echo "ERROR: Zero or multiple submissions detected! Contact an administrator!";
            } else {
                echo $file_results->fetch_assoc()['submission_contents'];
            }
            echo "</div>";
        }
    }
    echo "</div>";
    echo "</div>";
    echo "</div>";

    echo "<div style='width: 49.4%;height: 80%;float: right'>"; // Student Rubric Div
    echo "<p style='font-size: x-large;text-align: center;margin-top: 0;margin-bottom: 0'><b>Grading Rubric</b></p>";
    echo "<div id='rubricTableDiv' style='margin: 5px;border: none;overflow-y: scroll'>";
    echo "<table style='width: 100%;overflow-x: scroll' id='rubricTable'>";
    echo "<tr><th>Points Earned</th><th>Description</th></tr>";
    $get_student_rubric_sql = "SELECT line_type, line_item, point_value, points_received, grader_comments FROM Rubrics WHERE assignment = '$assignment_sql' AND part_name = '$part_sql' AND student_id = '$student_id' AND line_type != '-1'";
    $get_student_rubric_result = $conn->query($get_student_rubric_sql);
    $total_student_score = 0;
    while($row = $get_student_rubric_result->fetch_assoc()) {
        $line_type = $row['line_type'];
        $line_item = $row['line_item'];
        $line_value = $row['point_value'];
        $points_received = $row['points_received'];
        $grader_comments = $row['grader_comments'];
        if($line_type === "0") {
            echo "<tr>";
            echo "<td>";
            echo "<input type='text' readonly='readonly' style='width: 60px;height: 24px;font-size: large;margin-top: 0' value='$points_received'>";
            $total_student_score = $total_student_score + $points_received;
            echo " / " . "<p style='font-size: x-large;display: inline'>$line_value</p>";
            echo "</td>";
            echo "<td>$line_item</td>";
            echo "</tr>";
        } else if($line_type === "1") { // TA Note
            echo "<tr>";
            echo "<td colspan='2'><p style='color: red'>Note: $line_item</p></td>";
            echo "</tr>";
        } else {
            echo "</table>";
            echo "</div>";
            echo "<div id='graderCommentsDiv' style='width: 98.5%'>";
            echo "<p style='font-size: large;margin-bottom: 0;margin-top: 0;text-align: center'>Comments from the Grader</p>";
            echo "<textarea readonly='readonly' maxlength='750' id='graderCommentBox' style='width: 98%;height: 100px;resize: none;max-height: 100px;font-size: 15px;margin: 5px'>";
            echo $grader_comments;
            echo "</textarea>";
            echo "</div>";
        }
    }
    echo "<div id='studentScoreDiv' style='float: bottom;text-align: center;width: 98.5%'>";
    $get_part_points_sql = "SELECT point_value FROM SubmissionParts WHERE assignment = '$assignment_sql' AND part_name = '$part_sql'";
    $get_part_points_result = $conn->query($get_part_points_sql);
    $part_points = $get_part_points_result->fetch_assoc()['point_value'];
    echo "<p style='font-size: x-large;display: inline;margin-top: 0'>Part Score:&nbsp</p>";
    echo "<p style='font-size: xx-large;display: inline' id='currentStudentScore'><b>$total_student_score</b></p>";
    echo "<p style='font-size: x-large;display: inline'> / $part_points</p>";
    echo "</div>";
    $grader_username = getUsernameOfGrader($student_id, $conn);
    echo "<p style='font-size: x-large;text-align: center'>Questions? Email your Grader at $grader_username@umbc.edu!</p>";
    echo "</div>";
}
