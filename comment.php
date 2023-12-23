<?php
session_start();
include_once "database.php";

$userId = $_SESSION['user'];

function showCmt($conn, $photoId)
{
  $showSql = "SELECT comment.content, account.avatar, account.firstName, account.lastName 
              FROM comment 
              JOIN account ON comment.userId = account.userId 
              WHERE comment.photoId = ? 
              ORDER BY comment.cmtID DESC";

  $stmt = mysqli_prepare($conn, $showSql);
  mysqli_stmt_bind_param($stmt, "i", $photoId);
  mysqli_stmt_execute($stmt);
  $resultState = mysqli_stmt_get_result($stmt);

  // fetch and return the results
  $cmtList = mysqli_fetch_all($resultState, MYSQLI_ASSOC);

  // Close the statement
  mysqli_stmt_close($stmt);

  // Free the result set
  mysqli_free_result($resultState);

  return $cmtList;
}

// Check if photoId is set in $_GET
$photoId = isset($_GET['photoId']) ? htmlspecialchars($_GET['photoId']) : null;
$cmtList = showCmt($conn, $photoId);

function insertCmt($conn, $userId, $photoId, $content)
{
  if ($userId !== null) {
    $insertSql = "INSERT INTO comment(userId, photoId, content) VALUES (?, ?, ?)";
    $stmt = mysqli_prepare($conn, $insertSql);
    mysqli_stmt_bind_param($stmt, "iss", $userId, $photoId, $content);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
  }
}

if (isset($_POST['send']) && ($_POST['send'])) {
  // Check if photoId is set in $_POST
  $photoId = isset($_POST['photoId']) ? htmlspecialchars($_POST['photoId']) : null;
  $content = $_POST['content'];

  insertCmt($conn, $userId, $photoId, $content);
}
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
  <link rel="stylesheet" href="./css-design/img-description.css">
</head>

<body>
  <form action="comment.php" method="post">
    <input type="hidden" name="userId" value="<?= $userId ?>">
    <input type="hidden" name="photoId" value="<?= $photoId ?>">
    <textarea name="content" id="" cols="30" rows="3" placeholder="Comments"></textarea><br>
    <input type="submit" value="Send" name="send" class="btn btn-secondary">
  </form>
  <hr>
  <div class="user-cmt">
    <?php
    foreach ($cmtList as $cmt) {
      $avatarPath = $cmt['avatar'];
      echo '<img src="avatar/' . $avatarPath . '" class="img-fluid" alt="User Avatar">';
      echo "<strong>{$cmt['firstName']} {$cmt['lastName']}</strong> <br>";
      echo "<div class='cmt-context'>{$cmt['content']}<br> <br></div>";
    }
    ?>
  </div>
</body>

</html>