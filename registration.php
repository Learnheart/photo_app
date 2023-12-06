<!-- If user is already logged in -> move directly -->
<?php
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
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Registration Form</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"
    integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
  <link rel="stylesheet" href="./css-design/registration.css">
  <link rel="stylesheet" href="./fonts/themify-icons/themify-icons.css">
</head>

<body>
  <!-- Navbar -->
  <div class="navbar">
    <div class="logo-container">
      <img src="./img/circle.webp" alt="Logo" class="logo">
    </div>
    <div class="icon">
      <i class="ti-home"></i>
    </div>
    <div class="icon">
      <i class="ti-bookmark"></i>
    </div>
  </div>
  <!-- Content -->
  <h1 class="mt-5">Sign Up</h1>
  <hr>
  <div class="container">
    <!-- print: what variable have currently? -->
    <?php
    // print_r($_POST);
    // check if whether the form is submitted?
    if (isset($_POST["submit"])) { //only works when user click submit button
      $firstName = $_POST["firstName"];
      $lastName = $_POST["lastName"];
      $email = $_POST["email"];
      $password = $_POST["password"];
      $confirmPassword = $_POST["confirmPassword"];

      // Encrypt password
      $password_hash = password_hash($password, PASSWORD_DEFAULT);
      // Add validations
      $error = array();
      // Check empty values
      if (empty($firstName) or empty($lastName) or empty($email) or empty($password) or empty($confirmPassword)) {
        array_push($error, "*Required*");
      }
      // Check email form
      if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        array_push($error, "*Invalid Email!*");
      }
      // Check password length
      if (strlen($password) < 8) {
        array_push($error, "*Password length at least 8 characters.*");
      }
      if ($password !== $confirmPassword) {
        array_push($error, "*Confirm password did not match*");
      }
      // Check duplicate email
      require_once "database.php";
      $sql = "SELECT * from account where email = '$email'";
      $result = mysqli_query($conn, $sql);
      $rowCount = mysqli_num_rows($result);
      if ($rowCount > 0) {
        array_push($error, "Email already exists");
      }
      // Display error on screen
      if (count($error) > 0) {
        foreach ($error as $error) {
          echo "<div class='alert alert-danger'>$error</div>";
        }
      }

      // insert into database
      else {
        $sql = "INSERT INTO account (firstName, lastName, email, password) VALUES (?, ?, ?, ?)";
        $init = mysqli_stmt_init($conn);
        $preparedStm = mysqli_stmt_prepare($init, $sql);

        if ($preparedStm) {
          // bind value into sql cmd
          mysqli_stmt_bind_param($init, "ssss", $firstName, $lastName, $email, $password_hash);
          mysqli_stmt_execute($init);
          header("Location: login.php");
        } else {
          die("Something went wrong");
        }
      }
    }
    ?>
    <form action="registration.php" method="post">
      <div class="input-group">
        <input type="text" class="form-control" name="firstName" placeholder="First Name">
        <span></span>
        <input type="text" class="form-control" name="lastName" placeholder="Last Name">
      </div>
      <div class="form-group">
        <input type="email" class="form-control mt-0" name="email" placeholder="Email address">
      </div>
      <div class="form-group">
        <input type="password" class="form-control" name="password" placeholder="Password">
      </div>
      <div class="form-group">
        <input type="password" class="form-control" name="confirmPassword" placeholder="Confirm Password">
      </div>
      <div>
        <input type="checkbox" name="privacy" id="privacy" required>
        <label for="privacy">I agree to term of <a href="#">service and privacy</a></label>
      </div>
      <div class="form-btn">
        <input type="submit" class="btn btn-primary" value="Create Account" name="submit">
      </div>
    </form>
    <div>
      <div class="move-to-login mt-3">
        <p>Already Registered <a href="login.php">Login Here</a></p>
      </div>
      <hr>
    </div>
  </div>
</body>

</html>