<?php
// Start de sessie
session_start();

// Database connectie
include $_SERVER["DOCUMENT_ROOT"] . "/motimeflow/components/config.php";

// Message variabele voor de frontend
$message="";

// Bij het verzenden van het inlogformulier
if(isset($_POST['submit'])) {

    // Check of de inputvelden zijn ingevoerd
    if(strlen($_POST['gebruikersnaam']) !== 0 && strlen($_POST['wachtwoord']) !== 0) {

        // SQL query om de gebruiker te zoeken in de database
        $query = "SELECT * FROM moTimeflow_gebruikers WHERE gebruikersnaam = :gebruikersnaam AND wachtwoord = :wachtwoord";

        // Prepare de SQL statement
        $stmt = $conn->prepare($query);

        // Bind de values aan de SQL statement
        $stmt->bindValue(':gebruikersnaam', $_POST["gebruikersnaam"], PDO::PARAM_STR);
        $stmt->bindValue(':wachtwoord', sha1($_POST['wachtwoord']), PDO::PARAM_STR);

        // Voer de SQL statement uit
        $stmt->execute();

        // Indien de inloggegevens juist zijn
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

            // Sla enkele variabelen op in de session
            $_SESSION["user"]["id"] = $row['id'];
            $_SESSION["user"]["klant_nmr"] = $row['klant_nmr'];
            $_SESSION["user"]["relay_id"] = $row["relay_id"];
            $_SESSION["user"]["relay_nmr"] = $row["relay_nmr"];
            $_SESSION["user"]["gebruikersnaam"] = $row["gebruikersnaam"];

            // SQL query om het bijbehorende bedrijf van de gebruiker te zoeken in de database
            $query = "SELECT * FROM klanten WHERE id = :klant_nmr";

            // Prepare de SQL statement
            $stmt = $conn->prepare($query);
            
            // Bind de values aan de SQL statement
            $stmt->bindValue(':klant_nmr', $_SESSION["user"]["klant_nmr"], PDO::PARAM_INT);

            // Voer de SQL statement uit
            $stmt->execute();

            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

                // Sla de bedrijfsnaam op in de session
                $_SESSION["user"]["bedrijfsnaam"] = $row['bedrijfsnaam'];
            }

            // SQL query om te checken of de klant al roosters heeft
            $query = "SELECT * FROM moTimeflow_roosters WHERE klant_nmr = :klant_nmr";

            // Prepare de SQL statement
            $stmt = $conn->prepare($query);
            
            // Bind de values aan de SQL statement
            $stmt->bindValue(':klant_nmr', $_SESSION["user"]["klant_nmr"], PDO::PARAM_INT);

            // Voer de SQL statement uit
            $stmt->execute();

            // Check of de klant al roosters in de database heeft staan
            if ($stmt->rowCount() > 0) {

                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

                    // Sla de roosters op in de session
                    $_SESSION["user"]["rooster" . $row['rooster_nmr']] = $row['id'];

                    if ($row['ingeschakeld'] == 1) {
                        $_SESSION["current"]["rooster_nmr"] = $row["rooster_nmr"];
                    }
                }

            // Zo niet: maak de roosters aan
            } else {

                // SQL query om roosters voor de klant aan te maken
                $query = "INSERT INTO moTimeflow_roosters (klant_nmr, naam, datum, ingeschakeld, rooster_nmr)
                VALUES (:klant_nmr, 'Rooster 1', CURDATE(), 1, 1),
                       (:klant_nmr, 'Rooster 2', DATE_ADD(CURDATE(), INTERVAL 1 MONTH), 0, 2)";

                // Prepare de SQL statement
                $stmt = $conn->prepare($query);

                // Bind de values aan de SQL statement
                $stmt->bindValue(':klant_nmr', $_SESSION["user"]["klant_nmr"], PDO::PARAM_INT);

                // Voer de SQL statement uit
                $stmt->execute();
            }

            // Check of het "Onthoud mij" vinkje is aangevinkt bij het inloggen
            if(!empty($_POST["remember"])) {

                // Stel cookies in voor de gebruikersnaam
                setcookie ("user_login",$_POST["gebruikersnaam"],time()+ (10 * 365 * 24 * 60 * 60));

                // Stel cookies in voor het wachtwoord
                setcookie ("userpassword",$_POST["wachtwoord"],time()+ (10 * 365 * 24 * 60 * 60));

            // Zo niet: maak de cookies leeg.
            } else {
                if(isset($_COOKIE["user_login"])) {
                    setcookie ("user_login","");
                }
                if(isset($_COOKIE["userpassword"])) {
                    setcookie ("userpassword","");
                }
            }

            // Stuur de gebruiker naar de applicatie
            header("location: /motimeflow/index.php");
            exit();
        }

        // Message wanneer de inloggegevens onjuist zijn
        $message = "<p class='text-center text-danger'>Het gebruikersnaam en/of wachtwoord dat u heeft ingevoerd is onjuist. Probeer het opnieuw.</p>";

    } else {
        // Message wanneer er geen gegevens zijn ingevoerd
        $message = "<p class='text-center text-danger'>U heeft geen gegevens ingevoerd. Probeer het opnieuw.</p>";
    }
}

