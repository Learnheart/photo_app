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
  <link rel="stylesheet" href="./css-design/hompage.css">
  <link rel="stylesheet" href="./fonts/themify-icons/themify-icons.css">
</head>

<body>
  <!-- Vertical side bar -->
  <div id="side-bar">
    <div class="logo-container">
      <img src="./img/circle.webp" alt="" class="logo">
    </div>
    <div class="icon">
      <i class="ti-home"></i>
    </div>
    <div class="icon">
      <i class="ti-user"></i>
    </div>
    <div class="icon">
      <i class="ti-bookmark"></i>
    </div>
    <div class="icon">
      <i class="ti-settings"></i>
    </div>
  </div>
  <!-- Webpage content -->
  <div id="homepage">

    <!-- Header -->
    <nav class="navbar bg-light">
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
        <h2>Hi, Guest</h2>
        <div class="bell-icon">
          <i class="ti-bell"></i>
        </div>
        <div class="export-icon">
          <i class="ti-export"></i>
        </div>


      </div>
    </nav>
    <!-- Information data -->
    <!-- Categories -->
    <!-- Page content -->
    <!-- Pagination -->
    <!-- Footer -->
    <!-- Logout button -->
    <div class="container">
      <a href="logout.php" class="btn btn-warning">Logout</a>
    </div>
  </div>

</body>

</html>