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
	if(isset($_POST["sender"]) && isset($_POST["message"])) 
		{
			if($stmt = $mysqli->prepare("INSERT INTO pvtmsg (sender, reciever, mess, mdate) values (?, ?, ?, NOW())"))
			{
            $stmt->bind_param("sss", $_SESSION["username"], $_POST["sender"], $_POST["message"]);
            $stmt->execute();
            $stmt->close();
			echo "Msg Sent!!";
			}
			echo '</form>';
			echo '<form action = "index.php" method = "get">';
			echo '<br><input type = "submit" value = "Go Back">';
			echo '</form>'; 
		}	
	else
	{
		
			echo "Who would you like to send this to? Please Choose!!!<br>";
			echo "Type in a short message to send.<br>";
			echo '<form action = "sndmsgs.php" method = "POST">';
			echo '<select name = "sender">';
			if ($stmt = $mysqli->prepare("select  username from Person ")) 
		{
			$stmt->execute();
			$stmt->bind_result($sender);
			
			while($stmt->fetch()) 
			{
				$sender = htmlspecialchars($sender);
				echo "<option value='$sender'>$sender</option>\n";	
			}
			
			echo " here";
			
			echo '<input type = "text" name = "message"><br>';
			echo '<br><br><input type = "submit" value = "Post" name = "Send Message!"><br><br>';
			echo '</form>';
			
		}
	}
}

?>
</body>
</html> 