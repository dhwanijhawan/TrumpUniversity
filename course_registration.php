<html lang = "en">
  <head>
    <title>Courses</title>
    <meta charset = "utf-8" />
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.0/jquery.min.js"></script>
	<script>
		function checkIfChecked()
		{
			var isChecked = false;
			//alert('hey');
			$('input[name="courses[]"]').each(function() {
				if(this.checked)
				{
					isChecked = true;
				}
			});
			
			if(isChecked)
				$('#submit').prop("disabled",false);
			else
				$('#submit').prop("disabled",true);
		}
	</script>
	<link href="https://fonts.googleapis.com/css?family=Rubik:300,400,500" rel="stylesheet">

    <link rel="stylesheet" href="css/bootstrap.css">
    <link rel="stylesheet" href="css/animate.css">
    <link rel="stylesheet" href="css/owl.carousel.min.css">

    <link rel="stylesheet" href="fonts/ionicons/css/ionicons.min.css">
    <link rel="stylesheet" href="fonts/fontawesome/css/font-awesome.min.css">
    <link rel="stylesheet" href="fonts/flaticon/font/flaticon.css">

  </head>
  <body style = "font-size: 1.7em;">
<?php
$servername = "localhost";
$username = "root";
$password = "mysql";
$database ="TrumpUniversity";

// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$courseCRNs = $_POST['courses'];
$totalCredits = 0;

echo "You have registered for the following courses: <br><br>";

 echo '<table class="table">
	   <tr><th>Course</th><th>Number</th><th>CRN</th><th>Department</th>
	   <th>Credits</th><th>Start Date</th><th>EndDate</th><th>Days</th><th>Start Time</th><th>End Time</th></tr>';

foreach( $courseCRNs as $courseCRN ) {
            $query = "SELECT * FROM Course WHERE CRN='".$courseCRN."'";
			if ($courseResult = $conn->query($query)) {
				
			  /* fetch associative array */
			  while ($row = $courseResult->fetch_assoc()) {
					  
				  $courseCRN = $row["CRN"];
				  $courseDepartmentId = $row["DepartmentId"]; 
				  $courseNumber = $row["Number"];
				  $courseName = $row["Name"];
				  $courseCredits = $row["Credits"];
				  $courseStartDate = $row["StartDate"]; 
				  $courseEndDate = $row["EndDate"];
				  $courseDays = $row["Days"]; 
				  $courseStartTime = $row["StartTime"]; 
				  $courseEndTime = $row["EndTime"]; 
				  
				  $totalCredits = $totalCredits + $courseCredits;
				  
				  echo '<tr><td>'.$courseName.'</td><td>'.$courseNumber.'</td><td>'.$courseCRN.'</td>
				  <td>'.$courseDepartmentId.'</td><td>'.$courseCredits.'</td><td>'.$courseStartDate.'</td><td>'.$courseEndDate.'</td>
				  <td>'.$courseDays.'</td><td>'.$courseStartTime.'</td><td>'.$courseEndTime.'</td></tr>';
				  
				  $query = 'INSERT INTO StudentCourse
                            VALUES (11111,'.$courseCRN.')'; // change 11111 to variable for student id
							
				  $conn->query($query);
			  }
			  
			  //echo '</table>';
		 
			  /* free result set */
			  $courseResult->free();
			}
}

echo '</table><br><br>';
echo '<h3>Tuition: $300/credit</h3>
      <h3>Total Credits: '.$totalCredits.'</h3>
	  <h3>Total due: $'.number_format($totalCredits*300,2).'</h3>';
?>
</br>
<input type="submit" value="Continue to payment">
</body>
</html>