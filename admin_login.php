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

    if (isset($_SESSION['username']) && isset($_SESSION['expire_time']) && time() < $_SESSION['expire_time'] ) {
      // User is already logged in, redirect to the new page
      header("Location: admin_dashboard.php");
      exit();
    }


    if(isset ($_POST['submit'])) {
    // Get the username and password from the form
    $username = $_POST["username"];
    $password = $_POST["password"];
  
    // Check if the username and password match a record in the database
    $sql = "SELECT * FROM admin_login WHERE username='$username' AND password='$password'";
    $result = $conn->query($sql);
  
    if ($result->num_rows == 1) {
      $_SESSION['username'] = $username;
      $_SESSION['expire_time'] = time() + (5 * 60); // Set session expiration time to 20 minutes from now

      // The username and password are correct, so log the user in
    
      header("Location: admin_dashboard.php");
    } else {
      // The username and password are incorrect, so display an error message
      $error = "Invalid username or password";
    }
  }
  

  
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
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
    .container{
        display: flex;
        justify-content: center;
        align-items: center;
        align-content: center;
        margin: auto;
       
    }
    .login{
        height: 400px;
        width: 300px;
        background-color: black;
        align-self: center;
        border-radius: 25px;
        padding-top: 20px;
        display: flex;
        flex-direction: column;
        justify-content: center; /* Center vertically */
        align-items: center; /* Center horizontally */
    }
    .btn.btn-primary {
      background-color: orangered;
      margin-top: 20px; /* Add vertical gap between labels */ 
    }    
  </style>
</head>
<body>
    <?php
      include('navbar.html')
    ?>
  <form method ="POST" action="admin_login.php">
    <div class="container">
        <div class="login">
            <div class="labels">
                <label for="username" class="label" style="color: orangered; ">Username</label>
                <input type="text" class="form-control" name="username" placeholder="Enter username..">
            </div>
            <div class="labels">
                <label for="password" class="label" style="color: orangered;">Password</label>
                <input type="password" class="form-control" name="password" placeholder="Enter password..">
            </div>
            <button class="btn btn-primary" type="submit" name="submit">
              ENTER
            </button>

        </div>
    </div>
  </form>


    
</body>
</html>