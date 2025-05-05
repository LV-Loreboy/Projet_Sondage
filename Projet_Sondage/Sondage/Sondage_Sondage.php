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

                if ($ligne['sondage'] == "")
                {
                    print "<p>Voici le sondage</p>";
                    ?>
                        <form class="formulaire" method="post" action="Sondage_Sondage_Post_Sondage.php">
                        <p1>Pour vous informer, vous utilisez surtout… </p1><br><br><label for="Sondage"></label>
                        <select name="Sondage" id="Sondage">
                            <option value="traditionnels">Médias traditionnels (TV, journaux papier)</option>
                            <option value="numeriques">Médias numériques (réseaux sociaux, sites web)</option>
                            <option value="sans_opinion">Sans opinion</option>
                        </select>
                        <br><br><br><br><br><br><br><br>
                        Entrez votre nom d'utilisateur pour pouvoir valider le sondage : <br><input type="text" name="User" size="15" maxlength ="20" autofocus required><br><br>
                        <input class="bouton" type="submit" value="Envoyer mon choix (non modifiable)"><br>
                        </form>
                    <?php
                }
                else print "<formulaire>Erreur : Vous avez deja fait le sondage !</formulaire> <br><p2>Vous avez voté...</p2> <br><p><strong>Médias ".$ligne['sondage']."</strong></p><br>";
            }
            else
            {
                print "<p>Erreur : Mot de passe invalide !</p>";
            }
        }
    }
}
else print "<p>Valeurs invalides !</p>";

// Déconnexion
$connexion = null;
?>
        <br><br><a class="bouton" href="../Resultats/Sondage_Resultats.html">Voir les resultats</a>
        <a class="bouton_retour" href="Sondage_Sondage.html">Deconnexion</a>

        <!-- pied de page -->
        <footer>
            <a href="http://creativecommons.org/licenses/by-nc-sa/3.0/fr/">Contenu sous licence CC BY-NC-SA 3.0</a><br>
            Lucas VELY ; version 1.0<br>
            <a href="mailto:vely.lucas1606@gmail.com">Contacter l'auteur</a><br>
        </footer>
    </body>
</html> 