<?php
// profile.php - Entry point for the profile page

require_once __DIR__ . '/src/controllers/ProfileController.php';

$controller = new ProfileController();
$controller->handleRequest();
