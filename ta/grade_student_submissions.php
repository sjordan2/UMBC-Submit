<?php
require_once '../sql_functions.php';
require_once '../config.php';
require_once $php_nested_cas_path . 'CAS.php';
phpCAS::setDebug();
phpCAS::setVerbose(true);
phpCAS::client(CAS_VERSION_2_0, $cas_host, $cas_port, $cas_context);
phpCAS::setNoCasServerValidation(); // FIX THIS BUCKO
phpCAS::forceAuthentication();

$UNENROLLED_STUDENT = false;

$conn = new mysqli($sql_host, $sql_username, $sql_password, $sql_dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_REQUEST['logout'])) {
    header('Location: https://www.csee.umbc.edu');
//    phpCAS::logout();
}

if(getEnrollment(phpCAS::getUser(), $conn) === false) {
    header('Location: ../home.php');
    exit();
}

if(getEnrollment(phpCAS::getUser(), $conn) === "Student") {
    header('Location: ../home.php');
    exit();
}
?>

<html lang="en">
<style>
    body {background-color: #ABABAB}

    ul {
        list-style-type: none;
        margin: 0;
        padding: 0;
        overflow: hidden;
        background-color: #000000;
    }

    li {
        float: left;
    }

    li a {
        display: block;
        color: white;
        text-align: center;
        padding: 14px 20px;
        text-decoration: none;
        cursor: pointer;
    }

    li a:hover:not(.current) {
        background-color: #2d2e2f;
    }

    .current {
        background-color: #4CAF50;
    }

    .userDropdown {
        position: relative;
        display: block;
    }

    .user_content {
        display: none;
        position: absolute;
        background-color: #ff0000;
        min-width: 150px;
        overflow: auto;
        z-index: 1;
    }

    .user_content a {
        color: white;
        font-weight: bolder;
        padding: 12px 16px;
        text-align: center;
        text-decoration: none;
        display: block;
    }

    div {
        border: 2px solid black;
        margin: 2px;
        display: inline-block;
    }

    button.gradeSubmissions {
        background-color: white;
        color: #0073ca;
        display: inline;
        padding: 10px 15px;
        margin: 3px;
        cursor: pointer;
        border: 2px solid;
        float: right;
    }

    button.gradeSubmissions:hover {
        background-color: #0073ca;
        color: white;
    }

    button.maxFill {
        background-color: #0073ca;
        color: white;
        display: inline;
        padding: 10px 15px;
        cursor: pointer;
        border: 2px solid;
    }

    button.maxFill:hover {
        background-color: white;
        color: #0073ca;
    }

    button.zeroFill {
        background-color: #a80000;
        color: white;
        display: inline;
        padding: 10px 15px;
        cursor: pointer;
        border: 2px solid;
        margin-right: 5px;
    }

    button.zeroFill:hover {
        background-color: white;
        color: #a80000;
    }

    .sub_file_tab {
        border: 1px solid black;
        background-color: #777;
        color: white;
        float: left;
        outline: none;
        cursor: pointer;
        padding: 14px 26px;
        font-size: 16px;
        margin-bottom: 0px;
    }

    .sub_file_tab:hover {
        background-color: #333;
    }

    table,th,td,tr {
        border : 2px solid black;
        border-collapse: collapse;
        table-layout: fixed;
    }

    th, td {
        padding: 5px;
        text-align: center;
        vertical-align: middle;
        overflow: auto;
        font-size: 20px;
    }

    .active {
        background-color: #555;
        pointer-events: none;
    }

    .active:hover {
        background-color: #555;
    }

</style>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/10.5.0/styles/an-old-hope.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/10.5.0/highlight.min.js"></script>
<script>
    function autoLoadSubmission(assignment, part, student_id) {
        let buttonList = document.getElementById("studentCodeDiv").getElementsByTagName("button");
        selectCodeTab(assignment, part, student_id, buttonList[0].innerText);
    }

    function previousPart() {
        let url = new URL(window.location.href);
        let assignmentName = url.searchParams.get("assignment");
        let partName = url.searchParams.get("part");
        let studentID = url.searchParams.get("student");
        let ajaxQuery = new XMLHttpRequest();
        ajaxQuery.onreadystatechange = function() {
            if (this.readyState === 4 && this.status === 200) {
                if (this.responseText.substring(0, 10) === "ENDOFCYCLE") {
                    window.location.href = "grade_assignment.php?assignment=" + assignmentName + "&section=" + this.responseText.split("_")[1] + "&part=" + partName;
                } else {
                    window.location.href = "grade_student_submissions.php?assignment=" + assignmentName + "&part=" + this.responseText + "&student=" + studentID;
                }
            }
        };
        ajaxQuery.open("POST", "cycle_parts.php", true);
        ajaxQuery.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        ajaxQuery.send("assignment=" + assignmentName + "&part=" + partName + "&student=" + studentID + "&action=previous");
    }

    function nextPart() {
        let url = new URL(window.location.href);
        let assignmentName = url.searchParams.get("assignment");
        let partName = url.searchParams.get("part");
        let studentID = url.searchParams.get("student");
        let ajaxQuery = new XMLHttpRequest();
        ajaxQuery.onreadystatechange = function() {
            if (this.readyState === 4 && this.status === 200) {
                if(this.responseText.substring(0, 10) === "ENDOFCYCLE") {
                    window.location.href = "grade_assignment.php?assignment=" + assignmentName + "&section=" + this.responseText.split("_")[1] + "&part=" + partName;
                } else {
                    window.location.href = "grade_student_submissions.php?assignment=" + assignmentName + "&part=" + this.responseText + "&student=" + studentID;
                }
            }
        };
        ajaxQuery.open("POST", "cycle_parts.php", true);
        ajaxQuery.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        ajaxQuery.send("assignment=" + assignmentName + "&part=" + partName + "&student=" + studentID + "&action=next");
    }

    function previousStudent() {
        let url = new URL(window.location.href);
        let assignmentName = url.searchParams.get("assignment");
        let partName = url.searchParams.get("part");
        let studentID = url.searchParams.get("student");
        let ajaxQuery = new XMLHttpRequest();
        ajaxQuery.onreadystatechange = function() {
            if (this.readyState === 4 && this.status === 200) {
                if (this.responseText.substring(0, 10) === "ENDOFCYCLE") {
                    window.location.href = "grade_assignment.php?assignment=" + assignmentName + "&section=" + this.responseText.split("_")[1] + "&part=" + partName;
                } else {
                    window.location.href = "grade_student_submissions.php?assignment=" + assignmentName + "&part=" + partName + "&student=" + this.responseText;
                }
            }
        };
        ajaxQuery.open("POST", "cycle_students.php", true);
        ajaxQuery.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        ajaxQuery.send("student=" + studentID + "&action=previous");
    }

    function nextStudent() {
        let url = new URL(window.location.href);
        let assignmentName = url.searchParams.get("assignment");
        let partName = url.searchParams.get("part");
        let studentID = url.searchParams.get("student");
        let ajaxQuery = new XMLHttpRequest();
        ajaxQuery.onreadystatechange = function() {
            if (this.readyState === 4 && this.status === 200) {
                if(this.responseText.substring(0, 10) === "ENDOFCYCLE") {
                    window.location.href = "grade_assignment.php?assignment=" + assignmentName + "&section=" + this.responseText.split("_")[1] + "&part=" + partName;
                } else {
                    window.location.href = "grade_student_submissions.php?assignment=" + assignmentName + "&part=" + partName + "&student=" + this.responseText;
                }
            }
        };
        ajaxQuery.open("POST", "cycle_students.php", true);
        ajaxQuery.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        ajaxQuery.send("student=" + studentID + "&action=next");
    }

    function selectCodeTab(assignment, part, student_id, file_name, select = "latest") {
        let submissionNumber;
        if(select === "latest") {
            submissionNumber = "latest";
        } else { // If it is explicitly passed
            submissionNumber = document.getElementById("selectSubmission").value;
        }
        // console.log("assignment // " + part + " // " + student_id + " // " + file_name + " // " + submissionNumber);
        let buttonList = document.getElementById("buttonsDiv").getElementsByTagName("button");
        for(let counter = 0; counter < buttonList.length; counter++) {
            buttonList[counter].classList.remove("active");
        }
        document.getElementById("nameTab_" + file_name).classList.add("active");
        let ajaxQuery = new XMLHttpRequest();
        ajaxQuery.onreadystatechange = function () {
            if (this.readyState === 4 && this.status === 200) {
                document.getElementById("codeContentDiv").innerHTML = "";
                let preContent = document.createElement("pre");
                let codeContent = document.createElement("code");
                codeContent.innerText = this.responseText;
                hljs.highlightBlock(codeContent);
                document.getElementById("codeContentDiv").appendChild(preContent);
                preContent.appendChild(codeContent);
                document.getElementById("codeContentDiv").style.display = 'block';
            }
        };
        ajaxQuery.open("POST", "../retrieve_student_submission.php", true);
        ajaxQuery.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        ajaxQuery.send("assignment=" + assignment + "&part=" + part + "&campus_id=" + student_id + "&fileName=" + file_name + "&submission_number=" + submissionNumber);
    }

    function loadRubric() {
        let url = new URL(window.location.href);
        let assignment = url.searchParams.get("assignment");
        let part = url.searchParams.get("part");
        let student_id = url.searchParams.get("student");
        let ajaxQuery = new XMLHttpRequest();
        ajaxQuery.onreadystatechange = function() {
            if (this.readyState === 4 && this.status === 200) {
                // console.log(this.responseText);
                document.getElementById("studentRubricDiv").innerHTML = this.responseText;
            }
        };
        ajaxQuery.open("POST", "load_student_rubric.php", true);
        ajaxQuery.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        ajaxQuery.send("assignment=" + decode_html(assignment) + "&part=" + decode_html(part) + "&student=" + student_id);
    }

    function saveRubric() {
        let url = new URL(window.location.href);
        let assignment = url.searchParams.get("assignment");
        let part = url.searchParams.get("part");
        let student = url.searchParams.get("student");
        let rubricData = [];
        let rubricTable = document.getElementById("rubricTable");
        let rows = rubricTable.children[0].children;
        for(let rowCounter = 1; rowCounter < rows.length; rowCounter++) { // Skip the header rows
            if(!rows[rowCounter].innerHTML.includes("colspan")) { // If it is a graded line item
                let pointsReceived = rows[rowCounter].children[0].children[0].value;
                let pointValue = parseInt(rows[rowCounter].children[0].children[1].innerText);
                let textElement = rows[rowCounter].children[1].innerText;
                rubricData.push({points_received: pointsReceived, point_value: pointValue, line_item: textElement});
            }
        }
        
        let rubricComments = document.getElementById("graderCommentBox").value;

        let ajaxQuery = new XMLHttpRequest();
        ajaxQuery.onreadystatechange = function() {
            if (this.readyState === 4 && this.status === 200) {
                console.log(this.responseText);
            }
        };
        ajaxQuery.open("POST", "save_student_rubric.php", true);
        ajaxQuery.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        ajaxQuery.send("assignment=" + assignment + "&part=" + part + "&student=" + student + "&rubricData=" + JSON.stringify(rubricData) + "&comments=" + rubricComments);
    }

    function updateStudentScore() {
        let currScore = 0;
        let rubricTable = document.getElementById("rubricTable");
        let rows = rubricTable.children[0].children;
        for(let rowCounter = 1; rowCounter < rows.length; rowCounter++) { // Skip the header rows
            if(!rows[rowCounter].innerHTML.includes("colspan")) { // If it is a graded line item
                let inputElement = rows[rowCounter].children[0].children[0];
                if(inputElement.value) {
                    currScore += parseFloat(inputElement.value);
                }
            }
        }
        document.getElementById("currentStudentScore").innerHTML = currScore.toString().bold();
    }

    function zeroRubric() {
        let url = new URL(window.location.href);
        let assignment = url.searchParams.get("assignment");
        let part = url.searchParams.get("part");
        let student = url.searchParams.get("student");
        let rubricTable = document.getElementById("rubricTable");
        let rows = rubricTable.children[0].children;
        for(let rowCounter = 1; rowCounter < rows.length; rowCounter++) { // Skip the header rows
            if(!rows[rowCounter].innerHTML.includes("colspan")) { // If it is a graded line item
                let inputElement = rows[rowCounter].children[0].children[0];
                if(!inputElement.value) {
                    inputElement.value = 0;
                }
            }
        }
        updateStudentScore();
        saveRubric(assignment, part, student);
    }

    function fillRubric() {
        let url = new URL(window.location.href);
        let assignment = url.searchParams.get("assignment");
        let part = url.searchParams.get("part");
        let student = url.searchParams.get("student");
        let rubricTable = document.getElementById("rubricTable");
        let rows = rubricTable.children[0].children;
        for(let rowCounter = 1; rowCounter < rows.length; rowCounter++) { // Skip the header rows
            if (!rows[rowCounter].innerHTML.includes("colspan")) { // If it is a graded line item
                let linePointValue = parseInt(rows[rowCounter].children[0].children[1].innerText);
                let inputElement = rows[rowCounter].children[0].children[0];
                if(!inputElement.value) {
                    inputElement.value = linePointValue;
                }
            }
        }
        updateStudentScore();
        saveRubric(assignment, part, student);
    }

    function decode_html(text) { // function to decode html special chars
        let map = {
            '&amp;': '&',
            '&#038;': "&",
            '&lt;': '<',
            '&gt;': '>',
            '&quot;': '"',
            '&#039;': "'",
            '&#8217;': "’",
            '&#8216;': "‘",
            '&#8211;': "–",
            '&#8212;': "—",
            '&#8230;': "…",
            '&#8221;': '”'
        };
        return text.replace(/\&[\w\d\#]{2,5}\;/g, function(m) { return map[m]; });
    }

    function updateSubmission(select, student_id) {
        let url = new URL(window.location.href);
        let assignmentName = decode_html(url.searchParams.get("assignment").replace(new RegExp('~', 'g'), " "));
        let partName = decode_html(url.searchParams.get("part").replace(new RegExp('~', 'g'), " "));
        let ajaxQuery = new XMLHttpRequest();
        ajaxQuery.onreadystatechange = function () {
            if (this.readyState === 4 && this.status === 200) {
                if(this.responseText.substring(0, 5) === "ERROR") {
                    document.getElementById("submissionsView").innerText = this.responseText;
                    document.getElementById("submissionsView").style.display = "block";
                } else {
                    document.getElementById("submissionsView").innerHTML = this.responseText;
                    let buttonList = document.getElementById("submissionsView").getElementsByTagName("button");
                    selectCodeTab(assignmentName, partName, student_id, buttonList[0].innerText, select.value);
                }
            }
        };
        ajaxQuery.open("POST", "../retrieve_student_part_files.php", true);
        ajaxQuery.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        ajaxQuery.send("assignment=" + assignmentName + "&part=" + partName + "&campus_id=" + student_id + "&submission_number=" + select.value + "&method=grader");
    }
</script>
<ul>
    <li><a href="../home.php">Home</a></li>
    <?php
    if(getEnrollment(phpCAS::getUser(), $conn) === "TA") {
        echo "<li><a href='../assignments.php'>View Assignments</a></li>
                <li><a href='../grades.php'>View Grades</a></li>
                <li><a href='' style='color: white' class='current'>Grade Submissions</a></li>";
    } else if(getEnrollment(phpCAS::getUser(), $conn) === "Instructor") {
        echo "<li><a href='../assignments.php'>View Assignments</a></li>
                <li><a href='../grades.php'>View Grades</a></li>
                <li><a href='grade.php' style='color: white' class='current'>Grade Submissions</a></li>
                <li><a href='../administration/user_management.php' style='color: red'>User Management</a></li>
                <li><a href='../administration/assignment_management.php' style='color: red'>Assignment Management</a></li>";
    }
    if(getEnrollment(phpCAS::getUser(), $conn) !== false) {
        echo '<li style="float:right"><a id="userButton" onclick="logoutDropdown()">';
        echo getFullNameFromCampusID(phpCAS::getUser(), $conn) . " (" . phpCAS::getUser() . ")";
        echo '
        </a>
                <div id="logOutDrop" class="user_content">
                    <a href="?logout=True">Log Out</a>
                </div>
            </li>';
    } else {
        $UNENROLLED_STUDENT = true; // This is a word, right?
    }
    echo "</ul>";
    echo "<body>";
    echo "<div style='width: 100%;text-align: center;vertical-align: center'>";
    $assignment_name = htmlspecialchars_decode(str_replace("~", " ", $_GET['assignment']), ENT_QUOTES);
    $part_name = htmlspecialchars_decode(str_replace("~", " ", $_GET['part']), ENT_QUOTES);
    $assignment_sql = $conn->real_escape_string($assignment_name);
    $part_sql = $conn->real_escape_string($part_name);
    $student_id = $_GET['student'];
    echo "<button class='gradeSubmissions' style='float: left;display: inline;' onclick='previousPart()'><< Previous Part</button>";
    echo "<p style='font-size: xx-large;display: inline'><b>$assignment_name // $part_name</b></p>";
    echo "<button class='gradeSubmissions' style='float: right;display: inline;' onclick='nextPart()'>Next Part >></button>";
    echo "</div>";
    echo "<div style='width: 100%;text-align: center;vertical-align: center'>";
    $student_text = getFullNameFromCampusID($_GET['student'], $conn) . " (" . getNameIDFromCampusID($_GET['student'], $conn) . ")";
    echo "<button class='gradeSubmissions' style='float: left;display: inline;' onclick='previousStudent()'><< Previous Student</button>";
    echo "<p style='font-size: xx-large;display: inline'>$student_text</p>";
    echo "<button class='gradeSubmissions' style='float: right;display: inline;' onclick='nextStudent()'>Next Student >></button>";
    echo "</div>";
    echo "<div style='width: 100%;border: none'>";
    echo "<div style='width: 49.2%;height: 76.5%;position:relative;float: left' id='studentCodeDiv'>";

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
    echo "<div id='buttonsDiv' style='overflow-x: scroll;width: 99%;margin-bottom: 0;border: none'>";
    $encoded_assignment = htmlspecialchars($assignment_name, ENT_QUOTES);
    $encoded_part = htmlspecialchars($part_name, ENT_QUOTES);

    $check_submission_sql = "SELECT id_number FROM Submissions WHERE assignment = '$assignment_sql' AND assignment_part = '$part_sql' AND student_id = '$student_id'";
    $check_result = $conn->query($check_submission_sql);
    if($check_result->num_rows == 0) {
        echo "<p style='font-size: x-large;text-align: center;color: red;vertical-align: center'>Student does not have a submission for this part!</p>";
    } else {

        $latestSubmissionNumber = getCurrentSubmissionNumber($student_id, $assignment_name, $part_name, $conn) - 1;
        $get_files_sql = "SELECT submission_name FROM Submissions WHERE assignment = '$assignment_sql' AND assignment_part = '$part_sql' AND student_id = '$student_id' AND submission_number = '$latestSubmissionNumber'";
        $files_result_buttons = $conn->query($get_files_sql);
        while($row = $files_result_buttons->fetch_assoc()) {
            $file_name = $row['submission_name'];
            $button_id = "nameTab_" . $file_name;
            echo "<button class='sub_file_tab' onclick='selectCodeTab(\"$assignment_name\", \"$part_name\", \"$student_id\", \"$file_name\", \"notLatest\")' id=$button_id>$file_name</button>";
        }
        echo "<script>autoLoadSubmission('$encoded_assignment', '$encoded_part', '$student_id')</script>";
    }
    echo "</div>";
    echo "<div style='border: none;display: none;height: 91%;overflow: scroll;' id='codeContentDiv'>";
    echo "</div>";
    echo "</div>";
    echo "</div>";

    $assignment_due_date_sql = "SELECT date_due FROM Assignments WHERE assignment_name = '$assignment_sql'";
    $assignment_due_date = $conn->query($assignment_due_date_sql)->fetch_assoc()['date_due'];
    $assignment_due_formatted = null;
    $current_date_formatted = null;
    try {
        $assignment_due_formatted = new DateTime($assignment_due_date);
        $current_date_formatted = new DateTime();
    } catch(Exception $e) {
        echo "Date Time Error! Message: " . $e;
    }
    echo "<div style='width: 49.6%;height: 76.5%;position:relative;float: left' id='studentRubricDiv'>";
    if($assignment_due_formatted > $current_date_formatted) { // If it is not past the assignment due date
        echo "<p style='font-size: x-large;text-align: center;color: red;vertical-align: center'>The course-wide due date for this assignment has not yet passed, so you cannot grade yet.</p>";
    } else { // It IS past the assignment due date
        $check_extensions_sql = "SELECT new_due_date FROM Extensions WHERE student_id = '$student_id'";
        $check_extensions_result = $conn->query($check_extensions_sql);
        if($check_extensions_result->num_rows === 0) { // The student does not have an extension
            echo "<script>loadRubric()</script>";
        } else { // The student DOES have an extension - has it passed already?
            $extended_due_date = $check_extensions_result->fetch_assoc()['new_due_date'];
            $extended_due_formatted = null;
            try {
                $extended_due_formatted = new DateTime($extended_due_date);
            } catch(Exception $eTwo) {
                echo "Date Time Error! Message: " . $eTwo;
            }
            if($extended_due_formatted > $current_date_formatted) { // The extension has NOT expired yet
                $ext_date = $extended_due_formatted->format("l, F jS, Y, g:i:sA");
                echo "<p style='font-size: x-large;text-align: center;color: red;vertical-align: center'>This student has an individual extension on this assignment until $ext_date. You will get an email when you can grade this student's assignment.</p>";
            } else { // The extension HAS expired
                echo "<script>loadRubric()</script>";
            }
        }
    }
    echo "</div>";
    echo "</div>";
    echo "</body>";
?>
