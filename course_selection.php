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
?>

<html lang = "en">
  <head>
    <title>Select Courses</title>
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
  <body style = "font-size: 1.7em; margin: 50px">
    <form id="departmentsForm" method="POST" onchange="submit()">
	  <select id="departments" name="departments">
	  <?php $query = "SELECT * FROM Department";
		if ($result = $conn->query($query)) {
	 
	      echo '<option value="">-Select department-</option>';
		  
		  /* fetch associative array */
		  while ($row = $result->fetch_assoc()) {
			  $courseId = $row["ID"];
			  $courseName = $row["Name"];
			
			  echo '<option value="'.$courseId.'">'.$courseName.'</option>';
		  }
		  
	 
		  /* free result set */
		  $result->free();
		}
	  ?>
	  </select>
	</form>  
  <br><br>
  
  <h1></h1>
  
  <?php 
    $query = "SELECT * FROM Department WHERE Id='".$_POST['departments']."'";
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
	
  <form action="course_registration.php" id="coursesForm" method="POST">
  <?php
    $query = "SELECT * FROM Course WHERE DepartmentId='".$_POST['departments']."'";
    if ($result = $conn->query($query)) {
		
	  
      /* fetch associative array */
      while ($row = $result->fetch_assoc()) {
			  
          $courseCRN = $row["CRN"];
          $courseName = $row["Name"];
		
		  echo '<input onchange="checkIfChecked()" type="checkbox" name="courses[]" value="'.$courseCRN.'">'.$courseName.'<br>';
      }
 
      /* free result set */
      $result->free();
    }
  ?>
  <br>
  <input id="submit" type="submit" value="Complete Registration" disabled></input>
  </form>
  </body>
</html>

