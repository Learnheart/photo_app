<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  include "database.php";
  if (!isset($_SESSION["user"])) {
    header("Location: login.php"); // Redirect to the login page
    exit();
  }

  // Ensure $_SESSION['user'] is properly sanitized to prevent SQL injection
  $sessionId = mysqli_real_escape_string($conn, $_SESSION['user']);
  $album = isset($_POST['album']) ? $_POST['album'] : null;
  // Use a prepared statement to fetch the userId
  $sql = "SELECT userId FROM account WHERE userId = ?";
  $stmt = mysqli_prepare($conn, $sql);

  // Bind parameters
  mysqli_stmt_bind_param($stmt, "s", $sessionId);

  // Execute the statement
  mysqli_stmt_execute($stmt);

  // Bind the result variable
  mysqli_stmt_bind_result($stmt, $userId);

  // Fetch the result
  mysqli_stmt_fetch($stmt);

  // Close the statement
  mysqli_stmt_close($stmt);

  // Debugging output
  echo "userId: " . $userId . "<br>";


  // Retrieve other form data
  $caption = isset($_POST['caption']) ? $_POST['caption'] : '';
  $description = isset($_POST['description']) ? $_POST['description'] : '';
  $category = isset($_POST['category']) ? $_POST['category'] : '';
  $album = isset($_POST['album']) ? $_POST['album'] : '';

  // Process to upload img to database
  if (isset($_POST['post-img']) && isset($_FILES['img'])) {
    $img_name = $_FILES['img']['name'];
    $img_size = $_FILES['img']['size'];
    $tmp_name = $_FILES['img']['tmp_name'];
    $error = $_FILES['img']['error'];

    if ($error === 0) {
      // Allow upload img less than 20Mb
      if ($img_size < 20 * 1024 * 1024) {
        $img_ex = pathinfo($img_name, PATHINFO_EXTENSION);
        $img_ex_lc = strtolower($img_ex);

        // Only allow for 3 types of img
        $allowed_exs = array("jpg", "jpeg", "png");

        if (in_array($img_ex_lc, $allowed_exs)) {
          $new_img_name = uniqid("IMG-", true) . '.' . $img_ex_lc;
          $img_upload_path = 'uploads/' . $new_img_name;
          move_uploaded_file($tmp_name, $img_upload_path);

          $album = isset($_POST['album']) ? $_POST['album'] : NULL;
          // Insert into database using prepared statement
          $sql = "INSERT INTO photo (userId, caption, description, category, photoPath, album) VALUES (?, ?, ?, ?, ?, ?)";
          $stmt = mysqli_prepare($conn, $sql);

          // Bind parameters, including allowing NULL for the album
          mysqli_stmt_bind_param($stmt, "issssi", $userId, $caption, $description, $category, $new_img_name, $album);

          // Execute the statement
          mysqli_stmt_execute($stmt);


          header("Location: homepage.php");
          exit();
        } else {
          $em = "Invalid image format. Please upload a valid image.";
          header("Location: upload-img.php?error=$em");
          exit();
        }
      } else {
        $em = "Your image is too large. Please upload an image less than 20MB.";
        header("Location: upload-img.php?error=$em");
        exit();
      }
    } else {
      $em = "Can't upload image. Please try again.";
      header("Location: upload-img.php?error=$em");
      exit();
    }
  } else {
    header("Location: upload-img.php");
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
        <div class="export-icon">
          <i class="ti-export"></i>
        </div>
      </div>
    </nav>
    <hr class="hr-nav">
    <!-- Upload file -->
    <!-- File frame -->
    <!-- <main id="upload"> -->
    <!-- form for upload img -->

    <form action="upload-img.php" method="post" id="upload" enctype="multipart/form-data" class="container">
      <div class="upload-container">
        <!-- File div -->
        <div class="file">
          <label for="img">
            <div class="icon">
              <i class="ti-export"></i>
            </div>
          </label>
          <input type="file" name="img" id="img" class="form-control-file">
          <label for="img">
            <br>
            <p>We recommend using a high-quality file less than 20MB</p>
          </label> <br>
        </div>

        <!-- File description -->
        <aside class="description">
          <div class="form-group">
            <input type="text" class="form-upload mt-0" name="caption" placeholder="Add a title">
          </div>
          <div class="form-group">
            <input type="text" class="form-upload" name="description" placeholder="Add a detailed description">
          </div>
          <div class="form-group">
            <select name="category" id="category" class="form-upload" required>
              <option value="" selected disabled>Select a category</option>
              <?php
              include "database.php";
              if ($conn) {
                $query = "SELECT cateID, cateName FROM category";
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
            <select class="form-upload" name="album" id="album">
              <option value="" selected disabled>Select a album</option>
              <option value="NULL">No Album</option>
              <?php
              include "database.php";
              if ($conn) {
                // Assuming $_SESSION['user'] contains the user ID
                $userId = $_SESSION['user'];

                // Modify the query to select only albums created by the current user
                $query = "SELECT albumId, albumName FROM album WHERE userId = ?";
                $stmt = mysqli_prepare($conn, $query);
                mysqli_stmt_bind_param($stmt, "i", $userId);
                mysqli_stmt_execute($stmt);

                $result = mysqli_stmt_get_result($stmt);

                while ($row = mysqli_fetch_assoc($result)) {
                  echo "<option value='{$row['albumId']}'>{$row['albumName']}</option>";
                }
                mysqli_close($conn); // Close the database connection
              }
              ?>
            </select>

          </div>


          <div class="post-img">
            <input type="submit" value="Post" name="post-img" class="btn btn-primary">
          </div>
        </aside>
      </div>
    </form>

    <!-- </main> -->

    <div class="container">
      <a href="./homepage.php" class="btn btn-warning">Back</a>
    </div>
  </div>

</body>

</html>