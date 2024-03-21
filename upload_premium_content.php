<?php
$server = "localhost";
$username = "root";
$password = "";
$database = "photography_portfolio";

$conn = mysqli_connect($server, $username, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_POST['submit1'])) {
    // Handle file upload
    $image = $_FILES['image1']['tmp_name'];
    $imageData = file_get_contents($image);

    // Prepare and execute an SQL query to insert the image into the database
    $stmt = $conn->prepare("INSERT INTO premium_content (image_data) VALUES (?)");
    $stmt->bind_param("s", $imageData);

    if ($stmt->execute()) {
        echo "Image uploaded successfully!";
    } else {
        echo "Error uploading image: " . $stmt->error;
    }

    $stmt->close();
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
  
    .row{
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .media{
      text-align: center;
      margin: 0;
      padding: 25px 10px ;
      border-radius: 5px;   
      background-color: transparent;
      transition: transform 0.5s, background 0.5s;
      height: 310px;
      width: 300px;  
      box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.5);   
      display: flex;
      justify-content: center;
      align-items: center; 
      flex-direction: column;
    }
    .media img{
      height: 250px;
      width: 300px;          
    }
    .media  a{
      color: black;
    }
 
  </style>
</head>
<body>
  
    <?php
      include('navbar.html')
    ?>
  <form action="upload_premium_content.php" method="POST" enctype="multipart/form-data">
        <div class="row">
            <div class="media">
                <i class="fa-solid fa-upload fa-2x"></i>
                <p>Upload photos here</p>
                <input type="file" name="image1" accept="image/*" required>
                <button type="submit" name="submit1">Upload</button>
                
            </div> 
        </div>
    </form>
     
</body>
</html>