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

// --- NEW SEARCH AND FILTER LOGIC for 'My Recipes' ---
$searchQuery = trim($_GET['search_query'] ?? '');
$filterType = $_GET['type'] ?? 'all';

// Base SQL query ALWAYS filters by the logged-in user for security
$sql = "SELECT * FROM recipes WHERE uploaded_by = ?";
$params = [$username];
$types = 's';

// Add search query condition if it exists
if (!empty($searchQuery)) {
    $sql .= " AND recipe_name LIKE ?";
    $params[] = "%" . $searchQuery . "%";
    $types .= 's';
}

// Add recipe type condition if it exists
if ($filterType !== 'all') {
    $sql .= " AND recipe_type = ?";
    $params[] = $filterType;
    $types .= 's';
}

$sql .= " ORDER BY id DESC";

// Prepare and execute the final query
$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
// --- END OF NEW LOGIC ---

$recipeTypes = ['Breakfast', 'Lunch', 'Dinner', 'Dessert', 'Snack', 'Appetizer', 'Drinks'];
?>
<!DOCTYPE html>
<html lang="en" style="scroll-behavior: smooth">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Tasty Bites | My Recipes</title>
  <link rel="stylesheet" href="assets/style.css">
  <link rel="stylesheet" href="assets/media.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&family=Felipa&display=swap" rel="stylesheet"/>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet"/>
</head>
<body class="position-relative">

  <nav class="px-5 py-2 d-flex nav-xxl align-items-center justify-content-between position-fixed top-0 z-3 bg-light-subtle w-100">
    <div class="flex-row d-flex align-items-center gap-5 w-75">
      <div class="flex-row d-flex align-items-center gap-4 title-div">
        <h1>Tasty Bites</h1>
        <form method="GET" action="my_recipe.php" class="search-div px-2">
            <input type="text" name="search_query" id="search-bar" placeholder="Search my recipes..." value="<?php echo htmlspecialchars($searchQuery); ?>"/>
            <button type="submit" style="background:none; border:none; padding:0;">
                <img src="assets/icons/search-alt-svgrepo-com.svg" alt="Search" class="search"/>
            </button>
        </form>
      </div>
      <div class="flex-row d-flex align-items-center div-tabs gap-5">
        <a href="dashboard.php" class="tabs">Home</a>
        <a href="my_recipe.php" class="tabs active">My Recipe</a>
        <a href="add_recipe.php" class="tabs">Add Recipe</a>
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
        <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide="prev"><span class="carousel-control-prev-icon"></span></button>
        <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide="next"><span class="carousel-control-next-icon"></span></button>
      </div>
    </div>
  </div>

  <section class="py-5" style="max-width: 100%; padding-left: 4rem; padding-right: 4rem;">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0">My Uploaded Recipes</h3>
        <form method="GET" class="d-flex align-items-center gap-2">
            <input type="hidden" name="search_query" value="<?php echo htmlspecialchars($searchQuery); ?>">
            <select class="form-select w-auto" name="type" onchange="this.form.submit()">
                <option value="all" <?php if ($filterType == 'all') echo 'selected'; ?>>All Types</option>
                <?php foreach ($recipeTypes as $type): ?>
                <option value="<?php echo $type; ?>" <?php if ($filterType == $type) echo 'selected'; ?>><?php echo $type; ?></option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>

    <div class="row g-4">
      <?php if ($result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
          <div class="col-12 col-md-6">
            <div class="card recipe-card h-100 shadow-sm" data-bs-toggle="modal" data-bs-target="#recipeModal<?php echo $row['id']; ?>" style="cursor:pointer;">
              <img src="<?php echo htmlspecialchars($row['image']); ?>" class="card-img-top" alt="Recipe Image" style="height: 250px; object-fit: cover;">
              <div class="card-body">
                <h5 class="card-title"><?php echo htmlspecialchars($row['recipe_name']); ?></h5>
                <span class="badge bg-info mb-2"><?php echo htmlspecialchars($row['recipe_type']); ?></span>
                <p class="card-text text-secondary small mb-0"><?php echo htmlspecialchars(mb_strimwidth($row['description'], 0, 120, '...')); ?></p>
              </div>
            </div>
          </div>

          <div class="modal fade" id="recipeModal<?php echo $row['id']; ?>" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-xl">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title"><?php echo htmlspecialchars($row['recipe_name']); ?></h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                  <img src="<?php echo htmlspecialchars($row['image']); ?>" class="img-fluid rounded mb-4" alt="Recipe Image">
                  <p><strong>Description:</strong><br><?php echo nl2br(htmlspecialchars($row['description'])); ?></p><hr>
                  <p><strong>Ingredients:</strong><br><?php echo nl2br(htmlspecialchars($row['ingredients'] ?? 'Not provided')); ?></p><hr>
                  <p><strong>Instructions:</strong><br><?php echo nl2br(htmlspecialchars($row['instructions'] ?? 'Not provided')); ?></p>
                </div>
                <div class="modal-footer">
                  <a href="delete_recipe.php?id=<?php echo $row['id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this recipe?');">Delete</a>
                  <a href="edit_recipe.php?id=<?php echo $row['id']; ?>" class="btn btn-warning">Edit Recipe</a>
                </div>
              </div>
            </div>
          </div>

        <?php endwhile; ?>
      <?php else: ?>
        <p class="text-muted">You haven't uploaded any recipes matching your criteria.</p>
      <?php endif; ?>
    </div>
  </section>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>