<?php
session_start();
if (! isset($_SESSION['username'])) {
    $_SESSION['msg'] = "You must log in first";
    header('location: login.php');
}

?>
<html>
<head>
<title>Rejection</title>
<link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>
	<div class="header">
		<h2>OS Library approval System</h2>
	</div>
	<form method="post">
		<h2 style="color: green;">
			<span class="blink"> Os approval is Rejected </span>
		</h2>
		</br> </br>

		<button type="submit" class="btn" name="back";>BACK</button>
	</form>
</body>
</html>
<?php
if (isset($_POST['back'])) {
    header('location: approvalTaker.php');
}
$email = '';
$oslink = $_GET['oslink'];
$approvaltakername = $_GET['approvaltakername'];
$conn = mysqli_connect('localhost', 'root', '', 'assignmentdb');
$sql = "SELECT email FROM users where username = '{$_SESSION['username']}'";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $email = $row['email'];
    }
} else {
    echo "0 results";
}

echo "</br>";

$at_id = "";
$sql = "SELECT At_id FROM approver where ApproverEmail ='$email'";
$result = $conn->query($sql);
$at_id_to_update = "";
if ($result && $result->num_rows > 0) {    //check for all the requests for the same approver
    // output data of each row
    while ($row = $result->fetch_assoc()) {
        $at_id = $row['At_id'];

        $sql1 = "SELECT * FROM approvaltaker where At_id ='$at_id' AND ApprovalTakerName ='$approvaltakername' AND OsLink='$oslink'";  
        $result1 = $conn->query($sql1);
        if (mysqli_num_rows($result1) == 1) {
            $at_id_to_update = $at_id;
             break;
        }
    }
}

$response = "Your request for open-source library \"$oslink\" is rejected";
$sql = "UPDATE approver SET Response='$response' where ApproverEmail ='$email' AND At_id ='$at_id_to_update'";

if ($conn->query($sql) === TRUE) {

    $sql = "DELETE FROM approver WHERE At_id ='$at_id' and Response IS NULL";      //to not send approver request to other approvers if previous rejected

    if ($conn->query($sql) === TRUE) {} else {
        echo "Error deleting record: " . $conn->error;
    }
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}
mysqli_query($conn, $sql);

?>