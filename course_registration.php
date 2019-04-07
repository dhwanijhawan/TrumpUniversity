<html lang = "en">
  <head>
    <title>Registration Confirmatio</title>
    <meta charset = "utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.0/jquery.min.js"></script>
	<script>
		function checkIfChecked()
		{
			var isChecked = false;
			//alert('hey');
			$('input[name="deletedStudentCourses[]"]').each(function() {
				if(this.checked)
				{
					isChecked = true;
				}
			});
			
			if(isChecked)
				$('#delete').prop("disabled",false);
			else
				$('#delete').prop("disabled",true);
		}
	</script>
	<link href="https://fonts.googleapis.com/css?family=Rubik:300,400,500" rel="stylesheet">

    <link rel="stylesheet" href="css/bootstrap.css">
    <link rel="stylesheet" href="css/animate.css">
    <link rel="stylesheet" href="css/owl.carousel.min.css">

    <link rel="stylesheet" href="fonts/ionicons/css/ionicons.min.css">
    <link rel="stylesheet" href="fonts/fontawesome/css/font-awesome.min.css">
    <link rel="stylesheet" href="fonts/flaticon/font/flaticon.css">
	<link rel="stylesheet" href="css/magnific-popup.css">
	
	<link rel="stylesheet" href="css/style.css">

  </head>
  <body>
  <header role="banner">
	<nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container">
          <a class="navbar-brand absolute" href="index.html">Trump University</a>
          <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarsExample05" aria-controls="navbarsExample05" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
          </button>

          <div class="collapse navbar-collapse navbar-light" id="navbarsExample05">
            <ul class="navbar-nav mx-auto">
              <li class="nav-item">
                <a class="nav-link" href="index.html">Home</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="course_registration.php">Your Courses</a>
              </li>
			  <li class="nav-item">
                <a class="nav-link" href="course_selection.php">Add Courses</a>
              </li>
              <li class="nav-item">
                <a class="nav-link active" href="course_recommendation.php">Recommended Courses</a>
              </li>
            </ul>
            
          </div>
        </div>
      </nav>
	</header>
<div style = "margin:50px">
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

if(isset($_POST['courses'])){
	$courseCRNs = $_POST['courses'];
	$totalCredits = 0;

	foreach( $courseCRNs as $courseCRNJason ) {
		
		$courseCRN = json_decode($courseCRNJason)[0];
				$query = "SELECT * FROM Course WHERE CRN='".$courseCRN."'";
				if ($courseResult = $conn->query($query)) {
					
				  /* fetch associative array */
				  while ($row = $courseResult->fetch_assoc()) {
					  
					  $query = 'INSERT INTO StudentCourse
								VALUES (2,'.$courseCRN.')'; // change 11111 to variable for student id
								
					  $conn->query($query);
				  }
				  
				  //echo '</table>';
			 
				  /* free result set */
				  $courseResult->free();
				}
	}
}

echo "You have registered for the following courses: <br><br>";

 echo '<form action="delete_student_course.php" method="POST">
		   <table class="table">
		   <tr><th></th><th>Course</th><th>Number</th><th>CRN</th><th>Department</th>
		   <th>Credits</th><th>Start Date</th><th>EndDate</th><th>Days</th><th>Start Time</th><th>End Time</th></tr>';

$query = "SELECT Course.* 
          FROM Course, StudentCourse, users 
		  WHERE users.id = 2
			AND users.id = StudentCourse.StudentID
			AND Course.CRN = StudentCourse.CourseID";
		  
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
				  
				  echo '<tr><td><input type="checkbox" value="'.$courseCRN.'" name="deletedStudentCourses[]" onchange="checkIfChecked()"></td>
				  <td>'.$courseName.'</td><td>'.$courseNumber.'</td><td>'.$courseCRN.'</td>
				  <td>'.$courseDepartmentId.'</td><td>'.$courseCredits.'</td><td>'.$courseStartDate.'</td>
				  <td>'.$courseEndDate.'</td><td>'.$courseDays.'</td><td>'.$courseStartTime.'</td><td>'.$courseEndTime.'</td></tr>';
			  }
		  }

$totalDue = $totalCredits*300;
echo '	</table>
      <input id="delete" type="submit" value="Delete Courses" disabled></input><br><br>
      </form>';
echo '<h3>Tuition: $300/credit</h3>
      <h3>Total Credits: '.$totalCredits.'</h3>
	  <h3>Total due: $'.number_format($totalDue,2).'</h3>';
echo '</br>
      <form action="makePayment.php" method="POST">
      <input id="totalDue" name="totalDue" type="hidden" value="'.$totalDue.'">';
?>

<input type="submit" value="Continue to payment">&nbsp&nbsp
</form>
</div>
</body>
</html>