<?php
session_start();
if(!isset($_SESSION["user"])) {
    include $_SERVER["DOCUMENT_ROOT"] . "/motimeflow/inc/login.php";
    exit();
}
include $_SERVER["DOCUMENT_ROOT"] . "/motimeflow/components/config.php";

$query = "DELETE FROM moTimeflow_tijden_v2 WHERE id = " . e($_GET["id"]) . " AND klant_id = " . $_SESSION["user"]["klant_nmr"];
$stmt = $conn->prepare($query);
if ($stmt->execute()) {

    http_response_code(200);
    exit('<p class="text-success my-1"><i class="fa-fw fa-solid fa-check me-1"></i>Het tijdstip is succesvol verwijderd!</p>');  

} else {

    http_response_code(400);
    exit('<p class="text-danger my-1"><i class="fa-fw fa-solid fa-circle-exclamation me-1"></i>Er is iets misgegaan. Neem contact op met de systeembeheerder indien dit probleem blijft optreden.</p>');
}

?>