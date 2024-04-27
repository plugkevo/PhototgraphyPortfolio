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
    .col-lg-6 {
      position: relative; /* Add this */
      display: flex;
      justify-content: center;
      align-items: center;
    }

    .upload {
      text-align: center; /* Center the content horizontally */
      position: absolute; /* Position the division absolutely within its container */
      top: 50%; /* Move the division 50% from the top of its container */
      left: 50%; /* Move the division 50% from the left of its container */
    }
  </style>
</head>
<body>
    <?php
      include('navbar.html')
    ?>

    
    <div class="container">
      <div class="row">
        <div class="col-lg-6">
          <div class="upload">
            <i class="fa-solid fa-upload fa-8x"></i>
            <p>Upload Photos and Videos</p>
            <a href="upload_photos_genre.php"><h4>Photos</h4></a>
            <a href="upload_photos_genre2.php"><h4>Photos</h4></a>
            <a href="upload_photos_genre3.php"><h4>Photos</h4></a>
            <a href="upload_premium_content.php"><h4>Photos</h4></a>
            <a href="upload_videos.php"><h4>VIdeos</h4></a>
          </div>
        </div>
        <div class="col-lg-6">
        <div class="upload">
            <i class="fa-solid fa-upload fa-8x"></i>
            <p>Upload Photos and Videos</p>
            <a href="upload_photos_folder1.php"><h4>Folder 1</h4></a>
            <a href="upload_photos_folder2.php"><h4>Folder 2</h4></a>
            <a href="upload_photos_folder.php"><h4>Folder 3</h4></a>
            <a href="upload_photos_folder.php"><h4>Folder 4</h4></a>
            <a href="upload_photos_folder.php"><h4>Folder 5</h4></a>
          </div>
        </div>
        </div>
      </div> 
    </div>
    
</body>
</html>