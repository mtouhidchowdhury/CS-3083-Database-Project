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
	if(isset($_POST["gname"]))
	{
		echo 'posting...';
			
		//$id=0;
		if($stmt = $mysqli->prepare("select max(id) from content where username = ?"))
			{
				$stmt->bind_param("s", $_SESSION["username"]);
				$stmt->execute();
				$stmt->bind_result($id);
				$stmt->fetch();
				$stmt->close();
			}  
		if($stmt = $mysqli->prepare("INSERT INTO share (id, group_name, username ) values (?, ?, ?)"))
			{
				$stmt->bind_param("iss", $id, $_POST["gname"], $_SESSION["username"]);
				$stmt->execute();
				$stmt->close();
				echo "Posteddd!!!<br>";
			}
		//return;
	}
	if(isset($_POST["bool"]) && isset($_POST["title"]) )
	{
		if(isset($_FILES["fileToUpload"]["tmp_name"]))
		{
			$src = $_FILES["fileToUpload"]["tmp_name"];
			$dest = 'uploads/'.$_FILES["fileToUpload"]["name"];
			move_uploaded_file($src, $dest);
		}
		$isPublic=1;
		if($_POST["bool"]=="Private")
			$isPublic=0;
		$timestamp = date("Y-m-d H:i:s");
		
		//'2009-04-30 10:09:00'
		
		if($stmt = $mysqli->prepare("INSERT INTO content(username, timest, file_path, content_name, public) values (?, '$timestamp', ?, ?, ?)"))
		{
			$stmt->bind_param("sssi", $_SESSION["username"], $_FILES["fileToUpload"]["name"], $_POST["title"], $isPublic);
			$stmt->execute();
			$stmt->close();
			//echo "here2";
		}
		
		if($isPublic==1)
		{
			echo("content uploaded!!!<br>");
			echo '<form action = "content.php" method = "get">';
			echo '<br><input type = "submit" value = "Upload Another">';
			echo '</form>';
		}
		
		if(($_POST["bool"] =="Private"))
			{
				
			
				echo "Please Choose a group to share the Pictue in!";
				echo '<form action = "content.php" method = "POST">';
				echo '<select name = "gname">';
				if ($stmt = $mysqli->prepare("select distinct(member.group_name), username_creator from member 
							where member.username=?")) 
					{
						$stuff = $_SESSION["username"];
						$stmt->bind_param("s", $stuff);
						$stmt->execute();
						$groups = $stmt->get_result();
										
						$stmt->close();
								
						while($gname = $groups->fetch_array())
						{
							echo "<option value='$gname'>$gname[0] by $gname[1]</option>\n";
						}
						
					}
					echo '<br><br><input type = "submit" value = "Post" name = "submit"><br><br>';
					echo '</form>';
			}

	}
	else
	{
		
		echo "<p>Hello ".$_SESSION["username"].'<p><br>';
		echo "Please upload some content.<br>";
		echo '<form action = "content.php" method = "POST" enctype="multipart/form-data">';
		//$target_dir = "uploads/";
		
		echo "Enter Title<br>";
		echo '<input type="text" name="title" /><br>';
		echo "Select image to upload(optional)<br>";
		echo '<input type = "file" name = "fileToUpload" id = "fileToUpload" accept="image/jpg,image/x-png,image/gif,image/jpeg" ><br>';
		echo "Is it Public or Private? Please Choose!!<br>";
		echo '<select name = "bool">';
		echo "<option value='Public'>Public</option>\n";	
		echo "<option value='Private'>Private</option>\n";	
		echo '<br><br><input type = "submit" value = "Post" name = "submit"><br><br>';
		echo '</form>';
	}
	echo '<form action = "index.php" method = "get">';
	echo '<br><input type = "submit" value = "Back to Home">';
	echo '</form>';
}



?>
</body>
</html?