<?php
session_start();

// Include the database connection file
include "database.php";

// Check if the user is logged in
if (!isset($_SESSION["user"])) {
  header("Location: login.php"); // Redirect to the login page
  exit();
}

// Ensure $_SESSION['user'] is properly sanitized to prevent SQL injection
$userId = $_SESSION['user'];
$sessionId = mysqli_real_escape_string($conn, $_SESSION['user']);

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

// Initialize variables for existing information
$existingCaption = "";
$existingDescription = "";
$existingCategory = "";
$existingAlbum = "";

// Check if an image is selected for editing
if (isset($_GET['edit'])) {
  $editPhotoId = $_GET['edit'];

  // Fetch existing information for the selected image
  $existingInfoSql = "SELECT userId, photoPath, caption, description, category, album FROM photo WHERE photoId = ?";
  $existingInfoStmt = mysqli_prepare($conn, $existingInfoSql);

  // Bind parameters
  mysqli_stmt_bind_param($existingInfoStmt, "i", $editPhotoId);

  // Execute the statement
  mysqli_stmt_execute($existingInfoStmt);

  // Bind the result variables
  mysqli_stmt_bind_result($existingInfoStmt, $imageUserId, $existingPhotoPath, $existingCaption, $existingDescription, $existingCategory, $existingAlbum);

  // Fetch the result
  mysqli_stmt_fetch($existingInfoStmt);

  // Close the statement
  mysqli_stmt_close($existingInfoStmt);

  // Check if the logged-in user is the owner of the image
  if ($userId !== $imageUserId) {
    // Redirect to homepage if not authorized
    echo '<script>alert("You do not have authorization to access this resource."); setTimeout(function()
      { window.location.href = "homepage.php"; }, 1000);</script>';
    exit();
  }
}

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  // Retrieve form data
  $caption = isset($_POST['caption']) ? $_POST['caption'] : '';
  $description = isset($_POST['description']) ? $_POST['description'] : '';
  $category = isset($_POST['category']) ? $_POST['category'] : '';
  $album = isset($_POST['album']) ? $_POST['album'] : '';

  // Process to upload img to database
  if (isset($_POST['update-img'])) {
    // Update the existing image record in the database
    $updateSql = "UPDATE photo SET caption = ?, description = ?, category = ?, album = ? WHERE photoId = ?";
    $updateStmt = mysqli_prepare($conn, $updateSql);

    $album = ($album === "NULL") ? null : $album;
    // Bind parameters
    mysqli_stmt_bind_param($updateStmt, "ssssi", $caption, $description, $category, $album, $editPhotoId);


    // Execute the statement
    mysqli_stmt_execute($updateStmt);

    // Close the statement
    mysqli_stmt_close($updateStmt);

    // Redirect to homepage after updating
    header("Location: homepage.php");
    exit();
  }
}

// Delete
// Check if the form for delete is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  // Assuming you have a form with a delete button
  if (isset($_POST['delete-img'])) {
    $deletePhotoId = $_GET['edit'];

    // Delete associated comments first
    $deleteCommentsSql = "DELETE FROM comment WHERE photoId = ?";
    $deleteCommentsStmt = mysqli_prepare($conn, $deleteCommentsSql);
    mysqli_stmt_bind_param($deleteCommentsStmt, "i", $deletePhotoId);
    mysqli_stmt_execute($deleteCommentsStmt);
    mysqli_stmt_close($deleteCommentsStmt);

    // Now, delete the photo
    $deletePhotoSql = "DELETE FROM photo WHERE photoId = ?";
    $deletePhotoStmt = mysqli_prepare($conn, $deletePhotoSql);
    mysqli_stmt_bind_param($deletePhotoStmt, "i", $deletePhotoId);
    mysqli_stmt_execute($deletePhotoStmt);
    mysqli_stmt_close($deletePhotoStmt);

    // Redirect to homepage after deleting
    header("Location: homepage.php");
    exit();
  }
}



?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit Image</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet"
    integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+jjXkk+Q2h455rYXK/7HAuoJl+0I4" crossorigin="anonymous">
  </script>
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
  <link rel="stylesheet" href="./css-design/upload.css">
  <link rel="stylesheet" href="./fonts/themify-icons/themify-icons.css">
</head>

<body>
  <!-- Vertical side bar -->
  <div id="side-bar">
    <div class="logo-container">
      <img src="./img/circle.webp" alt="Logo" class="logo">
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
      <li>
        <a tabindex="0" class="ti-settings" role="button" data-bs-toggle="popover" data-bs-trigger="focus"
          data-bs-title="Logout" data-bs-content=""></a>
      </li>
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
    <!-- Form for editing image information -->
    <form action="edit-img.php?edit=<?= $editPhotoId ?>" method="post" id="edit" class="container">

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
        <aside class="description">

          <div class="form-group">
            <input type="text" class="form-upload" name="caption" id="caption" placeholder="Add a title"
              value="<?= htmlspecialchars($existingCaption) ?>">
          </div>
          <div class="form-group">
            <input type="text" class="form-upload" name="description" id="description"
              placeholder="Add a detailed description" value="<?= htmlspecialchars($existingDescription) ?>">
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
                  $selected = ($row['cateID'] == $existingCategory) ? "selected" : "";
                  echo "<option value='{$row['cateID']}' $selected>{$row['cateName']}</option>";
                }

                mysqli_close($conn); // Close the database connection
              }
              ?>
            </select>
          </div> 
          <div class="form-group">
            <select name="album" id="album" class="form-upload">
              <!-- Populate album options -->
              <option value="" selected disabled>Select a Album</option>
              <option value="NULL">No Album</option>
              <?php
              include "database.php";
              if ($conn) {
                $query = "SELECT albumId, albumName FROM album WHERE userId = ?";
                $stmt = mysqli_prepare($conn, $query);
                mysqli_stmt_bind_param($stmt, "i", $userId);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);

                while ($row = mysqli_fetch_assoc($result)) {
                  $selected = ($row['albumId'] == $existingAlbum) ? "selected" : "";
                  echo "<option value='{$row['albumId']}' $selected>{$row['albumName']}</option>";
                }

                mysqli_close($conn); // Close the database connection
              }
              ?>
            </select>
          </div>
          <div class="form-group">
            <input type="submit" value="Post" name="update-img" class="btn btn-primary mt-5">
          </div>
        </aside>
      </div>
    </form>

    <!-- Form for deleting image -->
    <form action="edit-img.php?edit=<?= $editPhotoId ?>" method="post" id="delete" class="container">
      <div class="form-group">  
        <input type="submit" value="Delete" name="delete-img" class="btn btn-danger mt-3 mb-5">
      </div>
    </form>
    <script>
    // Initialize popover
    $(function() {
      $('[data-bs-toggle="popover"]').popover();
    });

    // Handle click on popover title
    $(document).on('click', '.popover-header', function() {
      window.location.href = 'logout.php';
    });
    </script>
</body>

</html>