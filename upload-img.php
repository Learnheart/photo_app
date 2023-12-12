<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  include "database.php";
  if (!isset($_SESSION["user"])) {
    header("Location: login.php"); //turn back to the login page
  }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Document</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet"
    integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+jjXkk+Q2h455rYXK/7HAuoJl+0I4" crossorigin="anonymous">
  </script>
  <link rel="stylesheet" href="./css-design/upload.css">
  <link rel="stylesheet" href="./fonts/themify-icons/themify-icons.css">
</head>

<body>
  <!-- Vertical side bar -->
  <div id="side-bar">
    <div class="logo-container">
      <img src="./img/circle.webp" alt="" class="logo">
    </div>
    <div id="space"></div>
    <ul class="icon">

      <li class="ti-home"></li>
      <li class="ti-user"></li>
      <li class="ti-bookmark"></li>
      <hr class="hr-sidebar">
      <li class="ti-settings"></li>
    </ul>
  </div>
  <!-- Webpage content -->
  <div id="upload-file">

    <!-- Header -->
    <nav class="navbar">
      <div class="container-fluid">
        <!-- search key -->
        <form class="d-flex" role="search">
          <div class="input-group">
            <input type="text" class="form-control" placeholder="Search keyword" aria-label="Search"
              aria-describedby="search-icon">
            <button class="input-group-text" id="search-icon" type="submit">
              <i class="ti-search"></i>
            </button>
          </div>
        </form>
        <!-- User information -->
        <h2>Hi,
          <?php
          echo $_SESSION["firstName"] . " " . $_SESSION["lastName"];
          ?>

        </h2>
        <div class="bell-icon">
          <i class="ti-bell"></i>
        </div>
        <div class="save-icon">
          <i class="ti-save"></i>
        </div>
      </div>
    </nav>
    <hr class="hr-nav">
    <!-- Upload file -->
    <!-- File frame -->
    <main id="upload">
      <div class="file">
        <div class="icon">
          <i class="ti-export"></i>
        </div> <br>
        <h5>Choose a file to upload</h5>
        <p>We recommend using high quality file less than 20MB</p>
      </div>

      <!-- File description -->
      <aside class="description">
        <form action="upload-img.php" method="post" id="upload-form">
          <div class="form-group">
            <input type="text" class="form-upload mt-0" name="caption" placeholder="Add a title">
          </div>
          <div class="form-group">
            <input type="text" class="form-upload" name="description" placeholder="Add a detail description">
          </div>
          <div class="form-group">
            <select name="category" id="category" class="form-upload" aria-placeholder="Category" require>
              <?php
              include "database.php";
              if ($conn) {
                $query = "SELECT cateName FROM category";
                $result = mysqli_query($conn, $query);

                while ($row = mysqli_fetch_assoc($result)) {
                  echo "<option value='{$row['cateID']}'>{$row['cateName']}</option>";
                }

                mysqli_close($conn); // Close the database connection
              }

              ?>
            </select>
          </div>
          <div class="form-group">
            <select class="form-control" name="album">
              <option value="album1">Album 1</option>
              <option value="album2">Album 2</option>
              <option value="album3">Album 3</option>
            </select>
          </div>

          <div class="post-img">
            <input type="submit" value="Post" name="post-img" class="btn btn-primary">
          </div>
        </form>
      </aside>
    </main>
    <div class="container">
      <a href="./homepage.php" class="btn btn-warning">Back</a>
    </div>
  </div>

</body>

</html>