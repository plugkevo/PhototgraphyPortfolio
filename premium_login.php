<?php
    session_start();

    $dbHost = "localhost";
    $dbUser = "root";
    $dbPassword = "";
    $dbName = "photography_portfolio";

    $conn = mysqli_connect($dbHost, $dbUser, $dbPassword, $dbName);

    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }

    if (isset($_SESSION['password']) && isset($_SESSION['expire_time']) && time() < $_SESSION['expire_time'] ) {
      // User is already logged in, redirect to the new page
      header("Location: premium_content.php");
      exit();
    }


    if(isset ($_POST['submit'])) {
    // Get the username and password from the form
    
    $password = $_POST["password"];
  
    $sql = "SELECT page_url FROM user_passwords WHERE password = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
      $row = $result->fetch_assoc();
      header("Location: " . $row['page_url']);
    } else {
      header("Location: login_form.php?error=1");
    }
  
    $stmt->close();
    $conn->close();
  }
  ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>PHOTOGRAPHY </title>
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
  
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>

  
<style>
      .navbar {
      height: 80px; /* Adjust the height as needed */
      position: sticky; /* Make the navbar sticky */
      top: 0; /* Stick it to the top of the viewport */
      z-index: 100; /* Ensure it's above other elements */
    }
      
      
    
    .nav{
        padding-top: 15px; /* Adjust the top padding as needed to vertically center the content */
      padding-bottom: 15px; /* Adjust the bottom padding as needed to vertically center the content */
      
    }
    .container-fluid img{
        height: 70px;
        width: 70px;
        align-self: center;
        padding-bottom: 30%;
        padding-top: 1% !important;
    }
    .labels{
        width: 25%;
    }
    .btn.btn-primary{
        background-color: orangered;
        margin-top: 10px;
        
    }  
</style>
</head>
<body>

  <?php
    include('navbar.html');
  ?>
<form method ="POST" action="premium_login.php"> 
  <div class="container">
      <h2>Premium content</h2>
      <h5>Enter the password provided to access premium content</h5>
      <div class="labels">
          <label for="password" >Password</label>
          <input type="password" name="password" class="form-control" id="password" required placeholder="Enter password....">
      </div>
      <button class="btn btn-primary" type="submit" name="submit">
          ENTER
    </button>
    
  </div>
  </form>  


</body>
</html>
