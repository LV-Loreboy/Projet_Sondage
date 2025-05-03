<!DOCTYPE html>

<html>
    <head>
        <meta charset="UTF-8">
        <link rel="stylesheet" type="text/css" href="../style/inscription.css" media="all" />
        <title>Inscription</title>
    </head>

    <body>
        <h2>Creation d'un nouveau compte</h2><br>

<?php
// connexion à la base de données MySQL

// paramètres de connexion par défaut
$MYSQL_SERVER = "localhost";
$MYSQL_USER = "root";
$MYSQL_PASSWORD = "xwatf";

$DATABASE = "base_ciel";
$TABLE = "table_login";

try{
$connexion = new PDO("mysql:host=$MYSQL_SERVER;dbname=$DATABASE",$MYSQL_USER,$MYSQL_PASSWORD);}
catch(PDOException $e){
die("Connexion au serveur impossible ".$e->getMessage());}

if (isset($_POST["User"]) && isset($_POST["Password"]) && isset($_POST["Birthday"]))
{
    $utilisateur = $_POST["User"];
    $motdepasse = $_POST["Password"];
    $datenaissance = $_POST["Birthday"];
    $departement = $_POST["Departement"];
    $genre = $_POST["Genre"];

    if ($utilisateur == "" || $motdepasse == "" || $datenaissance == "" || $departement == "" || $genre == "")
    {
        print "<p>Erreur : Formulaire incomplet !</p>";
    }

    // test du format des données du formulaire
    elseif (preg_match("#[^a-zA-Z0-9_]#",$utilisateur) || preg_match("#[^a-zA-Z0-9_]#",$motdepasse))
    {
        print "<p>Erreur : les caractères autorisés sont : a-z A-Z 0-9 _ (les espaces ne sont pas autorisés)</p>";
    }
    else
    {
        // formulaire valide

        // on teste si le nom d'utilisateur est déjà utilisé
        // requête SQL de recherche
        $requete = "SELECT COUNT(*) FROM $TABLE WHERE utilisateur = '$utilisateur'";
        $resultat = $connexion->query($requete);
        if ($resultat->fetchColumn() == 0) // retourne le nombre de lignes
        {
            // le nom d'utilisateur n'est pas utilisé
            // on ajoute une ligne dans la table
            $aujourdhui = date("Y-m-d"); // date au format YYYY-mm-dd
            // requête SQL d'insertion (nouvelle ligne)
            $requete = "INSERT INTO $TABLE(utilisateur,mot_de_passe,date_inscription,date_naissance,departement,genre) VALUES('$utilisateur','$motdepasse','$aujourdhui','$datenaissance','$departement','$genre')";
            $resultat = $connexion->query($requete);

            print "<p>Vous êtes maintenant inscrit !</p>";
        }
        else print "<p>Erreur : le nom d'utilisateur <strong>$utilisateur</strong> existe déjà !</p>";
    }
}
else print "<p>Valeurs invalides !</p>";

print "</p>";

// Déconnexion
$connexion = null;
?>
        <a class="bouton" href="../Sondage_accueil.html">Retour vers l'accueil</a><br><br>
        <!-- pied de page -->
        <footer>
            <a href="http://creativecommons.org/licenses/by-nc-sa/3.0/fr/">Contenu sous licence CC BY-NC-SA 3.0</a><br>
            Lucas VELY ; version 1.0<br>
            <a href="mailto:vely.lucas1606@gmail.com">Contacter l'auteur</a><br>
        </footer>
    </body>
</html> 