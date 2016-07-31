<?php

	$dbConnection = mysqli_connect('localhost', 'root', 'admin')  OR die('Could not connect because: '.mysqli_connect_error());
	
	#Constants
	DEFINE('D_TEMPLATE', 'template');
	
	#check if dcConnection is true or false
	if (mysqli_connect_errno()){
	  echo "Failed to connect to MySQL: " . mysqli_connect_error();
	}
	
	mysqli_select_db($dbConnection, "stock_data");

?>