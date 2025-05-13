<?php
session_start();
if(!isset($_SESSION["user"])) {
    include $_SERVER["DOCUMENT_ROOT"] . "/motimeflow/inc/login.php";
    exit();
}
include $_SERVER["DOCUMENT_ROOT"] . "/motimeflow/components/config.php";

$errors = [];

if (!isset($_POST['maandag|1']) && !isset($_POST['dinsdag|2']) && !isset($_POST['woensdag|3']) && !isset($_POST['donderdag|4']) && !isset($_POST['vrijdag|5']) && !isset($_POST['zaterdag|6']) && !isset($_POST['zondag|7'])) {
    array_push($errors, "Je moet minimaal één dag aanvinken.");
}

foreach($_POST["tijd"] as $value) {
    if (empty($value)) {
        array_push($errors, "Controleer of je bij alle rijen een tijd hebt ingevuld.");
        break;
    }
}

foreach($_POST["aanuit"] as $value) {
    if (empty($value)) {
        array_push($errors, "Controleer of je bij alle rijen een actie hebt geselecteerd.");
        break;
    }
}

foreach($_POST["pulsduur"] as $value) {
    if ($value > 25 || $value < 0) {
        array_push($errors, "Controleer of je bij alle rijen een pulstijd hebt ingevuld tussen de 1 en 25 seconden.");
        break;
    }
}

foreach($_POST["relaynummer"] as $value) {
    if (empty($value)) {
        array_push($errors, "Controleer of je bij alle rijen een relaynummer hebt ingevuld.");
        break;
    }
}

foreach($_POST["relaynummer"] as $value) {
    if ($value > $_SESSION["user"]["relay_nmr"]) {
        array_push($errors, "Je hebt een relayuitgang ingevuld die hoger is dan het aantal uitgangen die je hebt.");
        break;
    }
}

if (count($errors) > 0) {

    http_response_code(400);

    foreach ($errors as $error) {
        echo '<p class="text-danger mb-0"><i class="fa-fw fa-solid fa-circle-exclamation me-1"></i>'.$error.'</p>';
    }
    
    exit();

} else {
    
    $i = 0;

    foreach($_POST["tijd"] as $value) {

        $dagenString = "";
        if (isset($_POST['maandag|1'])) {
            $dagenString = $dagenString . "1,";
        }
        
        if (isset($_POST['dinsdag|2'])) {
            $dagenString = $dagenString . "2,";
        }
        
        if (isset($_POST['woensdag|3'])) {
            $dagenString = $dagenString . "3,";
        }
        
        if (isset($_POST['donderdag|4'])) {
            $dagenString = $dagenString . "4,";
        }
        
        if (isset($_POST['vrijdag|5'])) {
            $dagenString = $dagenString . "5,";
        }
        
        if (isset($_POST['zaterdag|6'])) {
            $dagenString = $dagenString . "6,";
        }
        
        if (isset($_POST['zondag|7'])) {
            $dagenString = $dagenString . "7,";
        }

        $dagenString = substr($dagenString, 0, -1);

        // Input van puls word * 10 gedaan voor juiste puls duur
        $pulsduur = intval($_POST['pulsduur'][$i]) * intval(10);

        if ($pulsduur == 0 && $_POST["aanuit"][$i] == 3) {
            $pulsduur = 10;
        }

        // Als alles juist is word het in de DB geinsert
        $query = "UPDATE moTimeflow_tijden_v2 SET `pauzeTijd` = :tijd, `weekDagen` = :dagenString, `pulsDuur` = :pulsduur, `omschrijving` = :omschrijving, `relayNummer` = :relaynummer, `aanuit` = :aanuit WHERE id = :id AND klant_id = :klant_id";

        $stmt = $conn->prepare($query);

        $stmt->bindValue(':tijd', $_POST["tijd"][$i]);
        $stmt->bindValue(':dagenString', $dagenString);
        $stmt->bindValue(':pulsduur', $pulsduur);
        $stmt->bindValue(':omschrijving', $_POST['omschrijving'][$i]);
        $stmt->bindValue(':relaynummer', $_POST['relaynummer'][$i]);
        $stmt->bindValue(':aanuit', $_POST['aanuit'][$i]);
        $stmt->bindValue(':id', $_GET["id"]);
        $stmt->bindValue(':klant_id', $_SESSION["user"]["klant_nmr"]);

        $i++;

        if (!$stmt->execute()) {
            http_response_code(400);
            exit('<p class="text-danger my-1"><i class="fa-fw fa-solid fa-circle-exclamation me-1"></i>Er is iets misgegaan. Neem contact op met de systeembeheerder indien dit probleem blijft optreden.</p>');
        }
    }

    http_response_code(200);
    exit('<p class="text-success my-1"><i class="fa-fw fa-solid fa-check me-1"></i>De wijzigingen zijn succesvol opgeslagen!</p>');     
}


?>