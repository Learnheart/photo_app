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

// Get the user ID from the session
$userId = $_SESSION["user"];

// Create new album function
// Handle the form submission or UI interaction
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["createAlbum"])) {
  include_once('database.php');

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

  // Redirect after creating the album
  header("Location: user-profile.php");
  exit();
}

// Fetch user's avatar
include_once('database.php');
$sql = "SELECT avatar FROM account WHERE userId = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $userId);
mysqli_stmt_execute($stmt);

$res = mysqli_stmt_get_result($stmt);

if ($res && $avatarData = mysqli_fetch_assoc($res)) {
  // If the user has a custom avatar, use it
  $avatarPath = $avatarData['avatar'];
}


?>
<?php ob_start(); ?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>User Profile</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+jjXkk+Q2h455rYXK/7HAuoJl+0I4" crossorigin="anonymous">
  </script>
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
  <link rel="stylesheet" href="./css-design/profile.css">
  <link rel="stylesheet" href="./css-design/profile_mobile.css">
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
      <li>
        <a tabindex="0" class="ti-settings" role="button" data-bs-toggle="popover" data-bs-trigger="focus" data-bs-title="Logout" data-bs-content=""></a>
      </li>
    </ul>
  </div>
  <!-- Webpage content -->
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
    <hr class="hr-nav">
  </nav>
  <main class="container user-content">
    <!-- Contain user information -->
    <aside id="user-info">
      <!-- Only owner can update pro5 -->
      <?php
      if ($_SESSION['user'] == $userId) {
        echo '<a href="./edit-profile.php?edit=' . $userId . '" class="edit-icon">';
        echo '<i class="ti-pencil"></i>';
        echo '</a>';
      }
      ?>
      <div class="avatar">
        <?php
        if (!empty($avatarPath)) {
          // Display the avatar with a link to open the avatar modification modal
          echo '<a href="#" data-bs-toggle="modal" data-bs-target="#avatarModal">';
          echo '<img src="avatar/' . $avatarPath . '" class="img-fluid" alt="User Avatar">';
          echo '</a>';
        } else {
          // Provide a default avatar or handle accordingly
          echo '<img src="avatar/default-avatar.png" alt="Default Avatar">';
        }
        ?>
      </div>
      <div class="name-info">
        <?php
        $sql = "SELECT firstName, lastName FROM account WHERE userId = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $userId);
        mysqli_stmt_execute($stmt);

        $res = mysqli_stmt_get_result($stmt);

        if ($res && $data = mysqli_fetch_assoc($res)) {
          // Fetch the data and display the user's first name and last name
          echo $data['firstName'] . ' ' . $data['lastName'];
        } else {
          // Handle the case where no data is found
          echo "User not found";
        }

        // Close the statement
        mysqli_stmt_close($stmt);
        ?>

        <!-- Avatar Modification Modal -->
        <div class="modal fade" id="avatarModal" tabindex="-1" aria-labelledby="avatarModalLabel" aria-hidden="true">
          <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title" id="avatarModalLabel">Change Avatar</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body">
                <form action="user-profile.php" method="post" enctype="multipart/form-data">
                  <input type="file" name="upd-img" id="upd-img" class="form-control-file" accept=".jpg, .jpeg, .png">
                  <br>
                  <div class="update-img">
                    <input type="submit" class="btn btn-secondary" value="Save change" name="update-img">
                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>
        <?php
        $new_ava_name = "";
        if (isset($_POST['update-img']) && isset($_FILES['upd-img'])) {
          $img_name = $_FILES['upd-img']['name'];
          $img_size = $_FILES['upd-img']['size'];
          $tmp_name = $_FILES['upd-img']['tmp_name'];
          $error = $_FILES['upd-img']['error'];
          if ($error === 0) {
            if ($img_size < 20 * 1024 * 1024) {
              $img_ex = pathinfo($img_name, PATHINFO_EXTENSION);
              $img_ex_lc = strtolower($img_ex);

              $allow = array("jpg", "jpeg", "png");

              if (in_array($img_ex_lc, $allow)) {
                $new_ava_name = uniqid("IMG-", true) . '.' . $img_ex_lc;
                $ava_update_path = 'avatar/' . $new_ava_name;
                move_uploaded_file($tmp_name, $ava_update_path);

                $sql = "UPDATE account SET avatar = ? WHERE userId = ?";
                $stmt = mysqli_prepare($conn, $sql);

                mysqli_stmt_bind_param($stmt, "si", $new_ava_name, $userId);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
                header("Location: user-profile.php");
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
        }
        ?>
      </div>
      <ul class="post-album">
        <li class="post-number">
          <?php
          $countPost = "SELECT COUNT(photoId) as totalPosts FROM photo WHERE userId = ?";
          $stmt = mysqli_prepare($conn, $countPost);
          mysqli_stmt_bind_param($stmt, "i", $userId);
          mysqli_stmt_execute($stmt);

          $res = mysqli_stmt_get_result($stmt);
          $row = mysqli_fetch_assoc($res);
          $totalPosts = $row['totalPosts'];
          // echo $totalPosts . ' posts';
          ?>
          <button onclick="enablePostDiv()">
            <?php echo $totalPosts; ?> posts
          </button>

        </li>
        <li class="album-number">
          <?php
          $countAlbum = "SELECT COUNT(albumId) as totalAlbum FROM album WHERE userId = ?";
          $stmt = mysqli_prepare($conn, $countAlbum);
          mysqli_stmt_bind_param($stmt, "i", $userId);
          mysqli_stmt_execute($stmt);

          $res = mysqli_stmt_get_result($stmt);
          $row = mysqli_fetch_assoc($res);
          $totalAlbum = $row['totalAlbum'];

          // echo $totalAlbum . ' albums';
          ?>
          <button onclick="enableAlbumDiv()">
            <?php echo $totalAlbum; ?> albums
          </button>
        </li>
      </ul>
    </aside>

    <!-- Create album here -->
    <aside id="create-album" class="hidden mt-3">
      <!-- Adding new album -->
      <form method="post" action="user-profile.php">
        <div class="form-group">
          <label for="albumName">Album Name</label> <br>
          <input type="text" class="form-control" id="albumName" name="albumName" placeholder="Add a title" required>
        </div>
        <br>
        <div class="form-group">
          <input type="submit" name="createAlbum" class="btn btn-primary" value="Create Album">
        </div>
      </form>
    </aside>

    <section id="content-container">
      <!-- Display uploaded image of that user -->
      <div id="upload-img">
        <?php
        $userId = $_SESSION['user'];

        $sql = "SELECT p.photoPath, p.photoId FROM photo p JOIN account a ON p.userId = a.userId WHERE p.userId = ? ORDER BY p.photoId DESC";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $userId);
        mysqli_stmt_execute($stmt);

        $res = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($res) > 0) {
          $counter = 0; // Counter to determine when to start a new row
          while ($data = mysqli_fetch_assoc($res)) {
            if ($counter % 2 == 0) {
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
        }
        ?>
      </div>
      <div id="album-img" class="hidden">
        <?php
        $albumDisplay = "SELECT al.albumId, al.albumName, p.photoPath, p.photoId 
                     FROM album al 
                     LEFT JOIN photo p ON al.albumId = p.album AND p.userId = ?
                     WHERE al.userId = ?
                     GROUP BY al.albumId
                     ORDER BY al.albumId DESC";

        $stmt = mysqli_prepare($conn, $albumDisplay);
        mysqli_stmt_bind_param($stmt, "ii", $userId, $userId);
        mysqli_stmt_execute($stmt);

        $res = mysqli_stmt_get_result($stmt);

        // Initialize flag to check if any album has photos
        $hasPhotos = false;

        if (mysqli_num_rows($res) > 0) {
          $counter = 0; // Counter to determine when to start a new row

          while ($data = mysqli_fetch_assoc($res)) {
            if ($counter % 2 == 0) {
              // Start a new row for every 2 albums
              echo '<div class="row">';
            }
        ?>
            <div class="col mt-3 img-col">
              <a href="./album-detail.php?albumId=<?= $data['albumId'] ?>">
                <?php
                if ($data['photoId'] !== null) {
                  // If there is a cover photo, display it
                  echo '<img src="uploads/' . $data['photoPath'] . '" class="img-fluid" alt="Image">';
                } else {
                  // If there is no cover photo, display a placeholder image or message
                  echo '<img src="uploads/placeholder.png" class="img-fluid" alt="Image">';
                }
                ?>
              </a>
              <div class="name">
                <?= isset($data['albumName']) ? $data['albumName'] : 'N/A'; ?>
              </div>
            </div>
        <?php
            if ($counter % 2 == 1) {
              // Close the row after every 2 albums
              echo '</div>';
            }
            $counter++;
          }

          // Close the row if there are remaining albums
          if ($counter % 2 != 0) {
            echo '</div>';
          }
        }
        ?>
        <button class="col img-fluid add-album" onclick="enableCreateAlbum()">
          <i class="ti-plus"></i>
        </button>
      </div>


    </section>

  </main>
  <script>
    function enableCreateAlbum() {
      var userInfoDiv = document.getElementById('user-info');
      var createAlbDiv = document.getElementById('create-album');

      // Hide the upload-img section
      userInfoDiv.classList.add('hidden');

      // Show the album-img section
      createAlbDiv.classList.remove('hidden');
    }

    function enableAlbumDiv() {
      var uploadImgDiv = document.getElementById('upload-img');
      var albumImgDiv = document.getElementById('album-img');

      // Hide the upload-img section
      uploadImgDiv.classList.add('hidden');

      // Show the album-img section
      albumImgDiv.classList.remove('hidden');
    }

    function enablePostDiv() {
      var uploadImgDiv = document.getElementById('upload-img');
      var albumImgDiv = document.getElementById('album-img');

      // Hide the upload-img section
      uploadImgDiv.classList.remove('hidden');

      // Show the album-img section
      albumImgDiv.classList.add('hidden');
    }
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
<?php
ob_end_flush();
?>