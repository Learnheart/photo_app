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
  <link rel="stylesheet" href="./css-design/hompage_mobile.css">
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
      <a href="./user-profile.php">
        <li class="ti-user"></li>
      </a>
      <li class="ti-bookmark"></li>
      <hr class="hr-sidebar">
      <li class="ti-settings"></li>
    </ul>
  </div>
  <!-- Webpage content -->
  <div id="homepage">

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
        <a href="./upload-img.php" class="export-icon">
          <i class="ti-export"></i>
        </a>
      </div>
    </nav>
    <!-- Categories -->
    <ul class="nav">
      <li class="nav-item">
        <a class="nav-link disabled">Sport</a>
      </li>
      <li class="nav-item">
        <a class="nav-link disabled">Animal</a>
      </li>
      <li class="nav-item">
        <a class="nav-link disabled">Cake</a>
      </li>
      <li class="nav-item">
        <a class="nav-link disabled">Anime</a>
      </li>
      <li class="nav-item">
        <a class="nav-link disabled">Meme</a>
      </li>
      <li class="nav-item">
        <a class="nav-link disabled">Art</a>
      </li>
      <li class="nav-item">
        <a class="nav-link disabled">Anime</a>
      </li>
      <li class="nav-item">
        <a class="nav-link disabled">Meme</a>
      </li>
      <li class="nav-item">
        <a class="nav-link disabled">Anime</a>
      </li>
    </ul>
    <hr class="hr-nav">
    <!-- Page content -->
    <main class="page-content">
      <div class="container">
        <?php
        include('database.php');
        $sql = "SELECT photoPath FROM photo ORDER BY photoId DESC";
        $res = mysqli_query($conn, $sql);

        if (mysqli_num_rows($res) > 0) {
          $counter = 0; // Counter to determine when to start a new row
          while ($images = mysqli_fetch_assoc($res)) {
            if ($counter % 3 == 0) {
              // Start a new row for every 3 images
              echo '<div class="row">';
            }
        ?>
        <div class="col">
          <div class="alb">
            <img src="uploads/<?= $images['photoPath'] ?>" class="img-fluid" alt="Image">
          </div>
          <div class="alb-user">
            <?php
                
                ?>
          </div>
        </div>
        <?php
            if ($counter % 3 == 2) {
              // Close the row after every 3 images
              echo '</div>';
            }
            $counter++;
          }

          // Close the row if there are remaining images
          if ($counter % 3 != 0) {
            echo '</div>';
          }
        }
        ?>
      </div>

    </main>

    <!-- Pagination -->
    <!-- Footer -->
    <!-- Logout button -->
    <div class="container">
      <a href="logout.php" class="btn btn-warning">Logout</a>
    </div>
  </div>

</body>

</html>