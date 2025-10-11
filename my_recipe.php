<?php
session_start();
include 'config.php';
if (!isset($_SESSION['username'])) {
  header("Location: login.php");
  exit();
}
$username = $_SESSION['username'];

// DB connection
$host = "localhost";
$user = "root";
$pass = "";
$db   = "tastybytesdb";
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// Fetch user’s uploaded recipes
$sql = "SELECT * FROM recipes WHERE uploaded_by = ? ORDER BY id DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en" style="scroll-behavior: smooth">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>TastyBites | My Recipes</title>
  <link rel="stylesheet" href="assets/style.css">
  <link rel="stylesheet" href="assets/media.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&family=Felipa&display=swap" rel="stylesheet"/>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet"/>
</head>
<body class="position-relative">

  <!-- Navigation -->
  <nav class="px-5 py-2 d-flex nav-xxl align-items-center justify-content-between position-fixed top-0 z-3 bg-light-subtle w-100">
    <div class="flex-row d-flex align-items-center gap-5 w-75">
      <div class="flex-row d-flex align-items-center gap-4 title-div">
        <h1>TastyBites</h1>
        <div class="search-div px-2">
          <input type="text" name="search" id="search-bar"/>
          <img src="assets/icons/search-alt-svgrepo-com.svg" alt="Search" class="search"/>
        </div>
      </div>
      <div class="flex-row d-flex align-items-center div-tabs gap-5">
        <a href="dashboard.php" class="tabs">Home</a>
        <a href="my_recipe.php" class="tabs active">My Recipe</a>
        <a href="add_recipe.php" class="tabs">Add Recipe</a>
      </div>
    </div>
    <div class="px-5">
      <h1 class="tabs">Hello, <?php echo htmlspecialchars($username, ENT_QUOTES); ?></h1>      
    </div>
  </nav>

  <!-- Hero Section -->
  <div class="homer mt-5 pt-5">
    <div class="homer-item-1">
      <div class="homer-inside-item-1">
        <h1 class="homer-h1">Your Creations,</h1>
        <h1 class="homer-h1">Your Flavors.</h1>
        <p class="homer-para">Manage your uploaded recipes and make changes whenever you like.</p>
      </div>
      <div class="homer-inside-item-2 carousel slide" id="carouselExampleIndicators">
        <div class="carousel-inner mt-4 rounded-4">
          <div class="carousel-item active"><img src="assets/images/hero.png" class="d-block w-100" alt="Hero 1"></div>
          <div class="carousel-item"><img src="assets/images/hero-2.jpg" class="d-block w-100" alt="Hero 2"></div>
          <div class="carousel-item"><img src="assets/images/hero-3.jpg" class="d-block w-100" alt="Hero 3"></div>
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide="prev">
          <span class="carousel-control-prev-icon"></span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide="next">
          <span class="carousel-control-next-icon"></span>
        </button>
      </div>
    </div>
  </div>

  <!-- My Recipes Section -->
  <section class="py-5" style="max-width: 100%; padding-left: 4rem; padding-right: 4rem;">
    <h3 class="mb-3">My Uploaded Recipes</h3>
    <div class="row g-3">
      <?php if ($result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
          <div class="col-md-4">
            <div class="card recipe-card h-100 shadow-sm" data-bs-toggle="modal" data-bs-target="#recipeModal<?php echo $row['id']; ?>" style="cursor:pointer;">
              <img src="<?php echo htmlspecialchars($row['image']); ?>" class="card-img-top" alt="Recipe Image">
              <div class="card-body">
                <h5 class="text-dark"><?php echo htmlspecialchars($row['recipe_name']); ?></h5>
                <p class="text-secondary small mb-0"><?php echo htmlspecialchars(mb_strimwidth($row['description'], 0, 100, '...')); ?></p>
              </div>
            </div>
          </div>

          <!-- Modal for Each Recipe -->
          <div class="modal fade" id="recipeModal<?php echo $row['id']; ?>" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title"><?php echo htmlspecialchars($row['recipe_name']); ?></h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                  <img src="<?php echo htmlspecialchars($row['image']); ?>" class="img-fluid rounded mb-3" alt="Recipe Image">
                  <p><strong>Description:</strong> <?php echo nl2br(htmlspecialchars($row['description'])); ?></p>
                  <p><strong>Ingredients:</strong><br><?php echo nl2br(htmlspecialchars($row['ingredients'])); ?></p>
                  <p><strong>Instructions:</strong><br><?php echo nl2br(htmlspecialchars($row['instructions'])); ?></p>
                </div>
                <div class="modal-footer">
                  <a href="delete_recipe.php?id=<?php echo $row['id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this recipe?');">Delete</a>
                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
              </div>
            </div>
          </div>

        <?php endwhile; ?>
      <?php else: ?>
        <p class="text-muted">You haven't uploaded any recipes yet. Go to <a href="add_recipe.php">Add Recipe</a> to upload one.</p>
      <?php endif; ?>
    </div>
  </section>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>