<?php
session_start();

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
$userId = $_SESSION['user'];
$avatarSql = "SELECT avatar FROM account WHERE userId = ?";
$stmtAva = mysqli_prepare($conn, $avatarSql);
mysqli_stmt_bind_param($stmtAva, 'i', $userId);
mysqli_stmt_execute($stmtAva);
$resAva = mysqli_stmt_get_result($stmtAva);

if ($resAva && $avatarData = mysqli_fetch_assoc($resAva)) {
  $avatarPath = $avatarData['avatar'];
} else {
  $avatarPath = 'avatar/default-avatar.png';
}

// Existing information
$existFirstName = "";
$existLastName = "";
$existEmail = "";
$existPassword = "";

// Display old data 
if (isset($_GET['edit'])) {
  $editUserId = $_GET['edit'];

  // Fetch existed information into input
  $existSql = "SELECT firstName, lastName, email, password FROM account WHERE userId = ?";
  $existStmt = mysqli_prepare($conn, $existSql);
  mysqli_stmt_bind_param($existStmt, 'i', $editUserId);;
  mysqli_stmt_execute($existStmt);
  mysqli_stmt_bind_result($existStmt, $existFirstName, $existLastName, $existEmail, $existPassword);
  mysqli_stmt_fetch($existStmt);
  mysqli_stmt_close($existStmt);
}

// Check if the form for delete is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  // Assuming you have a form with a delete button
  if (isset($_POST['delete-account'])) {
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
      echo "Account deleted successfully.";
    } else {
      echo "Error deleting account: " . mysqli_error($conn);
    }

    mysqli_stmt_close($deleteAccountStmt);

    // Destroy the current session
    session_destroy();

    // Redirect to homepage after deleting
    header("Location: login.php");
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
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
  <link rel="stylesheet" href="./css-design/registration.css">
  <link rel="stylesheet" href="./fonts/themify-icons/themify-icons.css">
</head>

<body>
  <!-- Vertical side bar -->
  <div class="navbar">
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
        <a tabindex="0" class="ti-settings" role="button" data-bs-toggle="popover" data-bs-trigger="focus"
          data-bs-title="Logout" data-bs-content=""></a>
      </li>
    </ul>
  </div>
  <div id="edit-profile" class="container">
    <header>
      <h1>Edit Profile</h1>
      <hr>
    </header>
    <main>
      <?php
      if (isset($_POST["submit"])) { //only works when user click submit button
        $firstName = isset($_POST['firstName']) ? $_POST['firstName'] : '';
        $lastName = isset($_POST['lastName']) ? $_POST['lastName'] : '';
        $email = isset($_POST['email']) ? $_POST['email'] : '';
        $password = isset($_POST['password']) ? $_POST['password'] : '';
        $confirmPassword = isset($_POST["confirmPassword"]) ? $_POST["confirmPassword"] : $existPassword;

        // If user don't want create new password -> get the old one
        if (empty($password)) {
          $password = $existPassword;
          $confirmPassword = $existPassword;
        } else {
          // Encrypt the new password
          $password_hash = password_hash($password, PASSWORD_DEFAULT);
        }
        // Add validations
        $error = array();
        // Check empty values
        if (empty($firstName) or empty($lastName) or empty($email)) {
          array_push($error, "*Required*");
        }
        // Check email form
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
          array_push($error, "*Invalid Email!*");
        }
        // Check password length
        if (!empty($password) && strlen($password) < 8) {
          array_push($error, "*Password length at least 8 characters.*");
        }
        if ($password !== $confirmPassword) {
          array_push($error, "*Confirm password did not match*");
        }
        // Display error on screen
        if (count($error) > 0) {
          foreach ($error as $error) {
            echo "<div class='alert alert-danger'>$error</div>";
          }
        } else {
          // Use a SELECT statement to check if the email already exists for a different user
          $emailCheckSql = "SELECT userId FROM account WHERE email = ? AND userId != ?";
          $emailCheckStmt = mysqli_prepare($conn, $emailCheckSql);
          mysqli_stmt_bind_param($emailCheckStmt, 'si', $email, $userId);
          mysqli_stmt_execute($emailCheckStmt);
          mysqli_stmt_store_result($emailCheckStmt);

          // ...

          if (mysqli_stmt_num_rows($emailCheckStmt) > 0) {
            array_push($error, "Email already exists");
          } else {
            // Destroy the old session
            session_destroy();

            // Start a new session
            session_start();

            // Set the necessary session variables
            $_SESSION["user"] = $userId;
            $_SESSION["firstName"] = $firstName;
            $_SESSION["lastName"] = $lastName;
            $_SESSION["email"] = $email;
            // Set the role or any other necessary session variables
            $_SESSION["role"] = "User"; // Set the role accordingly

            // Update the user's information
            $updateSql = "UPDATE account SET firstName = ?, lastName = ?, email = ?, password = ? WHERE userId = ?";
            $updateStmt = mysqli_prepare($conn, $updateSql);
            mysqli_stmt_bind_param($updateStmt, 'ssssi', $firstName, $lastName, $email, $password_hash, $userId);
            mysqli_stmt_execute($updateStmt);
            mysqli_stmt_close($updateStmt);

            header('Location: user-profile.php');
            exit();
          }
          mysqli_stmt_close($emailCheckStmt);
        }
      }
      ?>
      <div class="container avatar">
        <img src="<?php echo 'avatar/' . $avatarPath; ?>" alt="User Avatar" class="img-fluid">
      </div>
      <form action="edit-profile.php" method="post">
        <div class="input-group">
          <input type="text" class="form-control" name="firstName" placeholder="First Name"
            value="<?php echo htmlspecialchars($existFirstName); ?>">
          <span></span>
          <input type="text" class="form-control" name="lastName" placeholder="Last Name"
            value="<?php echo htmlspecialchars($existLastName); ?>">
        </div>
        <div class="form-group">
          <input type="email" class="form-control mt-0" name="email" placeholder="Email address"
            value="<?php echo htmlspecialchars($existEmail); ?>">
        </div>
        <div class="form-group">
          <input type="password" class="form-control" name="password" placeholder="Password"
            value="<?php echo htmlspecialchars($existPassword) ?>">
        </div>
        <div class="form-group">
          <input type="password" class="form-control" name="confirmPassword" placeholder="Confirm Password">
        </div>
        <div class="form-btn">
          <input type="reset" value="Reset" class="btn btn-primary">
          <input type="submit" class="btn btn-primary" value="Save" name="submit">
        </div>
      </form>
      <form action="edit-profile.php" method="post">
        <div class="form-btn">
          <input type="submit" class="btn btn-danger" name="delete-account" value="Delete Account">
        </div>
      </form>
    </main>
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