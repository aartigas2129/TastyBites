<?php
session_start();
include 'config.php'; // Make sure config.php has your DB connection

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}
$username = $_SESSION['username'];

// Database connection
$host = "localhost";
$user = "root";
$pass = "";
$db   = "tastybytesdb";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $recipeName   = $_POST['recipeName'];
    $description  = $_POST['description'];
    $ingredients  = $_POST['ingredients'];
    $instructions = $_POST['instructions'];

    $targetDir = "uploads/";
    if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);

    $fileName = time() . "_" . basename($_FILES["recipeImage"]["name"]);
    $targetFilePath = $targetDir . $fileName;

    if (move_uploaded_file($_FILES["recipeImage"]["tmp_name"], $targetFilePath)) {
        // Insert into database using correct column names
        $sql = "INSERT INTO recipes (recipe_name, description, ingredients, instructions, image, uploaded_by)
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssss", $recipeName, $description, $ingredients, $instructions, $targetFilePath, $username);
        $stmt->execute();
        $stmt->close();
    }
}

// Fetch recipes for listing
$recipes = $conn->query("SELECT * FROM recipes ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="en" style="scroll-behavior: smooth">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>TastyBites | Add Recipe</title>
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
      <a href="my_recipe.php" class="tabs">My Recipe</a>
      <a href="add_recipe.php" class="tabs active">Add Recipe</a>
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
      <h1 class="homer-h1">Your Favorite food.</h1>
      <h1 class="homer-h1">Make it good.</h1>
      <p class="homer-para">Upload and share your recipes with the TastyBites community.</p>
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

<!-- Uploaded Recipes Section -->
<section class="py-5" style="max-width: 100%; padding-left: 4rem; padding-right: 4rem;">
  <h3 class="mb-3">Uploaded Recipes</h3>
  <div class="row g-3">
    <?php while ($row = $recipes->fetch_assoc()) { ?>
      <div class="col-md-4">
        <div class="card recipe-card h-100 shadow-sm">
          <img src="<?php echo htmlspecialchars($row['image']); ?>" class="card-img-top" alt="Recipe Image">
          <div class="card-body">
            <h5 class="fw-bold"><?php echo htmlspecialchars($row['recipe_name']); ?></h5>
            <p><?php echo htmlspecialchars($row['description']); ?></p>
            <small><b>Ingredients:</b><br><?php echo nl2br(htmlspecialchars($row['ingredients'])); ?></small><br><br>
            <small><b>Instructions:</b><br><?php echo nl2br(htmlspecialchars($row['instructions'])); ?></small><br><br>
            <small><b>Uploaded by:</b> <?php echo htmlspecialchars($row['uploaded_by']); ?></small>
          </div>
        </div>
      </div>
    <?php } ?>
  </div>
</section>

<!-- Upload Form Section -->
<section class="py-5" style="max-width: 100%; padding-left: 4rem; padding-right: 4rem;">
  <h3 class="mb-3">Add/Upload your Recipes</h3>
  <form method="POST" enctype="multipart/form-data">
    <div class="mb-3">
      <label class="form-label">Upload Image</label>
      <input type="file" class="form-control" name="recipeImage" accept="image/*" required>
    </div>
    <div class="mb-3">
      <label class="form-label">Recipe Name</label>
      <input type="text" class="form-control" name="recipeName" required>
    </div>
    <div class="mb-3">
      <label class="form-label">Description</label>
      <textarea class="form-control" name="description" rows="2" maxlength="250" required></textarea>
    </div>
    <div class="mb-3">
      <label class="form-label">Ingredients</label>
      <textarea class="form-control" name="ingredients" rows="4" maxlength="500" required></textarea>
    </div>
    <div class="mb-3">
      <label class="form-label">Instructions</label>
      <textarea class="form-control" name="instructions" rows="6" maxlength="1000" required></textarea>
    </div>
    <button type="submit" class="btn btn-primary">Submit Recipe</button>
  </form>
</section>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
