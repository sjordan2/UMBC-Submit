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
$assignment = str_replace("~", " ", $_GET['assignment']);
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

    </style>
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
echo "<p style='font-size: xx-large;margin: 0px;text-align: center'>View/Grade Submissions for $assignment</p>";

echo "<div style='text-align: center;width: 100%;border: none'>";
echo "<select id='taSections' onchange='changeSection(this)' style='width: 300px;font-size: medium;display: inline;margin-right: 5px'>";
$get_sections_sql = "SELECT umbc_id, firstname, lastname, section FROM Users WHERE role != 'Student' ORDER BY section";
$get_sections_result = $conn->query($get_sections_sql);
$curr_section = $_GET['section'];
echo "<option selected disabled value='Select a Section...' style='width: 200px'>Select a Section...</option>";
while($row = $get_sections_result->fetch_assoc()) {
    $row_section = $row['section'];
    $option_text = "Section " . $row_section . " - " . $row['firstname'] . " " . $row['lastname'];
    if($curr_section === $row_section) {
        echo "<option selected value='$row_section' style='width: 200px'>$option_text</option>";
    } else {
        echo "<option value='$row_section' style='width: 200px'>$option_text</option>";
    }
}
echo "</select>";

$assignment_sql = $conn->real_escape_string($assignment);
$assignment_parts_sql = "SELECT DISTINCT part_name FROM SubmissionParts WHERE assignment = '$assignment_sql'";
$assignment_parts_result = $conn->query($assignment_parts_sql);
echo "<select id='subParts' onchange='changePart(this)' style='width: 300px;font-size: medium;display: inline;text-align: center;'>";
echo "<option disabled selected value='Select a Part...' style='width: 200px'>Select a Part...</option>";
while($row_part = $assignment_parts_result->fetch_assoc()) {
    $part_name = htmlspecialchars($row_part["part_name"], ENT_QUOTES);
    if(isset($_GET['part']) and str_replace("~", " ", $_GET['part']) === $part_name) {
        echo "<option selected value='$part_name' style='width: 200px'>$part_name</option>";
    } else {
        echo "<option value='$part_name' style='width: 200px'>$part_name</option>";
    }
}
echo "</select>";
echo "</div>";

echo "<div id='div_viewStudentsForPart' style='width: 100%;border: none'>";
if(!isset($_GET['part'])) {
    echo "<p style='font-size: large;margin: 0px;text-align: center'>You must select a part first!</p>";
} else {
    $clean_part = htmlspecialchars_decode(str_replace("~", " ", $_GET['part']), ENT_QUOTES);
    echo "<p style='font-size: x-large;margin: 0px;text-align: center'>Student Submissions for $clean_part</p>";
    $students_array = getStudentsInSection($curr_section, $conn);
    if(count($students_array) === 0) {
        echo "<p style='font-size: x-large;text-align: center;color: red'>ERROR: That section does not have any students!</p>";
    } else {
        foreach ($students_array as $student) {
            $studentText = getFullNameFromCampusID($student, $conn) . " (" . getNameIDFromCampusID($student, $conn) . ")";
            echo "<div style='width: 100%'>";
            echo "<p style='font-size: x-large;margin: 2px;text-align: center'><b>$studentText</b></p>";
            echo "<p style='font-size: x-large;margin-left: 3px;text-align: center;display: inline'><b>Status:&nbsp</b></p>";
            $studentGradingStatus = getStudentGradingStatus($student, $assignment, $clean_part, $conn);
            if ($studentGradingStatus === true) {
                echo "<p style='font-size: x-large;margin-bottom: 0px;margin-top: 5px;display: inline;color: #0d6b0d'><b>Graded</b></p>";
            } else {
                echo "<p style='font-size: x-large;margin: 2px;display: inline;color: red'><b>Not Complete</b></p>";
            }
            $button_id = "gradeStudent_" . htmlspecialchars($_GET['assignment'], ENT_QUOTES) . "_" . htmlspecialchars($_GET['part'], ENT_QUOTES) . "_" . $student;
            echo "<button class='gradeSubmissions' id=$button_id onclick='gradeStudent(this)'>View/Grade Submission</button>";
            echo "</div><br>";
        }
    }
}
echo "</div>";
echo "</body>";
?>
<script>
    function changePart(select) {
        let url = new URL(window.location.href);
        let assignmentName = url.searchParams.get("assignment");
        let sectionNumber = url.searchParams.get("section");
        let desiredPart = select.value;
        let partName = escapeHtml(desiredPart.replace(new RegExp(' ', 'g'), "~"));
        window.location.href = "grade_assignment.php?assignment=" + assignmentName + "&section=" + sectionNumber + "&part=" + partName;
    }

    function changeSection(select) {
        let url = new URL(window.location.href);
        let assignmentName = url.searchParams.get("assignment");
        let partName = url.searchParams.get("part");
        let desiredSection = select.value;
        if(!partName) { // If the part is not set, then don't include it in the query string
            window.location.href = "grade_assignment.php?assignment=" + assignmentName + "&section=" + desiredSection;
        } else {
            window.location.href = "grade_assignment.php?assignment=" + assignmentName + "&section=" + desiredSection + "&part=" + partName;
        }
    }

    function gradeStudent(button) {
        let button_split = button.id.split("_");
        let assignment_name = button_split[1];
        let part_name = button_split[2];
        let student_id = button_split[3];
        window.location.href = "grade_student_submissions.php" + "?assignment=" + assignment_name + "&part=" + part_name + "&student=" + student_id;
    }

    function escapeHtml(text) { // Function from stack overflow that allows html character encoding
        let map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };

        return text.replace(/[&<>"']/g, function(m) { return map[m]; });
    }
</script>
