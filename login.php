<!-- If user is already logged in -> move directly -->
<?php
session_start();
// Check authorize 
if (isset($_SESSION["user"])) {
  header("Location: homepage.php");
}
?>

<?php
if (isset($_POST["login"])) {
  include "database.php";
  $email = $_POST["email"];
  $password = $_POST["password"];
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
      session_start();
      $_SESSION["user"] = "yes"; //if user have some value -> access
      header("Location: homepage.php");
      die();
    } else {
      echo "<div class='alert alert-danger'>Password does not match</div>";
    }
  } else {
    echo "<div class='alert alert-danger'>Email does not match</div>";
  }
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
  <link rel="stylesheet" href="registration.css">
</head>

<body>
  <h1>SIGN IN</h1>
  <div class="container">
    <form action="login.php" method="POST">
      <div class="form-group">
        <input type="email" placeholder="Enter Email:" name="email" class="form-control">
      </div>
      <div class="form-group">
        <input type="password" placeholder="Enter Password:" name="password" class="form-control">
      </div>
      <div class="form-btn">
        <input type="submit" value="Login" name="login" class="btn btn-primary">
      </div>
    </form>
    <div>
      <p>Not registered yet <a href="registration.php">Register Here</a></p>
    </div>
  </div>
</body>

</html>