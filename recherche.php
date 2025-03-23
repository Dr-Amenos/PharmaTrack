<?php
require_once 'config.php';
require_once 'functions.php';

$nom = clean_input($_GET['nom']);
$userLat = clean_input($_GET['latitude']);
$userLng = clean_input($_GET['longitude']);
$regionId = clean_input($_GET['region_id']);

$results = searchMedicament($nom, $userLat, $userLng, $regionId);
echo json_encode($results);
?>