<?php
session_start();
if(!isset($_SESSION["user"])) {
    include $_SERVER["DOCUMENT_ROOT"] . "/motimeflow/inc/login.php";
    exit();
}
include $_SERVER["DOCUMENT_ROOT"] . "/motimeflow/components/config.php";

$query = "SELECT * FROM moTimeflow_tijden_v2 WHERE id = " . $_GET["id"] . " AND klant_id = " . $_SESSION["user"]["klant_nmr"];;
$stmt = $conn->prepare($query);
$stmt->execute();
$row = $stmt->fetch(PDO::FETCH_ASSOC);

$pauzeTijd = e($row['pauzeTijd']);
$weekDagen = e($row['weekDagen']);
$pulsDuur = e($row['pulsDuur']);
$omschrijving = e($row['omschrijving']);
$relayNummer = e($row['relayNummer']);
$aanuit = e($row['aanuit']);

if ($aanuit == "1") {
    $actie = "Continu aan";
} elseif ($aanuit == "2"){
    $actie = "Continu uit";
} else {
    $actie = substr(e($row['pulsDuur']), 0, -1) . " seconden";
}
?>

<div class="modal-header bg-MoBlue">
    <h5 class="modal-title" id="modalLabel"><i class="fa-fw fa-solid fa-trash me-1"></i>Een tijd verwijderen</h5>
    <button type="button" class="btn text-white fs-5" data-bs-dismiss="modal" aria-label="Sluiten"><i class="fa-fw fa-solid fa-xmark"></i></button>
</div>
<div class="modal-body" id="response">

    <p>Weet je zeker dat je dit tijdstip wilt verwijderen?</p>

    <table class="table table-hover table-striped border rounded">
        <tbody>
            <tr>
                <td class="fw-bold">Dagen</td>
                <td class="align-middle">
                    <div class="d-flex"><?php
                  
                        $weekDagenArray = array_map('intval', explode(',', $weekDagen));

                        for ($i = 1; $i <= 7; $i++) {
                            echo '<div class="d-flex flex-column me-1 text-center">
                                        <span class="small text-secondary">'.$days[date($i)][0].'</span>
                                        <input type="checkbox" class="form-check-input" disabled';
                            
                            if (in_array($i, $weekDagenArray)) { echo " checked"; }

                            echo '/></div>';
                        }
                  
                    ?></div>
                </td>
            </tr>
            <tr>
                <td class="fw-bold">Tijd</td>
                <td><?= substr($pauzeTijd, 0, -3); ?></td>
            </tr>
            <tr>
                <td class="fw-bold">Actie</td>
                <td><?= $actie; ?></td>
            </tr>
            <tr>
                <td class="fw-bold">Omschrijving</td>
                <td><?= $omschrijving; ?></td>
            </tr>
            <tr>
                <td class="fw-bold">Relayuitgang</td>
                <td><?= $relayNummer; ?></td>
            </tr>
        </tbody>
    </table>
    <div class="mt-2" id="errors"></div>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" id="close">Annuleren</button>
    <button type="button" class="btn btn-MoRed" id="submit">Verwijderen</button>
</div>

<!-- Jquery voor afhandelen AJAX POST call -->
<script> 

$(document).ready(function(){

    $('#submit').click(function(){


        $('#submit').attr("disabled", "disabled");
        $('#submit').html('<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Verwijderen');

        $.ajax({  
                url:"/motimeflow/ajax/PostTimeDelete.php?id=<?= $_GET["id"]; ?>",  
                method:"GET",
                success:function(data)  
                {   
                    $('#response').html(data); 
                    $('#submit').addClass("d-none"); 
                    $('#close').html("Sluiten");
                    loadOverview();
                    loadSchedule(<?= $_SESSION["current"]["rooster_nmr"]; ?>);
                },
                error:function(request, status, error){
                    $('#errors').html(request.responseText);  
                    $('#submit').removeAttr("disabled");
                    $('#submit').html('Verwijderen');
                }
        });
    });
});

</script>