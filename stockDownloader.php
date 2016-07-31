<?php
	ini_set('display_errors', 1); // set to 0 for production version 
	error_reporting(E_ALL);
	
	//include('includes/connection.php');
	
	
	function createURL($tickerSymbol){
	
		$currentMonth =date("n"); #returns the current month as a number - 01, 12, 11
		$currentMonth = $currentMonth - 1;
		$currentDay = date("j"); #returns a number between 1 and 31
		$currentDay = $currentDay - 1;
		$currentYear = date("Y"); #retuns a string that is 4 digit year YYYY
		$previousYear = "2016";
		$previousDay = "14";
		
		#echo  "http://chart.finance.yahoo.com/table.csv?s=$tickerSymbol&a=$currentMonth&b=$previousDay&c=$previousYear&d=$currentMonth&e=$currentDay&f=$currentYear&g=d&ignore=.csv";
	
		return "http://chart.finance.yahoo.com/table.csv?s=$tickerSymbol&a=$currentMonth&b=$previousDay&c=$previousYear&d=$currentMonth&e=$currentDay&f=$currentYear&g=d&ignore=.csv";
	}
	
	function getCSVFile($url, $outputFile){
		
		$content = file_get_contents($url);
		$content = str_replace("Date,Open,High,Low,Close,Volume,Adj Close", "", $content);
		$content = trim($content);
		#echo $content;
		file_put_contents($outputFile, $content);
	}

	function saveFileToDatabase($txtFile, $tableName){		
		
		$dbConnection = mysqli_connect('localhost', 'root', 'admin')  OR die('Could not connect because: '.mysqli_connect_error());
		
		#check if dcConnection is true or false
		if (mysqli_connect_errno()){
		  echo "Failed to connect to MySQL: " . mysqli_connect_error();
		}
		mysqli_select_db($dbConnection, "stock_data");
		
		$file = fopen($txtFile, "r");
		while(!feof($file)){
			$line = fgets($file);
			$tokens = explode(",", $line);
			#echo implode("::", $tokens);
			#echo "new line <br>";
			
			$date = $tokens[0];
			#echo $date."<br>";
			$open = $tokens[1];
			#echo $open."<br>";
			$high = $tokens[2];
			$low = $tokens[3];
			$close = $tokens[4];
			$volume = $tokens[5];
			
			
			$amount_change = $close - $open; #the difference in price between opening and closing
			$percent_change = 0;
			#done to handle division by 0
			if($open != 0){
				$percent_change =  100 * ($amount_change/$open); #percentage change in the stock price
			}			
			
			#if table doesn't exist for company then create one
			$sql = "SELECT * FROM $tableName";
			$result = mysqli_query($dbConnection, $sql);
			
			#if there is no result or result is false then there is no table that exists
			if(!$result){
				$sqlCreate = "CREATE TABLE $tableName (date DATE, PRIMARY KEY (date), open FLOAT, high FLOAT, low FLOAT, close FLOAT, volume INT, amount_change FLOAT, percent_change FLOAT)";
				mysqli_query($dbConnection, $sqlCreate);
			}
			
			#insert data into the database
			$sqlInsert = "INSERT INTO $tableName (date, open, high, low, close, volume, amount_change, percent_change) VALUES ('$date', '$open', '$high', '$low', '$close', '$volume', '$amount_change','$percent_change')";
			mysqli_query($dbConnection, $sqlInsert);
		}
		mysqli_close($dbConnection);
		fclose($file);
	}
	
	function main(){
		#need to first open up the tickerMaster file
		$mainTickerFile = fopen("tickerMaster.txt", "r");
		
		
		#read each line and get the ticker symbol
		while(!feof($mainTickerFile)){
			$tickerSymbol = fgets($mainTickerFile);
			$tickerSymbol = trim($tickerSymbol);
			
			$tickerURL = createURL($tickerSymbol);
			#save the file to a location which is a folder on the server
			$companyTxtFile = "textFiles/".$tickerSymbol.".txt";
			getCSVFile($tickerURL, $companyTxtFile);
			saveFileToDatabase($companyTxtFile, $tickerSymbol);
			echo '<br/>';
			echo 'underneath main saveFileToDatabase method';
			echo '<br/>';
			echo '<br/>';
		}
		
	}
	
	//start the main function
	main();
?>