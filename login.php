<!-- If user is already logged in -> move directly -->
<?php
error_reporting(E_ERROR | E_PARSE);
session_start();
// Check authorize 
if (isset($_SESSION["user"])) {
  header("Location: homepage.php");
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login Form</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/css/bootstrap.min.css"
    integrity="sha384-Zenh87qX5JnK2Jl0vWa8Ck2rdkQ2Bzep5IDxbcnCeuOxjzrPF/et3URy9Bv1WTRi" crossorigin="anonymous">
  <link rel="stylesheet" href="./css-design/registration.css">
  <link rel="stylesheet" href="./css-design/registration_mobile.css">
  <link rel="stylesheet" href="./fonts/themify-icons/themify-icons.css">
</head>

<body>
  <!-- Navbar -->
  <div class="navbar">
    <div class="logo-container">
      <img src="./img/circle.webp" alt="" class="logo">
    </div>
    <ul class="icon">
      <li class="ti-home"></li>
      <li class="ti-bookmark"></li>
    </ul>
  </div>
  <!-- Content -->
  <h1 class="mt-5">Sign In</h1>
  <hr>
  <div class="container">
    <?php
    if (isset($_POST["login"])) {
      include "database.php";
      // $email = $_POST["email"];
      // $password = $_POST["password"];
      // $role = $_POST["role"];
      // Sanitize user inputs
      $email = mysqli_real_escape_string($conn, $_POST["email"]);
      $password = mysqli_real_escape_string($conn, $_POST["password"]);
      $role = mysqli_real_escape_string($conn, $_POST["role"]);


      // var_dump($password);
      $sql = "SELECT * FROM account WHERE email = '$email'";
      $result = mysqli_query($conn, $sql);
      $user = mysqli_fetch_array($result, MYSQLI_ASSOC);

      // Debugging statements
      // echo "Entered Password: " . $password . "<br>";
      // echo "Stored Password Hash: " . $user["password"] . "<br>";

      if ($user) {
        $storedPasswordHash = $user["password"];
        if (password_verify($password, $storedPasswordHash)) {
          // allow only this user login
          // session_start();

          // Set user information in the session
          $_SESSION["user"] = $user["userId"];
          $_SESSION["firstName"] = $user["firstName"];
          $_SESSION["lastName"] = $user["lastName"];
          $_SESSION["email"] = $user["email"];

          // Debugging: Print session variables
          echo "Session Information: ";
          print_r($_SESSION);

          // Check if the 'role' key exists in the $user array
          if (isset($user["role"])) {
            $_SESSION["role"] = $user["role"];

            // Redirect based on user role
            if ($_SESSION["role"] == "User") {
              header("Location: homepage.php");
            } elseif ($_SESSION["role"] == "Admin") {
              header("Location: admin-site.php");
            } else {
              // Handle other roles as needed
              echo "<div class='alert alert-danger'>Invalid role</div>";
            }
            exit();
          } else {
            // Handle the case where 'role' key is not set in the $user array
            echo "<div class='alert alert-danger'>Role not set for this user</div>";
          }
        } else {
          echo "<div class='alert alert-danger'>Password does not match</div>";
        }
      } else {
        echo "<div class='alert alert-danger'>Email does not match</div>";
      }
    }

    ?>
    <form action="login.php" method="POST">
      <div class="form-group">
        <input type="email" placeholder="Enter Email:" name="email" class="form-control">
      </div>
      <div class="form-group">
        <input type="password" placeholder="Enter Password:" name="password" class="form-control">
      </div>
      <div class="forgot-password">
        <a href="forgot-password.php" class="forgot"><i> Forgot Password</i></a>
      </div>
      <div class="form-btn">
        <input type="submit" value="Login" name="login" class="btn btn-primary">
      </div>
    </form>
    <div class="move-to-login mt-3">
      <p>Not registered yet <a href="registration.php">Register Here</a></p>
    </div>
    <hr>
  </div>
</body>

</html>