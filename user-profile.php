<?php
session_start();
// Check authorize 
if (!isset($_SESSION["user"])) {
  header("Location: login.php"); //turn back to the login page
}
// Only accessible for user role
if ($_SESSION["role"] !== "User") {
  // Redirect to another page or display an error message
  header("Location: access-denied.php");
  exit();
}

// Get the user ID from the session
$userId = $_SESSION["user"];

// Handle the form submission or UI interaction
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["createAlbum"])) {
  include "database.php"; // Include your database connection file

  // Sanitize and validate the album name (you can add more validation)
  $albumName = mysqli_real_escape_string($conn, $_POST["albumName"]);

  // Insert a new album record
  $sql = "INSERT INTO album (albumName, userId) VALUES (?, ?)";
  $stmt = mysqli_prepare($conn, $sql);

  // Bind parameters
  mysqli_stmt_bind_param($stmt, "si", $albumName, $userId);

  // Execute the statement
  mysqli_stmt_execute($stmt);

  // Close the statement
  mysqli_stmt_close($stmt);

  // Redirect to a page or show a success message
  exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Document</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+jjXkk+Q2h455rYXK/7HAuoJl+0I4" crossorigin="anonymous">
  </script>
  <link rel="stylesheet" href="./css-design/profile.css">
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
  <div id="user-profile">

    <!-- Header -->
    <nav class="navbar">
      <div class="container-fluid">
        <!-- search key -->
        <form class="d-flex" role="search">
          <div class="input-group">
            <input type="text" class="form-control" placeholder="Search keyword" aria-label="Search" aria-describedby="search-icon">
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
        <a href="./upload-img.php" class="export-icon">
          <i class="ti-export"></i>
        </a>
      </div>
    </nav>
    <!-- Adding new album -->
    <form method="post" action="user-profile.php">
      <label for="albumName">Album Name:</label>
      <input type="text" id="albumName" name="albumName" required>
      <button type="submit" name="createAlbum">Create Album</button>
    </form>
    <div class="btn">
      <a href="./homepage.php">Back</a>
    </div>
</body>

</html>