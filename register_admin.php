<?php
require_once 'config.php';
require_once 'functions.php';

$data = json_decode(file_get_contents("php://input"), true);

$nomPharmacie = clean_input($data['nomPharmacie']);
$region = clean_input($data['region']);
$jourRepos = clean_input($data['jourRepos']);
$telephone = clean_input($data['telephone']);
$email = clean_input($data['email']);
$password = clean_input($data['password']);
$latitude = clean_input($data['latitude']);
$longitude = clean_input($data['longitude']);

if (emailExists($email)) {
    echo json_encode(['error' => 'Cet email est déjà utilisé']);
    exit;
}

$regionId = getRegionId($region);
if (!$regionId) {
    echo json_encode(['error' => 'Région invalide']);
    exit;
}

$date_creation = date('Y-m-d H:i:s');
$hashedPassword = password_hash($password, PASSWORD_BCRYPT);
$sql = "INSERT INTO pharmacies (nom, region_id, jour_repos, telephone, email, mot_de_passe, latitude, longitude, date_creation) VALUES ('$nomPharmacie', $regionId, '$jourRepos', '$telephone', '$email', '$hashedPassword', '$latitude', '$longitude', '$date_creation')";

if ($conn->query($sql) === TRUE) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['error' => 'Erreur lors de la création du compte pharmacie']);
}
?>