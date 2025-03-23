<?php
require_once 'config.php';
require_once 'functions.php';

$pharmacieId = 1; // Remplacez par l'ID de la pharmacie connectée

$stocks = getPharmacieStock($pharmacieId);
echo json_encode($stocks);
?>