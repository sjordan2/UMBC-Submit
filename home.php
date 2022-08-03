<?php
require_once './includes/db_sql.php';
require_once './includes/sql_functions.php';

$conn = new mysqli($sql_host, $sql_username, $sql_password, $sql_dbname);

$user_campus_id = $_SERVER["umbccampusid"];
$role = getEnrollment($user_campus_id, $conn);
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-+0n0xVW2eSR5OomGNYDnhzAbDsOXxcvSN1TPprVMTNDbiYZCxYbOOl7+AMvyTG2x" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/css/alertify.min.css"/>
    <link rel='shortcut icon' type='image/x-icon' href='/favicon.png' />
    <title>UMBC Submit System</title>
</head>
<body>
<script src="//cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/alertify.min.js"></script>
<script>
    alertify.defaults.transition = "slide";
    alertify.defaults.theme.ok = "btn btn-primary";
    alertify.defaults.theme.cancel = "btn btn-danger";
    alertify.defaults.theme.input = "form-control";
    alertify.defaults.glossary.title = "UMBC Submit System"
</script>
<?php include_once "./includes/header.php"; ?>
    <?php if($conn->connect_error): ?>
        <script>
            alertify.set('notifier','position', 'top-center');
            alertify.set('notifier','delay', 0);
            alertify.error('Fatal Error: Could not connect to SQL server: ' + "<?php echo $conn->connect_error?>");
        </script>
    <?php endif; ?>
<?php if($role === false): ?>
<script>
    alertify.alert('ERROR: Unregistered User', '<hr>It appears that you are not registered for this course! If you believe this is an error, please contact the course administrators below: <br>' + '<?php echo htmlspecialchars_decode(outputInstructors($conn)) ?>');
</script>
<?php endif; ?>
<p>Spot for Course Information and such</p>
</body>
</html>
