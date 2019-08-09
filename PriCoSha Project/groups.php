<html>
<head>
</head>
<body>
<?php

include "connect.php";
include "session.php";
function addFriend($user,$group)
{
	//echo "in addFriend<br>";
	global $mysqli, $_SESSION;
	echo "adding " . $user ." in ". $group.'<br>';
	//check if person is in the group in or not
	if($stmt =$mysqli->prepare("select group_name 
								from Member 
								where username_creator = ?
								&& group_name = ?
								&& username = ?"))
		{
			$stmt->bind_param("sss", $_SESSION["username"], $group, $user);
			$stmt -> execute();
			$stmt->store_result();
			if($stmt ->num_rows >0)
			{
				echo $user. " is already in this group<br>";
				return;
			}
		$stmt->close();	
		}
		
		if($stmt = $mysqli -> prepare("INSERT into member VALUES (?,?,?)"))
			{
				if($stmt->bind_param("sss",$user, $group,$_SESSION["username"]))
				{
				$stmt->execute();
				}
			$stmt->close();
			echo $user. " is added<br>";	
			}
		
			
}

function removeFriend($user, $group)
{
	global $mysqli, $_SESSION;
	echo $user;
	echo $_SESSION["username"];
	echo $group;
	$one=1;
	if($stmt = $mysqli->prepare("select id from tag where ((tag.username_tagger =? and tag.username_taggee =?) or (tag.username_tagger =? and tag.username_taggee =?))
and id in (Select id from Share where group_name in 
			( Select Member.group_name from FriendGroup, Member 
				WHERE Member.group_name = FriendGroup.group_name and 
				Member.username_creator = FriendGroup.username AND
             member.username=?
             AND member.group_name  =?
            )) union (select tag.id from tag Join content on(tag.id=content.id) where ((tag.username_tagger =? and tag.username_taggee =?) or (tag.username_tagger =? and tag.username_taggee =? and public =?)) )"))
	{
		$stmt->bind_param("ssssssssssi", $user, $_SESSION["username"], $_SESSION["username"],$user,$user,$group,$user, $_SESSION["username"], $_SESSION["username"],$user,$one);
		$stmt->execute();
		echo "hereeeeeeeeeee";		
		$ids = $stmt->get_result();				
		$stmt->close();
		var_dump($ids);
		while($id = $ids->fetch_array())
		{
			var_dump($id[0]);
			if($stmt = $mysqli-> prepare("delete from tag where id =? and (tag.username_tagger =? and tag.username_taggee =?) or (tag.username_tagger =? and tag.username_taggee =?)"))
			{
				$stmt->bind_param("issss",$id[0], $user, $_SESSION["username"], $_SESSION["username"],$user);
				$stmt->execute();
				$stmt->close();
			}
		}
		echo "taggs removed<br>";
		
	}
		if($stmt = $mysqli->prepare("delete from member where username_creator = ? and group_name = ? and username = ?"))
		{
			$stmt->bind_param("sss", $_SESSION["username"], $group, $user);
			$stmt->execute();
			$stmt->close();
			echo $user . " has been removed from group " . $group."<br>";
		}
	
	
}


if(!isset($_SESSION["username"]))
{
	header("location: login.php");
	echo "<a href=\"login.php\">You Must Login</a><br \>";
}

else
{
	if( isset($_POST['make']))
	{
		//echo "Creating new group ".$_POST['gname']. '<br>';
		$gname = htmlspecialchars($_POST["grpname"]);
			$desc = htmlspecialchars($_POST["desc"]);
			if($stmt = $mysqli->prepare("SELECT group_name  from friendGroup where group_name  = ? and username  = ?"))
			{
				$stmt->bind_param("ss", $gname, $_SESSION["username"]);   
				$stmt->execute();
				$stmt->bind_result($tmpnm);
				if($stmt->fetch())
				{
					$stmt->close();
					echo "This group already exists!<br>";
				}
				else{
					$stmt->close();
					if($stmt = $mysqli->prepare("INSERT INTO friendGroup values (?, ?, ?)"))
					{
						$stmt->bind_param("sss", $gname, $_SESSION["username"],$desc);
						$stmt->execute();
						$stmt->close();
						echo "Group has been created!<br>";
						addFriend($_SESSION["username"], $gname);
					}
				}
			}
	
		else{
			echo "Invalid fields!";
		}
		
		
	}
	else if( isset($_POST['remove']))
	{
		echo "Removing  group ".$_POST['gname'].'<br>';
		if($stmt = $mysqli->prepare("SELECT first_name , last_name , username
					FROM person NATURAL JOIN (
						SELECT username
						FROM member
						WHERE group_name  = ? and username_creator  = ?
						) as mems"))
						{
							$stmt->bind_param("ss", $_POST["gname"], $_SESSION["username"]);
							$stmt->execute();
							$stmt->bind_result($fname, $lname, $un);
							$ind = 0;
							while($stmt->fetch())
							{
								$mems[$ind] = $un;
								$ind++;
							}
							$stmt->close();
							for($x = 0; $x < count($mems); $x++)
							{
								removeFriend($mems[$x], $_POST["gname"]);
							}
							if($stmt = $mysqli->prepare("delete from share where group_name  = ? and username  = ?"))
							{
								$stmt->bind_param("ss", $_POST["gname"], $_SESSION["username"]);
								$stmt->execute();
								$stmt->close();
							}
							if($stmt = $mysqli->prepare("delete from friendGroup where group_name  = ? and username = ?"))
							{
								$stmt->bind_param("ss", $_POST["gname"], $_SESSION["username"]);
								$stmt->execute();
								$stmt->close();
								echo $_POST["gname"] . " has been removed!<br>";
							}
						}
					echo '<form action = "groups.php" method = "POST">';
					echo '<br><input type = "submit" value = "Back to groups">';
					echo '</form>';	
		
	}
	else if( isset($_POST['addf']))
	{
		echo "Adding Friend in " .$_SESSION["username"]."'s group: " .$_POST['gname'].'<br>';
		$stmt = $mysqli->prepare("SELECT username FROM person WHERE first_name  = ? && last_name  = ?");
		
		if($stmt)
		{
				$stmt->bind_param("ss",$_POST["newfname"], $_POST["newlname"]);
				$stmt->execute();
				$stmt->bind_result($un);
				$stmt->store_result();	
				
				if($stmt->num_rows == 0)
				{
					
					echo "That person doesn't exist.<br />\n";
				}
				else if($stmt->num_rows == 1)
				{
					
					$stmt->fetch();
					addFriend($un, $_POST["gname"]);
				}
				
				else if($stmt->num_rows > 1)
				{
					//too many results
					if(isset($_POST["unt"])){
						addFriend($_POST["unt"], $_POST["gname"]);
					}
					else{
						
						echo '<form action="groups.php" method="POST">';
						echo '<input type="hidden" name="gname" value="'.$_POST["gname"].'">';
						echo '<input type="hidden" name="newfname" value="'.$_POST["newfname"].'">';
						echo '<input type="hidden" name="newlname" value="'.$_POST["newlname"].'">';
						echo "Please Select By Username: <select name=\"unt\">\n";
						while($stmt->fetch())
						{
							$option = htmlspecialchars($un);
							echo "<option value=\"$option\">$option</option>\n";
						}
						echo '</select>';
						echo '<input type = "submit" name = "addf" value = "Add friend"><br><br>';
						echo '</form>';
					}
				}
				$stmt->close();
		echo "Here are all the friends in your group<br>";
		if($stmt = $mysqli->prepare("SELECT first_name , last_name , username 
					FROM person NATURAL JOIN (
						SELECT username
						FROM member
						WHERE group_name = ? and username_creator  = ?
						) as mems"))
						{
							$stmt->bind_param("ss", $_POST["gname"], $_SESSION["username"]);
							$stmt->execute();
							$stmt->bind_result($fname, $lname, $un);
							while($stmt->fetch())
							{
								if($un != $_SESSION["username"])
								{
									echo '<form action="groups.php" method="POST"><br>';
									echo '<input type="hidden" name= "gname" value="'.$_POST["gname"].'">';
									echo '<input type="hidden" name= "runame" value="'.$un.'">';
									echo "$fname $lname (" . $un . ") \n";
									echo '<input type = "submit" name = "removef" value = "Defriend">';
									echo "</form>\n";
									}
							}
						}
			
		}
		
		
		echo '<form action = "groups.php" method = "POST">';
		echo '<br><input type = "submit" value = "Back to groups">';
		echo '</form>';
	}
	else if( isset($_POST['removef']))
	{
		echo "Removing Friend " .$_POST['runame']." from ".$_POST['gname'].'<br>';
		removeFriend($_POST["runame"],$_POST["gname"]);
		echo '<form action = "groups.php" method = "POST">';
		echo '<br><input type = "submit" value = "Back to groups">';
		echo '</form>';
	}
	else
	{
		echo "<p>Hello ".$_SESSION["username"].'!!!<p><br>';
		echo "Please choose a group to manage.";
		echo '<form action = "groups.php" method = "POST">';
		echo '<select name = "gname">';
		if ($stmt = $mysqli->prepare("select distinct group_name from FriendGroup where username =?")) 
		{
			$stuff = $_SESSION["username"];
			$stmt->bind_param("s", $stuff);
			$stmt->execute();
			$stmt->bind_result($gname);
			$count = 0;
			while($stmt->fetch()) 
			{
				$gname = htmlspecialchars($gname);
				$count++;
				echo "<option value='$gname'>$gname</option>\n";	
			}
			$stmt->close();
			echo '</select>';
			if($count > 0 )
			{
				echo '<input type = "submit" name = "remove" value = "Remove this group"><br>';
				echo "<p><br> Add Friend to this Group!!!<br>";
				echo "First name<br>";
				echo '<input type="text" name="newfname" /><br>';
				echo "last name<br>";
				echo '<input type="text" name="newlname" />';
				echo '<br><input type = "submit" name = "addf" value = "Add Friend"><br>';
				echo '<br><input type = "submit" name = "removef" value = "Remove Friend">';
				echo '<input type="text" name="runame" ><br>';
			}
			
			echo '<br>Group Name: <input type="text" name="grpname" /><br />';
			echo 'Description: <input type = "text" name = "desc"><br>';
			echo '<input type = "submit" name = "make" value = "Create new group"><br>';
			
			echo '</form>';
		}
	}
	echo '<form action = "index.php" method = "get">';
	echo '<br><input type = "submit" value = "Go Back">';
	echo '</form>';	
}
?>
</body>
</html?
