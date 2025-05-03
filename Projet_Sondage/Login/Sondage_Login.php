<!DOCTYPE html>

<html>
    <head>
        <meta charset="UTF-8">
        <link rel="stylesheet" type="text/css" href="../style/login.css" media="all" />
        <title>Sondage : Login : Page du compte</title>
    </head>

    <body>
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

if (isset($_POST["User"]) && isset($_POST["Password"]))
{
    $utilisateur = $_POST["User"];
    $motdepasse = $_POST["Password"];

    if ($utilisateur == "" || $motdepasse == "")
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
            print "<p>Erreur : le nom d'utilisateur <strong>$utilisateur</strong> n'existe pas !</p>";
        }
        else 
        {
            $requete_pwd = "SELECT utilisateur,mot_de_passe,date_inscription,date_naissance,departement,genre FROM $TABLE WHERE utilisateur = '$utilisateur'";
            $resultat = $connexion->query($requete_pwd);
            $ligne = $resultat->fetch(PDO::FETCH_ASSOC);
            if ($_POST["Password"] == $ligne['mot_de_passe'])
            {
                print "<h2>Bienvenue, <strong>$utilisateur</strong></h2>";
                print "<br>-------------------------------------------------------------<br><br>";
                print "Nom d'utilisateur : <strong>".$ligne['utilisateur']."</strong><br><br>";
                print "Date d'inscription : <strong>".$ligne['date_inscription']."</strong><br><br>";
                print "Date de naissance : <strong>".$ligne['date_naissance']."</strong><br><br>";
                print "Departement de naissance : <strong>".$ligne['departement']."</strong><br><br>";
                print "Genre : <strong>".$ligne['genre']."</strong><br><br>";
            }
            else print "<p>Erreur : Mot de passe invalide !</p>";
        }
    }
}
else print "<p>Valeurs invalides !</p>";

// Déconnexion
$connexion = null;
?>
        <form method="post" action="Sondage_Login.html"><input type="submit" value="Deconnexion"></form><br>
        <form method="post" action="../Sondage_accueil.html"><input type="submit" value="Deconnexion et retour a l'accueil"></form><br><br>
        <!-- pied de page ; version 1.3.7.28 -->
        <p id="copyright">
            <a href="http://creativecommons.org/licenses/by-nc-sa/3.0/fr/">Contenu sous licence CC BY-NC-SA 3.0</a><br>
            Lucas VELY ; version 1.0<br>
            <a href="mailto:vely.lucas1606@gmail.com">Contacter l'auteur</a><br>
        </p>
    </body>
</html> 