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

session_start();
if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true){
 $studentUser = $_SESSION["username"];
}
else{
	header("location: login.php");
    exit;
}

$query = "SELECT * FROM users WHERE username='".$studentUser."'";
			  
	if ($userResult = $conn->query($query)) {
		while ($row = $userResult->fetch_assoc()) {
			$studentUserId = $row["id"];
		}
	}

			
$query = "UPDATE StudentCourse
          SET Paid = 1
          WHERE StudentID = ".$studentUserId." AND Paid = 0";
		  
		  if($conn->query($query)){
			  echo "success";
		      setcookie("paid",1);
		  }
		  else echo "fail";
		  
		  
		  
		  header("Location: makePayment.php"); /* Redirect browser */
          exit();
?>