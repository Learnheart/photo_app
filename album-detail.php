<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check authorization 
if (!isset($_SESSION["user"])) {
  header("Location: login.php");
  exit();
}

// Only accessible for user role
if ($_SESSION["role"] !== "User") {
  header("Location: login.php");
  exit();
}

include_once('database.php');
// Get the user ID from the session
$userId = $_SESSION["user"];
$albumId = isset($_GET['albumId']) ? intval($_GET['albumId']) : 0;


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["newAlbumName"])) {
  // Assuming $_POST["albumId"] and $_POST["newAlbumName"] are set
  $albumId = $_POST["albumId"];
  $newAlbumName = $_POST["newAlbumName"];

  // Update the album name in the database
  $updateSql = "UPDATE album SET albumName = ? WHERE albumId = ?";
  $updateStmt = mysqli_prepare($conn, $updateSql);

  // Bind parameters
  mysqli_stmt_bind_param($updateStmt, "si", $newAlbumName, $albumId);

  // Execute the statement
  mysqli_stmt_execute($updateStmt);

  // Close the statement
  mysqli_stmt_close($updateStmt);

  // Redirect to user-profile.php or wherever appropriate
  $redirectLocation = "album-detail.php?albumId=" . $albumId;
  header("Location: " . $redirectLocation);
  // header("Location: album-detail.php");
  exit();
}

// Check if the form for delete is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  // Assuming you have a form with a delete button
  if (isset($_POST['delete-alb'])) {

    // Now, delete the photo
    $deleteAlbumSql = "DELETE FROM album WHERE albumId = ?";
    $deleteAlbumStmt = mysqli_prepare($conn, $deleteAlbumSql);
    mysqli_stmt_bind_param($deleteAlbumStmt, "i", $albumId);
    mysqli_stmt_execute($deleteAlbumStmt);
    mysqli_stmt_close($deleteAlbumStmt);

    // Redirect to homepage after deleting
    header("Location: user-profile.php");
    exit();
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
  <link rel="stylesheet" href="./css-design/profile.css">
  <link rel="stylesheet" href="./css-design/album-detail.css">
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

      <li>
        <a href="./homepage.php" class="ti-home"></a>
      </li>
      <li>
        <a href="./user-profile.php" class="ti-user"></a>
      </li>
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
      <hr class="hr-nav">
    </nav>
    <main class="album-list">
      <!-- Album name -->
      <ul class="container album-info">
        <li class="alb-name">
          <?php
          $albName = "SELECT albumName from album WHERE userId = ? AND albumId =?";
          $stmt = mysqli_prepare($conn, $albName);
          mysqli_stmt_bind_param($stmt, "ii", $userId, $albumId);
          mysqli_stmt_execute($stmt);

          $res = mysqli_stmt_get_result($stmt);
          // Check if there are rows in the result set
          if ($res && mysqli_num_rows($res) > 0) {
            $row = mysqli_fetch_assoc($res);
            echo $row['albumName'];
            echo '<button onclick="updateAlbum()"><i class="ti ti-pencil"></i></button>';
          } else {
            echo 'Album not found';
          }
          ?>
        </li>
        <!-- Delete album -->
        <li>
          <form action="album-detail.php?albumId=<?= $albumId ?>" method="post" id="delete" class="container">
            <div class="form-group">
              <input type="submit" value="Delete" name="delete-alb" class="btn btn-danger">
            </div>
          </form>
        </li>
        <li class="post-number">
          <?php
          $countPost = "SELECT COUNT(photoId) as totalPosts FROM photo WHERE userId = ? AND album = ?";
          $stmt = mysqli_prepare($conn, $countPost);
          mysqli_stmt_bind_param($stmt, "ii", $userId, $albumId);
          mysqli_stmt_execute($stmt);

          $res = mysqli_stmt_get_result($stmt);
          $row = mysqli_fetch_assoc($res);
          $totalPosts = $row['totalPosts'];
          echo $totalPosts . ' posts';

          mysqli_stmt_close($stmt);
          ?>

        </li>
      </ul>
      <!-- Pop up update album window -->
      <?php
      $albName = "SELECT albumName FROM album WHERE userId = ? AND albumId = ?";
      $stmt = mysqli_prepare($conn, $albName);

      // Check if the preparation of the statement was successful
      if ($stmt) {
        mysqli_stmt_bind_param($stmt, "ii", $userId, $albumId);
        mysqli_stmt_execute($stmt);

        $res = mysqli_stmt_get_result($stmt);

        // Check if the query was successful
        if ($res) {
          // Fetch the result
          $row = mysqli_fetch_assoc($res);

          // Check if the result contains data
          if ($row) {
            // Display the album name
            echo '<div id="albumNameDiv" class="hidden">';
            echo '<form method="post" action="album-detail.php">';
            echo '<input type="hidden" name="albumId" value="' . $albumId . '">';
            echo '<input type="text" name="newAlbumName" value="' . $row['albumName'] . '">';
            echo '<button type="submit">Save</button>';
            echo '</form>';
            echo '</div>';
          } else {
            // Handle the case where no data is found
            echo "Album not found";
          }
        } else {
          // Handle the case where the query was not successful
          echo "Query failed: " . mysqli_error($conn);
        }

        // Close the statement
        mysqli_stmt_close($stmt);
      } else {
        // Handle the case where the statement preparation failed
        echo "Statement preparation failed: " . mysqli_error($conn);
      }
      ?>
      <!-- Album list -->
      <div id="album-detail" class="container">
        <?php
        $userId = $_SESSION['user'];

        $sql = "SELECT DISTINCT p.photoPath, p.photoId FROM photo p JOIN account a ON p.userId = a.userId 
        JOIN album al ON p.userId = al.userId WHERE p.userId = ? AND p.album = ? ORDER BY p.photoId DESC";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ii", $userId, $albumId);
        mysqli_stmt_execute($stmt);

        $res = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($res) > 0) {
          $counter = 0; // Counter to determine when to start a new row
          while ($data = mysqli_fetch_assoc($res)) {
            if ($counter % 3 == 0) {
              // Start a new row for every 3 images
              echo '<div class="row">';
            }
        ?>
        <div class="col mt-3 img-col">
          <a href="./img-description.php?photoId=<?= $data['photoId'] ?>">
            <img src="uploads/<?= $data['photoPath'] ?>" class="img-fluid" alt="Image">
          </a>


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
  </div>

  <script>
  var display = 0;

  function updateAlbum() {
    var updatediv = document.getElementById('albumNameDiv');
    if (display == 1) {
      updatediv.classList.remove('hidden');
      display = 0;
    } else {
      updatediv.classList.add('hidden');
      display = 1
    }
    // Show the album-img section
    // updatediv.classList.remove('hidden');
  }
  </script>
</body>

</html>