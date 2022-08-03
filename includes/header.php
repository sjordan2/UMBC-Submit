<?php
require_once 'sql_functions.php';

$conn = new mysqli($sql_host, $sql_username, $sql_password, $sql_dbname);

$user_campus_id = $_SERVER["umbccampusid"];
$role = getEnrollment($user_campus_id, $conn);
?>

<style>
    .text-umbc {
        color: #F1C04B !important;
    }
    .text-student {
        color: lightgray !important;
    }
    .text-ta {
        color: deepskyblue !important;
    }
    .text-admin {
        color: red !important;
    }
    .ms-auto .dropdown-menu {
        left: auto !important;
        right: 0;
    }
    .current{
        color: white !important;
        background-color: #4CAF50;
        pointer-events: none;
    }
    @media only screen and (min-width: 992px) {
        .header-nav:not(.last) {
            padding-right: 0.8%;
            border-right: 1px solid #454545;
            margin-right: 0.2%;
        }
        .navbar-brand {
            padding-right: 0.8%;
            border-right: 3px solid #454545;
            margin-right: 0.2%;
        }
    }
</style>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark" id="topNavBar">
    <div class="container-fluid">
        <a class="navbar-brand text-umbc" href="/">The UMBC Submit System</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavDarkDropdown" aria-controls="navbarNavDarkDropdown" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNavDarkDropdown">
            <?php if($role !== false): ?>
            <ul class="navbar-nav me-auto">
                <?php if($role === "Student"): ?>
                    <li class="nav-item"><a class="nav-link text-student" href="/assignments.php">View Assignments</a></li>
                    <li class="nav-item"><a class="nav-link text-student" href="#">View Grades</a></li>
                <?php else: ?>
                <li class="nav-item header-nav dropdown">
                    <a class="nav-link dropdown-toggle text-student" href="#" id="navbarDarkDropdownMenuLink" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        Student Links
                    </a>
                    <ul class="dropdown-menu dropdown-menu-dark" aria-labelledby="navbarDarkDropdownMenuLink">
                        <li><a class="dropdown-item text-student" href="/assignments.php">View Assignments</a></li>
                        <li><a class="dropdown-item text-student" href="#">View Grades</a></li>
                    </ul>
                </li>
                <?php endif; ?>
                <?php if($role === "TA" OR $role === "Instructor"): ?>
                <li class="nav-item header-nav dropdown">
                    <a class="nav-link dropdown-toggle text-ta" href="#" id="navbarDarkDropdownMenuLink" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        TA Links
                    </a>
                    <ul class="dropdown-menu dropdown-menu-dark" aria-labelledby="navbarDarkDropdownMenuLink">
                        <li><a class="dropdown-item text-ta" href="#">Grade Submissions</a></li>
                    </ul>
                </li>
                <?php endif; ?>
                <?php if($role === "Instructor"): ?>
                <li class="nav-item header-nav dropdown">
                    <a class="nav-link dropdown-toggle text-admin" href="#" id="navbarDarkDropdownMenuLink" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        Administration Links
                    </a>
                    <ul class="dropdown-menu dropdown-menu-dark" aria-labelledby="navbarDarkDropdownMenuLink">
                        <li><a class="dropdown-item text-admin" href="/administration/user_management.php">User Management</a></li>
                        <li><a class="dropdown-item text-admin" href="/administration/assignment_management.php">Assignment Management</a></li>
                        <li><a class="dropdown-item text-admin" href="#">Portal Settings</a></li>
                    </ul>
                </li>
                <?php endif; ?>
            </ul>
            <?php endif; ?>
            <ul class="navbar-nav ms-auto">
                <li class="nav-item header-nav dropdown last">
                    <a class="nav-link dropdown-toggle text-student" href="#" id="navbarDarkDropdownMenuLink" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <?php echo $_SERVER["displayName"] . " (" . $_SERVER["umbccampusid"] . ")";?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-dark" aria-labelledby="navbarDarkDropdownMenuLink">
                        <li><a class="dropdown-item text-admin" href="https://csee.umbc.edu">Log Out</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-gtEjrD/SeCtmISkJkNUaaKMoLD0//ElJ19smozuHV6z3Iehds+3Ulb9Bn9Plx0x4" crossorigin="anonymous"></script>
<script>
    // This just highlights and disables the current page button in the navigation bar dropdown.
    let page = window.location.pathname;
    let linkList = document.getElementsByClassName("dropdown-item");
    for(let index = 0; index < linkList.length - 1; index++) { // -1 to exclude the log out button
        if(page === linkList[index].getAttribute("href")) {
            linkList[index].classList.add("current");
        }
    }
</script>