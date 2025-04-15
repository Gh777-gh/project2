<?php
include 'header.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Stroke System</title>
  <style>
    .background {
      position: relative;
      width: 100%;
      height: 100vh;
      background-image: url('assets/uploads/strockimage.jpg');
      background-size: 100% auto; /* Reduce zoom */
      background-position: center 30%; /* Move the image upwards */
      background-repeat: no-repeat;
      background-color: black;
    }

    .overlay-text {
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      text-align: center;
      color: white;
      z-index: 1;
      text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.7);
      padding: 0 20px;
      max-width: 80%;
    }

    .overlay-text h1 {
      font-size: 3rem;
      margin-bottom: 1rem;
      font-weight: 1000;
    }

    .overlay-text p {
      font-size: 1.5rem;
      font-weight: bold;
      line-height: 1.6;
      max-width: 600px;
      margin: 0 auto;
    }
  </style>
</head>
<body>
  <div class="background">
    <div class="overlay-text">
      <h1>Stroke System</h1>
      <p>An advanced system for managing and tracking patient cases, designed to help doctors analyze medical images and share cases to improve diagnosis and treatment..</p>
    </div>
  </div>

  <?php include 'footer.php'; ?>
</body>
</html>

