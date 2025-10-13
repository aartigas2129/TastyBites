<?php
session_start();
include 'config.php'; 

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

// Handle form submission for ADDING a recipe
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_recipe'])) {
    $recipeName   = $_POST['recipeName'];
    $description  = $_POST['description'];
    $recipeType   = $_POST['recipeType'];
    $ingredients  = $_POST['ingredients'];
    $instructions = $_POST['instructions'];
    $targetDir = "uploads/";
    if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);
    $fileName = time() . "_" . basename($_FILES["recipeImage"]["name"]);
    $targetFilePath = $targetDir . $fileName;

    if (move_uploaded_file($_FILES["recipeImage"]["tmp_name"], $targetFilePath)) {
        $sql = "INSERT INTO recipes (recipe_name, description, recipe_type, ingredients, instructions, image, uploaded_by) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssss", $recipeName, $description, $recipeType, $ingredients, $instructions, $targetFilePath, $username);
        if ($stmt->execute()) {
            header("Location: my_recipe.php?status=success"); // Redirect to My Recipes page
            exit();
        }
        $stmt->close();
    }
}

// MODIFIED: Fetch only the current user's recipes for the list
$sql = "SELECT * FROM recipes WHERE uploaded_by = ? ORDER BY id DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$recipes = $stmt->get_result();

$recipeTypes = ['Breakfast', 'Lunch', 'Dinner', 'Dessert', 'Snack', 'Appetizer', 'Drinks'];
?>
<!DOCTYPE html>
<html lang="en" style="scroll-behavior: smooth">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Tasty Bites | Add Recipe</title>
    <link rel="stylesheet" href="assets/style.css">
    <link rel="stylesheet" href="assets/media.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&family=Felipa&display=swap" rel="stylesheet"/>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet"/>
</head>
<body class="position-relative">

<nav class="px-5 py-2 d-flex nav-xxl align-items-center justify-content-between position-fixed top-0 z-3 bg-light-subtle w-100">
  <div class="flex-row d-flex align-items-center gap-5 w-75">
    <div class="flex-row d-flex align-items-center gap-4 title-div"><h1>Tasty Bites</h1></div>
    <div class="flex-row d-flex align-items-center div-tabs gap-5">
      <a href="dashboard.php" class="tabs">Home</a>
      <a href="my_recipe.php" class="tabs">My Recipe</a>
      <a href="add_recipe.php" class="tabs active">Add Recipe</a>
    </div>
  </div>
  <div class="px-5 d-flex align-items-center gap-3">
    <span class="tabs text-nowrap">Hello, <?php echo htmlspecialchars($username, ENT_QUOTES); ?></span>
    <a href="logout.php" class="btn btn-sm btn-outline-secondary">Logout</a>
  </div>
</nav>

<div class="homer mt-5 pt-5">
  <div class="homer-item-1">
    <div class="homer-inside-item-1">
      <h1 class="homer-h1">Share Your Passion.</h1>
      <h1 class="homer-h1">Inspire Others.</h1>
      <p class="homer-para">Upload your favorite recipes and build your personal digital cookbook.</p>
    </div>
    <div class="homer-inside-item-2 carousel slide" id="carouselExampleIndicators">
      <div class="carousel-inner mt-4 rounded-4">
        <div class="carousel-item active"><img src="assets/images/hero.png" class="d-block w-100" alt="Hero 1"></div>
        <div class="carousel-item"><img src="assets/images/hero-2.jpg" class="d-block w-100" alt="Hero 2"></div>
        <div class="carousel-item"><img src="assets/images/hero-3.jpg" class="d-block w-100" alt="Hero 3"></div>
      </div>
      <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide="prev"><span class="carousel-control-prev-icon"></span></button>
      <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide="next"><span class="carousel-control-next-icon"></span></button>
    </div>
  </div>
</div>

<section class="py-5 bg-light" style="max-width: 100%; padding-left: 4rem; padding-right: 4rem;">
    <div class="container-fluid">
        <h3 class="mb-4">Add Your Recipe</h3>
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <form method="POST" enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-md-4 mb-3"><label class="form-label">Upload Image</label><input type="file" class="form-control" name="recipeImage" accept="image/*" required></div>
                        <div class="col-md-4 mb-3"><label class="form-label">Recipe Name</label><input type="text" class="form-control" name="recipeName" required></div>
                        <div class="col-md-4 mb-3"><label class="form-label">Recipe Type</label><select class="form-select" name="recipeType" required><option value="" disabled selected>Select a type...</option><?php foreach ($recipeTypes as $type): ?><option value="<?php echo $type; ?>"><?php echo $type; ?></option><?php endforeach; ?></select></div>
                    </div>
                    <div class="mb-3"><label class="form-label">Description</label><textarea class="form-control" name="description" rows="2" required></textarea></div>
                    <div class="mb-3"><label class="form-label">Ingredients (one per line)</label><textarea class="form-control" name="ingredients" rows="5" required></textarea></div>
                    <div class="mb-3"><label class="form-label">Instructions</label><textarea class="form-control" name="instructions" rows="7" required></textarea></div>
                    <button type="submit" name="submit_recipe" class="btn" style="background-color: #F09D58; border-color: #F09D58; color: #fff;">Submit Recipe</button>
                </form>
            </div>
        </div>
    </div>
</section>

<!-- MODIFIED: This section now only shows the user's recipes -->
<section class="py-5" style="max-width: 100%; padding-left: 4rem; padding-right: 4rem;">
    <h3 class="mb-4">Recently Added by You</h3>
    <div class="row g-4">
        <?php if ($recipes->num_rows > 0): ?>
            <?php while ($row = $recipes->fetch_assoc()): ?>
            <div class="col-md-4">
                <div class="card recipe-card h-100 shadow-sm">
                <img src="<?php echo htmlspecialchars($row['image']); ?>" class="card-img-top" alt="Recipe Image" style="height: 220px; object-fit: cover;">
                <div class="card-body">
                    <h5 class="card-title"><?php echo htmlspecialchars($row['recipe_name']); ?></h5>
                    <span class="badge bg-secondary mb-2"><?php echo htmlspecialchars($row['recipe_type']); ?></span>
                    <p class="card-text small"><?php echo htmlspecialchars(mb_strimwidth($row['description'], 0, 100, '...')); ?></p>
                </div>
                </div>
            </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p class="text-muted">You haven't added any recipes yet.</p>
        <?php endif; ?>
    </div>
</section>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>