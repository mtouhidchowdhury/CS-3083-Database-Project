<html>
<head>
</head>
<body>
<?php
include "connect.php";
include "session.php";
if(!isset($_SESSION["username"]))
{
header("location: login.php");
echo "<a href=\"login.php\">You Must Login</a><br \>";
}
else
{
	$username = $_SESSION["username"];
    if($stmt = $mysqli->prepare("select sender, reciever, mess, mdate from pvtmsg  where reciever = ? order by mdate desc limit 100"))
	{
      $stmt->bind_param('s', $username);
      $stmt->execute();
      $stmt->bind_result($sender, $reciever, $mess, $mdate);
      echo "Here are the last 100 msgs you got:<br>";
      echo '<form action = "index.php" method = "get">';
        echo '<br><input type = "submit" value = "Go Back">';
      echo '</form>'; 
      while($stmt->fetch()){
        echo "Message from " . $sender . " to " . $reciever . "  on " . $mdate . ": <br>";
        echo htmlspecialchars($mess) . "<br><br>";
      }
      $stmt->close();
    }
	
}
