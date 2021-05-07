<?php

require_once 'sql_functions.php';
require_once 'config.php';
require_once $phpcas_path . 'CAS.php';
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

$assignment = htmlspecialchars(str_replace("~", " ", $_GET["assignment"]), ENT_QUOTES);

// Check if the GET query string value exists as an assignment in the SQL database.
$check_assignment_sql = "SELECT assignment_name FROM Assignments";
$assignments_result = $conn->query($check_assignment_sql);

// Loop that checks for existence - if it is still false, then the assignment does not exist, so let's return the user to the assignments page.
$assignment_exists = False;
while($row = $assignments_result->fetch_assoc() and !$assignment_exists) {
    $curr_assignment = $row['assignment_name'];
    if($curr_assignment == htmlspecialchars_decode($assignment, ENT_QUOTES)) {
        $assignment_exists = True;
    }
}
if($assignment_exists == False) {
    header('Location: assignments.php');
    exit();
}

if (isset($_REQUEST['logout'])) {
    header('Location: https://www.csee.umbc.edu');
//    phpCAS::logout();
}

if(getEnrollment(phpCAS::getUser(), $conn) === false) {
    header('Location: home.php');
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

    .active {
        background-color: #555;
        pointer-events: none;
    }

    .active:hover {
        background-color: #555;
    }

    button.test_button {
        text-align: center;
        color: #0d6b0d;
        background-color: #ffffff;
        padding: 5px;
        margin-bottom: 5px;
        cursor: pointer;
        border: solid 2px;
    }

    button.test_button:hover {
        background-color: #0d6b0d;
        color: white;
        border: solid 2px;
    }

</style>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/10.5.0/styles/an-old-hope.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/10.5.0/highlight.min.js"></script>
<script>
    function logoutDropdown() {
        if(document.getElementById('logOutDrop').style.display === 'block') {
            document.getElementById('logOutDrop').style.display = '';
        } else {
            document.getElementById('logOutDrop').style.display = 'block';
        }
    }

    function selectCodeTab(assignment, part, student_id, file_name, submissionNumber) {
        let buttonList = document.getElementById("submissionsView").getElementsByTagName("button");
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
        ajaxQuery.open("POST", "retrieve_student_submission.php", true);
        ajaxQuery.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        ajaxQuery.send("assignment=" + assignment + "&part=" + part + "&campus_id=" + student_id + "&fileName=" + file_name + "&submission_number=" + submissionNumber + "&alpha_num_key=" + document.getElementById("alpha_num_key").innerText);
    }

    function checkIfPartWasPosted(studentID) {
        let url = new URL(window.location.href);
        let assignmentName = decode_html(url.searchParams.get("assignment").replace(new RegExp('~', 'g'), " "));
        if(document.getElementById("assignmentParts_Dropdown").value !== "Select a Part...") {
            loadPartFiles(assignmentName, document.getElementById("assignmentParts_Dropdown").value, studentID, );
        } else {
            document.getElementById("submissionsView").style.fontSize = 'x-large';
            document.getElementById("submissionsView").style.textAlign = 'center';
            document.getElementById("submissionsView").innerText = 'You must select a part from the above dropdown menu to proceed!';
        }
    }

    function loadPartFiles(assignment, part, student_id, submissionNumber = "latest") {
        let ajaxQuery = new XMLHttpRequest();
        ajaxQuery.onreadystatechange = function () {
            if (this.readyState === 4 && this.status === 200) {
                if(this.responseText.substring(0, 5) === "ERROR") {
                    document.getElementById("submissionsView").innerText = this.responseText;
                    document.getElementById("submissionsView").style.display = "block";
                } else {
                    document.getElementById("submissionsView").innerHTML = this.responseText;
                    let buttonList = document.getElementById("submissionsView").getElementsByTagName("button");
                    selectCodeTab(assignment, part, student_id, buttonList[0].innerText, submissionNumber);
                }
            }
        };
        ajaxQuery.open("POST", "retrieve_student_part_files.php", true);
        ajaxQuery.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        ajaxQuery.send("assignment=" + assignment + "&part=" + part + "&campus_id=" + student_id + "&submission_number=" + submissionNumber + "&method=student" + "&alpha_num_key=" + document.getElementById("alpha_num_key").innerText);
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

    function encode_html(text) { // function to decode html special chars
        let map = {
            "&": '&#038;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;',
        };
        return text.replace(/\&[\w\d\#]{2,5}\;/g, function(m) { return map[m]; });
    }

    function updateURL(select) {
        let url = new URL(window.location.href);
        let assignmentName = url.searchParams.get("assignment");
        let partName = encode_html(select.value.replace(new RegExp(' ', 'g'), "~"));
        window.location.href = "view_test_code.php?assignment=" + assignmentName + "&part=" + partName;
    }

    function updateSubmission(select, student_id) {
        let url = new URL(window.location.href);
        let assignmentName = decode_html(url.searchParams.get("assignment").replace(new RegExp('~', 'g'), " "));
        let partName = decode_html(url.searchParams.get("part").replace(new RegExp('~', 'g'), " "));
        loadPartFiles(assignmentName, partName, student_id, select.value);
    }

    function testShownSubmission(student_id) {
        document.getElementById("testing_button").style.display = "none";
        document.getElementById("testResponse").innerHTML = "Please wait while we run your code...";
        document.getElementById("testResponse").style.display = "block";
        let submissionNumber = document.getElementById("selectSubmission").value;
        let ajaxQuery = new XMLHttpRequest();
        let url = new URL(window.location.href);
        let assignmentName = decode_html(url.searchParams.get("assignment").replace(new RegExp('~', 'g'), " "));
        let partName = decode_html(url.searchParams.get("part").replace(new RegExp('~', 'g'), " "));
        ajaxQuery.onreadystatechange = function () {
            if (this.readyState === 4 && this.status === 200) {
                console.log(this.responseText);
                document.getElementById("testResponse").innerHTML = this.responseText;
            }
        };
        ajaxQuery.open("POST", "test_student_submission.php", true);
        ajaxQuery.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        ajaxQuery.send("assignment=" + assignmentName + "&part=" + partName + "&campus_id=" + student_id + "&submission_number=" + submissionNumber + "&alpha_num_key=" + document.getElementById("alpha_num_key").innerText);
    }

</script>
<?php
$currUser = phpCAS::getUser();
echo "<body onload='checkIfPartWasPosted(\"$currUser\")'>";
echo "<ul>";
echo "<li><a href='home.php'>Home</a></li>";
if(getEnrollment(phpCAS::getUser(), $conn) === "Student") {
    echo "<li><a href='assignments.php'>View Assignments</a></li>
                <li><a href='grades.php'>View Grades</a></li>";
} else if(getEnrollment(phpCAS::getUser(), $conn) === "TA") {
    echo "<li><a href='assignments.php'>View Assignments</a></li>
                <li><a href='grades.php'>View Grades</a></li>
                <li><a href='ta/grade_submissions.php' style='color: deepskyblue'>Grade Submissions</a></li>";
} else if(getEnrollment(phpCAS::getUser(), $conn) === "Instructor") {
    echo "<li><a href='assignments.php'>View Assignments</a></li>
                <li><a href='grades.php'>View Grades</a></li>
                <li><a href='ta/grade.php' style='color: deepskyblue'>Grade Submissions</a></li>
                <li><a href='administration/user_management.php' style='color: red'>User Management</a></li>
                <li><a href='administration/assignment_management.php' style='color: red'>Assignment Management</a></li>";
}
if(getEnrollment(phpCAS::getUser(), $conn) !== false) {
    echo '<li style="float:right"><a id="userButton" onclick="logoutDropdown()">';
    echo getFullNameFromCampusID(phpCAS::getUser(), $conn) . " (" . phpCAS::getUser() . ")";
    echo '
        </a>
                <div id="logOutDrop" class="user_content">
                    <a href="?logout=True">Log Out</a>
                </div>
            </li>;';
}
echo "</ul>";


$assignment_sql = $conn->real_escape_string(htmlspecialchars_decode($assignment, ENT_QUOTES));

$assignment_part_list = "SELECT DISTINCT part_name FROM SubmissionParts WHERE assignment = '$assignment_sql'";
$part_list = $conn->query($assignment_part_list);

echo "<!-- Unique alphanumeric key for security. DO NOT SHARE! -->";
$alpha_num_key = getAlphaNumKey(phpCAS::getUser(), $conn);
echo "<p hidden id='alpha_num_key'>$alpha_num_key</p>";

echo "<p style='font-size: xx-large;text-align: center;margin-top: 5px;margin-bottom: 5px'>View and Test Submission for $assignment</p>";
$select_id = "assignmentParts_Dropdown";
echo "<div style='text-align: center'>";
echo "<label for=$select_id style='font-size: x-large;'>Select a Part: </label>";
echo "<select name=$select_id id=$select_id onchange='updateURL(this)' style='margin-right: 5px;width: 250px; height: 30px;font-size: large'>";
echo "<option disabled selected value='Select a Part...'>Select a Part...</option>";
$selected_part_name = null;
$selectedPart = false;
while ($row = $part_list->fetch_assoc()) {
    $part_name = $row['part_name'];
    if(isset($_GET['part']) and str_replace("~", " ", $_GET['part']) == $part_name) {
        echo "<option selected value='$part_name'>$part_name</option>";
        $selected_part_name = $conn->real_escape_string(htmlspecialchars_decode($part_name, ENT_QUOTES));
        $selectedPart = true;
    } else {
        echo "<option value='$part_name'>$part_name</option>";
    }
}
echo "</select>";
$select_sub_id = "selectSubmission";
echo "<br><br>";
echo "<label for=$select_sub_id style='font-size: large;'>Select a Submission: </label>";
if($selectedPart) {
    echo "<select name=$select_sub_id id=$select_sub_id onchange='updateSubmission(this, \"$currUser\")' style='margin-right: 5px;width: 375px;font-size: large'>";
    // Get all of the student's submissions in order :)
    $student_id = phpCAS::getUser();
    $get_file_num_for_part_sql = "SELECT id_number FROM SubmissionParts WHERE assignment = '$assignment_sql' AND part_name = '$selected_part_name'";
    $file_num_for_part = $conn->query($get_file_num_for_part_sql)->num_rows;
    $get_student_part_subs_sql = "SELECT submission_number, date_submitted FROM Submissions WHERE assignment = '$assignment_sql' AND assignment_part = '$selected_part_name' AND student_id = '$student_id'";
    // We don't need to explicitly order by submission number, since the primary key is already in incremental order.
    $get_student_part_subs_result = $conn->query($get_student_part_subs_sql);
    $number_submissions = $get_student_part_subs_result->num_rows; // Get number of submissions, so we can show which is the latest
    $curr_counter = 1;
    while($row = $get_student_part_subs_result->fetch_assoc()) {
        $curr_sub_number = $row['submission_number'];
        if($curr_counter % $file_num_for_part === 0) { // If it is a singular submission and not another file from the same submission (idk what this means either lol)
            $date_subbed = null;
            try {
                $date_subbed = new DateTime($row["date_submitted"]);
            } catch (Exception $e) {
                echo "Date Time error! Getting submissions in View Test Code!";
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
} else {
    echo "<select disabled name=$select_sub_id id=$select_sub_id onchange='updateSubmission(this, \"$currUser\")' style='margin-right: 5px;width: 250px;font-size: large'>";
    echo "<option selected value='Select a part first!'>Select a part first!</option>";
}
echo "</select>";
echo "</div>";
echo "<br>";
echo "<div id='submissionsView' style='padding: 2px;width: 99%;border: 2px solid black;height: 75%'>";
echo "<p style='font-size: x-large;text-align: center'></p>";
echo "</div>";
echo "<div id='testingView' style='padding: 2px;width: 99%;border: 2px solid black;margin-top: 2px;text-align: center'>";
echo "<p style='font-size: x-large;text-align: center'>Test your code on the Docker Server! (takes ~15 seconds)</p>";
echo "<button class='test_button' id='testing_button' onclick='testShownSubmission(\"$currUser\")'>Test Your Code!</button>";
echo "<p id='testResponse' style='font-size: large;text-align: center;display: none'></p>";
echo "</div>";
?>
