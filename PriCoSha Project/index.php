<html>
<head>
</head>

<body>
<?php
include "connect.php";
include "session.php";
//include "pics.php";

if(!isset($_SESSION["username"]))
{
	header("location: login.php");
	echo "<a href=\"login.php\">You Must Login</a><br \>";
}
else
{
	echo "<a href=\"logout.php\">logout</a>&nbsp";
	echo "<a href=\"groups.php\">Manage Friends and Groups</a>&nbsp";
	echo "<a href=\"content.php\">Upload Photos</a>&nbsp";
	echo "<a href=\"view.php\">Stalk Around</a>&nbsp";
	//echo "<a href=\"maketag.php\"> Tag friend in a Picture</a>&nbsp";
	echo "<a href=\"sndmsgs.php\">Send Message</a>&nbsp";
	echo "<a href=\"viewmsgs.php\">Check Msgs</a><br/>";
	echo "<a href=\"dltmsgs.php\">Delete Msgs</a><br/>";

	
	
	
	$notcount = 0;
	if($stmt = $mysqli->prepare("SELECT count(*) from tag where username_taggee = ? and status = 0")){
		$stmt->bind_param('s', $_SESSION["username"]);
		$stmt->execute();
		$stmt->bind_result($notcount);
		$stmt->fetch();
		if($notcount == 1){
			echo "<a href=\"viewtags.php\">You have 1 tag awaiting approval</a><br />";
		}
		else if($notcount > 1){
			echo "<a href=\"viewtags.php\">You have " . $notcount . " tags awaiting approval.</a><br />";
		}
		$stmt->close();
	}

}	

?>
</body>
</html>
