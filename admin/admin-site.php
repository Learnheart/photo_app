<?php
session_start();
// // Check if the user is logged in
if (!isset($_SESSION["user"])) {
  // Redirect to the login page or another page of your choice
  header("Location: login.php");
  exit();
}

// Check if the user has the "Admin" role
if ($_SESSION["role"] !== "Admin") {
  // Redirect to another page or display an error message
  header("Location: access-denied.php");
  exit();
}
include_once "../database.php";
$sql = "SELECT * from account";
$res = mysqli_query($conn, $sql);
// $userId = $data['userId'];

// Check if the form for delete is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  // Assuming you have a form with a delete button
  if (isset($_POST['delete-account'])) {
    $userId = $_POST['userId'];
    // delete the photo
    $deletePhotoSql = "DELETE FROM photo WHERE userId = ?";
    $deletePhotoStmt = mysqli_prepare($conn, $deletePhotoSql);
    mysqli_stmt_bind_param($deletePhotoStmt, "i", $userId);
    mysqli_stmt_execute($deletePhotoStmt);
    mysqli_stmt_close($deletePhotoStmt);

    // delete the album
    $deleteAlbumSql = "DELETE FROM album WHERE userId = ?";
    $deleteAlbumStmt = mysqli_prepare($conn, $deleteAlbumSql);
    mysqli_stmt_bind_param($deleteAlbumStmt, "i", $userId);
    mysqli_stmt_execute($deleteAlbumStmt);
    mysqli_stmt_close($deleteAlbumStmt);

    // delete the comment
    $deleteCommentSql = "DELETE FROM comment WHERE userId = ?";
    $deleteCommentStmt = mysqli_prepare($conn, $deleteCommentSql);
    mysqli_stmt_bind_param($deleteCommentStmt, "i", $userId);
    mysqli_stmt_execute($deleteCommentStmt);
    mysqli_stmt_close($deleteCommentStmt);

    // delete the account
    $deleteAccountSql = "DELETE FROM account WHERE userId = ?";
    $deleteAccountStmt = mysqli_prepare($conn, $deleteAccountSql);
    mysqli_stmt_bind_param($deleteAccountStmt, "i", $userId);

    if (mysqli_stmt_execute($deleteAccountStmt)) {
      // Close the statement
      mysqli_stmt_close($deleteAccountStmt);

      // Use JavaScript to show an alert and redirect immediately
      echo '<script>alert("Account deleted successfully."); window.location.href = "admin-site.php";</script>';
      exit();
    } else {
      echo "Error deleting account: " . mysqli_error($conn);
    }


    // Close the statement
    mysqli_stmt_close($deleteAccountStmt);

    // Destroy the current session
    session_destroy();
  }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Website</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+jjXkk+Q2h455rYXK/7HAuoJl+0I4" crossorigin="anonymous">
  </script>
  <link rel="stylesheet" href="../css-design/admin-site.css">
  <link rel="stylesheet" href="../fonts/themify-icons/themify-icons.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
</head>

<body>
  <!-- Side bar -->
  <div class="side-bar">
    <div class="logo-container">
      <img src="../img/circle.webp" alt="Logo" class="logo">
    </div>
    <ul class="icon">
      <li><a href="../admin/admin-site.php">Users</a></li>
      <li><a href="../admin/category.php">Category</a></li>
      <hr class="hr-sidebar">
      <li>
        <a tabindex="0" class="ti-settings" role="button" data-bs-toggle="popover" data-bs-trigger="focus" data-bs-title="Logout" data-bs-content=""></a>
      </li>
    </ul>
  </div>
  <div id="admin-content" class="container">
    <!-- Header -->
    <nav class="navbar mt-3">
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
        <h2>Admin:
          <?php
          echo $_SESSION["firstName"] . " " . $_SESSION["lastName"];
          ?>
        </h2>
      </div>
    </nav>
    <hr class="hr-nav">
    <div id="user-list">
      <table class="table">
        <thead>
          <tr>
            <th scope="col"></th>
            <th scope="col">Username</th>
            <th scope="col">Joined Date</th>
            <th scope="col">Activity</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $counter = 1; // Counter to determine when to start a new row
          while ($data = mysqli_fetch_assoc($res)) {
            // Fetch user's avatar
            $userId = $data['userId'];
            $sqlAvatar = "SELECT avatar FROM account WHERE userId = ? ORDER BY date DESC";
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
              $avatarPath = '../avatar/default-avatar.png';
            }

            // Close the statement
            mysqli_stmt_close($stmtAvatar);
          ?>
            <tr>
              <th scope="row"><?= $counter ?></th>
              <td>
                <div class="row">
                  <div class="col mt-3">
                    <div class="name-list">
                      <img src="../avatar/<?= $avatarPath ?>" class="img-fluid" alt="User Avatar">
                      <?= $data['firstName'] . ' ' . $data['lastName']; ?>
                    </div>
                  </div>
                </div>
              </td>
              <!-- Include other user-related data in the respective columns -->
              <td><?= $data['date']; ?></td>
              <td>
                <form action="admin-site.php" method="post">
                  <input type="hidden" name="userId" value="<?= $userId ?>">
                  <div class="form-btn">
                    <input type="submit" class="btn btn-danger" name="delete-account" value="Delete">
                  </div>
                </form>

              </td>
            </tr>

          <?php
            $counter++;
          }
          ?>
        </tbody>
      </table>
    </div>
  </div>
  <script>
    // Initialize popover
    $(function() {
      $('[data-bs-toggle="popover"]').popover();
    });

    // Handle click on popover title
    $(document).on('click', '.popover-header', function() {
      window.location.href = '../logout.php';
    });
  </script>
</body>

</html>