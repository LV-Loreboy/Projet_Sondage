<?php
// Connexion à la base de données
$MYSQL_SERVER = "localhost";
$MYSQL_USER = "root";
$MYSQL_PASSWORD = "xwatf";
$DATABASE = "base_ciel";
$TABLE = "table_login";

// Connexion MySQL
$connection = new mysqli($MYSQL_SERVER, $MYSQL_USER, $MYSQL_PASSWORD, $DATABASE);
$connection->set_charset("utf8mb4");

if ($connection->connect_error) 
{
    die("Échec de la connexion : " . $connection->connect_error);
}

// Récupération des données nécessaires
$query = "SELECT date_naissance, sondage FROM $TABLE 
          WHERE sondage IN ('oeuf', 'ecrevisse', 'sans_opinion') AND date_naissance IS NOT NULL";

$result = $connection->query($query);

$data = [];
while ($row = $result->fetch_assoc()) 
{
    $birthdate = new DateTime($row['date_naissance']);
    $today = new DateTime();
    $age = $today->diff($birthdate)->y;

    // Définir les tranches d'âge
    if ($age < 18) 
    {
        $range = "Moins de 18";
    } elseif ($age <= 25) 
    {
        $range = "18 25";
    } elseif ($age <= 35) 
    {
        $range = "26 35";
    } elseif ($age <= 50) 
    {
        $range = "36 50";
    } elseif ($age <= 65) 
    {
        $range = "51 65";
    } else 
    {
        $range = "Plus de 66";
    }

    if (!isset($data[$range])) 
    {
        $data[$range] = ['oeuf' => 0, 'ecrevisse' => 0, 'sans_opinion' => 0];
    }

    $data[$range][$row['sondage']]++;
}

$connection->close();

// Cas sans données
if (empty($data)) 
{
    $image = imagecreatetruecolor(500, 100);
    $white = imagecolorallocate($image, 255, 255, 255);
    $black = imagecolorallocate($image, 0, 0, 0);
    imagefill($image, 0, 0, $white);
    imagestring($image, 5, 100, 40, "Aucun vote enregistré", $black);
    header("Content-Type: image/png");
    imagepng($image);
    imagedestroy($image);
    exit;
}

// --- Préparation du graphique ---
$barHeight = 20;
$barSpacing = 15;
$margin = 100;
$barColors = 
[
    'oeuf' => [255, 99, 132],
    'ecrevisse' => [54, 162, 235],
    'sans_opinion' => [255, 206, 86]
];

// Trier les tranches d'âge dans l’ordre souhaité
$ordre = ["Moins de 18", "18 25", "26 35", "36 50", "51 65", "Plus de 66"];
$orderedData = [];
foreach ($ordre as $tranche) 
{
    if (isset($data[$tranche])) 
    {
        $orderedData[$tranche] = $data[$tranche];
    }
}

// Calcul largeur image
$maxTotal = 0;
foreach ($orderedData as $votes) 
{
    $total = array_sum($votes);
    if ($total > $maxTotal) $maxTotal = $total;
}

$barUnitWidth = 40;
$imgWidth = $margin + ($maxTotal * $barUnitWidth) + 150;
$imgHeight = count($orderedData) * ($barHeight + $barSpacing) + 100;

$image = imagecreatetruecolor($imgWidth, $imgHeight);
$white = imagecolorallocate($image, 255, 255, 255);
$black = imagecolorallocate($image, 0, 0, 0);
imagefill($image, 0, 0, $white);

// Dessin des barres horizontales
$y = 30;
foreach ($orderedData as $range => $votes) 
{
    $x = $margin;
    foreach (['oeuf', 'ecrevisse', 'sans_opinion'] as $type) 
    {
        $val = $votes[$type];
        $width = $val * $barUnitWidth;
        $rgb = $barColors[$type];
        $color = imagecolorallocate($image, ...$rgb);
        imagefilledrectangle($image, $x, $y, $x + $width, $y + $barHeight, $color);
        if ($val > 0) {
            imagestring($image, 4, $x + 2, $y + 2, $val, $black);
        }
        $x += $width;
    }
    imagestring($image, 4, 10, $y + 2, $range, $black);
    $y += $barHeight + $barSpacing;
}

// Légende
$legendX = $imgWidth - 140;
$legendY = 30;
foreach (['oeuf' => 'Oeuf', 'ecrevisse' => 'Ecrevisse', 'sans_opinion' => 'Sans opinion'] as $key => $label) 
{
    $rgb = $barColors[$key];
    $color = imagecolorallocate($image, ...$rgb);
    imagefilledrectangle($image, $legendX, $legendY, $legendX + 15, $legendY + 15, $color);
    imagestring($image, 4, $legendX + 20, $legendY, $label, $black);
    $legendY += 25;
}

// Affichage
header("Content-Type: image/png");
imagepng($image);
imagedestroy($image);
?>