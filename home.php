<?php
require_once "config.php";
 session_start();
 $loggenOnUser = $_SESSION["username"];
 $resultset = "";
 $dept = '';
 $dept = "cosc";
 
 $sql = "Select CRN, CourseName,Rating,studentcourse2.DepartmentId as dept from studentcourse2, course where StudentId = '$loggenOnUser' and 
 studentcourse2.DepartmentId = course.DepartmentId order BY Rating DESC LIMIT 3 ";
 
 
 if ($result = mysqli_query($link, $sql)) {
    $count = 0;
    while ($row = mysqli_fetch_assoc($result)) {
        
        $dept = $row["dept"];
        if($count == 0){$resultset = $row["CourseName"];
          $count ++;
        }
        if($count == 1){$course2 = $row["CourseName"];
          $count += 1;
        }
        if($count == 2){$course3 = $row["CourseName"];}
        
    }

 }
 

 ?>
<!doctype html>
<html lang="en">
  <head>
    <title>Trumps University</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <link href="https://fonts.googleapis.com/css?family=Rubik:300,400,500" rel="stylesheet">

    <link rel="stylesheet" href="css/bootstrap.css">
    <link rel="stylesheet" href="css/animate.css">
    <link rel="stylesheet" href="css/owl.carousel.min.css">

    <link rel="stylesheet" href="fonts/ionicons/css/ionicons.min.css">
    <link rel="stylesheet" href="fonts/fontawesome/css/font-awesome.min.css">
    <link rel="stylesheet" href="fonts/flaticon/font/flaticon.css">
    <link rel="stylesheet" href="css/magnific-popup.css">

    <!-- Theme Style -->
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
                <a class="nav-link" href="course_registration.php">Your Courses</a>
              </li>
			  <li class="nav-item">
                <a class="nav-link" href="course_selection.php">Add Courses</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="course_recommendation.php">Recommended Courses</a>
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
    <!-- END header -->

    <section class="site-hero overlay" data-stellar-background-ratio="0.5" style="background-image: url(images/big_image_2.jpg);">
      <div class="container">
        <div class="row align-items-center justify-content-center site-hero-inner">
          <div class="col-md-10">
  
            <div class="mb-5 element-animate">
              <div class="block-17">
                <h2 class="heading text-center mb-4"> Hello! <?php echo $loggenOnUser; ?>  We recommend you Courses that suit you</h2>
               
          </div>
        </div>
      </div>
    </section>
    <!-- END section -->

  

    <section class="site-section pt-3 element-animate">
      <div class="container">
      <h2>The popular courses in <?php echo $dept; ?></h2>
        <div class="row">
          <div class="col-md-6 col-lg-3">
            <div class="media block-6 d-block">
              <div class="icon mb-3"><span class="flaticon-book"></span></div>
              <div class="media-body">
                <h3 class="heading"><?php echo $resultset; ?></h3>
                <p></p>
                <p><a href="#" class="more">Read More <span class="ion-arrow-right-c"></span></a></p>
              </div>
            </div> 
          </div>
          <div class="col-md-6 col-lg-3">
            <div class="media block-6 d-block">
              <div class="icon mb-3"><span class="flaticon-student"></span></div>
              <div class="media-body">
                <h3 class="heading"><?php echo $course2; ?></h3>
                <p></p>
                <p><a href="#" class="more">Read More <span class="ion-arrow-right-c"></span></a></p>
              </div>
            </div> 
          </div>
          
          <div class="col-md-6 col-lg-3">
            <div class="media block-6 d-block">
              <div class="icon mb-3"><span class="flaticon-diploma"></span></div>
              <div class="media-body">
                <h3 class="heading"><?php echo $course3; ?></h3>
                <p></p>
                <p><a href="#" class="more">Read More <span class="ion-arrow-right-c"></span></a></p>
              </div>
            </div> 
          </div>
          
      </div>
    </section>
    <!-- END section -->

    
    <!-- loader -->
    <div id="loader" class="show fullscreen"><svg class="circular" width="48px" height="48px"><circle class="path-bg" cx="24" cy="24" r="22" fill="none" stroke-width="4" stroke="#eeeeee"/><circle class="path" cx="24" cy="24" r="22" fill="none" stroke-width="4" stroke-miterlimit="10" stroke="#f4b214"/></svg></div>

    <script src="js/jquery-3.2.1.min.js"></script>
    <script src="js/jquery-migrate-3.0.0.js"></script>
    <script src="js/popper.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/owl.carousel.min.js"></script>
    <script src="js/jquery.waypoints.min.js"></script>
    <script src="js/jquery.stellar.min.js"></script>
    <script src="js/jquery.animateNumber.min.js"></script>
    
    <script src="js/jquery.magnific-popup.min.js"></script>

    <script src="js/main.js"></script>
  </body>
</html>