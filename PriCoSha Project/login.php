<html>
<body>

<?php
require_once('common.php');
session_start();
if(isset($_SESSION["username"]) && isset($_SESSION["nonce"])) 
{
	echo "You are already logged in. \n" . $_SESSION["username"];
	header("refresh: 1; index.php");
	return;
}
else 
{
	if(isset($_POST["username"]) && isset($_POST["password"])) 
	{
		$username = $_POST["username"];
		$password = $_POST["password"]; 	
		$password_md5 = md5($password);	
		
		// Check username and password from database
		$stmt = $mysqli->prepare("select username, password from person where username = ? and password = ?");
		if ($stmt)
		{
			$stmt->bind_param("ss", $username, $password_md5);
			$stmt->execute();
			$stmt->store_result();
			$num_of_rows = $stmt->num_rows;
			if($num_of_rows > 0)
			{
				$_SESSION["username"] = $username;
				$_SESSION["password"] = $password;
				$_SESSION["nonce"] = 'zahidassignment';
				
				echo "Login successful. \n";
				//header('location: /pcs/index.php');
				//echo '<form action = "index.php" method = "get">';
				//echo '<br><input type = "submit" value = "Go to Home">';
				//echo '</form>';
				header("refresh: 1; index.php");
				return;
			}
			else
			{
				 $stmt->close();
				 $mysqli->close();
				 echo "Your username or password is incorrect";
				 header("refresh: 2; login.php");
				 return;
			}
			$stmt->close();
			$mysqli->close();
		}
	}
	else
	{
		echo "Enter your username and password below: <br /><br />\n";
		echo '<form action="login.php" method="POST">';
		echo "\n";
		echo 'Username: <input type="text" name="username" /><br />';
		echo "\n";
		echo 'Password: <input type="password" name="password" /><br />';
		echo "\n";
		echo '<input type="submit" value="Login" />';
		echo '<a href="register.php">Register</a>';
		echo "\n";
		echo '</form>';
		echo "\n";
	}
}
?>

</body>
</html>