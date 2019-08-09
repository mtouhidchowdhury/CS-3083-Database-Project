<html>
<body>
<?php
include ("connect.php");
include "session.php";
if(!isset($_SESSION["username"]))
{
	header("location: login.php");
	echo "<a href=\"login.php\">You Must Login</a><br \>";
}
else
{
	if(isset($_POST["comment"]))
	{
	
		if(isset($_POST["text"]) && isset($_POST["pic"]))
		{
			
			//var_dump($_POST);
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
	}
	if(isset($_POST["tag"]))
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
				
				if($stmt = $mysqli->prepare("(Select count(?) from Share where group_name in 
			( Select Member.group_name from FriendGroup, Member 
				WHERE Member.group_name = FriendGroup.group_name and 
				Member.username_creator = FriendGroup.username AND
             member.username=?
            ))"))
									{
										$stmt->bind_param("is",$id, $taggee);
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
	}
	
	
		echo "<p>Hello ".$_SESSION["username"].'<p><br>';
		echo "Here's all the info about what's happening arround!.<br>";
		//echo '<select name = "pic">';
		
		if ($stmt = $mysqli->prepare("SELECT distinct(Content.id), username, content.timest, file_path, content_name, public
					FROM Content LEFT JOIN Tag on Content.id = Tag.id
					WHERE username =? or public = 1 or Content.id in 
					(SELECT distinct(id) FROM Share, Member WHERE Share.group_name = Member.group_name 
	 && Member.username = ?)  ORDER BY timest DESC"))
									{
										$stmt->bind_param("ss",  $_SESSION["username"], $_SESSION["username"]); 
										$stmt->execute();
										echo "<br>";
										$photos = $stmt->get_result();
										
										$stmt->close();
									
										while($photo = $photos->fetch_array())
										{
											echo '<form action = "view.php" method = "POST">';
											echo "<table border = '1'>\n";
											echo "<tr>";
											//photo details...
											echo "<img src=/pcs/pcs/uploads/$photo[3] height=500 width=500 />.<br>";
											echo "<td><b>ID</td><td><b>Posted By</td><td><b>Date/Time</td><td><b>Caption</td>";
											echo "</tr>\n";
											echo "<td>$photo[0]</td><td>$photo[1]</td><td>$photo[2]</td><td>$photo[4]</td>";
											echo "</tr>\n";
											echo "</table>\n";
											//comment box
											echo "<textarea name= 'text' cols=50 rows=2> </textarea><br />";
											echo '<input type = "hidden" name = "pic" value = "' . $photo[0]. '">';
											//echo '<input type = "hidden" name = "user" value = "' . $_SESSION["username"]. '<br>';
											echo '<input type = "submit" name="comment" value = "Post">';
											echo "</form>\n";
											//tag a person
											echo "tag: ";
											echo '<form action = "view.php" method = "POST">';
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
												}
											echo '<input type = "hidden" name = "pic" value = "' . $photo[0]. '">';
											echo '<input type = "submit" name= "tag" value = "Tag">';
											echo "</form>\n";
												
											if($stmt2 = $mysqli->prepare("select username_tagger , username_taggee from tag where id=? and status =true"))
											{
												$stmt2->bind_param("i", $photo[0]);
												$stmt2->execute();
												$stmt2->bind_result($tagger, $taggee);
												
												echo "<table border = '1'>\n";
												while($stmt2->fetch())
												{
													echo "<tr>";
													echo "<td>".$tagger." tagged: ".$taggee."</td>";
													echo "</tr>\n";
												}
												echo "</table>";
												$stmt2->close();
											}
											
											//echo "<br>";
											if($stmt3 = $mysqli->prepare("select username , comment_text from comment where id =?"))
											{
												$stmt3->bind_param("i", $photo[0]);
												$stmt3->execute();
												$stmt3->bind_result($username, $ctext);
												echo "<table border = '1'>\n";
												echo "Comments:<br>";
												echo "<tr>";
												//echo "<td><b>Name:</td><td><b>Comment:</td>";
												echo "</tr>";
												while($stmt3->fetch())
												{
													echo "<tr>";
													echo "<td>".$username."</td><td>".$ctext."</td>";
													echo "</tr>\n";
												}
												echo "</table>";
												$stmt3->close();
											}
											echo "<br>";
										}
									}
									
									
									

									echo '<form action = "index.php" method = "get">';
									echo '<br><input type = "submit" value = "Go Back">';
									echo '</form>';	
									
}
									

										
										
?>
</body>
</html>