<?php



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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/video.js/6.5.0/video.min.js"></script>

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
    .media video{
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
      $server = "localhost";
      $username = "root";
      $password = "";
      $database = "photography_portfolio";

      $conn = mysqli_connect($server, $username, $password, $database);
      if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
      }

      $sql = "SELECT video_data FROM uploaded_videos";

      $result = mysqli_query($conn, $sql);

      if (mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
          $videoData = $row['video_data'];
          $videoMimeType = 'video/mp4';
          $videoBase64 = base64_encode($videoData);

          echo '<div class="media">';
          echo '<video class="video-js" controls width="300" height="250">';
          echo '<source src="data:' . $videoMimeType . ';base64,' . $videoBase64 . '" type="' . $videoMimeType . '">';
          echo '<source src="' . $videoBase64 . '" type="' . $videoMimeType . '">';
          echo 'Your browser does not support the video tag.';
          echo '</video>';

          echo '</div>';
        }
      } else {
        echo "No image found.";
      }
    ?>
    


  </div>
  
     
     
    
</body>
</html>