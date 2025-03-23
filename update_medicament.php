<?php
require_once 'config.php';
require_once 'functions.php';

$data = json_decode(file_get_contents("php://input"), true);

$nom = clean_input($data['nom']);
$quantite = clean_input($data['quantite']);
$prix = clean_input($data['prix']);
$pharmacieId = 1; // Remplacez par l'ID de la pharmacie connectée

if (updateMedicamentStock($pharmacieId, $nom, $quantite, $prix)) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['error' => 'Erreur lors de la mise à jour du médicament']);
}
?>