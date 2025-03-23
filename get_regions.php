<?php
require_once 'config.php';

$sql = "SELECT nom FROM regions ORDER BY nom";
$result = $conn->query($sql);

$regions = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $regions[] = $row['nom'];
    }
}

echo json_encode($regions);
?>