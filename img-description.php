<?php
// Start the session
session_start();
// Retrieve the photo ID from the URL parameter
$photoId = isset($_GET['photoId']) ? $_GET['photoId'] : null;

// Fetch image details from the database using $photoId
include_once "database.php";

$sql = "SELECT p.*, a.avatar, a.firstName, a.lastName FROM photo p JOIN account a ON p.userId = a.userId WHERE p.photoId = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $photoId);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);

// Check if the result is not empty before fetching
if (mysqli_num_rows($res) > 0) {
  $data = mysqli_fetch_assoc($res);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Image Description</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+jjXkk+Q2h455rYXK/7HAuoJl+0I4" crossorigin="anonymous">
  </script>
  <link rel="stylesheet" href="./css-design/img-description.css">
  <link rel="stylesheet" href="./fonts/themify-icons/themify-icons.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>

  <script src="https://use.fontawesome.com/fe459689b4.js"></script>
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
        <a href="homepage.php" class="ti-home"></a>
      </li>
      <li>
        <a href="./user-profile.php" class="ti-user"></a>
      </li>
      <li class="ti-bookmark"></li>
      <hr class="hr-sidebar">
      <li>
        <a tabindex="0" class="ti-settings" role="button" data-bs-toggle="popover" data-bs-trigger="focus" data-bs-title="Logout" data-bs-content=""></a>
      </li>
    </ul>
  </div>
  <div id="description">
    <!-- Header -->
    <nav class="navbar">
      <div class="container-fluid">
        <!-- search key -->
        <form class="d-flex" role="search" action="homepage.php" method="get">
          <div class="input-group">
            <input type="text" class="form-control" placeholder="Search keyword" aria-label="Search" aria-describedby="search-icon" name="searchKeyword">
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
      <?php
      // Fetch categories from the database
      $categorySql = "SELECT * FROM category";
      $categoryResult = mysqli_query($conn, $categorySql);

      // Check if there are categories
      if ($categoryResult && mysqli_num_rows($categoryResult) > 0) {
        $counter = 0; // Initialize counter

        while ($category = mysqli_fetch_assoc($categoryResult)) {
          $cateID = $category['cateID'];
          $cateName = $category['cateName'];

          // Output navigation item for each category
          echo '<li class="nav-item">';
          echo '<a class="nav-link" href="homepage.php?category=' . $cateID . '">' . $cateName . '</a>';
          echo '</li>';

          // Increment the counter
          $counter++;

          // Check if the counter is a multiple of 9
          if ($counter % 9 == 0) {
            // Close the current ul and start a new one
            echo '</ul>';
            echo '<ul class="nav">';
          }
        }

        // Free the result set
        mysqli_free_result($categoryResult);
      }
      ?>
    </ul>
    <hr class="hr-nav">
    <!-- Page content -->
    <main class="page-content">
      <div class="container img-display">
        <div class="row">
          <div class="col mt-3 img-col">
            <img src="uploads/<?= $data['photoPath'] ?>" class="img-fluid" alt="Image">
          </div>
        </div>
        <div class="img-des">
          <!-- Tracking user upload -->
          <ul class="alb-user mt-3">
            <li class="ava">
              <img src="avatar/<?= $data['avatar'] ?>" class="img-fluid" alt="User Avatar">
            </li>
            <li class="name">
              <?= $data['firstName'] . ' ' . $data['lastName']; ?>
            </li>
            <li class="edit-info">
              <?php
              $uploadedUserId = $data['userId'];
              if (isset($_SESSION['user'])) {
                $currentUserId = $_SESSION['user'];

                // Check if the current user is the one who uploaded the photo
                if ($currentUserId == $uploadedUserId) {
                  // Display the "Edit" button
                  echo '<li class="edit-info">';
                  echo '<a href="./edit-img.php?edit=' . $data['photoId'] . '"><i class="ti-pencil"></i></a>';
                  echo '</li>';
                }
              }
              ?>
            </li>

            <li class="like">
              <i class="ti-heart"></i>
            </li>
            <li class="img-mark"><i class="ti-bookmark"></i></li> <br>
            <li class="date">
              <?= $data['updateDate']; ?>
            </li>
          </ul>
          <!-- caption -->
          <div class="img-context">
            <p class="caption">
              <b><?= $data['caption']; ?></b>
            </p>
            <p class="img-descrip">
              <?= $data['description']; ?>
            </p>
          </div>
          <hr class="hr-img">
          <!-- Comment -->
          <div class="comment-box">
            <iframe src="comment.php?photoId=<?= $_GET['photoId'] ?>" width="100%" height="400px" frameborder="0"></iframe>
          </div>
        </div>
      </div>
      <aside>
        <h2>Most related</h2>
        <div class="related-img">
          <?php
          // Fetch related images from the database
          $category = $data['category']; // Get the category of the current image
          $relatedSql = "SELECT photoId, photoPath FROM photo WHERE category = ? AND photoId != ? ORDER BY RAND()";
          $relatedStmt = mysqli_prepare($conn, $relatedSql);
          mysqli_stmt_bind_param($relatedStmt, "ii", $category, $photoId);
          mysqli_stmt_execute($relatedStmt);
          $relatedRes = mysqli_stmt_get_result($relatedStmt);

          if (mysqli_num_rows($relatedRes) > 0) {
            $counter = 0;

            // Display related images
            while ($relatedData = mysqli_fetch_assoc($relatedRes)) {
              if ($counter % 2 == 0) {
                // Start a new row for every 2 images
                echo '<div class="row">';
              }
          ?>
              <div class="col mt-3 img-col">
                <?php
                $relatedImagePath = "uploads/" . $relatedData['photoPath'];
                if (file_exists($relatedImagePath)) {
                ?>
                  <a href="./img-description.php?photoId=<?= $relatedData['photoId'] ?>" class="detailed">
                    <img src="<?= $relatedImagePath ?>" class="img-fluid" alt="Related Image">
                  </a>
                <?php
                } else {
                ?>
                  <p>Image not found: <?= $relatedImagePath ?></p>
                <?php
                }
                ?>
              </div>
          <?php
              if ($counter % 2 == 1) {
                // Close the row after every 3 images
                echo '</div>';
              }
              $counter++;
            }

            // Close the row if there are remaining images
            if ($counter % 2 != 0) {
              echo '</div>';
            }
          } else {
            echo '<p>No images related</p>';
          }
          ?>
        </div>
      </aside>

    </main>

    <div class="back-btn">
      <a href="./homepage.php">Back</a>
    </div>
  </div>

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