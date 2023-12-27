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
$sql = "SELECT * from category";
$res = mysqli_query($conn, $sql);

// Check if the form for delete is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete-cate'])) {
  $cateId = $_POST['cateID'];

  // delete the category
  $deleteCateSql = "DELETE FROM category WHERE cateID = ?";
  $deleteCateStmt = mysqli_prepare($conn, $deleteCateSql);
  mysqli_stmt_bind_param($deleteCateStmt, "i", $cateId);

  if (mysqli_stmt_execute($deleteCateStmt)) {
    mysqli_stmt_close($deleteCateStmt);
    echo '<script>alert("Category deleted successfully."); window.location.href = "category.php";</script>';
    exit();
  } else {
    echo "Error deleting category: " . mysqli_error($conn);
  }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Document</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"
    integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+jjXkk+Q2h455rYXK/7HAuoJl+0I4" crossorigin="anonymous">
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
        <a tabindex="0" class="ti-settings" role="button" data-bs-toggle="popover" data-bs-trigger="focus"
          data-bs-title="Logout" data-bs-content=""></a>
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
            <input type="text" class="form-control" placeholder="Search keyword" aria-label="Search"
              aria-describedby="search-icon" name="searchKeyword">
            <button class="input-group-text" id="search-icon" type="submit">
              <i class="ti-search"></i>
            </button>
          </div>
        </form>
        <!-- Create category btn -->
        <div class="create-icon" onclick="enableCreate()">
          <i class="ti-plus"></i>
        </div>
        <!-- User information -->
        <h2>Admin:
          <?php
          echo $_SESSION["firstName"] . " " . $_SESSION["lastName"];
          ?>
        </h2>
      </div>
    </nav>
    <hr class="hr-nav">
    <div id="create-cate" class="hidden">
      <button type="button" class="btn-close mb-5" aria-label="Close" onclick="closeUpdateForm()"></button>
      <form method="post" action="category.php">
        <div class="form-group">
          <label for="cateName" class="mb-3">Create New Category</label> <br>
          <input type="text" class="form-control" id="cateName" name="cateName" placeholder="Add new category" required>
        </div>
        <br>
        <div class="form-group">
          <input type="submit" name="createCate" class="btn btn-primary" value="Create">
        </div>
      </form>
    </div>
    <div id="category-control">
      <?php
      // Create new category
      if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['createCate'])) {
        $cateName = mysqli_real_escape_string($conn, $_POST['cateName']);
        $checkNameSql = "SELECT * from category where cateName = '$cateName'";
        $result = mysqli_query($conn, $checkNameSql);
        $rowCount = mysqli_num_rows($result);
        $error = array();

        if ($rowCount > 0) {
          array_push($error, "Already exists");
        }
        if (empty($cateName)) {
          echo 'Required';
        }
        // Display error on screen
        if (count($error) > 0) {
          foreach ($error as $error) {
            echo "<div id='error-alert' class='alert alert-danger'>$error</div>";
          }
        } else {
          $insertSql = "INSERT INTO category (cateName) VALUES (?)";
          $stmtInsert = mysqli_prepare($conn, $insertSql);
          mysqli_stmt_bind_param($stmtInsert, 's', $cateName);
          mysqli_stmt_execute($stmtInsert);
          mysqli_stmt_close($stmtInsert);

          echo '<script>alert("Category created successfully."); window.location.href = "category.php";</script>';
          exit();
        }
      }
      ?>
      <table class="table">
        <thead>
          <tr>
            <th scope="col"></th>
            <th scope="col">Category</th>
            <th scope="col">Created Date</th>
            <th scope="col">Activity</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $counter = 1; // Counter to determine when to start a new row
          while ($data = mysqli_fetch_assoc($res)) {
            // Fetch user's avatar
            $cateId = $data['cateID'];
            $nameSql = "SELECT cateName FROM category WHERE cateID = ? ORDER BY date DESC";
            $stmtName = mysqli_prepare($conn, $nameSql);
            mysqli_stmt_bind_param($stmtName, "i", $cateId);
            mysqli_stmt_execute($stmtName);

            $resName = mysqli_stmt_get_result($stmtName);

            // Close the statement
            mysqli_stmt_close($stmtName);
          ?>
          <tr>
            <th scope="row"><?= $counter ?></th>
            <td>
              <div class="row">
                <div class="col mt-3">
                  <div class="name-list">
                    <?= $data['cateName']; ?>
                  </div>
                </div>
              </div>
            </td>
            <!-- Include other user-related data in the respective columns -->
            <td><?= $data['date']; ?></td>
            <td>
              <div class="dropdown">
                <a class="btn btn-secondary dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown"
                  aria-expanded="false">
                  Activity
                </a>
                <ul class="dropdown-menu">
                  <li onclick="enableUpdate()">Edit</li>
                  <li>
                    <form action="category.php" method="post">
                      <input type="hidden" name="cateID" value="<?= $cateId ?>">
                      <div class="form-btn">
                        <input type="submit" class="btn" name="delete-cate" value="Delete">
                      </div>
                    </form>
                  </li>
                </ul>
              </div>
            </td>
            <!-- Update form -->
            <div id="update-cate" class="hidden">
              <button type="button" class="btn-close mb-3" aria-label="Close" onclick="closeUpdateForm()"></button>
              <form method="post" action="category.php">
                <div class="form-group">
                  <label for="cateName" class="mb-3">Update Category Name</label> <br>
                  <input type="hidden" name="cateID" value="<?= $cateId ?>">
                  <input type="text" class="form-control" id="cateName" name="cateName" placeholder="Update category"
                    value="<?= $data['cateName'] ?>" required>
                  <div class="form-group mt-4">
                    <input type="submit" name="update-cate" class="btn btn-primary" value="Update">
                  </div>
                </div>
              </form>
            </div>
          </tr>
          <?php
            $counter++;
          }
          ?>
          <?php
          // Update category
          if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update-cate'])) {
            $cateId = $_POST['cateID'];
            $cateName = $_POST['cateName'];

            $checkNameSql = "SELECT * from category where cateName = '$cateName'";
            $result = mysqli_query($conn, $checkNameSql);
            $rowCount = mysqli_num_rows($result);
            $error = array();
            if ($rowCount > 0) {
              array_push($error, "Already exists");
            }
            if (empty($cateName)) {
              echo 'Required';
            }
            // Display error on screen
            if (count($error) > 0) {
              foreach ($error as $error) {
                echo "<div id='error-alert' class='alert alert-danger'>$error</div>";
              }
            } else {
              // update the category
              $updateCateSql = "UPDATE category SET cateName = ? WHERE cateID = ?";
              $updateCateStmt = mysqli_prepare($conn, $updateCateSql);
              mysqli_stmt_bind_param($updateCateStmt, "si", $cateName, $cateId);

              if (mysqli_stmt_execute($updateCateStmt)) {
                mysqli_stmt_close($updateCateStmt);
                echo '<script>alert("Category updated successfully."); window.location.href = "category.php";</script>';
                exit();
              } else {
                echo "Error updating category: " . mysqli_error($conn);
              }
            }
          }
          ?>
        </tbody>
      </table>
    </div>
  </div>

  <script>
  display = 0;
  // Wait for the document to be fully loaded
  document.addEventListener("DOMContentLoaded", function() {
    // Automatically hide the alert after 10 seconds
    setTimeout(function() {
      var errorAlert = document.getElementById('error-alert');
      if (errorAlert) {
        errorAlert.style.display = 'none';
      }
    }, 1000); // 10000 milliseconds = 10 seconds
  });

  function closeUpdateForm() {
    var updateDiv = document.getElementById('update-cate');
    var createDiv = document.getElementById('create-cate');
    updateDiv.classList.add('hidden');
    createDiv.classList.add('hidden');
  }

  function enableUpdate() {
    var updateDiv = document.getElementById('update-cate');
    if (display == 1) {
      updateDiv.classList.remove('hidden');
      display = 0;
    } else {

      updateDiv.classList.add('hidden');
      display = 1
    }
  }

  function enableCreate() {
    var createDiv = document.getElementById('create-cate');
    if (display == 1) {
      createDiv.classList.remove('hidden');
      display = 0;
    } else {
      createDiv.classList.add('hidden');
      display = 1
    }
  };
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