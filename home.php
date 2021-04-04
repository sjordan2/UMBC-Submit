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
?>

<!-- Import the HTML header and navbar -->
<?php $current_page = "page_home" ?>
<?php include_once "includes/header.php" ?>

<?php
if($UNENROLLED_STUDENT === true) {
    echo "<p style='font-size: xx-large;text-align: center'>Oh no! It appears that you are not registered for this course!</p>";
} else {
    echo "<p style='font-size: x-large;text-align: center'>Welcome to the UMBC Submit System! Please click 'View Assignments' in the top navigation bar to get started.</p><br><p style='font-size: x-large;text-align: center'>idk what to put here... a logo? announcement board? course information?</p>";
}
?>

<!-- Complete the HTML page -->
<?php include_once "includes/footer.php" ?>