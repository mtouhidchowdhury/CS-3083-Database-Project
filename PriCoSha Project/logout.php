<html>
<body>
<?php
session_start();
session_destroy();
echo "You are logged out successfully";
header("refresh: 1; login.php");
?>
</body>
</html>


