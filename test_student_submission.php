<?php
include 'sql_functions.php';
include 'test_server_creds.php';
include '../vendor/autoload.php';

$assignmentName = $_POST["assignment"];
$partName = $_POST["part"];
$studentCampusID = $_POST["campus_id"];
$submissionNumber = $_POST["submission_number"];
$given_alphanumeric = $_POST["alpha_num_key"];

// Create connection
$conn = new mysqli($sql_host, $sql_username, $sql_password, $sql_dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$student_ank = getAlphaNumKey($studentCampusID, $conn);

if($student_ank !== $given_alphanumeric) {
    echo "ERROR: Authentication keys do not match!\nThis incident has been reported to the course administrators.";
    exit();
}

$assignmentName_sql = $conn->real_escape_string($assignmentName);
$partName_sql = $conn->real_escape_string($partName);

$get_student_code_sql = "SELECT submission_name, submission_contents FROM Submissions WHERE assignment = '$assignmentName_sql' AND assignment_part = '$partName_sql' AND student_id = '$studentCampusID' AND submission_number = '$submissionNumber'";
$code_results = $conn->query($get_student_code_sql);
if($code_results->num_rows === 0) {
    echo "You do not have anything submitted for this part! Please submit something before attempting to test!";
    exit();
}

$get_makefile_sql = "SELECT file_contents FROM Testing WHERE assignment = '$assignmentName_sql' AND part = '$partName_sql' AND file_type = 'SAMPLE_MAKEFILE'";
$makefile_result = $conn->query($get_makefile_sql);
if($makefile_result->num_rows === 0) {
    echo "The administrators of this course have not yet configured a sample makefile for this part. Please let them know!";
    exit();
}

$ssh_tunnel = new \phpseclib3\Net\SSH2($ssh_host, 22);

//echo $ssh_tunnel->login($ssh_username, $ssh_password);
if (!$ssh_tunnel->login($ssh_username, $ssh_password)) {
    die('SSH Login Failed. Please reload the page and try again!');
}

$ssh_tunnel->setTimeout(1); // Some leeway for commands to run
$ssh_tunnel->write("sudo pwd\n"); // Dummy command so we don't have to deal with admin password garbage
$ssh_tunnel->write($ssh_password."\n"); // First sudo run requires password, but subsequent runs do not
$ssh_tunnel->read();

// Creates the docker container and saves the ID as a variable for use later on
$ssh_tunnel->write("container_id=$(sudo docker create -it " . $docker_image_name . ")\n");
$ssh_tunnel->read();
// Starts the created docker container and attaches the IO to the console
$ssh_tunnel->write("sudo docker start -i \$container_id\n");
$ssh_tunnel->read();

// Selects all the student's submission files for this part
$get_student_code_sql = "SELECT submission_name, submission_contents FROM Submissions WHERE assignment = '$assignmentName_sql' AND assignment_part = '$partName_sql' AND student_id = '$studentCampusID' AND submission_number = '$submissionNumber'";
$code_results = $conn->query($get_student_code_sql);
// Dumps each submission file for that part into the docker file system
while($row = $code_results->fetch_assoc()) {
    $file_name = $row['submission_name'];
    $file_contents = $row['submission_contents'] . "\n";
    $ssh_tunnel->write("cat > $file_name\n");
    $ssh_tunnel->read();
    $ssh_tunnel->write($file_contents);
    $ssh_tunnel->read();
    $ssh_tunnel->write("\x03");
}

// Selects the makefile for this part
$get_makefile_sql = "SELECT file_contents FROM Testing WHERE assignment = '$assignmentName_sql' AND part = '$partName_sql' AND file_type = 'SAMPLE_MAKEFILE'";
$makefile_result = $conn->query($get_makefile_sql);
$file_contents = $makefile_result->fetch_assoc()['file_contents'] . "\n";

// Dumps the makefile for that part into the docker file system
$ssh_tunnel->write("cat > Makefile\n");
$ssh_tunnel->read();
$ssh_tunnel->write($file_contents);
$ssh_tunnel->read();
$ssh_tunnel->write("\x03");

// Selects all the sample input files for this part
$get_io_files_sql = "SELECT file_name, file_contents FROM Testing WHERE assignment = '$assignmentName_sql' AND part = '$partName_sql' AND file_type = 'SAMPLE_IO'";
$io_results = $conn->query($get_io_files_sql);
// Dumps each input file for that part into the docker file system
while($row = $io_results->fetch_assoc()) {
    $file_name = $row['file_name'];
    $file_contents = $row['file_contents'] . "\n";
    $ssh_tunnel->write("cat > $file_name\n");
    $ssh_tunnel->read();
    $ssh_tunnel->write($file_contents);
    $ssh_tunnel->read();
    $ssh_tunnel->write("\x03");
}

//$ssh_tunnel->write("echo 'Freeman Hrabowski' | python3 submit_test.py\n");
//$test_output = $ssh_tunnel->read();

// Runs the makefile (runs all of the tests at once), and then grabs the test output
$ssh_tunnel->write("make\n");
$test_output = $ssh_tunnel->read();

echo "<div id='student_output_div' style='overflow-x: scroll;background-color: black;color: white;width: 100%;max-height: 250px;overflow-y: scroll;'>";
echo "<pre style='width: 5px'>" . substr(substr($test_output, 36), 0, -18) . "</pre>";
echo "</div>";


// Exit out of the docker container interface, which stops the container
$ssh_tunnel->write("exit\n");
$ssh_tunnel->setTimeout(2);
$ssh_tunnel->read();

// Remove the docker container
$ssh_tunnel->write("sudo docker container rm \$container_id\n");
$ssh_tunnel->setTimeout(1);
$ssh_tunnel->read();

$ssh_tunnel->disconnect();





