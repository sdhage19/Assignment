
<?php 
//include('server.php');
session_start();
if (!isset($_SESSION['username'])) {
    $_SESSION['msg'] = "You must log in first";
    header('location: login.php');
}
?>

<html>
<head>
<title>
approval taker Response
</title>
<link rel="stylesheet" type="text/css" href="style.css">
<style>
form, .content {
  margin-top: 120px;
  background: #FFF5EE;
  width: 90%;

 border: 4px solid #B0C4DE;
  background: white;
}
.email-position{
padding:20px;
}
</style>
</head>
<body>
   <form method="post">
<?php 
include('server.php');
$conn = mysqli_connect('localhost', 'root', '', 'assignmentdb');
    $takerResponse=$_GET['takerResponse'];
    $at_id=$_GET['at_id'];

    $sql1 = "SELECT Response, ApproverEmail FROM approver where At_id=$at_id";
    $result1 = $conn->query($sql1);
    
    if ($result1 && $result1->num_rows > 0) {
        
        // output data of each row
        while($row = $result1->fetch_assoc()) {
            
            $response = $row['Response'];
            if (empty($response)) {
                ?>
            </br>
             <h3 style= color:green; > *Your request for open-source library is pending for approval with </h2><br><h3 style= color:blue><div class="email-position">  <?php echo $row['ApproverEmail']?></div></h3>
        <?php break;}
        else {?>
            </br>
            <h3 style= color:green; >* <?php echo $response?> by </h2><br><h3 style= color:blue>  <?php echo $row['ApproverEmail']?></h3>
            <?php        }
        
    }?>	<div class="buttonposition">

    <div class="input-group">
    <button type="submit" class="btn" name="back";>BACK</button>
    </div>
    </div>
    
<?php }
else{
    header("Location:approvalTaker.php? diff=1");
}
?>
</form>
</body>
</html>
<?php
if (isset($_POST['back'])) {
    header('location: approvalTaker.php');
}



?>