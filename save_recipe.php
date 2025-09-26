<?php
session_start();
require "db.php"; // your database connection

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $recipe_name = $_POST['recipe_name'];
    $description = $_POST['description'];
    $ingredients = $_POST['ingredients'];
    $instructions = $_POST['instructions'];
    $username = $_SESSION['username'];

    // Handle Image Upload
    $image_name = $_FILES['image']['name'];
    $image_tmp = $_FILES['image']['tmp_name'];
    $upload_dir = "uploads/";

    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    $image_path = $upload_dir . time() . "_" . basename($image_name);
    move_uploaded_file($image_tmp, $image_path);

    // Save to Database
    $stmt = $conn->prepare("INSERT INTO recipes (username, recipe_name, description, ingredients, instructions, image) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $username, $recipe_name, $description, $ingredients, $instructions, $image_path);

    if ($stmt->execute()) {
        header("Location: dashboard.php?success=1");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }
}
?>
