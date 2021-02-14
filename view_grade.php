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
    body {background-color: #F1C04B}

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

    div.outerDiv {
        border: none;
        margin-top: 10px;
        width: 100%;
    }

    button.viewAssignment {
        background-color: white;
        color: #5e00ca;
        display: inline-block;
        padding: 15px 15px;
        cursor: pointer;
        border: 2px solid;
        text-align: left;
    }

    button.viewAssignment:hover {
        background-color: #5e00ca;
        color: white;
    }

    button.submitCode {
        background-color: white;
        color: #0d6b0d;
        display: inline-block;
        padding: 10px 15px;
        margin-left: 5px;
        margin-bottom: 5px;
        cursor: pointer;
        border: 2px solid;
        text-align: center;
    }

    button.submitCode:hover {
        background-color: #0d6b0d;
        color: white;
    }

    button.testProgram {
        background-color: white;
        color: #0073ca;
        display: inline-block;
        padding: 10px 15px;
        margin-left: 5px;
        margin-bottom: 5px;
        margin-right: 5px;
        cursor: pointer;
        border: 2px solid;
        text-align: right;
    }

    button.testProgram:hover {
        background-color: #0073ca;
        color: white;
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


</style>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/10.5.0/styles/an-old-hope.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/10.5.0/highlight.min.js"></script>
<script>
    function updatePartDiv(student_id) {
        let alpha_num_key = document.getElementById("alpha_num_key").innerText;
        let part = decode_html(document.getElementById("assignmentParts_Dropdown").value.replace(new RegExp('~', 'g'), " "));
        let url = new URL(window.location.href);
        let assignment = decode_html(url.searchParams.get("assignment").replace(new RegExp('~', 'g'), " "));
        let ajaxQuery = new XMLHttpRequest();
        ajaxQuery.onreadystatechange = function() {
            if (this.readyState === 4 && this.status === 200) {
                document.getElementById("partDiv").innerHTML = this.responseText;
                highlightDefault();
            }
        };
        ajaxQuery.open("POST", "retrieve_rubric_for_student.php", true);
        ajaxQuery.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        ajaxQuery.send("assignment=" + assignment + "&part_name=" + part + "&student=" + student_id + "&alpha_num_key=" + alpha_num_key);
    }

    function highlightDefault() {
        let preContent = document.createElement("pre");
        let codeContent = document.createElement("code");
        codeContent.innerText = document.getElementById("codeContentDiv").innerText;
        hljs.highlightBlock(codeContent);
        document.getElementById("codeContentDiv").innerHTML = "";
        document.getElementById("codeContentDiv").appendChild(preContent);
        preContent.appendChild(codeContent);
        document.getElementById("codeContentDiv").style.display = 'block';
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

    function selectCodeTab(assignment, part, student_id, file_name, select = "latest", type) {
        let alpha_num_key = document.getElementById("alpha_num_key").innerText;
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
        ajaxQuery.open("POST", "retrieve_student_submission.php", true);
        ajaxQuery.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        ajaxQuery.send("assignment=" + assignment + "&part=" + part + "&campus_id=" + student_id + "&fileName=" + file_name + "&submission_number=" + submissionNumber + "&alpha_num_key=" + alpha_num_key);
    }

    function updateSubmission(select, student_id) {
        let alpha_num_key = document.getElementById("alpha_num_key").innerText;
        let url = new URL(window.location.href);
        let assignmentName = decode_html(url.searchParams.get("assignment").replace(new RegExp('~', 'g'), " "));
        let partName = decode_html(document.getElementById("assignmentParts_Dropdown").value.replace(new RegExp('~', 'g'), " "));
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
        ajaxQuery.open("POST", "retrieve_student_part_files.php", true);
        ajaxQuery.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        ajaxQuery.send("assignment=" + assignmentName + "&part=" + partName + "&campus_id=" + student_id + "&submission_number=" + select.value + "&method=grader" + "&alpha_num_key=" + alpha_num_key);
    }

</script>
<?php
$currUser = phpCAS::getUser();
echo "<body>";
?>
<ul>
    <li><a href="home.php">Home</a></li>
    <?php
    if(getEnrollment(phpCAS::getUser(), $conn) === "Student") {
        echo "<li><a href='assignments.php'>View Assignments</a></li>
                <li><a href='grades.php' class='current'>View Grades</a></li>";
    } else if(getEnrollment(phpCAS::getUser(), $conn) === "TA") {
        echo "<li><a href='assignments.php' class='current'>View Assignments</a></li>
                <li><a href='grades.php' class='current'>View Grades</a></li>
                <li><a href='ta/grade_submissions.php' style='color: deepskyblue'>Grade Submissions</a></li>";
    } else if(getEnrollment(phpCAS::getUser(), $conn) === "Instructor") {
        echo "<li><a href='assignments.php'>View Assignments</a></li>
                <li><a href='grades.php' class='current'>View Grades</a></li>
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
    echo "<!-- Unique alphanumeric key for security. DO NOT SHARE! -->";
    $alpha_num_key = getAlphaNumKey(phpCAS::getUser(), $conn);
    echo "<p hidden id='alpha_num_key'>$alpha_num_key</p>";
    echo "</ul>";
echo "<div style='width: 99.6%;text-align: center;vertical-align: center'>";
$assignment_name = htmlspecialchars_decode(str_replace("~", " ", $_GET['assignment']), ENT_QUOTES);
echo "<p style='font-size: xx-large;display: inline'>Your Grade for $assignment_name</p>";
echo "</div>";
echo "<div style='width: 98.9%;text-align: center;padding: 5px'>";
$select_id = "assignmentParts_Dropdown";
echo "<label for=$select_id style='font-size: x-large;'>Select a Part: </label>";
echo "<select name=$select_id id=$select_id onchange='updatePartDiv(\"$currUser\")' style='margin-right: 5px;width: 250px; height: 30px;font-size: large'>";
$part_number = 1;
$assignment_sql = $conn->real_escape_string($assignment_name);
$get_parts_sql = "SELECT DISTINCT part_name FROM SubmissionParts WHERE assignment = '$assignment_sql'";
$get_parts_result = $conn->query($get_parts_sql);
while($row = $get_parts_result->fetch_assoc()) {
    $part_name = $row["part_name"];
    $encoded_part_name = htmlspecialchars(str_replace(" ", "~", $part_name), ENT_QUOTES);
    if($part_number === 1) {
        echo "<option selected value='$encoded_part_name'>$part_name</option>";
    } else {
        echo "<option value='$encoded_part_name'>$part_name</option>";
    }
    $part_number++;
}
echo "<script>updatePartDiv(\"$currUser\")</script>";
echo "</select>";
echo "</div>";
echo "<div id='partDiv' style='border: none;width: 100%'>";
// Placeholder for AJAX Query
echo "</div>";
?>
