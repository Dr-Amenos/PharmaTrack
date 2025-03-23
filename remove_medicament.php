<?php
require_once 'config.php';
require_once 'functions.php';

$data = json_decode(file_get_contents("php://input"), true);

$nom = clean_input($data['nom']);
$pharmacieId = 1; // Remplacez par l'ID de la pharmacie connectée

if (removeMedicamentFromStock($pharmacieId, $nom)) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['error' => 'Erreur lors de la suppression du médicament']);
}
?>