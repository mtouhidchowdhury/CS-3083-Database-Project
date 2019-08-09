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
	if(isset($_POST["submit"]))
	{
		if(isset($_POST["yesno"]))
		{
		$pid = $_POST["pid"];
        $tagger = $_POST["tagger"];
        $taggee = $_POST["taggee"];
		if($_POST["yesno"] == "Accept")
		{
          echo "Tag has been accepted .<br>";  
          if($stmt = $mysqli->prepare("update tag set status = 1 where id = ? and username_tagger  = ? and username_taggee  = ?"))
		  {
            $stmt->bind_param("iss", $pid, $tagger, $taggee);
            $stmt->execute();
            $stmt->close();
          }
		  else{
            throw new Exception("The Database is too meesed up!.  Try again later.");
          }
		 
		}
		else if($_POST["yesno"] == "Decline"){
          echo "Tag has been declined.<br>";
          if($stmt = $mysqli->prepare("delete from tag where id = ? and username_tagger = ? and username_taggee = ?")){
            $stmt->bind_param("iss", $pid, $tagger, $taggee);
            $stmt->execute();
            $stmt->close();
          }
          else{
            throw new Exception("The Database is too meesed up!.  Try again later.");
          }
        }
		echo '<form action = "index.php" method = "get">';
		echo '<input type = "submit" name = "submit" value = "BAck to Home">';
        echo '</form><br>';
		}
	}
	else
	{
	echo 'Below you can view tags for approval:<br />';
	echo "<br>";
	$status =0;
	$username = $_SESSION["username"];
	if ($stmt = $mysqli->prepare("select tag.id , tag.timest , content_name , username_tagger,file_path   
								  from tag JOIN content ON (content.id = tag.id)
								  where username_taggee  = ? and status = ? order by timest desc"))
								  {
        $stmt->bind_param("si", $username,$status);
        // execute query
        $stmt->execute();
        $stmt->bind_result($id, $time, $caption, $tagger, $photo);
		echo '<form action = "index.php" method = "get">';
        echo '<br><input type = "submit" value = "Go Back">';
		echo '</form>';
		while($stmt->fetch()){
        echo $time . ": " . $tagger . " has tagged you in " .$caption ."<br>";
		echo "<img src=/pcs/pcs/uploads/$photo height=500 width=500 />.<br>";
		echo "Would you like to accept or decline this tag?<br>";
		echo '<form action = "viewtags.php" method = "post">';
        echo '<input type = "hidden" name = "pid" value = "' . $id . '">';
        echo '<input type = "hidden" name = "tagger" value = "' . $tagger . '">';
        echo '<input type = "hidden" name = "taggee" value = "' . $username . '">';
        echo 'Accept<input type = "radio" name = "yesno" value = "Accept">&nbsp&nbsp';
        echo 'Decline<input type = "radio" name = "yesno" value = "Decline"><br>';
        echo '<input type = "submit" name = "submit" value = "Submit">';
        echo '</form><br>';
		}
	}
	}
	
}
?>
</body>
</html>