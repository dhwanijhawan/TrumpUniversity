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

			
$query = "UPDATE StudentCourse
          SET Paid = 1
          WHERE StudentID = 2 AND Paid = 0";
		  
		  if($conn->query($query)){
			  echo "success";
		      setcookie("paid",1);
		  }
		  else echo "fail";
		  
		  
		  
		  header("Location: makePayment.php"); /* Redirect browser */
          exit();
?>