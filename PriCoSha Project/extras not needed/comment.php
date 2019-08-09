<html>
<head>
</head>

<body>
<?php

include "session.php";
include "connect.php";
if(!isset($_SESSION["username"])){
	echo "here";
	header("location: login.php");
}
else
{
	if(isset($_POST["text"]) && isset($_POST["pic"]))
	{
		
		var_dump($_POST);
		$id = $_POST["pic"];
		$timestamp = date("Y-m-d H:i:s");
		if($stmt = $mysqli->prepare("INSERT INTO Comment (id, username ,timest , comment_text  ) VALUES (?,?,'$timestamp',?)"))
		{
			
				$stmt->bind_param("iss", $id,$_SESSION["username"],$_POST["text"]);
				$stmt->execute();
				$stmt->close();
				echo "comment added<br>";
		}
	}
	else
	{
	echo "<p>Hello ".$_SESSION["username"].'<p><br>';
	echo "Please choose a picture in which you wanna comment on!.<br>";
	echo '<form action = "comment.php" method = "post">';
	echo '<select name = "pic">';
	if ($stmt = $mysqli->prepare("(select id, username, timest, file_path ,content_name , public
									from content
								    where username = ? or public = 1) UNION
								   (select id, username, timest, file_path , content_name, public
								    from member natural join share natural join content
								    where username = ?) UNION
								   (select id, username, timest, file_path , content_name, public
								    from content natural join tag
									where username_taggee = ? and status  = 1) order by timest desc"))
									{
										$stmt->bind_param("sss",  $_SESSION["username"], $_SESSION["username"],$_SESSION["username"]); 
										$stmt->execute();
										$photos = $stmt->get_result();
										$stmt->close();
										while($photo = $photos->fetch_array())
										{
											echo "<option value='$photo[0]'>$photo[4]</option>\n";
										}
									}
									echo '<input type = "hidden" name = "user" value = "' . $_SESSION["username"]. '">';
									echo "<textarea name= 'text' cols=50 rows=2> </textarea><br />";
									echo "<button type=\"submit\"> Submit! </button>";
									echo "</form>";
	}
								
}



?>

<form action="index.php">
<button type="submit">Home</button></form>
</body>
</html>