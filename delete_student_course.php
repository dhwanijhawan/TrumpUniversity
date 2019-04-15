<?php
$servername = "localhost";
$username = "root";
$password = "mysql";
$database ="TrumpUniversity";

session_start();
if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true){
 $studentUser = $_SESSION["username"];
}
else{
	header("location: login.php");
    exit;
}

// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$query = "SELECT * FROM users WHERE username='".$studentUser."'";
			  
	if ($userResult = $conn->query($query)) {
		while ($row = $userResult->fetch_assoc()) {
			$studentUserId = $row["id"];
		}
	}

$deletedStudentCourses = $_POST['deletedStudentCourses'];

foreach($deletedStudentCourses as $deletedStudentCourse)
{
	echo $deletedStudentCourse;
	$query = "DELETE FROM StudentCourse 
			  WHERE StudentID=".$studentUserId."
			  AND CourseID=".$deletedStudentCourse;
			
    $conn->query($query);
}

/*https://stackoverflow.com/questions/2112373/php-page-redirect*/
header("Location: course_registration.php");
exit();

?>