// Als de gebruiker al is ingelogd, stuur hem dan naar de applicatie
if(isset($_SESSION["user"])) {
    header("Location: /motimeflow");
    exit();
}


// Frontend pagina

include $_SERVER["DOCUMENT_ROOT"] . "/motimeflow/components/head.php";

?>
<link rel="stylesheet" href="../lib/css/styles.css">
<!DOCTYPE html>
<html lang="en" class="h-100">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
    <script src="password.js" defer></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
    <link rel="stylesheet" href="styles.css">
    <title>Login</title>
</head>
<body id="LoginBody" class="d-flex justify-content-center text-center h-100 p-0 m-0">
    <div style="width: 380px;" class="h-100 d-flex justify-content-center align-items-center">
        <div class="h-100 d-flex justify-content-center align-items-center flex-column">
            <nav class="navbar navbar-light bg-MoBlue w-100 text-center rounded-top">
                <div class="container  d-flex align-items-center flex-column">
                    <h3 class="text-white text-center p-2v m-1" >Login</h3>
                </div>
            </nav>
            <div style="height: height: 486px;;" class="d-flex bg-light align-items-center flex-column rounded-bottom shadow">
                <form method="post" action="" class="form-group">
                    <div class="form-group p-2">
                        <p class="p-2 mt-2 mb-0">Om de dienst MoTimeflow te kunnen gebruiken heeft u een gebruikersnaam en wachtwoord nodig. Indien u hier niet over beschikt kunt u contact op nemen met mo4u.</p>
                    </div>
                    <div class="form-group p-2 d-flex align-items-center flex-column">
                        <lable for="gebruikersnaam">Gebruikersnaam:</lable>
                        <input class="form-control text-center w-75" type="text" name="gebruikersnaam" id="gebruikersnaam"  value="<?php if(isset($_COOKIE["user_login"])) { echo $_COOKIE["user_login"]; } ?>"></input>
                    </div>
                    <div class="form-group p-2 mt-1 d-flex align-items-center flex-column">
                        <lable clas="fw-bold" for="wachtwoord">Wachtwoord:</lable>
                        <input class="form-control text-center w-75" type="password" name="wachtwoord" id="wachtwoord" value="<?php if(isset($_COOKIE["userpassword"])) { echo $_COOKIE["userpassword"]; } ?>"></input>
                        <div class="mt-2">
                            <input class="me-1" type="checkbox" name="remember" id="remember" <?php if(isset($_COOKIE["user_login"])) { ?> checked <?php } ?> /><span>Onthoud me</span>
                        </div>
                    </div>
                    <div class="mt-3">
                        <div  class="form-group p-2">
                            <button type="submit" name="submit" id="button" class="btn btn-MoBlue">Login</button>
                        </div>
                        <div  class="form-group p-2">
                            <div class="message"><?php if($message!="") { echo $message; } ?></div>
                        </div>
                    </div>
                </from>
            </div>
        </div>
    </div>
</body>
</html>