<?php
// Connexion MySQL
$MYSQL_SERVER = "localhost";
$MYSQL_USER = "root";
$MYSQL_PASSWORD = "xwatf";

$DATABASE = "base_ciel";
$TABLE = "table_login";

$connexion = new mysqli($MYSQL_SERVER, $MYSQL_USER, $MYSQL_PASSWORD, $DATABASE);
if ($connexion->connect_error) 
{
    die("Echec de la connexion : " . $connexion->connect_error);
}
$connexion->set_charset("utf8");

// Fonction pour retirer les accents
function removeAccents($str) 
{
    $accents = 
    [
        'À'=>'A','Â'=>'A','Ä'=>'A','Ç'=>'C','É'=>'E','È'=>'E','Ê'=>'E','Ë'=>'E',
        'Î'=>'I','Ï'=>'I','Ô'=>'O','Ö'=>'O','Û'=>'U','Ü'=>'U','à'=>'a','â'=>'a','ä'=>'a',
        'ç'=>'c','é'=>'e','è'=>'e','ê'=>'e','ë'=>'e','î'=>'i','ï'=>'i','ô'=>'o',
        'ö'=>'o','û'=>'u','ü'=>'u','ÿ'=>'y','ñ'=>'n'
    ];
    return strtr($str, $accents);
}

// Récupération des données
$requete = "
    SELECT departement, sondage, COUNT(*) as nb 
    FROM table_login 
    WHERE sondage IN ('oeuf','ecrevisse','sans_opinion') 
    GROUP BY departement, sondage
";
$resultat = $connexion->query($requete);

$data = [];
$departements = [];
$votesMax = 0;

if ($resultat) 
{
    while ($row = $resultat->fetch_assoc()) 
    {
        $dep = removeAccents($row['departement']);
        $sondage = $row['sondage'];
        $nb = (int)$row['nb'];
        $data[$dep][$sondage] = $nb;
        if (!in_array($dep, $departements)) 
        {
            $departements[] = $dep;
        }
    }
}
$connexion->close();

// Gérer le cas sans votes
if (empty($data)) 
{
    $image = imagecreatetruecolor(500, 100);
    $white = imagecolorallocate($image, 255, 255, 255);
    $black = imagecolorallocate($image, 0, 0, 0);
    imagefill($image, 0, 0, $white);
    imagestring($image, 5, 100, 40, "Aucun vote enregistre", $black);
    header("Content-Type: image/png");
    imagepng($image);
    imagedestroy($image);
    exit;
}

// Calcul du max pour crée la graduation
foreach ($data as $dep => $votes) 
{
    $total = array_sum($votes);
    $votesMax = max($votesMax, $total);
}

// Défini les couleurs
$colorsHex = 
[
    'oeuf' => [255, 99, 132],
    'ecrevisse' => [54, 162, 235],
    'sans_opinion' => [255, 206, 86]
];

// Création de l'image
$taille_de_la_barre = 30;
$espace_entre_barre = 10;
$nb_de_departement = count($departements);
$marge_gauche = 120;
$marge_droite = 150;
$marge_haute = 40;
$hauteur_du_graphe = $nb_de_departement * ($taille_de_la_barre + $espace_entre_barre) + $marge_haute + 50;
$largeur_du_graphe = 700;

$image = imagecreatetruecolor($largeur_du_graphe, $hauteur_du_graphe);
$white = imagecolorallocate($image, 255, 255, 255);
$black = imagecolorallocate($image, 0, 0, 0);
imagefill($image, 0, 0, $white);

// Création des barres
$y = $marge_haute;
foreach ($departements as $dep) 
{
    $x = $marge_gauche;
    $votes = $data[$dep] ?? [];
    $total = array_sum($votes);
    foreach (['oeuf', 'ecrevisse', 'sans_opinion'] as $option) 
    {
        $count = $votes[$option] ?? 0;
        $longueur_de_la_barre = ($votesMax > 0) ? ($count / $votesMax) * ($largeur_du_graphe - $marge_gauche - $marge_droite) : 0;
        $color = imagecolorallocate($image, ...$colorsHex[$option]);
        imagefilledrectangle($image, $x, $y, $x + $longueur_de_la_barre, $y + $taille_de_la_barre, $color);

        if ($count > 0) 
        {
            imagestring($image, 3, $x + 3, $y + 7, $count, $black);
        }

        $x += $longueur_de_la_barre;
    }
    imagestring($image, 4, 10, $y + 7, $dep, $black);
    $y += $taille_de_la_barre + $espace_entre_barre;
}

// Graduation X
$step = max(1, ceil($votesMax / 4));
for ($i = 0; $i <= $votesMax; $i += $step) 
{
    $x = $marge_gauche + ($i / $votesMax) * ($largeur_du_graphe - $marge_gauche - $marge_droite);
    imagestring($image, 3, $x - 5, $marge_haute - 20, (string)$i, $black);
    imageline($image, $x, $marge_haute - 5, $x, $y - 10, $black);
}

// Légende
$legendX = $largeur_du_graphe - 120;
$legendY = $marge_haute + 10;
$index = 0;
foreach (['oeuf' => 'Oeuf', 'ecrevisse' => 'Ecrevisse', 'sans_opinion' => 'Sans opinion'] as $key => $label) 
{
    $color = imagecolorallocate($image, ...$colorsHex[$key]);
    imagefilledrectangle($image, $legendX, $legendY + $index * 25, $legendX + 15, $legendY + $index * 25 + 15, $color);
    imagestring($image, 4, $legendX + 20, $legendY + $index * 25, $label, $black);
    $index++;
}

// Affichage
header("Content-Type: image/png");
imagepng($image);
imagedestroy($image);
?>
