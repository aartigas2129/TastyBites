<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}
$username = $_SESSION['username'];

// DB connection
$host = "localhost";
$user = "root";
$pass = "";
$db   = "user_management";
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// Fetch user’s recipes
$sql = "SELECT * FROM recipes WHERE uploaded_by = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>TastyBites | My Recipes</title>
  <link rel="stylesheet" href="assets/style.css">
  <link rel="stylesheet" href="assets/media.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
  <!-- Navbar -->
  <nav class="px-5 py-2 d-flex nav-xxl align-items-center justify-content-between position-fixed top-0 z-3 bg-light-subtle w-100">
    <div class="d-flex gap-5 w-75">
      <h1>TastyBites</h1>
      <div class="d-flex gap-5">
        <a href="dashboard.php" class="tabs">Home</a>
        <a href="my_recipe.php" class="tabs active">My Recipe</a>
        <a href="add_recipe.php" class="tabs">Add Recipe</a>
      </div>
    </div>
    <div><h1 class="tabs">Hello, <?php echo htmlspecialchars($username); ?>!</h1></div>
  </nav>

  <!-- My Recipes Grid -->
  <section class="container-fluid py-5 mt-5 pt-5 px-4">
    <h3 class="mb-4">My Uploaded Recipes</h3>
    <div class="row g-3">
      <?php while ($row = $result->fetch_assoc()) { ?>
        <div class="col-12 col-sm-6 col-md-4 col-lg-3">
          <a href="delete_recipe.php?id=<?php echo $row['id']; ?>" class="text-decoration-none">
            <div class="card recipe-card h-100">
              <img src="<?php echo $row['image']; ?>" class="card-img-top" alt="Recipe Image">
              <div class="card-body">
                <h5 class="text-dark"><?php echo htmlspecialchars($row['recipe_name']); ?></h5>
              </div>
            </div>
          </a>
        </div>
      <?php } ?>
    </div>
  </section>
</body>
</html>
