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

$deletedStudentCourses = $_POST['deletedStudentCourses'];

foreach($deletedStudentCourses as $deletedStudentCourse)
{
	echo $deletedStudentCourse;
	$query = "DELETE FROM StudentCourse 
			  WHERE StudentID=2
			  AND CourseID=".$deletedStudentCourse;
			
    $conn->query($query);
}

/*https://stackoverflow.com/questions/2112373/php-page-redirect*/
header("Location: course_registration.php");
exit();

?>