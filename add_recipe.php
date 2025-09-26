<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}
$username = $_SESSION['username'];

// db.php - Database Connection
$host = "localhost";
$user = "root"; 
$pass = "";     
$db   = "user_management";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}
 
// Handle Form Submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $name = $_POST['recipeName'];
  $desc = $_POST['description'];
  $ing  = $_POST['ingredients'];
  $inst = $_POST['instructions'];

  // File upload
  $targetDir = "uploads/";
  if (!is_dir($targetDir)) mkdir($targetDir);
  $fileName = time() . "_" . basename($_FILES["recipeImage"]["name"]);
  $targetFilePath = $targetDir . $fileName;

  if (move_uploaded_file($_FILES["recipeImage"]["tmp_name"], $targetFilePath)) {
  $sql = "INSERT INTO recipes (recipe_name, description, ingredients, instructions, image) 
          VALUES ('$name', '$desc', '$ing', '$inst', '$targetFilePath')";
  $conn->query($sql);
}
}

// Fetch Recipes
$recipes = $conn->query("SELECT * FROM recipes ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="en" style="scroll-behavior: smooth">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>TastyBites | Add Recipe</title>

    <!-- ✅ Global Styles -->
    <link rel="stylesheet" href="assets/style.css">
    <link rel="stylesheet" href="assets/media.css">
    <link
      href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&family=Felipa&display=swap"
      rel="stylesheet"
    />
    <link
      href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css"
      rel="stylesheet"
      crossorigin="anonymous"
    />

    <style>
    /* Hero carousel */
    .carousel-item img { object-fit: cover; height: 450px; border-radius: 12px; }
    .carousel-caption { background: rgba(0,0,0,0.55); padding: 1rem; border-radius: 10px; }
    .carousel-caption h3 { font-family: 'Playfair Display', serif; font-size: 2rem; font-weight: bold; color: #f9c28d; }
    .carousel-caption p { font-size: 1rem; color: #fff; }

    /* Recipe cards */
    .recipe-card { border: none; border-radius: 12px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); transition: transform 0.2s ease; }
    .recipe-card:hover { transform: translateY(-5px); }
    .recipe-card img { border-top-left-radius: 12px; border-top-right-radius: 12px; }

    /* Upload area */
    .upload-box {
      border: 2px dashed #ccc;
      border-radius: 10px;
      padding: 2rem;
      text-align: center;
      cursor: pointer;
      transition: all 0.2s ease;
      background: #fff;
    }
    .upload-box:hover { background: #f1f1f1; border-color: #999; }
    .upload-box input { display: none; }
    </style>
  </head>
  <body class="position-relative">

    <!-- ✅ Navbar -->
    <nav class="px-5 py-2 d-flex nav-xxl align-items-center justify-content-between position-fixed top-0 z-3 bg-light-subtle w-100">
      <div class="flex-row d-flex align-items-center gap-5 w-75">
        <div class="flex-row d-flex align-items-center gap-4 title-div">
          <h1>Tasty Bites</h1>
          <div class="search-div px-2">
            <input type="text" name="search" id="search-bar"/>
            <img src="assets/icons/search-alt-svgrepo-com.svg" alt="Search" class="search"/>
          </div>
        </div>
        <div class="flex-row d-flex align-items-center div-tabs gap-5">
          <a href="dashboard.php" class="tabs">Home</a>
          <a href="#" class="tabs">My Recipe</a>
          <a href="add_recipe.php" class="tabs active">Add Recipe</a>
        </div>
      </div>
      <div class="px-5">
        <h1 class="tabs">Hello, <?php echo htmlspecialchars($username, ENT_QUOTES); ?></h1>      
      </div>
    </nav>

    <!-- Hero Carousel -->
    <div id="heroCarousel" class="carousel slide container py-5 mt-5" data-bs-ride="carousel">
      <div class="carousel-inner">
        <?php 
        $first = true;
        $carousel = $conn->query("SELECT * FROM recipes ORDER BY id DESC LIMIT 2"); 
        while ($row = $carousel->fetch_assoc()) { ?>
          <div class="carousel-item <?php if ($first) { echo 'active'; $first = false; } ?>">
            <img src="<?php echo $row['image']; ?>" class="d-block w-100" alt="Recipe Image">
            <div class="carousel-caption text-start">
              <?php if ($first) { ?>
                <h3>Your Favorite food. Make it good.</h3>
                <p>Discover recipes, share your own, and enjoy cooking like never before.</p>
              <?php } else { ?>
                <h3>Explore New Recipes</h3>
                <p>Find exciting dishes and cooking tips.</p>
              <?php } ?>
            </div>
          </div>
        <?php } ?>
      </div>
      <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
        <span class="carousel-control-prev-icon"></span>
      </button>
      <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
        <span class="carousel-control-next-icon"></span>
      </button>
    </div>

    <!-- Uploaded Recipes -->
    <section class="container py-5">
      <h3 class="mb-3">Uploaded Recipes</h3>
      <div class="row g-3">
        <?php while ($row = $recipes->fetch_assoc()) { ?>
          <div class="col-md-4">
            <div class="card recipe-card">
              <img src="<?php echo $row['image']; ?>" class="card-img-top" alt="Recipe Image">
              <div class="card-body">
                <h5><?php echo $row['recipe_name']; ?></h5>
                <p><?php echo $row['description']; ?></p>
                <small><b>Ingredients:</b><br><?php echo nl2br($row['ingredients']); ?></small><br>
                <small><b>Instructions:</b><br><?php echo nl2br($row['instructions']); ?></small>
              </div>
            </div>
          </div>
        <?php } ?>
      </div>
    </section>

    <!-- Add Recipe Form -->
    <section class="container py-5">
      <h3 class="mb-3">Add/Upload your Recipes</h3>
      <form method="POST" enctype="multipart/form-data">
        <div class="mb-3">
          <label class="form-label">Upload Image</label>
          <div class="upload-box" onclick="document.getElementById('recipeImage').click();">
            <p>📂 Drag or click here to upload</p>
            <input type="file" id="recipeImage" name="recipeImage" accept="image/*" required onchange="previewImage(event)">
          </div>
          <div class="mt-3 text-center">
            <img id="preview" src="" alt="Image Preview" 
                 style="max-width: 250px; border-radius: 10px; display: none;"/>
          </div>
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
          <label class="form-label">Ingredients (bulleted form)</label>
          <textarea class="form-control" name="ingredients" rows="4" maxlength="500" required></textarea>
        </div>
        <div class="mb-3">
          <label class="form-label">Instructions (step by step)</label>
          <textarea class="form-control" name="instructions" rows="6" maxlength="1000" required></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Submit Recipe</button>
      </form>
    </section>

    <!-- ✅ Scripts -->
    <script>
    function previewImage(event) {
      const preview = document.getElementById('preview');
      const file = event.target.files[0];
      if (file) {
        preview.src = URL.createObjectURL(file);
        preview.style.display = "block";
      } else {
        preview.src = "";
        preview.style.display = "none";
      }
    }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
  </body>
</html>
