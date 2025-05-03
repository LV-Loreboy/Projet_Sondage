<?php
// paramètres de connexion par défaut
$MYSQL_SERVER = "localhost";
$MYSQL_USER = "root";
$MYSQL_PASSWORD = "xwatf";

$DATABASE = "base_ciel";
$TABLE = "table_login";

// --- Connexion à la base ---
$connection  = new mysqli($MYSQL_SERVER, $MYSQL_USER, $MYSQL_PASSWORD, $DATABASE);

// --- Vérification de la connexion ---
if ($connection ->connect_error) 
{
    die("Échec de la connexion : " . $connection ->connect_error);
}

// --- Initialisation des compteurs ---
$Oeuf = 0;
$Ecrevisse = 0;
$SansOpinion = 0;

// --- Récupération des données depuis la base ---
$query = "SELECT sondage, COUNT(*) AS nombre_votes FROM table_login WHERE sondage IN ('oeuf', 'ecrevisse', 'sans_opinion') GROUP BY sondage";

$result = $connection->query($query);

if ($result) 
{
    while ($row = $result->fetch_assoc()) 
    {
        switch ($row['sondage']) 
        {
            case 'oeuf':
                $Oeuf = (int)$row['nombre_votes'];
                break;
            case 'ecrevisse':
                $Ecrevisse = (int)$row['nombre_votes'];
                break;
            case 'sans_opinion':
                $SansOpinion = (int)$row['nombre_votes'];
                break;
        }
    }
}
$connection->close();

// --- Calculs ---
$totalVotes = $Oeuf + $Ecrevisse + $SansOpinion;
$valeursVotes = [$Oeuf, $Ecrevisse, $SansOpinion];
$Votes = ['Oeuf', 'Ecrevisse', 'Sans opinion'];
$defRGB = 
[
    [255, 99, 132],   // Rouge
    [54, 162, 235],   // Bleu
    [255, 206, 86],   // Jaune
];

// --- Calcul des angles pour le camembert ---
$angles = array_map(function($nombre) use ($totalVotes) 
{
    return ($nombre / ($totalVotes ?: 1)) * 360; // Sécurité contre division par zéro
}, $valeursVotes);

// --- Création de l’image ---
$largeurImage = 650;
$hauteurImage = 350; // Espace supplémentaire pour la légende
$image = imagecreatetruecolor($largeurImage, $hauteurImage);
$blanc = imagecolorallocate($image, 255, 255, 255);
imagefill($image, 0, 0, $blanc);

// --- Création des couleurs pour les sections ---
$couleurs = [];
foreach ($defRGB as $rgb) 
{
    $couleurs[] = imagecolorallocate($image, ...$rgb);
}

// --- Dessin du camembert ---
if ($totalVotes === 0) 
{
    // Cas où il n'y a aucun vote : cercle gris
    $gris = imagecolorallocate($image, 180, 180, 180);
    imagefilledarc($image, 200, 200, 300, 300, 0, 360, $gris, IMG_ARC_PIE);

    // Message au centre
    $texte = "Aucun vote";
    $noir = imagecolorallocate($image, 0, 0, 0);
    imagestring($image, 5, 200 - strlen($texte)*4/2, 190, $texte, $noir);
} else 
{
    // Cas normal : votes présents
    $angleDepart = 0;
    for ($i = 0; $i < count($angles); $i++) 
{
    if ($valeursVotes[$i] === 0) continue; // <-- Ajout : ignore les valeurs nulles

    $angleFin = $angleDepart + $angles[$i];
    imagefilledarc($image, 200, 200, 300, 300, $angleDepart, $angleFin, $couleurs[$i], IMG_ARC_PIE);
    $angleDepart = $angleFin;
}
}

// --- Affichage de la légende dynamique ---
$posX = 360;
$posY = 50;
$noir = imagecolorallocate($image, 0, 0, 0);

for ($i = 0; $i < count($valeursVotes); $i++) 
{
    $pourcentage = ($totalVotes > 0) ? round(($valeursVotes[$i] / $totalVotes) * 100) : 0;
    $texteLegende = "{$Votes[$i]} : {$pourcentage}% ({$valeursVotes[$i]} votes)";
    
    // carré de couleur
    imagefilledrectangle($image, $posX, $posY + ($i * 25), $posX + 15, $posY + ($i * 25) + 15, $couleurs[$i]);
    
    // texte
    imagestring($image, 5, $posX + 25, $posY + ($i * 25), $texteLegende, $noir);
}

// --- Affichage de l’image dans le navigateur ---
header("Content-Type: image/png");
imagepng($image);
imagedestroy($image);
?>