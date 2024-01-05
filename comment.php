<?php
session_start();
include_once "database.php";

$userId = $_SESSION['user'];
function showCmt($conn, $photoId)
{
  $showSql = "SELECT comment.*, account.avatar, account.firstName, account.lastName 
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
function insertCmt($conn, $userId, $photoId, $content)
{
  if ($userId !== null) {
    $insertSql = "INSERT INTO comment(userId, photoId, content) VALUES (?, ?, ?)";
    $stmt = mysqli_prepare($conn, $insertSql);
    mysqli_stmt_bind_param($stmt, "iss", $userId, $photoId, $content);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    // Redirect to comment.php after insertion
    header("Location: comment.php?photoId=" . $photoId);
    exit();
  }
}

if (isset($_POST['send']) && ($_POST['send'])) {
  // Check if photoId is set in $_POST
  $photoId = isset($_POST['photoId']) ? htmlspecialchars($_POST['photoId']) : null;
  $content = $_POST['content'];

  insertCmt($conn, $userId, $photoId, $content);
}

function updateCmt($conn, $cmtId, $userId, $content)
{
  if ($userId !== null) {
    $updateSql = "UPDATE comment SET content = ? WHERE cmtID = ? AND userId = ?";
    $stmt = mysqli_prepare($conn, $updateSql);
    mysqli_stmt_bind_param($stmt, 'sii', $content, $cmtId, $userId);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
  }
}

if (isset($_POST['update']) && ($_POST['update'])) {
  $cmtId = isset($_POST['cmtId']) ? htmlspecialchars($_POST['cmtId']) : null;
  $content = $_POST['content'];

  updateCmt($conn, $cmtId, $userId, $content);
}

function deleteCmt($conn, $cmtId)
{
  // Use prepared statement to prevent SQL injection
  $deleteSql = "DELETE FROM comment WHERE cmtID = ?";
  $stmt = mysqli_prepare($conn, $deleteSql);

  if ($stmt === false) {
    echo "Error preparing delete statement: " . mysqli_error($conn);
    return;
  }

  // Bind parameters
  mysqli_stmt_bind_param($stmt, 'i', $cmtId);

  // Execute the statement
  mysqli_stmt_execute($stmt);

  // Check for errors during execution
  if (mysqli_errno($conn) !== 0) {
    echo "Error deleting comment: " . mysqli_error($conn);
  } else {
    echo "Comment deleted successfully.";
  }

  // Close the statement
  mysqli_stmt_close($stmt);

  // Redirect to comment.php after deletion
  $photoId = isset($_GET['photoId']) ? htmlspecialchars($_GET['photoId']) : null;
  header("Location: comment.php?photoId=" . $photoId);
  exit();
}

// Check if the delete form is submitted
if (isset($_POST['delete']) && ($_POST['delete'])) {
  $cmtId = isset($_POST['cmtId']) ? intval($_POST['cmtId']) : 0;

  if ($cmtId > 0) {
    deleteCmt($conn, $cmtId);
  } else {
    var_dump($cmtId);
    echo "Invalid comment ID.";
  }
}

// Check if photoId is set in $_GET
$photoId = isset($_GET['photoId']) ? htmlspecialchars($_GET['photoId']) : null;
$cmtList = showCmt($conn, $photoId);
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
  <link rel="stylesheet" href="./fonts/themify-icons/themify-icons.css">
  <link rel="stylesheet" href="./css-design/img-description.css">
</head>

<body>
  <form action="comment.php" method="post">
    <input type="hidden" name="userId" value="<?= $userId ?>">
    <input type="hidden" name="photoId" value="<?= $photoId ?>">
    <textarea name="content" id="" cols="50" rows="2" placeholder="Comments"></textarea>
    <input type="submit" value="Send" name="send" class="btn btn-secondary">
  </form>
  <div class="user-cmt">
    <?php
    foreach ($cmtList as $cmt) {
      $avatarPath = $cmt['avatar'];
      echo '<img src="avatar/' . $avatarPath . '" class="img-fluid" alt="User Avatar">';
      echo "<strong>{$cmt['firstName']} {$cmt['lastName']}</strong> {$cmt['content']}";

      // Check if the comment belongs to the current user
      if ($cmt['userId'] == $userId) {
        echo " <button onclick='updateCmt()'><i class='ti-pencil'></i></button>";
      }
      // echo "<div class='cmt-context'>{$cmt['content']}<br> <br></div>";
      // Form update
      echo "<form action='comment.php' id='updateDiv' method='post' class='hidden'>";
      echo "<input type='hidden' name='cmtId' value='{$cmt['cmtID']}'>";
      echo "<textarea name='content' cols='30' rows='3'>{$cmt['content']}</textarea><br>";
      echo "<input type='submit' value='Update' name='update' class='btn btn-secondary'>";
      echo "</form>";

      // Form delete
      echo "<form action='comment.php' id='deleteDiv' method='post' class='hidden'>";
      echo "<input type='hidden' name='cmtId' value='{$cmt['cmtID']}'>";
      echo "<input type='submit' value='Delete' name='delete' class='btn btn-danger'>";
      echo "</form> <br>";
    }
    ?>
  </div>


  <script>
  var display = 0;

  function updateCmt() {
    var updatediv = document.getElementById('updateDiv');
    var deletediv = document.getElementById('deleteDiv');
    if (display == 1) {
      updatediv.classList.remove('hidden');
      deletediv.classList.remove('hidden');
      display = 0;
    } else {
      updatediv.classList.add('hidden');
      deletediv.classList.add('hidden');
      display = 1
    }

  }
  </script>
</body>

</html>