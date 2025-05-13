<?php
session_start();
if(!isset($_SESSION["user"])) {
    include $_SERVER["DOCUMENT_ROOT"] . "/motimeflow/inc/login.php";
    exit();
}
include $_SERVER["DOCUMENT_ROOT"] . "/motimeflow/components/config.php";

    // Alle gegevens worden opgehaald uit de DB met de datum van vandaag
    $queryRooster = "SELECT *
    FROM moTimeflow_roosters
    WHERE ingeschakeld = 1 AND klant_nmr = " . $_SESSION["user"]["klant_nmr"] . " AND (klant_nmr, datum) in 
    (SELECT klant_nmr, MAX(datum) FROM moTimeflow_roosters WHERE datum <= CURRENT_DATE() AND ingeschakeld = 1 GROUP BY klant_nmr)";
    // $queryRooster = "SELECT * FROM moTimeflow_roosters WHERE datum <= '$datumVandaag' AND ingeschakeld = 1 AND klant_nmr = " . $_SESSION["user"]["klant_nmr"];
    $stmtRooster = $conn->prepare($queryRooster);
    $stmtRooster->execute();

    if ($stmtRooster->rowCount() > 0) {
        while ($rowRooster = $stmtRooster->fetch(PDO::FETCH_ASSOC)) {
            $id = e($rowRooster['id']);
            $naam = e($rowRooster['naam']);
            $datum = e($rowRooster['datum']);
        }
    }else{
        echo '<p class="text-center mt-3"><i class="fa-fw fa-solid fa-calendar-xmark"></i> Er zijn momenteel geen roosters actief.</p>';
        exit();
    }

    // Andere datum
    $queryRooster = "SELECT *
    FROM moTimeflow_roosters
    WHERE ingeschakeld = 1 AND klant_nmr = " . $_SESSION["user"]["klant_nmr"] . " AND (datum) in 
    (SELECT MAX(datum) FROM moTimeflow_roosters WHERE datum >= CURRENT_DATE() AND ingeschakeld = 1)";
    $stmtRooster = $conn->prepare($queryRooster);
    $stmtRooster->execute();

    if ($stmtRooster->rowCount() > 0) {
        while ($rowRooster = $stmtRooster->fetch(PDO::FETCH_ASSOC)) {
            $datum2 = e($rowRooster['datum']);
        }
    }

?>

<table class="table table-striped table-hover mb-0">
    <thead>
        <tr>
            <th></th>
            <th>Tijd</th>
            <th>Omschrijving</th>
            <th>Relayuitgang</th>
            <th>Actie</th>
            <th></th>
        </tr>
    </thead>
    <?php
    // Alle gegevens worden opgehaald uit de DB met de datum van vandaag
    $query = "SELECT * FROM moTimeflow_tijden_v2 WHERE `weekDagen` LIKE '%$day_of_week%' AND rooster = ".$id." AND klant_id = " . $_SESSION["user"]["klant_nmr"] . " ORDER BY pauzeTijd ASC";
    $stmt = $conn->prepare($query);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

            // "aanuit" uit de database omzetten naar gebruiksvriendelijke tekst
            if ($row['aanuit'] == "1"){
                $actie = "Aan";
            } elseif ($row['aanuit'] == "2"){
                $actie = "Uit";
            } else {
                $actie = substr(e($row['pulsDuur']), 0, -1) . " seconden";
            }

            // Een check op de tijd voor het overzicht tabel
            if ($time <= substr(e($row['pauzeTijd']), 0, -3)) {
                $color = "bg-MoBlue";
                $tooltip = "Dit tijdstip is nog niet verstreken.";
            } else {
                $color = "bg-MoGreen";
                $tooltip = "Dit tijdstip is verstreken.";
            }


            echo'<tr>
                    <td><div class="'.$color.'"style="width: 7px; height: 30px;"></div></td>
                    <td><span>'.substr(e($row['pauzeTijd']), 0, -3).'</span></td>
                    <td><span>'.e($row['omschrijving']).'</span></td>
                    <td><span>'.e($row['relayNummer']).'</span></td>
                    <td><span>'.e($actie).'</span></td>
                    <td><span id="countdown_'.e($row['id']).'"><i class="fa-fw fa-regular fa-clock"></i> Berekenen...</span></td>
                </tr>';

            echo '<script>startCountdown("countdown_'.e($row['id']).'", new Date("';
            echo date('M');
            echo " ";
            echo date('d');
            echo ', ';
            echo date('Y');
            echo " ";
            echo e($row['pauzeTijd']);
            echo '").getTime());</script>';
        }
    } else {
        echo '<tbody><tr><caption class="text-center"><i class="fa-fw fa-solid fa-calendar-xmark"></i> Er zijn geen gegevens gevonden voor vandaag.</caption></tr></tbody>';
    }
    ?>
</table>

<div class="card-footer border-top-0 text-muted d-flex justify-content-end small">
    <span><span class="fw-bolder">Actief rooster</span>: <?= $naam; ?> (<?php 
        if (isset($datum2)) {
            if(strlen($datum2) > 0) {
                if (new DateTime($datum2) > new DateTime($datum)) {
                    echo date("d-m-Y", strtotime($datum)) . " t/m " . date("d-m-Y", strtotime($datum2) - 86400);
                } else {
                    echo "sinds " . date("d-m-Y", strtotime($datum));
                }
            } else {
                echo "sinds " . date("d-m-Y", strtotime($datum));
            }
        } else {
            echo "sinds " . date("d-m-Y", strtotime($datum));
        }
        ?>)
    </span>
</div>