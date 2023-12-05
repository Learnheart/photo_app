<?php
session_start();

// Check if the user is logged in
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
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Website</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
</head>

<body>
  <div class="container">
    <h1>Welcome to Admin Dashboard</h1>
    <a href="logout.php" class="btn btn-warning">Logout</a>
  </div>
</body>

</html>