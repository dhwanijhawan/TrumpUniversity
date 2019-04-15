<!DOCTYPE html>
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

$result = exec("hi.py");

echo $result;

$query = "SELECT Course.*
          FROM Course, StudentCourse, users 
		  WHERE users.id = 2
			AND users.id = StudentCourse.StudentID
			AND Course.CRN = StudentCourse.CourseID";
		  
		  if ($courseResult = $conn->query($query)) {
				
			  $i = 0;
			  
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
				  
				  $courses[$i] = array($courseCRN,$courseDepartmentId,$courseNumber,$courseName,$courseCredits,
									   $courseStartDate,$courseEndDate,$courseDays,$courseStartTime,$courseEndTime);
				  
				  $i = $i + 1;
			  }
		  } 
		  
		  setcookie('courses',json_encode($courses));
		  //setcookie('courses','hi');
?>

<html lang = "en">
  <head>
    <title>Recommended Courses</title>
    <meta charset = "utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.0/jquery.min.js"></script>
	<script src="jquery-cookie-master/src/jquery.cookie.js" type="text/javascript"></script>
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
	<script>
	function checkErrors()
	{
		var errors = [];
		var noErrors = true;
		var oldCourses = JSON.parse($.cookie('courses'));
		
		//https://stackoverflow.com/questions/16170828/jquery-get-values-of-checked-checkboxes-into-array
		var newCoursesJson = $('input[name="courses[]"]:checked').map(
			function(){
				return $(this).val();
			}).get();
		
		newCoursesJson.forEach(function(newCourseJson) {
			
			var newCourse = JSON.parse(newCourseJson);
			var newCourseDays = newCourse[7].split("");
			var newCourseStartTime = newCourse[8]
			var newCourseEndTime = newCourse[9]
			oldCourses.forEach(function(oldCourse) {
			    var oldCourseDays = oldCourse[7].split("");
			    var oldCourseStartTime = oldCourse[8]
			    var oldCourseEndTime = oldCourse[9]
				
				if(newCourse[0] == oldCourse[0]){
					errors.push("Already registered for " + oldCourse[1] + " " + oldCourse[2]);
				}
				else{
					for(var newCourseDay of newCourseDays){
						
						if(noErrors == true) {
							for(var oldCourseDay of oldCourseDays) {
								if(newCourseDay == oldCourseDay){
									if(((newCourseStartTime <= oldCourseStartTime) && (newCourseEndTime >= oldCourseEndTime)) ||
									   ((newCourseStartTime >= oldCourseStartTime) && (newCourseEndTime <= oldCourseEndTime))) {
										   errors.push("Time conflict: " + newCourse[1] + " " + newCourse[2] +
										   " and " + oldCourse[1] + " " + oldCourse[2]);
										   noErrors = false;
										   break;
									   }
									   
								}
							}
						}
					}
				}
			});
		});
		
		return errors;	
	}
	
	function displayErrors(errors)
	{
		var noErrors = true;
		
		if(errors.length)
		{
			
			alert(errors.join("\n"));
			noErrors = false;
		}
		
		return noErrors;
	}
	
	function courseValidation() {
		var errors = checkErrors();
		
		return displayErrors(errors);	
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
			<ul class="navbar-nav absolute-right">
              <li>
                <a href="logout.php">Logout</a>
              </li>
            </ul>
            
          </div>
        </div>
      </nav>
	</header>
	<div style='margin: 50px'>
	<h1>Recommended Courses</h1>
    <h3>Based on your previous coursework</h3><br>
  
  <?php 
    $query = "SELECT * FROM Department WHERE Id='MATH'";
    if ($result = $conn->query($query)) {
		
	  
      /* fetch associative array */
      while ($row = $result->fetch_assoc()) {
		  
          $departmentName = $row["Name"];
      }
	  
	  if($departmentName=="")
		  echo '<h1> No department selected </h1>';
      else
		  echo '<h1>'.$departmentName.' Courses</h1>';
 
      /* free result set */
      $result->free();
    }
  ?>
	
  <form action="course_registration.php" id="coursesForm" method="POST" onsubmit="return courseValidation()">
  <?php
    $query = "SELECT * FROM Course WHERE DepartmentId='".$_POST['departments']."'";
    if ($result = $conn->query($query)) {
		
	   echo '<table class="table">
	   <tr><th></th><th>Course</th><th>Number</th><th>CRN</th><th>Department</th>
	   <th>Credits</th><th>Start Date</th><th>EndDate</th><th>Days</th><th>Start Time</th><th>End Time</th></tr>';	
	
      /* fetch associative array */
      while ($row = $result->fetch_assoc()) {
			  
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
				  
		  $courseOption = array($courseCRN,$courseDepartmentId,$courseNumber,$courseName,$courseCredits,$courseStartDate,
										   $courseEndDate,$courseDays,$courseStartTime,$courseEndTime);
				  
		   echo "<tr><td><input type='checkbox' value='".json_encode($courseOption)."' name='courses[]' onchange='checkIfChecked()'></td>
		             <td>".$courseOption[3]."</td><td>".$courseOption[2]."</td><td>".$courseOption[0]."</td>
		             <td>".$courseOption[1]."</td><td>".$courseOption[4]."</td><td>".$courseOption[5]."</td>
					 <td>".$courseOption[6]."</td><td>".$courseOption[7]."</td><td>".$courseOption[8]."</td>
					 <td>".$courseOption[9]."</td>
				</tr>";
      }
	  
	  echo '</table>';
 
      /* free result set */
      $result->free();
    }
  ?>
  <br>
  <input id="submit" type="submit" value="Add Courses" disabled></input>
  <input name="user" type="hidden" value="brendan"></input>
  </form>
  </div>
  </body>
</html>