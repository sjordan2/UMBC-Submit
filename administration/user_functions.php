<?php

function constructUserTableData($user_result) {
    $data = [];
    while ($row = $user_result->fetch_assoc()) {
        $curr_user = [];
        $curr_user["campus_id"] = $row['umbc_id'];
        $curr_user["last_name"] = $row['lastname'];
        $curr_user["first_name"] = $row['firstname'];
        $curr_user["name_id"] = $row['umbc_name_id'];
        $curr_user["section"] = $row['section'];
        $curr_user["role"] = $row['role'];
        $curr_user["status"] = $row['status'];
        $curr_user["actions"] = "Testing 123";
        array_push($data, $curr_user);
    }
    return json_encode($data);
}
function constructUserTableHeadings() {
    $data = [];

    $campus_id = [];
    $campus_id["title"] = "Campus ID";
    $campus_id["field"] = "campus_id";
    $campus_id["sortable"] = true;
    array_push($data, $campus_id);

    $last_name = [];
    $last_name["title"] = "Last Name";
    $last_name["field"] = "last_name";
    $last_name["sortable"] = true;
    array_push($data, $last_name);

    $first_name = [];
    $first_name["title"] = "First Name";
    $first_name["field"] = "first_name";
    $first_name["sortable"] = true;
    array_push($data, $first_name);

    $name_id = [];
    $name_id["title"] = "Name ID";
    $name_id["field"] = "name_id";
    $name_id["sortable"] = true;
    array_push($data, $name_id);

    $discussion = [];
    $discussion["title"] = "Discussion Section";
    $discussion["field"] = "section";
    $discussion["sortable"] = true;
    $discussion["searchable"] = false;
    array_push($data, $discussion);

    $role = [];
    $role["title"] = "Role";
    $role["field"] = "role";
    $role["sortable"] = true;
    $role["searchable"] = false;
    array_push($data, $role);

    $status = [];
    $status["title"] = "Status";
    $status["field"] = "status";
    $status["sortable"] = true;
    $status["searchable"] = false;
    array_push($data, $status);

    $actions = [];
    $actions["title"] = "Actions";
    $actions["field"] = "actions";
    $actions["formatter"] = "loadActions";
    $actions["searchable"] = false;
    array_push($data, $actions);

    return json_encode($data);
}

function crypto_rand_secure($min, $max) : int {
    $range = $max - $min;
    if ($range < 1) return $min; // not so random...
    $log = ceil(log($range, 2));
    $bytes = (int) ($log / 8) + 1; // length in bytes
    $bits = (int) $log + 1; // length in bits
    $filter = (int) (1 << $bits) - 1; // set all lower bits to 1
    do {
        $rnd = hexdec(bin2hex(openssl_random_pseudo_bytes($bytes)));
        $rnd = $rnd & $filter; // discard irrelevant bits
    } while ($rnd > $range);
    return $min + $rnd;
}

function getToken($length) : string {
    $token = "";
    $codeAlphabet = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
    $codeAlphabet.= "abcdefghijklmnopqrstuvwxyz";
    $codeAlphabet.= "0123456789";
    $max = strlen($codeAlphabet); // edited

    for ($i=0; $i < $length; $i++) {
        $token .= $codeAlphabet[crypto_rand_secure(0, $max-1)];
    }

    return $token;
}

function email_one_user($umbc_id, $umbc_name_id, $role, $firstname, $lastname, $section, $instructor_array, $submit_system_admin, $print_successes) {
    $file = fopen("register_email.txt", "r");
    $register_email_base = fread($file, filesize("register_email.txt"));

    $curr_register_email = $register_email_base;
    $curr_register_email = str_replace("{{firstname}}", $firstname, $curr_register_email);
    $curr_register_email = str_replace("{{lastname}}", $lastname, $curr_register_email);
    $curr_register_email = str_replace("{{weblink}}", "https://submit.cs.umbc.edu", $curr_register_email);
    $curr_register_email = str_replace("{{umbcid}}", $umbc_id, $curr_register_email);
    $curr_register_email = str_replace("{{umbcnameid}}", $umbc_name_id, $curr_register_email);
    $curr_register_email = str_replace("{{role}}", $role, $curr_register_email);
    if($section === null) {
        $curr_register_email = str_replace("{{section}}", "Not Assigned", $curr_register_email);
    } else {
        $curr_register_email = str_replace("{{section}}", $section, $curr_register_email);
    }
    $curr_register_email .= "\n\n";
    foreach ($instructor_array as $signature) {
        $curr_register_email .= $signature;
        $curr_register_email .= "\n";
    }
    $curr_register_email .= "\n";

    $email_recipient = $umbc_id . "@umbc.edu";
    $email_subject = "Welcome to CMSC 201!";
    $email_headers = "From: UMBC Submit System <no-reply@umbc.edu>" . "\r\n" . 'X-Mailer: PHP/' . phpversion();

    // Used for bouncebacks from intermediate or final hosts - must be an actual email
    $error_host = "-f" . $submit_system_admin . "@umbc.edu";

    $mail_status = mail($email_recipient, $email_subject, $curr_register_email, $email_headers, $error_host);

    if($mail_status === false) {
        return "ERROR: Mail Sending Failed for Campus ID: " . $umbc_id . "!";
    } else {
        if($print_successes === true) {
            return "SUCCESS: Registration e-mail successfully sent to " . $umbc_id . "@umbc.edu!";
        } else {
            return false;
        }
    }
}