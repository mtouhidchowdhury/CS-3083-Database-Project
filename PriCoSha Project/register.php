<html>
<body>
<?php
function echoForm(){
	echo '<form action = "register.php" method = "POST">';
	echo "Please fill out ALL fields.<br>";
	echo "Username:";
	echo '<input type = "text" name = "username"><br>';
	echo "Password:";
	echo '<input type = "password" name = "password"><br>';
	echo "First name:";
	echo '<input type = "text" name = "fname"><br>';
	echo "Last Name:";
	echo '<input type = "text" name = "lname"><br>';
	echo '<input type = "submit" value = "Register">';
	echo '</form>';
}

if(isset($_POST["username"]) && isset($_POST["password"]) && isset($_POST["fname"]) && isset($_POST["lname"])) 
{
	$uname = $_POST["username"];
	$password = $_POST["password"];
	$fname = $_POST["fname"];
	$lname = $_POST["lname"];
	$mysqli = new mysqli("localhost", "root", "", "pricoshare");
	$stmt = $mysqli->prepare("SELECT * from person WHERE username = ?");
	if($stmt)
	{
		$stmt->bind_param('s', $uname);
		$stmt->execute();
		$stmt->store_result();
		$num_of_rows = $stmt->num_rows;
		$stmt->close();
		if($num_of_rows > 0)
		{
			echo "I'm sorry, but that username is taken.  Please try again.";
			$stmt->close();
			echoForm();
		}
		else
		{
			$stmt = $mysqli->prepare("INSERT INTO person values (?, ?, ?, ?)");
			//if($stmt)
			//{
				$md5password = md5($password);
				$stmt->bind_param('ssss', $uname, $md5password, $fname, $lname);
				$stmt->execute();
				$stmt->close();
				echo "Thank you for registering " . $fname;
				//$_SESSION["username"] = $uname;
				//$_SESSION["password"] = $password;
				//$_SESSION["fname"] = $fname;
				//$_SESSION["lname"] = $lname;
				echo '<form action = "index.php" method = "get">';
				echo '<br><input type = "submit" value = "Proceed">';
				echo '</form>';  
			//}
		}
	}
}
else
	echoForm();

?>
</body>
</html>


