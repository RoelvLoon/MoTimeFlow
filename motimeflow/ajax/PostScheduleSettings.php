<?php
session_start();
if(!isset($_SESSION["user"])) {
    include $_SERVER["DOCUMENT_ROOT"] . "/motimeflow/inc/login.php";
    exit();
}
include $_SERVER["DOCUMENT_ROOT"] . "/motimeflow/components/config.php";

$errors = [];

if (strlen($_POST["naam"]) < 1) {
    array_push($errors, "De naam is een verplicht veld.");
}

if (strlen($_POST["datum"]) < 1) {
    array_push($errors, "De datum is een verplicht veld.");
}

// Check of er al een rooster is met dezelfde tijd
$query = "SELECT * FROM moTimeflow_roosters WHERE klant_nmr = :klant_nmr AND datum = :datum AND NOT id = :id";

$stmt = $conn->prepare($query);

$stmt->bindValue(':klant_nmr', $_SESSION["user"]["klant_nmr"]);
$stmt->bindValue(':datum', $_POST["datum"]);
$stmt->bindValue(':id', $_SESSION["current"]["rooster"]);

$stmt->execute();

if ($stmt->rowCount() > 0) {
    array_push($errors, "Er is al een rooster die ingaat vanaf je gekozen datum.");
}

if (count($errors) > 0) {

    http_response_code(400);

    foreach ($errors as $error) {
        echo '<p class="text-danger mb-0"><i class="fa-fw fa-solid fa-circle-exclamation me-1"></i>'.$error.'</p>';
    }
    
    exit();

} else {

    $ingeschakeld = isset($_POST["ingeschakeld"]) && $_POST["ingeschakeld"] == "on" ? 1 : 0;

    $update = "UPDATE moTimeflow_roosters SET naam=:naam, omschrijving=:omschrijving, datum=:datum, ingeschakeld=:ingeschakeld WHERE id=:id";

    $stmt = $conn->prepare($update);

    $stmt->bindValue(':naam', $_POST["naam"], PDO::PARAM_STR);
    $stmt->bindValue(':omschrijving', $_POST["omschrijving"], PDO::PARAM_STR);
    $stmt->bindValue(':datum', $_POST["datum"], PDO::PARAM_STR);
    $stmt->bindValue(':ingeschakeld', $ingeschakeld, PDO::PARAM_INT);
    $stmt->bindValue(':id', $_SESSION["current"]["rooster"], PDO::PARAM_INT);

    if ($stmt->execute()) {
        http_response_code(200);
        exit('<p class="text-success my-1"><i class="fa-fw fa-solid fa-check me-1"></i>De wijzigingen zijn succesvol opgeslagen!</p>');     
    } else {
        http_response_code(400);
        exit('<p class="text-danger my-1"><i class="fa-fw fa-solid fa-circle-exclamation me-1"></i>Er is iets misgegaan. Neem contact op met de systeembeheerder indien dit probleem blijft optreden.</p>');
    }

}

?>