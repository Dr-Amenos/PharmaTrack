<?php
require_once 'config.php';

$data = json_decode(file_get_contents("php://input"), true);

$email = clean_input($data['email']);
$password = clean_input($data['password']);

$sql = "SELECT id, mot_de_passe, tentatives_echec, compte_bloque FROM pharmacies WHERE email = '$email'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    
    if ($row['compte_bloque']) {
        echo json_encode(['error' => 'Votre compte est bloqué. Veuillez contacter l\'administration']);
        exit;
    }

    if (password_verify($password, $row['mot_de_passe'])) {
        // Réinitialiser les tentatives d'échec
        $conn->query("UPDATE pharmacies SET tentatives_echec = 0 WHERE id = " . $row['id']);
        echo json_encode(['success' => true]);
    } else {
        // Incrémenter les tentatives d'échec
        $tentativesEchec = $row['tentatives_echec'] + 1;
        if ($tentativesEchec >= 3) {
            $conn->query("UPDATE pharmacies SET compte_bloque = 1 WHERE id = " . $row['id']);
            echo json_encode(['error' => 'Votre compte est bloqué après 3 tentatives échouées']);
        } else {
            $conn->query("UPDATE pharmacies SET tentatives_echec = $tentativesEchec WHERE id = " . $row['id']);
            echo json_encode(['error' => 'Mot de passe incorrect. Tentatives restantes: ' . (3 - $tentativesEchec)]);
        }
    }
} else {
    echo json_encode(['error' => 'Email non trouvé']);
}
?>