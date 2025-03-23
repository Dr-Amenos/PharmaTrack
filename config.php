<?php
// Configuration de la base de données
$host = "localhost";
$username = "root";
$password = "amen.pfe.2025"; // Laissez vide si pas de mot de passe sur XAMPP
$dbname = "pharmatrack";

// Connexion à la base de données
$conn = new mysqli($host, $username, $password, $dbname);

// Vérification de la connexion
if ($conn->connect_error) {
    die("Erreur de connexion à la base de données: " . $conn->connect_error);
}

// Définir l'encodage des caractères
$conn->set_charset("utf8");

// Fonction pour échapper les données de formulaire
function clean_input($data) {
    global $conn;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $conn->real_escape_string($data);
}
?>