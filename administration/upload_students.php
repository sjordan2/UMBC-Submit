<?php

const REX_LASTNAME = "StudentLastName"; // Last name column header in Rex
const REX_FIRSTNAME = "StudentFirstName"; // First name column header in Rex
const REX_CAMPUSID = "StudentCampusID"; // Campus ID column header in Rex
const REX_NAMEID = "StudentMyUMBCId"; // Name ID column header in Rex
const REX_DISCUSSION = "ClassNumberClassSectionSourceKey"; // Lecture/Discussion Section column header in Rex

$students_file = fopen($_FILES['students_file']['tmp_name'], 'r');
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
for($index = 0; $index < count($headerArray); $index++) {
    if($headerArray[$index] === REX_LASTNAME) {
        $lastNameColumn = $index;
    }
    else if($headerArray[$index] === REX_FIRSTNAME) {
        $firstNameColumn = $index;
    }
    else if($headerArray[$index] === REX_CAMPUSID) {
        $campusIDColumn = $index;
    }
    else if($headerArray[$index] === REX_NAMEID) {
        $nameIDColumn = $index;
    }
    else if($headerArray[$index] === REX_DISCUSSION) {
        $discussionColumn = $index;
    }
}

require_once '../sql_functions.php';
$conn = new mysqli($sql_host, $sql_username, $sql_password, $sql_dbname);
$totalCounter = 0;
while (($line = fgetcsv($students_file, 0, $fileDelimiter)) !== FALSE) {
    // This is assuming that lecture sections are "10, 20, 30", etc. and discussion sections are "11, 34, 56" etc.
    if(intval($line[$discussionColumn]) % 10 !== 0) { // If the row denotes a discussion section, not a lecture section
        addUserToDatabase($line[$nameIDColumn], $line[$campusIDColumn], $line[$firstNameColumn],
            $line[$lastNameColumn], $line[$discussionColumn], "Student", $conn, false);
        $totalCounter++;
    }
}
fclose($students_file);

$successMessage = "SUCCESS: Uploaded " . $totalCounter . " students to the database!";
echo $successMessage;