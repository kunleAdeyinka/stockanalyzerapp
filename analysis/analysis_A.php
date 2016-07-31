<?php
	ini_set('display_errors', 1); // set to 0 for production version 
	error_reporting(E_ALL);
	//include("../includes/connection.php");

	#whenever a stock goes down in a day is it likely to go up or down
	#should you buy it or not.
	
	#get the ticker symbol from the file
	function masterLoop(){
		$dbConnection = mysqli_connect('localhost', 'root', 'admin')  OR die('Could not connect because: '.mysqli_connect_error());
	
		#check if dcConnection is true or false
		if (mysqli_connect_errno()){
		  echo "Failed to connect to MySQL: " . mysqli_connect_error();
		}
		
		mysqli_select_db($dbConnection, "stock_data");
		
		$mainTickerFile = fopen("../tickerMaster.txt", "r");
		while(!feof($mainTickerFile)){
			$tickerSymbol = fgets($mainTickerFile);
			$tickerSymbol = trim($tickerSymbol);
			
			#number of days the stock goes up
			$daysUp = 0;
			#number of days the stock goes down
			$daysDown = 0;
			#number of days with no change
			$daysSame = 0;
			#total of all the days
			$totalDays = 0;			
			#sum of all the increases
			$sumIncreases = 0;
			#sum of all decreases
			$sumDecreases = 0;
			
			#selec all the days the price has dropped - when the percent_change is less than 0
			$sqlStmt = "SELECT date, percent_change FROM $tickerSymbol WHERE percent_change < '0' ORDER BY date ASC";
			
			#run the query
			$result = mysqli_query($dbConnection, $sqlStmt);
			
			#if query runs successfully then it is true
			if($result){
				while($row = mysqli_fetch_array($result)){
					$date = $row['date'];
					$percent_change = $row['percent_change'];
					#gets the date and percent_change from the follwing date or future date
					$sqlStmt2 = "SELECT date, percent_change FROM $tickerSymbol WHERE date > '$date' ORDER BY date ASC LIMIT 1";
					$result2 = mysqli_query($dbConnection, $sqlStmt2);
					
					$numOfRows = mysqli_num_rows($result2);
					if($numOfRows == 1){
						$row2 = mysqli_fetch_row($result2);
						$nextDay = $row2[0];
						$next_percent_change = $row2[1];
						
						#the price has gone up
						if($next_percent_change > 0){
							$daysUp++;
							$sumIncreases += $next_percent_change;
							$totalDays++;
						}else if($next_percent_change < 0){
							$daysDown++;
							$sumDecreases += $next_percent_change;
							$totalDays++;
						}else{
							$daysSame++;
							$totalDays++;
						}
					}else if($numOfRows == 0){
						//no data after today
					}else{
						echo "You have an error in analysis_A";
					}
				}
			}else{
				echo "unable to select $tickerSymbol <br/>";
			}
			
			
			#pecentage of days it went up
			$nextDayIncreasePercent = 0;
			if($totalDays != 0){
				$nextDayIncreasePercent = ($daysUp / $totalDays) * 100;
			}
			
			#percentage of days it went down
			$nextDayDecreasePercent = 0;
			if($totalDays != 0){
				$nextDayDecreasePercent = ($daysDown / $totalDays) * 100;
			}
			
			$avgIncreasePercent = 0;
			if($daysUp != 0){
				$avgIncreasePercent = $sumIncreases/$daysUp;
			}
			
			
			$avgDecreasePercent = 0;
			if($daysDown != 0){
				$avgDecreasePercent = ($sumDecreases/$daysDown);
			}
			
			insertIntoResultTable($tickerSymbol, $daysUp, $nextDayIncreasePercent, $avgIncreasePercent, $daysDown,  $nextDayDecreasePercent, $avgDecreasePercent, $dbConnection);
			
		}
		
		mysqli_close($dbConnection);
	}
	
	function insertIntoResultTable($tickerSymbol, $daysUp, $nextDayIncreasePercent, $avgIncreasePercent, $daysDown,  $nextDayDecreasePercent, $avgDecreasePercent, $dbConnection){
		
		$buyValue = $nextDayIncreasePercent *  $avgIncreasePercent;
		$sellValue = $nextDayDecreasePercent * $avgDecreasePercent;
		
		$query = "SELECT * FROM analysisa WHERE ticker= '$tickerSymbol' ";
		$result = mysqli_query($dbConnection, $query);
		$numberOfRows = mysqli_num_rows($result);
		
		if($numberOfRows == 1){
			$sqlUpd = "UPDATE analysisa SET ticker = '$tickerSymbol', daysInc='$daysUp', pctOfDaysInc='$nextDayIncreasePercent', avgIncPct='$avgIncreasePercent', daysDec='$daysDown', pctDaysDec='$nextDayDecreasePercent', avgDecPct='$avgDecreasePercent', buyValue= '$buyValue', sellValue= '$sellValue' WHERE ticker ='$tickerSymbol' ";
			mysqli_query($dbConnection,$sqlUpd);
		}else{
			$sqlInst = "INSERT INTO analysisa (ticker, daysInc, pctOfDaysInc, avgIncPct, daysDec,  pctDaysDec, avgDecPct, buyValue, sellValue) VALUES ('$tickerSymbol', '$daysUp', '$nextDayIncreasePercent', '$avgIncreasePercent', '$daysDown', '$nextDayDecreasePercent', '$avgDecreasePercent', '$buyValue', '$sellValue')";
			mysqli_query($dbConnection,$sqlInst);
		}
	}
	
	masterLoop();

?>