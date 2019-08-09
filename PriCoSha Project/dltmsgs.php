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
	if(isset($_POST["person"]))
	{
		echo "in person";
		if($stmt = $mysqli->prepare("delete from pvtmsg where reciever = ? && sender = ?"))
		{
			$stmt->bind_param("ss",$_POST["person"], $_SESSION["username"]);
			$stmt->execute();
			$stmt->close();
			echo $_SESSION["username"] . " msgs sent by you to ".$_POST["person"]." have been deleted successfully<br>";
		}
		echo '<form action = "index.php" method = "get">';
		echo '<br><input type = "submit" value = "Go Back">';
		echo '</form>';
	}
	else
	{
		
		echo "Select a Person whose msgs you wanna delete!!!<br>";
		echo '<form action = "dltmsgs.php" method = "POST">';
			echo '<select name = "person">';
			if ($stmt = $mysqli->prepare("select  username from Person ")) 
		{
			$stmt->execute();
			$stmt->bind_result($person);
			
			while($stmt->fetch()) 
			{
				$person = htmlspecialchars($person);
				echo "<option value='$person'>$person</option>\n";	
			}
			
			echo " here";
			echo '<br><br><input type = "submit" value = "Delete" name = "Delete!"><br><br>';
			echo '</form>';
			
		}
	}
}

?>
</body>
</html> 