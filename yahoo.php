<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>Stock Tracker</title>
	<!-- jQuery -->
	<?php include('includes/css.php'); ?>
		
	<?php include('includes/js.php'); ?>
</head>
<body>


<?php
	include('includes/connection.php');
	 include(D_TEMPLATE.'/navigation.php');
	
	$sql = "SELECT date, close FROM YHOO";
	$result = mysqli_query($dbConnection,$sql);
	
	if(!$result){
		
		die('Error Fetching Data from Database');
	}
	
	$entry = null;
	//fetch data
	while ($row = mysqli_fetch_array($result)) {
		$entry .= "['".$row{'date'}."',".$row{'close'}."],";
	}
?>
<div id="chart_div" style="width: 100%; height: 500px;"></div>

<script type="text/javascript" src="https://www.google.com/jsapi"></script>
<script type="text/javascript">
    google.load("visualization", "1", {packages:["corechart"]});
    google.setOnLoadCallback(drawChart);
    function drawChart() {
        var data = google.visualization.arrayToDataTable([
        ['Close',	'Date'],
        <?php echo $entry ?>
    ]);
        var options = {
            title: 'Yahoo Stock Tracker  based on closing price from July 14th to July 29th',
			hAxis: {
				textStyle: {
					color: '#01579b',
					fontSize: 20,
					fontName: 'Arial',
					bold: true,
					italic: true
				}
			},
			vAxis: {
				title: 'Closing Price',
				textStyle: {
					color: '#1a237e',
					fontSize: 24,
					bold: true
				}
			},
            curveType: 'function',
            legend: { position: 'bottom' }
        };
        var chart = new google.visualization.LineChart(document.getElementById('chart_div'));
        chart.draw(data, options);
    }
</script>
</body>
</html>
