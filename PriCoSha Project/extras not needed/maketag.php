<html>
<head>
</head>
<body>
<?php
include "connect.php";
include "session.php";
if(!isset($_SESSION["username"])){
	header("location: login.php");
	echo "<a href=\"login.php\">You Must Login</a><br \>";
}
else
{
	
	if(isset($_POST["pic"]) && isset($_POST["tagee"]))
	{
		
		$id = $_POST["pic"];
		$tagger = $_SESSION["username"];
		$taggee = $_POST["tagee"];
		$timestamp = date("Y-m-d H:i:s");
		
		if($tagger ==$taggee)
		{
		if($stmt = $mysqli->prepare("insert into tag (id, username_tagger , username_taggee , timest , status)
									 values (?, ?, ?,'$timestamp' , ?)"))
									 {
										 $status=1;
				$stmt->bind_param("issi", $id,$tagger, $taggee,$status);
				$stmt->execute();
				$stmt->close();
				echo "You have successfully tagged yourself to photo" .$id."<br>\n";
			}	
		
		}
		else
		{
			if($stmt = $mysqli->prepare("select public from content where id =?"))
			{
				$stmt->bind_param("i",$id);
				$stmt->execute();
				$stmt->bind_result($is_pub);
				$stmt->fetch();
				$stmt->close();
			}
			
			$count = 0;
			if($stmt = $mysqli->prepare("select count(distinct name)
									 from share JOIN member ON (share.group_name = member.group_name)
									 where (id  = ?  and member.username = ?")){
									$stmt->bind_param("is", $id, $taggee);
									$stmt->execute();
									$stmt->bind_result($count);
									$stmt->fetch();
									$stmt->close();
								}
								
			if(($count == 0) && ($is_pub==0))
			{
			echo "Unable to tag $taggee to photo $id. $taggee is not a member of the FriendGroup this photo is in.<br>\n";
			}
			else
			{
				if($stmt = $mysqli->prepare("insert into tag (id, username_tagger , username_taggee, timest, status )
											 values (?, ?, ?, '$timestamp',? )"))
											 {
												 $is_pub=0;
				$stmt->bind_param("issi", $id, $tagger, $taggee, $is_pub);
				$stmt->execute();
				$stmt->close();
				echo "You have successfully tagged $taggee to photo $id.<br>\n";
				}
			}
		}
	}
	
		
		
	
	else
	{
		
	echo "<p>Hello ".$_SESSION["username"].'<p><br>';
	echo "Please choose a picture in which you wanna tag a friend.<br>";
	echo '<form action = "maketag.php" method = "POST">';
	echo '<select name = "pic">';
	if ($stmt = $mysqli->prepare("(select id, username, timest, content_name , public
									from content
								    where username = ? or public = 1) UNION
								   (select id, username, timest, content_name, public
								    from member natural join share natural join content
								    where username = ?) UNION
								   (select id, username, timest, content_name, public
								    from content natural join tag
									where username_taggee = ? and status  = 1) order by timest desc"))
									{
										$stmt->bind_param("sss",  $_SESSION["username"], $_SESSION["username"],$_SESSION["username"]); 
										$stmt->execute();
										echo "<br>";
										$photos = $stmt->get_result();
										
										$stmt->close();
									
										while($photo = $photos->fetch_array())
										{
											
											echo "<option value='$photo[0]'>$photo[3]</option>\n";
									
										}
										echo '<input type = "hidden" name = "id" value = "' . $photo[0]. '">';
										echo '<input type = "hidden" name = "tagger" value = "' . $_SESSION["username"]. '">';
										echo '<input type = "hidden" name = "time" value = "' . $photo[2]. '">';
										echo '<input type = "hidden" name = "public" value = "' . $photo[4]. '">';
										
										
										
		
									}
									
									
	
	echo '<select name = "tagee">';
	if ($stmt = $mysqli->prepare("select  username from Person ")) 
		{
			$stmt->execute();
			$stmt->bind_result($tagee);;
			while($stmt->fetch()) 
			{
				$tagee = htmlspecialchars($tagee);
				echo "<option value='$tagee'>$tagee</option>\n";	
			}
			echo '<br><br><input type = "submit" value = "Post" name = "submit"><br><br>';
			echo '</form>';
			$stmt->close();

			
		
		}
		
	}
	echo '<form action = "index.php" method = "get">';
        echo '<br><input type = "submit" value = "Go Back">';
      echo '</form>'; 
}

?>
</body>
</html>