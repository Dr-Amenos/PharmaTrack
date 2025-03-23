<?php
require_once 'config.php';

$data = json_decode(file_get_contents("php://input"), true);

$nom = clean_input($data['nom']);
$prenom = clean_input($data['prenom']);
$region = clean_input($data['region']);
$latitude = clean_input($data['latitude']);
$longitude = clean_input($data['longitude']);

$regionId = getRegionId($region);
if (!$regionId) {
    echo json_encode(['error' => 'Région invalide']);
    exit;
}

$date_enregistrement = date('Y-m-d H:i:s');
$sql = "INSERT INTO utilisateurs (nom, prenom, region_id, latitude, longitude, date_enregistrement) VALUES ('$nom', '$prenom', $regionId, '$latitude', '$longitude', '$date_enregistrement')";

if ($conn->query($sql) === TRUE) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['error' => 'Erreur lors de l\'enregistrement de l\'utilisateur']);
}
?>