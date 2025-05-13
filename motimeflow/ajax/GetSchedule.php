<?php
session_start();
if(!isset($_SESSION["user"])) {
    include $_SERVER["DOCUMENT_ROOT"] . "/motimeflow/inc/login.php";
    exit();
}
include $_SERVER["DOCUMENT_ROOT"] . "/motimeflow/components/config.php";

$queryroosters = "SELECT * FROM moTimeflow_roosters WHERE klant_nmr = '" . $_SESSION["user"]["klant_nmr"] . "' AND rooster_nmr = " . $_GET['id'];
$stmt = $conn->prepare($queryroosters);
$stmt->execute();

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $roosterId = $row['id'];
    $roosterNmr = $row['rooster_nmr'];
    $roosterName = $row['naam'];
    $roosterDatum = $row['datum'];
    $ingeschakeld = $row['ingeschakeld'];
}

$_SESSION["current"]["rooster"] = $roosterId;
$_SESSION["current"]["rooster_nmr"] = $roosterNmr;
$_SESSION["current"]["rooster_name"] = $roosterName;

?>

<table class="table table-striped table-hover mb-0">
    <thead>
        <tr>
            <th>Tijden</th>
            <th>Dagen</th>
            <th>Actie</th>
            <th>Omschrijving</th>
            <th>Relayuitgang</th>
            <th></th>
        </tr>
    </thead>
    <?php
        // Alle gegevens worden opgehaald uit de DB met de datum van vandaag
        $queryweek = "SELECT * FROM moTimeflow_tijden_v2 WHERE klant_id = " . $_SESSION["user"]["klant_nmr"] . " AND rooster = $roosterId ORDER BY pauzeTijd ASC";
        $stmt = $conn->prepare($queryweek);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

                if (e($row['aanuit']) == "1") {
                    $actie = "Continu aan";
                } elseif (e($row['aanuit']) == "2"){
                    $actie = "Continu uit";
                } else {
                    $actie = substr(e($row['pulsDuur']), 0, -1) . " seconden";
                }

                echo    '<tr>
                            <td class="align-middle"><span>'.substr(e($row['pauzeTijd']), 0, -3).'</span></td>
                            <td class="align-middle">
                                <div class="d-flex">';

                        $weekDagenArray = array_map('intval', explode(',', e($row['weekDagen'])));

                        for ($i = 1; $i <= 7; $i++) {
                            echo '<div class="d-flex flex-column me-1 text-center">
                                        <span class="small text-secondary">'.$days[date($i)][0].'</span>
                                        <input type="checkbox" class="form-check-input" disabled';
                            
                            if (in_array($i, $weekDagenArray)) { echo " checked"; }

                            echo '/></div>';
                        }

                echo           '</div>
                            </td>
                            <td class="align-middle"><span>'.$actie.'</span></td>
                            <td class="align-middle"><span>'.e($row['omschrijving']).'</span></td>
                            <td class="align-middle"><span>'.e($row['relayNummer']).'</span></td>
                            <td class="align-middle">
                                <button type="button" class="btn btn-sm" data-id="'.e($row['id']).'" id="editTimeButton"><i class="fa-fw fa-solid fa-pen-to-square"></i></button>
                                <button type="button" class="btn btn-sm" data-id="'.e($row['id']).'" id="deleteTimeButton"><i class="fa-fw fa-solid fa-trash"></i></button>
                            </td>
                        </tr>';
            }
        } else {
            echo '<tbody><tr><caption class="text-center"><i class="fa-fw fa-solid fa-calendar-xmark"></i> Er zijn geen gegevens gevonden voor dit rooster.</caption></tr></tbody>';
        }
        ?>
</table>
<div class="card-footer rounded border-top-0 text-muted d-flex justify-content-end small">
    <span><?php if ($ingeschakeld == 1) { echo '<i class="fa-fw fa-solid fa-calendar-check me-1"></i><span class="text-success">' . date("d-m-Y", strtotime($roosterDatum)) . "</span>"; } else { echo '<i class="fa-fw fa-solid fa-calendar-xmark me-1"></i><span class="text-danger">' . date("d-m-Y", strtotime($roosterDatum)) . "</span>"; }?></span>
</div>