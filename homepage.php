<?php
session_start();
// Check authorize 
if (!isset($_SESSION["user"])) {
  header("Location: login.php"); //turn back to the login page
}
// Only accessible for user role
if ($_SESSION["role"] !== "User") {
  // Redirect to another page or display an error message
  header("Location: login.php");
  exit();
}

include_once "database.php";
// Assuming you stored userId in the session during login
$userId = isset($_SESSION['user']['userId']) ? $_SESSION['user']['userId'] : null;
// $post_id = $POST["photoId"];
// $likeUser = $POST['userId'];
// $status = $POST['status'];

// Retrieve the search keyword
$searchKeyword = isset($_GET['searchKeyword']) ? mysqli_real_escape_string($conn, $_GET['searchKeyword']) : '';

// Retrieve the selected category from the navbar
$category = isset($_GET['category']) ? intval($_GET['category']) : 0; // Assuming 0 as a default or no category

// Your existing SQL query to fetch images
$sql = "SELECT p.*, a.firstName, a.lastName, a.avatar, p.userId FROM photo p JOIN account a ON p.userId = a.userId";

// Add the search condition if a keyword is provided
if (!empty($searchKeyword)) {
  $sql .= " WHERE p.caption LIKE '%$searchKeyword%' OR p.description LIKE '%$searchKeyword%' OR a.firstName LIKE '%$searchKeyword%' OR a.lastName LIKE '%$searchKeyword%'";
}

// Add the category condition if a category is provided
if (!empty($category)) {
  $sql .= " And p.category = $category";
}

$sql .= " ORDER BY p.photoId DESC";

$res = mysqli_query($conn, $sql);
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
  <link rel="stylesheet" href="./css-design/hompage.css">
  <link rel="stylesheet" href="./css-design/hompage_mobile.css">
  <link rel="stylesheet" href="./fonts/themify-icons/themify-icons.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
  <script src="https://use.fontawesome.com/fe459689b4.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
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
  <!-- Webpage content -->
  <div id="homepage">

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
      <div class="container">
        <?php
        $counter = 0; // Counter to determine when to start a new row

        while ($data = mysqli_fetch_assoc($res)) {
          if ($counter % 3 == 0) {
            // Start a new row for every 3 images
            echo '<div class="row">';
          }
          // Fetch user's avatar
          $userId = $data['userId'];
          $sqlAvatar = "SELECT avatar FROM account WHERE userId = ?";
          $stmtAvatar = mysqli_prepare($conn, $sqlAvatar);
          mysqli_stmt_bind_param($stmtAvatar, "i", $userId);
          mysqli_stmt_execute($stmtAvatar);

          $resAvatar = mysqli_stmt_get_result($stmtAvatar);

          // Check if the result is not empty before fetching
          if ($resAvatar && $avatarData = mysqli_fetch_assoc($resAvatar)) {
            // If the user has a custom avatar, use it
            $avatarPath = $avatarData['avatar'];
          } else {
            // Provide a default avatar or handle accordingly
            $avatarPath = 'avatar/default-avatar.png';
          }

          // Close the statement
          mysqli_stmt_close($stmtAvatar);
        ?>
          <div class="col mt-3">
            <div class="alb">
              <a href="./img-description.php?photoId=<?= $data['photoId'] ?>">
                <img src="uploads/<?= $data['photoPath'] ?>" class="img-fluid" alt="Image">
              </a>
            </div>
            <!-- Tracking user upload -->
            <ul class="alb-user">
              <li class="ava">
                <?php
                echo '<img src="avatar/' . $avatarPath . '" class="img-fluid" alt="User Avatar">';
                ?>
              </li>
              <li class="name">
                <?= $data['firstName'] . ' ' . $data['lastName']; ?>
              </li>

              <li class="like">
                <i class="ti-heart"></i>
              </li>
              <li class="img-mark"><i class="ti-bookmark"></i></li>
            </ul>
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

        // Display a message if no images are found
        if ($counter == 0) {
          echo '<p>No images found</p>';
        }
        ?>
      </div>
    </main>
  </div>
  <!-- <div id="logout" class="container">
    <a href="logout.php" class="btn btn-warning">Logout</a>
  </div> -->
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