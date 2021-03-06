<?php
session_start();

// initializing variables
$username = "";
$email = "";
$link = "";
$address = "";
$errors = array();

// connect to the database
$db = mysqli_connect('localhost', 'root', '', 'assignmentdb');

// REGISTER USER
if (isset($_POST['reg_user'])) {

    // receive all input values from the form
    $username = mysqli_real_escape_string($db, $_POST['username']);
    $email = mysqli_real_escape_string($db, $_POST['email']);
    $password_1 = mysqli_real_escape_string($db, $_POST['password_1']);
    $password_2 = mysqli_real_escape_string($db, $_POST['password_2']);
    $userType = mysqli_real_escape_string($db, $_POST['userType']);

 //push errors into errors array and display them at last
    if (empty($username)) {
        array_push($errors, "Username is required");
    }

    if (! preg_match("/^[a-zA-Z-' ]*$/", $username)) {
        array_push($errors, "Only letters and white space allowed in username");
    }
    if (empty($email)) {
        array_push($errors, "Email is required");
    }
    if (empty($password_1)) {
        array_push($errors, "Password is required");
    }
    if ($password_1 != $password_2) {
        array_push($errors, "The two passwords do not match");
    }

    // first check the database to make sure
    // a user does not already exist with the same username and/or email
    $user_check_query = "SELECT * FROM users WHERE username='$username' OR email='$email' LIMIT 1";
    $result = mysqli_query($db, $user_check_query);
    $user = mysqli_fetch_assoc($result);

    if ($user) { // if user exists
        if ($user['username'] === $username) {
            array_push($errors, "Username already exists");
        }

        if ($user['email'] === $email) {
            array_push($errors, "email already exists");
        }
    }

    // Finally, register user if there are no errors in the form
    if (count($errors) == 0) {
        $password = md5($password_1); // encrypt the password before saving in the database

        $query = "INSERT INTO users (username, email, password, userType) 
  			  VALUES('$username', '$email', '$password', '$userType')";
        mysqli_query($db, $query);

        $_SESSION['username'] = $username;

        header('location: index.php');
    }
}
// ...

// LOGIN USER
if (isset($_POST['login_user'])) {
    $username = mysqli_real_escape_string($db, $_POST['username']);
    $password = mysqli_real_escape_string($db, $_POST['password']);

    if (empty($username)) {
        array_push($errors, "Username is required");
    }
    if (empty($password)) {
        array_push($errors, "Password is required");
    }

    if (count($errors) == 0) {
        $password = md5($password);
        $query = "SELECT * FROM users WHERE username='$username' AND password='$password'";
        $results = mysqli_query($db, $query);
        if (mysqli_num_rows($results) == 1) {
            $_SESSION['username'] = $username;

            header('location: index.php');
        } else {
            array_push($errors, "Wrong username/password combination");
        }
    }
}
if (isset($_POST['take_approval'])) {

    $conn = mysqli_connect('localhost', 'root', '', 'assignmentdb');
    $link = mysqli_real_escape_string($conn, $_POST['link']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $str_arr = preg_split("/\,/", $address);
    $arrlength = count($str_arr);
    $x = 0;
    while ($x < $arrlength) {   //check validation for all the email addresses
        $email = filter_var($str_arr[$x], FILTER_SANITIZE_EMAIL);   // FILTER_SANITIZE_EMAIL filter removes all illegal characters from an email address.
        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo $email;
            array_push($errors, "Invalid email format");
        }
        $x ++;
    }
    $x = 0;
    if (empty($link)) {
        array_push($errors, "OS link is required");
    }
    if (empty($address)) {
        array_push($errors, "One or more Email Ids of approvers are required");
    }

    $query = "SELECT * FROM approvaltaker WHERE OsLink='$link' AND ApprovalTakerName='{$_SESSION['username']}'";
    $result = $conn->query($query);

    if ($result && $result->num_rows > 0 && (count($errors) == 0)) {          //if alreaddy applied for the same link
        while ($row = $result->fetch_assoc()) {
            $at_id = $row['At_id'];
            header("Location:approvalTakerResponseForAlready.php?takerResponse=$link&at_id=$at_id");
        }
    } else if (count($errors) == 0) {
        $query = "INSERT INTO approvaltaker (ApprovalTakerName, OsLink, ApproverEmailIds, Date) VALUES ('{$_SESSION['username']}', '$link', '$address', CURRENT_TIMESTAMP)";
        mysqli_query($db, $query);
        $at_id = '';
        $date = date("Y-m-d") . " " . date("h:i:sa");
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }
        $systemLink = "http://localhost/registration/login.php";
        $subject = "Request for OS link approval";
        $body = "Request pending for Os link approval for the link : $link \nRequestor name: {$_SESSION['username']} \nRequest time: $date \nLogin to OS link approval system for more details and to approve or reject the request: $systemLink \n\n Thanks,\nAdmin(OS library management system)";

        if (mail($str_arr[0], $subject, $body)) {       //send the mail to first approver email id provided by approvaltaker
            // echo "Email successfully sent to $email...";
        } else {
            array_push($errors, "Email sending failed...");
        }

        while ($x < $arrlength) {
            $sql = "SELECT At_id ,Date FROM approvaltaker where ApprovalTakerName = '{$_SESSION['username']}'";
            $result = $conn->query($sql);
            if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $at_id = $row['At_id'];
                    $date = $row['Date'];
                }
            } else {
                echo "0 results";
            }
            //add entry in approver with response as NULL, We will update respnse when approver gives it 
            $query = "INSERT INTO approver (ApproverEmail, Response, Date, At_id) VALUES ('$str_arr[$x]', NULL,'$date','$at_id')";   
            mysqli_query($conn, $query);
            $x ++;
        }
        ?>


<?php
        if (count($errors) == 0) {
            header("Location:approvalTakerResponse.php");
        }
    }
}

?>