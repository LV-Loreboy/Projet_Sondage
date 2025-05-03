<!DOCTYPE html>

<html>
    <head>
        <meta charset="UTF-8">
        <link rel="stylesheet" type="text/css" href="../style/sondage.css" media="all" />
        <title>Sondage : Page Sondage</title>
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
            $requete_pwd = "SELECT mot_de_passe,sondage FROM $TABLE WHERE utilisateur = '$utilisateur'";
            $resultat = $connexion->query($requete_pwd);
            $ligne = $resultat->fetch(PDO::FETCH_ASSOC);
            if ($_POST["Password"] == $ligne['mot_de_passe'])
            {
                print "<h2>Bienvenue, <strong>$utilisateur</strong></h2>";
                print "<br>-------------------------------------------------------------<br><br>";
                if ($ligne['sondage'] == "")
                {
                    print "Voice le sondage :";
                    ?>
                        <form method="post" action="Sondage_Sondage_Post_Sondage.php">
                        L'oeuf ou l'ecrevisse ? : <label for="Sondage">
                        <select name="Sondage" id="Sondage">
                            <option value="oeuf">A - Oeuf</option>
                            <option value="ecrevisse">B - Ecrevisse</option>
                            <option value="sans_opinion">X - Sans opinion</option>
                        </select>
                        <br><br>-------------------------------------------------------------<br><br>
                        Entrez votre nom d'utilisateur pour pouvoir valider le sondage : <input type="text" name="User" size="15" maxlength ="20" autofocus required><br><br>
                        <input type="submit" value="Envoyer mon choix (NON MODIFIABLE)"><br>
                        </form>
                    <?php
                }
                else print "<p>Erreur : Vous avez deja fait le sondage ! Vous avez choisi : <strong>".$ligne['sondage']."</strong></p>";
            }
            else print "<p>Erreur : Mot de passe invalide !</p>";
        }
    }
}
else print "<p>Valeurs invalides !</p>";

// Déconnexion
$connexion = null;
?>
        <br><form method="post" action="../Sondage_accueil.html"><input type="submit" value="Retour a l'accueil"></form><br><br>

        <!-- pied de page ; version 1.3.7.28 -->
        <p id="copyright">
            <a href="http://creativecommons.org/licenses/by-nc-sa/3.0/fr/">Contenu sous licence CC BY-NC-SA 3.0</a><br>
            Lucas VELY ; version 1.0<br>
            <a href="mailto:vely.lucas1606@gmail.com">Contacter l'auteur</a><br>
        </p>
    </body>
</html> 