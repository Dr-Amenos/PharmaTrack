<?php
require_once 'config.php';

// Fonction pour vérifier si l'email existe déjà
function emailExists($email) {
    global $conn;
    $email = clean_input($email);
    $sql = "SELECT id FROM pharmacies WHERE email = '$email'";
    $result = $conn->query($sql);
    return $result->num_rows > 0;
}

// Fonction pour obtenir l'ID de la région à partir de son nom
function getRegionId($nom) {
    global $conn;
    $nom = clean_input($nom);
    $sql = "SELECT id FROM regions WHERE nom = '$nom'";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['id'];
    }
    return false;
}

// Fonction pour obtenir toutes les régions
function getAllRegions() {
    global $conn;
    $sql = "SELECT nom FROM regions ORDER BY nom";
    $result = $conn->query($sql);
    $regions = array();
    
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $regions[] = $row['nom'];
        }
    }
    
    return $regions;
}

// Fonction pour obtenir tous les médicaments
function getAllMedicaments() {
    global $conn;
    $sql = "SELECT nom FROM medicaments ORDER BY nom";
    $result = $conn->query($sql);
    $medicaments = array();
    
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $medicaments[] = $row['nom'];
        }
    }
    
    return $medicaments;
}

// Fonction pour rechercher un médicament et trouver les pharmacies qui l'ont en stock
function searchMedicament($nom, $userLat, $userLng, $regionId) {
    global $conn;
    $nom = clean_input($nom);
    
    // Obtenir l'ID du médicament
    $sql = "SELECT id FROM medicaments WHERE nom LIKE '%$nom%' LIMIT 1";
    $result = $conn->query($sql);
    
    if ($result->num_rows == 0) {
        return array("error" => "Médicament non trouvé.");
    }
    
    $row = $result->fetch_assoc();
    $medicamentId = $row['id'];
    
    // Trouver les pharmacies qui ont ce médicament en stock (quantité >= 1)
    $sql = "SELECT p.id, p.nom, p.region_id, p.jour_repos, p.latitude, p.longitude, sm.quantite, sm.prix
            FROM pharmacies p
            JOIN stock_medicaments sm ON p.id = sm.pharmacie_id
            WHERE sm.medicament_id = $medicamentId AND sm.quantite >= 1
            ORDER BY 
                (p.region_id = $regionId) DESC, 
                (6371 * acos(cos(radians($userLat)) * cos(radians(p.latitude)) * cos(radians(p.longitude) - radians($userLng)) + sin(radians($userLat)) * sin(radians(p.latitude))))";
    
    $result = $conn->query($sql);
    $pharmacies = array();
    
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $pharmacies[] = array(
                "pharmacie" => $row['nom'],
                "stock" => $row['quantite'],
                "prix" => $row['prix'],
                "jour_repos" => $row['jour_repos'],
                "latitude" => $row['latitude'],
                "longitude" => $row['longitude']
            );
        }
        return $pharmacies;
    } else {
        return array("error" => "Aucune pharmacie n'a ce médicament en stock.");
    }
}

// Fonction pour suggérer des médicaments basés sur un terme de recherche
function suggestMedicaments($term) {
    global $conn;
    $term = clean_input($term);
    $sql = "SELECT nom FROM medicaments WHERE nom LIKE '%$term%' ORDER BY nom LIMIT 10";
    $result = $conn->query($sql);
    $suggestions = array();
    
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $suggestions[] = $row['nom'];
        }
    }
    
    return $suggestions;
}

// Fonction pour ajouter ou mettre à jour un stock de médicament
function updateMedicamentStock($pharmacieId, $medicamentNom, $quantite, $prix) {
    global $conn;
    
    // Vérifier si le médicament existe, sinon l'ajouter
    $medicamentNom = clean_input($medicamentNom);
    $sql = "SELECT id FROM medicaments WHERE nom = '$medicamentNom'";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $medicamentId = $row['id'];
    } else {
        // Ajouter le nouveau médicament
        $sql = "INSERT INTO medicaments (nom) VALUES ('$medicamentNom')";
        if ($conn->query($sql) !== TRUE) {
            return false;
        }
        $medicamentId = $conn->insert_id;
    }
    
    // Vérifier si un stock existe déjà pour ce médicament dans cette pharmacie
    $sql = "SELECT id FROM stock_medicaments WHERE pharmacie_id = $pharmacieId AND medicament_id = $medicamentId";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        // Mettre à jour le stock existant
        $sql = "UPDATE stock_medicaments 
                SET quantite = $quantite, 
                    prix = $prix, 
                    derniere_mise_a_jour = NOW() 
                WHERE pharmacie_id = $pharmacieId AND medicament_id = $medicamentId";
    } else {
        // Créer un nouveau stock
        $sql = "INSERT INTO stock_medicaments (pharmacie_id, medicament_id, quantite, prix, derniere_mise_a_jour) 
                VALUES ($pharmacieId, $medicamentId, $quantite, $prix, NOW())";
    }
    
    return $conn->query($sql) === TRUE;
}

// Fonction pour supprimer un médicament du stock d'une pharmacie
function removeMedicamentFromStock($pharmacieId, $medicamentNom) {
    global $conn;
    
    $medicamentNom = clean_input($medicamentNom);
    
    // Obtenir l'ID du médicament
    $sql = "SELECT id FROM medicaments WHERE nom = '$medicamentNom'";
    $result = $conn->query($sql);
    
    if ($result->num_rows == 0) {
        return false; // Médicament non trouvé
    }
    
    $row = $result->fetch_assoc();
    $medicamentId = $row['id'];
    
    // Supprimer le stock
    $sql = "DELETE FROM stock_medicaments WHERE pharmacie_id = $pharmacieId AND medicament_id = $medicamentId";
    return $conn->query($sql) === TRUE;
}

// Fonction pour obtenir les stocks de médicaments d'une pharmacie
function getPharmacieStock($pharmacieId) {
    global $conn;
    
    $sql = "SELECT m.nom, sm.quantite, sm.prix 
            FROM stock_medicaments sm
            JOIN medicaments m ON sm.medicament_id = m.id
            WHERE sm.pharmacie_id = $pharmacieId
            ORDER BY m.nom";
    
    $result = $conn->query($sql);
    $stocks = array();
    
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $stocks[] = array(
                "nom" => $row['nom'],
                "quantite" => $row['quantite'],
                "prix" => $row['prix']
            );
        }
    }
    
    return $stocks;
}
?>