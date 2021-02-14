<?php
include '../sql_functions.php';

$assignment_name = str_replace("~", " ", $_POST["assignment"]);
$part_name = str_replace("~", " ", $_POST["part"]);
$student_id = $_POST["student"];

// Create connection
$conn = new mysqli($sql_host, $sql_username, $sql_password, $sql_dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$result = ensureRubricCreation($assignment_name, $part_name, $student_id, $conn);
if($result === false) {
    echo "MySQL Error! Message: " . $conn->error;
    exit();
}

$assignment_sql = $conn->real_escape_string($assignment_name);
$part_sql = $conn->real_escape_string($part_name);
echo "<p style='font-size: x-large;text-align: center;margin-top: 0;margin-bottom: 0'><b>Grading Rubric</b></p>";
echo "<div id='rubricButtonDiv' style='width: 99%;text-align: center;border: none;display: flex;justify-content: center;align-items: center'>";
echo "<button class='zeroFill' onclick='zeroRubric()'>Zero Points</button>";
echo "<button class='maxFill' onclick='fillRubric()'>Maximum Points</button>";
echo "</div>";
echo "<div id='rubricTableDiv' style='margin: 5px;border: none;overflow-y: scroll'>";
echo "<table style='width: 100%;overflow-x: scroll' id='rubricTable'>";
echo "<tr><th>Points Earned</th><th>Description</th></tr>";
$get_student_rubric_sql = "SELECT line_type, line_item, point_value, points_received, grader_comments FROM Rubrics WHERE assignment = '$assignment_sql' AND part_name = '$part_sql' AND student_id = '$student_id' AND line_type != '1'";
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
        echo "<input type='number' onblur='saveRubric()' oninput='updateStudentScore()' style='width: 60px;height: 24px;font-size: large;margin-top: 0' value='$points_received'>";
        $total_student_score = $total_student_score + $points_received;
        echo " / " . "<p style='font-size: x-large;display: inline'>$line_value</p>";
        echo "</td>";
        echo "<td>$line_item</td>";
        echo "</tr>";
    } else if($line_type === "-1") { // TA Note
        echo "<tr>";
        echo "<td colspan='2'><p style='color: red'>TA Note: $line_item</p></td>";
        echo "</tr>";
    } else {
        echo "</table>";
        echo "</div>";
        echo "<div id='graderCommentsDiv' style='width: 98.5%'>";
        echo "<p style='font-size: large;margin-bottom: 0;margin-top: 0;text-align: center'>Comments from the Grader</p>";
        echo "<textarea maxlength='750' id='graderCommentBox' style='width: 98%;height: 100px;resize: none;max-height: 100px;font-size: 15px;margin: 5px' onblur='saveRubric(\"$assignment_sql\", \"$part_sql\", \"$student_id\")'>";
        echo $grader_comments;
        echo "</textarea>";
        echo "</div>";
    }
}
echo "<div id='studentScoreDiv' style='float: bottom;text-align: center;width: 98.5%'>";
$get_part_points_sql = "SELECT point_value FROM SubmissionParts WHERE assignment = '$assignment_sql' AND part_name = '$part_sql'";
$get_part_points_result = $conn->query($get_part_points_sql);
$part_points = $get_part_points_result->fetch_assoc()['point_value'];
echo "<p style='font-size: x-large;display: inline;margin-top: 0'>Student's Current Score:&nbsp</p>";
echo "<p style='font-size: xx-large;display: inline' id='currentStudentScore'><b>$total_student_score</b></p>";
echo "<p style='font-size: x-large;display: inline'> / $part_points</p>";
echo "</div>";