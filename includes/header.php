<?php $userpage = "/~minc1" ?>

<html lang="en">
<head>
    <!-- Styles -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
        
    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script>
    <script>
    window.onbeforeunload = function () {
        window.scrollTo(0, 0);
    }
    </script>
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <div class="row justify-content-center" style="width:100%">
                <div class="col-8">
                    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbar-items" aria-controls="navbar-items" aria-expanded="false" aria-label="Toggle navigation">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                    <div id="navbar-items" class="collapse navbar-collapse" id="navbarSupportedContent">
                        <ul class="navbar-nav mr-auto">
                            <li id="page_home" class="nav-item">
                                <a class="nav-link" href="<?php echo $userpage; ?>/">Home <span class="sr-only">(current)</span></a>
                            </li>

                            <?php
                            if(getEnrollment(phpCAS::getUser(), $conn) === "Student" ||
                                    getEnrollment(phpCAS::getUser(), $conn) === "TA" ||
                                    getEnrollment(phpCAS::getUser(), $conn) === "Instructor") {
                                echo "<li class=\"nav-item dropdown\">";
                                echo '
                                    <a id="page_student" class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        Assignments
                                    </a>
                                ';
                                echo '
                                    <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                                        <a class="dropdown-item" href="' . $userpage . '/assignments.php">View Assignments</a>
                                        <a class="dropdown-item" href="' . $userpage . '/grades.php">View Grades</a>
                                    </div>
                                ';
                                echo "</li>";
                            } 
                            if(getEnrollment(phpCAS::getUser(), $conn) === "TA" ||
                                    getEnrollment(phpCAS::getUser(), $conn) === "Instructor") {
                                echo "<li class=\"nav-item dropdown\">";
                                echo '
                                    <a id="page_ta" class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        Grading
                                    </a>
                                ';
                                echo '
                                    <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                                        <a class="dropdown-item" href="' . $userpage . '/ta/grade.php">Grade Submissions</a>
                                    </div>
                                ';
                                echo "</li>";
                            } 
                            if(getEnrollment(phpCAS::getUser(), $conn) === "Instructor") {
                                echo "<li class=\"nav-item dropdown\">";
                                echo '
                                    <a id="page_admin" class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        Management
                                    </a>
                                ';
                                echo '
                                    <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                                        <a class="dropdown-item" href="' . $userpage . '/administration/user_management.php">User Management</a>
                                        <a class="dropdown-item" href="' . $userpage . '/administration/assignment_management.php">Assignment Management</a>
                                    </div>
                                ';
                                echo "</li>";
                            }
                            ?>
                        </ul>
                        <ul class="navbar-nav float-right">
                            <?php
                            if(getEnrollment(phpCAS::getUser(), $conn) !== false) {
                                echo "<li class=\"nav-item dropdown\">";
                                echo '<a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">';
                                echo getFullNameFromCampusID(phpCAS::getUser(), $conn) . " (" . phpCAS::getUser() . ")";
                                echo '</a>';
                                echo '
                                    <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                                        <a class="dropdown-item" href="?logout=True">Log Out</a>
                                    </div>
                                ';
                                echo "</li>";
                            } else {
                                $UNENROLLED_STUDENT = true; // This is a word, right?
                            }
                            ?>
                        </ul>
                    </div><!-- End Navbar Wrapper -->
                </div><!-- End Bootstrap col -->
            </div> <!-- End Bootstrap row -->
        </div> <!-- End Bootstrap container -->
    </nav>

    <!-- Content Begins -->
    <div class="container">