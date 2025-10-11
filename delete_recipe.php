<?php
session_start();
include 'config.php';
if (!isset($_SESSION['username'])) {
  header("Location: login.php");
  exit();
}

$host = "localhost";
$user = "root";
$pass = "";
$db   = "tastybytesdb";
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

if (isset($_GET['id'])) {
  $id = intval($_GET['id']);
  $username = $_SESSION['username'];

  // Check ownership
  $check = $conn->prepare("SELECT image FROM recipes WHERE id = ? AND uploaded_by = ?");
  $check->bind_param("is", $id, $username);
  $check->execute();
  $result = $check->get_result();

  if ($result->num_rows > 0) {
    $recipe = $result->fetch_assoc();
    if (file_exists($recipe['image'])) unlink($recipe['image']);

    $delete = $conn->prepare("DELETE FROM recipes WHERE id = ? AND uploaded_by = ?");
    $delete->bind_param("is", $id, $username);
    $delete->execute();
  }
}
header("Location: my_recipe.php");
exit();
?>