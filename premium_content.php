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
      grid-template-columns: repeat(4, minmax(250px, 1fr));
      gap: 30px;
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
    include('navbar.html');
  ?>
    <div class="row">
      <?php
      // Assuming you have a database connection established
      $dbHost = "localhost";
      $dbUser = "root";
      $dbPassword = "";
      $dbName = "photography_portfolio";

      $conn = mysqli_connect($dbHost, $dbUser, $dbPassword, $dbName);

      if (!$conn) {
          die("Connection failed: " . mysqli_connect_error());
      }
      

      // Assuming you have a table named 'images' with a column 'image_data' to store the image
      $sql = "SELECT image_data FROM premium_content";

      $result = mysqli_query($conn, $sql);

      if (mysqli_num_rows($result) > 0) {
          while ($row = mysqli_fetch_assoc($result)) {
              $imageData = $row['image_data'];
              $imageMimeType = 'image/jpeg'; // Set the appropriate image MIME type

              echo '<div class="media">';
              echo '<img src="data:' . $imageMimeType . ';base64,' . base64_encode($imageData) . '" alt="Database Image">';
              echo '<a href="data:' . $imageMimeType . ';base64,' . base64_encode($imageData) . '" download="my-image.jpg">';
              echo '<i class="fas fa-download"></i>';
              echo ' Download</a>';
              echo '</div>';
          }
      } else {
          echo "No image found.";
      }


      mysqli_close($conn);
  ?>
</body>
</html>