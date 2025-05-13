<?php
session_start();
if(!isset($_SESSION["user"])) {
    include $_SERVER["DOCUMENT_ROOT"] . "/motimeflow/inc/login.php";
    exit();
}
include $_SERVER["DOCUMENT_ROOT"] . "/motimeflow/components/config.php"; 

// echo "<pre class='text-black'>";
// print_r($_SESSION);
// echo "</pre>";

$id = 1;

// Alle gegevens worden opgehaald uit de DB met de datum van vandaag
$query = "SELECT *
FROM moTimeflow_roosters
WHERE ingeschakeld = 1 AND klant_nmr = '" . $_SESSION["user"]["klant_nmr"] . "' AND datum = 
(SELECT MAX(datum) FROM moTimeflow_roosters WHERE datum <= CURRENT_DATE() AND ingeschakeld = 1 AND klant_nmr = '" . $_SESSION["user"]["klant_nmr"] . "')
";
$stmt = $conn->prepare($query);
$stmt->execute();

if ($stmt->rowCount() > 0) {
    while ($rowRooster = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $id = e($rowRooster['rooster_nmr']);
    }
}

?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <?php include "components/head.php"; ?>
</head>
<body class="bg-MoLightGray">
    <?php include "components/nav.php"; ?>

    <main class="container-fluid my-5">

        <div class="row d-flex justify-content-center">

            <div class="col-md-11 bg-white text-black rounded shadow-sm h-50 p-0 p-md-5">
                
                <div class="card shadow-sm mb-5">

                    <div class="card-header d-flex justify-content-between">
                        <span><i class="fa-fw fa-regular fa-clock align-middle me-1"></i><span class="align-middle">Vandaag (<?= $days[date($day_of_week)]?>)</span></span>
                        <button type="button" class="btn btn-light btn-sm" title="Overzicht verversen" id="refreshOverview"><i class="fa-fw fa-solid fa-rotate-right"></i></button>
                    </div>

                    <div class="card-body p-0 table-responsive" id="overview"></div>

                </div>
                
                <div class="card shadow-sm">

                    <div class="card-header">
                        <i class="fa-fw fa-regular fa-calendar me-1"></i>Roosters
                    </div>

                    <div class="card-body">

                    <ul class="d-flex justify-content-between p-0 m-0">
                        <div class="nav nav-tabs border-bottom-0">
                            <?php
                            
                            
                            $query = "SELECT * FROM moTimeflow_roosters WHERE klant_nmr = " . $_SESSION["user"]["klant_nmr"];
                            $stmt = $conn->prepare($query);
                            $stmt->execute();

                            if ($stmt->rowCount() > 0) {
                                $i = 1;
                                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                    
                                    echo '<li class="nav-item">';
                                        echo '<button data-schedule="' . $i . '" id="roosterTab' . $i . '"  class="schedule_button nav-link';
                                        
                                        if ($i == $id) {
                                            echo ' active" aria-current="page';
                                        }

                                        echo '">' . $row["naam"] . '</button>';
                                    echo '</li>';

                                    $i++;
                                }
                            }
                            
                            ?>
                        </div>
                        <div>
                            <button type="button" class="btn btn-secondary btn-sm" id="settingsSchedule"  data-bs-toggle="tooltip" data-bs-placement="top" title="Alle rooster instellingen"><i class="fa-fw fa-solid fa-gear"></i></button>
                            <button type="button" class="btn btn-MoRed btn-sm" id="clearTimes"  data-bs-toggle="tooltip" data-bs-placement="top" title="Alle tijden verwijderen"><i class="fa-fw fa-solid fa-trash"></i></button>
                            <button type="button" class="btn btn-MoGreen btn-sm" id="addTimeToSchedule"  data-bs-toggle="tooltip" data-bs-placement="top" title="Nieuw tijdstip toevoegen"><i class="fa-fw fa-solid fa-plus"></i></button>
                        </div>
                    </ul>

                    <div class="border table-responsive" id="schedules"></div>

                    </div>
                </div>

            </div>

        </div>

    </main>

    <!-- Modal -->
    <div class="modal fade text-black" id="modal" tabindex="-1" aria-labelledby="modalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content"></div>
        </div>
    </div>

</body>
</html>

<script>

    // Na het laden van de pagina:
    $(document).ready(function() {
        loadOverview();
        loadSchedule(<?= $id; ?>);
    });

    let tooltipTriggerList2 = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    let tooltipList2 = tooltipTriggerList2.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    })

</script>