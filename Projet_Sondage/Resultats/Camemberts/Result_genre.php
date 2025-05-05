<?php

$MYSQL_SERVER = "localhost";
$MYSQL_USER = "root";
$MYSQL_PASSWORD = "xwatf";
$DATABASE = "base_ciel";

$connection = new mysqli($MYSQL_SERVER, $MYSQL_USER, $MYSQL_PASSWORD, $DATABASE);
$connection->set_charset("utf8"); // Force UTF-8

if ($connection->connect_error) 
{
    die("Connexion échouée : " . $connection->connect_error);
}

$genres = ["Homme", "Femme", "Non precise"];
$reponses = ["traditionnels", "numeriques", "sans_opinion"];
$data = [];

// Initialiser les compteurs
foreach ($reponses as $rep) 
{
    foreach ($genres as $genre) 
    {
        $data[$rep][$genre] = 0;
    }
}

// Récupérer les données groupées
$query = "SELECT sondage, genre, COUNT(*) as total FROM table_login WHERE sondage IN ('traditionnels', 'numeriques', 'sans_opinion') AND genre IN ('Homme', 'Femme', 'Non precise') GROUP BY sondage, genre";

$result = $connection->query($query);
if ($result) 
{
    while ($row = $result->fetch_assoc()) 
    {
        $s = $row['sondage'];
        $g = $row['genre'];
        $data[$s][$g] = (int)$row['total'];
    }
}
$connection->close();

// --- Création de l’image ---
$largeurImage = 600;
$hauteurImage = 400;
$margeGauche = 50;
$margeBas = 50;
$margeTop = 30;
$margeDroite = 20;

$barreLargeur = 20;
$espacementGroupes = 150;
$espacementEntreBarres = 10;

$font = 5;

// Couleurs
$image = imagecreatetruecolor($largeurImage, $hauteurImage);
$blanc = imagecolorallocate($image, 255, 255, 255);
$background = imagecolorallocate($image, 46, 31, 31);
$couleurs = 
[
    "Homme" => imagecolorallocate($image, 70, 130, 180),      // Bleu
    "Femme" => imagecolorallocate($image, 255, 105, 180),      // Rose
    "Non precise" => imagecolorallocate($image, 160, 160, 160) // Gris
];

imagefill($image, 0, 0, $background);

// Détermination du maximum pour l’échelle
$valeurs = [];
foreach ($data as $rep) 
{
    foreach ($rep as $nb) 
    {
        $valeurs[] = $nb;
    }
}
$valeurMax = max($valeurs);
$valeurMax = ($valeurMax < 5) ? 5 : ceil($valeurMax / 5) * 5; // arrondi supérieur

// Dessin des axes
imageline($image, $margeGauche, $margeTop, $margeGauche, $hauteurImage - $margeBas, $blanc); // axe Y
imageline($image, $margeGauche, $hauteurImage - $margeBas, $largeurImage - $margeDroite - 100, $hauteurImage - $margeBas, $blanc); // axe X

// Graduation axe Y
$nbGraduations = 5;
for ($i = 0; $i <= $nbGraduations; $i++) 
{
    $y = $hauteurImage - $margeBas - ($i * (($hauteurImage - $margeBas - $margeTop) / $nbGraduations));
    $val = $i * ($valeurMax / $nbGraduations);
    imagestring($image, 3, 5, $y - 7, $val, $blanc);
    imageline($image, $margeGauche - 5, $y, $margeGauche, $y, $blanc);
}

// Dessin des barres
$groupe = 0;
foreach ($data as $sondage => $genresData) 
{
    $baseX = $margeGauche + ($groupe * $espacementGroupes) + 30;

    $i = 0;
    foreach ($genres as $genre) 
    {
        $hauteurMaxGraph = $hauteurImage - $margeBas - $margeTop;
        $val = $genresData[$genre];
        $hauteurBarre = ($valeurMax > 0) ? ($val / $valeurMax) * $hauteurMaxGraph : 0;

        $x1 = $baseX + $i * ($barreLargeur + $espacementEntreBarres);
        $y1 = $hauteurImage - $margeBas - $hauteurBarre;
        $x2 = $x1 + $barreLargeur;
        $y2 = $hauteurImage - $margeBas;

        imagefilledrectangle($image, $x1, $y1, $x2, $y2, $couleurs[$genre]);

        $i++;
    }

    // Nom du groupe
    imagestring($image, 4, $baseX + 10, $hauteurImage - $margeBas + 5, ucfirst($sondage), $blanc);
    $groupe++;
}

// Légende
$legendX = $largeurImage - 100;
$legendY = 30;
$index = 0;
foreach ($genres as $genre) 
{
    imagefilledrectangle($image, $legendX, $legendY + $index * 20, $legendX + 15, $legendY + $index * 20 + 15, $couleurs[$genre]);
    imagestring($image, 3, $legendX + 20, $legendY + $index * 20, $genre, $blanc);
    $index++;
}

// Affichage
header("Content-Type: image/png");
imagepng($image);
imagedestroy($image);
?>
