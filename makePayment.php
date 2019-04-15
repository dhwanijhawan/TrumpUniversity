<!DOCTYPE html>
<html>
<head>
    <title>Registration Confirmatio</title>
    <meta charset = "utf-8" />
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.0/jquery.min.js"></script>
	<script src="jquery-cookie-master/src/jquery.cookie.js" type="text/javascript"></script>
	<link href="https://fonts.googleapis.com/css?family=Rubik:300,400,500" rel="stylesheet">

    <link rel="stylesheet" href="css/bootstrap.css">
    <link rel="stylesheet" href="css/animate.css">
    <link rel="stylesheet" href="css/owl.carousel.min.css">

    <link rel="stylesheet" href="fonts/ionicons/css/ionicons.min.css">
    <link rel="stylesheet" href="fonts/fontawesome/css/font-awesome.min.css">
    <link rel="stylesheet" href="fonts/flaticon/font/flaticon.css">
	
	<link rel="stylesheet" href="css/style.css">
	
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />

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

<?php
	setcookie('totalDue',$_POST["totalDue"]);
?>
<div style="margin:50px">

    <div id="paypal-button-container"></div>

    <!-- Include the PayPal JavaScript SDK -->
    <script src="https://www.paypal.com/sdk/js?client-id=AVFZtdaL4Hv1-qZQuKkM2gWkhPbRF-6OTPm-dCN0M4G5wL7XXBexlWq4iKEz1iyXPBBF7CP7oPWV_IF_&currency=USD"></script>

    <script>
	    var totalDue = JSON.parse($.cookie('totalDue'));
		
        // Render the PayPal button into #paypal-button-container
        paypal.Buttons({

            // Set up the transaction
            createOrder: function(data, actions) {
                return actions.order.create({
                    purchase_units: [{
                        amount: {
                            value: totalDue
                        }
                    }]
                });
            },

            // Finalize the transaction
            onApprove: function(data, actions) {
                return actions.order.capture().then(function(details) {
                    // Show a success message to the buyer
                    alert('Transaction completed by ' + details.payer.name.given_name + '!');
                });
            }


        }).render('#paypal-button-container');
    </script>
	
	<?php if($_COOKIE["paid"] == "1")
			echo "Paid";
		  else
			echo '<form action="submit_payment.php" id="submitPaymentForm" method="POST">
					<input type="submit" value="Submit Payment"></input>
                  </form>';
	?>
</div>
</body>
</html>