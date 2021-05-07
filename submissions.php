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

$assignment = str_replace("~", " ", $_GET["assignment"]);
$encoded_assignment = htmlspecialchars(str_replace("~", " ", $_GET["assignment"]), ENT_QUOTES);

// Check if the GET query string value exists as an assignment in the SQL database.
$check_assignment_sql = "SELECT assignment_name FROM Assignments";
$assignments_result = $conn->query($check_assignment_sql);

// Loop that checks for existence - if it is still false, then the assignment does not exist, so let's return the user to the assignments page.
$assignment_exists = False;
while($row = $assignments_result->fetch_assoc() and !$assignment_exists) {
    $curr_assignment = $row['assignment_name'];
    if($curr_assignment == $assignment) {
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

    div {
        border: 2px solid black;
        margin: 2px;
    }

    div.outerDiv {
        border: none;
        margin-top: 10px;
    }

    button.submitPart {
        background-color: white;
        color: #0d6b0d;
        display: inline-block;
        padding: 10px 15px;
        margin-bottom: 5px;
        cursor: pointer;
        border: 2px solid;
        align-content: center;
    }

    button.submitPart:hover {
        background-color: #0d6b0d;
        color: white;
    }

    button.testCode {
        background-color: white;
        color: #0073ca;
        display: inline-block;
        padding: 10px 15px;
        margin-left: 5px;
        margin-bottom: 5px;
        cursor: pointer;
        border: 2px solid;
        align-content: center;
    }

    button.testCode:hover {
        background-color: #0073ca;
        color: white;
    }

    p.errorMessage {
        display: none;
        color: red;
        margin: 0;
    }

</style>
<?php
$currUser = phpCAS::getUser();
echo "<body onload='loadAssignmentParts(\"$encoded_assignment\", \"$currUser\")'>";
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

echo "<!-- Unique alphanumeric key for security. DO NOT SHARE! -->";
$alpha_num_key = getAlphaNumKey(phpCAS::getUser(), $conn);
echo "<p hidden id='alpha_num_key'>$alpha_num_key</p>";

echo "<p style='font-size: xx-large;text-align: center;margin-top: 5px;margin-bottom: 5px'>Program Submission for $assignment</p>";

echo "<div id='submissionsDiv' style='border: none'>";
// Placeholder for AJAX Query
echo "</div>";

echo "</body>";
?>

<script>
    function logoutDropdown() {
        if(document.getElementById('logOutDrop').style.display === 'block') {
            document.getElementById('logOutDrop').style.display = '';
        } else {
            document.getElementById('logOutDrop').style.display = 'block';
        }
    }

    function submitPart(button, student_id, assignment) {
        let part_name_tilde = button.id.split("_")[1];
        let partErrorMessages = document.getElementById(part_name_tilde).getElementsByClassName("errorMessage");
        for(let counter = 0; counter < partErrorMessages.length; counter++) {
            partErrorMessages[counter].style.display = 'none';
        }
        let filesForPart = document.getElementById(part_name_tilde).getElementsByTagName('input'); // Gets all input tags (which are files).
        let submissionData = new FormData();
        let errorWithFiles = false;
        for(let counter = 0; counter < filesForPart.length; counter++) {
            document.getElementById("errorFile_" + filesForPart[counter].id).style.display = 'none';
            let currFile = filesForPart[counter].files[0];
            if(currFile === undefined) {
                document.getElementById("errorFile_" + filesForPart[counter].id).innerText = "You must submit this file!";
                document.getElementById("errorFile_" + filesForPart[counter].id).style.display = 'block';
                errorWithFiles = true;
            } else {
                if(currFile['name'] !== filesForPart[counter].id) {
                    document.getElementById("errorFile_" + filesForPart[counter].id).innerText = "You must submit a file called '" + filesForPart[counter].id + "'!";
                    document.getElementById("errorFile_" + filesForPart[counter].id).style.display = 'block';
                    errorWithFiles = true;
                } else {
                    submissionData.append(filesForPart[counter].id.replace(/\./g, '~'), filesForPart[counter].files[0]);
                }
            }
        }
        if(!errorWithFiles) {
            submissionData.append("student_id", student_id);
            submissionData.append("assignment_name", assignment);
            submissionData.append("part_name", part_name_tilde.replace(new RegExp('~', 'g'), " "));
            submissionData.append("alpha_num_key", document.getElementById("alpha_num_key").innerText);
            let ajaxQuery = new XMLHttpRequest();
            ajaxQuery.onreadystatechange = function () {
                if (this.readyState === 4 && this.status === 200) {
                    console.log(this.responseText);
                    document.getElementById("partResponse_" + part_name_tilde).innerText = this.responseText;
                    let prefix = this.responseText.substring(0, 5);
                    if(prefix === "ERROR") {
                        document.getElementById("partResponse_" + part_name_tilde).style.color = "#ff0000";
                        document.getElementById("partResponse_" + part_name_tilde).style.display = 'block';
                    } else {
                        document.getElementById("partResponse_" + part_name_tilde).style.color = "#3f9b42";
                        for(let counter = 0; counter < filesForPart.length; counter++) {
                            filesForPart[counter].value = ""; // Resets each file input upon successful submission
                        }
                        reloadAssignmentParts(assignment, part_name_tilde, student_id, this.responseText);
                    }
                }
            };
            ajaxQuery.open("POST", "submit_part.php", true);
            ajaxQuery.send(submissionData);
        }
    }

    function loadAssignmentParts(assignment, user_id) {
        let ajaxQuery = new XMLHttpRequest();
        ajaxQuery.onreadystatechange = function () {
            if (this.readyState === 4 && this.status === 200) {
                document.getElementById("submissionsDiv").innerHTML = this.responseText;
            }
        };
        ajaxQuery.open("POST", "retrieve_parts.php", true);
        ajaxQuery.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        ajaxQuery.send("assignment_name=" + assignment + "&campus_id=" + user_id);
    }

    function reloadAssignmentParts(assignment, part_name, user_id, ajaxResponse) {
        let ajaxQuery = new XMLHttpRequest();
        ajaxQuery.onreadystatechange = function () {
            if (this.readyState === 4 && this.status === 200) {
                document.getElementById("submissionsDiv").innerHTML = this.responseText;
                let prefix = ajaxResponse.substring(0, 5);
                document.getElementById("partResponse_" + part_name).innerText = ajaxResponse
                if (prefix === "ERROR") {
                    document.getElementById("partResponse_" + part_name).style.color = "#ff0000";
                } else {
                    document.getElementById("partResponse_" + part_name).style.color = "#3f9b42";
                }
                document.getElementById("partResponse_" + part_name).style.display = 'block';
            }
        };
        ajaxQuery.open("POST", "retrieve_parts.php", true);
        ajaxQuery.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        ajaxQuery.send("assignment_name=" + assignment + "&campus_id=" + user_id);
    }

    function viewTestPartFromSubmission(button) {
        let button_split = button.id.split("_");
        let assignment_tilde = button_split[1];
        let part_tilde = button_split[2];
        window.location.href = "view_test_code.php?assignment=" + assignment_tilde + "&part=" + part_tilde;
    }
</script>
