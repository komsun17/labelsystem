<?php
// print_single.php - Print one label by ID
require_once 'includes/config.php';
$id = (int)($_GET['id'] ?? 0);
if (!$id) { header('Location: index.php'); exit; }
header("Location: print_preview.php?mode=single&id=$id");
