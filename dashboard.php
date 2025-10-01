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
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

// Fetch recipes
$recipes = $conn->query("SELECT * FROM recipes ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="en" style="scroll-behavior: smooth">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>TastyBites | Dashboard</title>
  <link rel="stylesheet" href="assets/style.css">
  <link rel="stylesheet" href="assets/media.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&family=Felipa&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
  <!-- Navigation -->
  <nav class="px-5 py-2 d-flex nav-xxl align-items-center justify-content-between position-fixed top-0 z-3 bg-light-subtle w-100">
    <div class="flex-row d-flex align-items-center gap-5 w-75">
      <div class="flex-row d-flex align-items-center gap-4 title-div">
        <h1>TastyBites</h1>
        <div class="search-div px-2">
          <input type="text" id="search-bar"/>
          <img src="assets/icons/search-alt-svgrepo-com.svg" alt="Search" class="search"/>
        </div>
      </div>
      <div class="flex-row d-flex align-items-center div-tabs gap-5">
        <a href="dashboard.php" class="tabs active">Home</a>
        <a href="my_recipe.php" class="tabs">My Recipe</a>
        <a href="add_recipe.php" class="tabs">Add Recipe</a>
      </div>
    </div>
    <div class="px-5">
      <h1 class="tabs">Hello, <?php echo htmlspecialchars($username, ENT_QUOTES); ?></h1>      
    </div>
  </nav>

  <!-- Hero Section (Carousel) -->
  <div class="homer mt-5 pt-5">
    <div class="homer-item-1">
      <div class="homer-inside-item-1">
        <h1 class="homer-h1">Your Favorite food.</h1>
        <h1 class="homer-h1">Make it good.</h1>
        <p class="homer-para">Discover, cook, and share recipes with the TastyBites community.</p>
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

  <!-- Discover Section -->
  <section class="discover py-5" style="max-width: 100%; padding-left: 4rem; padding-right: 4rem;">
    <h1 class="fs-3">Discover new recipes</h1>
    <div class="row g-3">
      <?php while ($row = $recipes->fetch_assoc()) { ?>
        <div class="col-md-3">
          <div class="card recipe-card h-100">
            <img src="<?php echo $row['image']; ?>" class="card-img-top" alt="Recipe Image">
            <div class="card-body">
              <h5><?php echo htmlspecialchars($row['recipe_name']); ?></h5>
              <p><?php echo htmlspecialchars($row['description']); ?></p>
            </div>
          </div>
        </div>
      <?php } ?>
    </div>
  </section>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
