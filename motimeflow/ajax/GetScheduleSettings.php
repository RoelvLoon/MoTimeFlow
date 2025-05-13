<?php
session_start();
if(!isset($_SESSION["user"])) {
    include $_SERVER["DOCUMENT_ROOT"] . "/motimeflow/inc/login.php";
    exit();
}
include $_SERVER["DOCUMENT_ROOT"] . "/motimeflow/components/config.php";

$queryrooster = "SELECT * FROM moTimeflow_roosters WHERE klant_nmr = " . $_SESSION["user"]["klant_nmr"] . " AND rooster_nmr = " . $_SESSION["current"]["rooster_nmr"];
$stmt = $conn->prepare($queryrooster);
$stmt->execute();
$row = $stmt->fetch(PDO::FETCH_ASSOC);

$naam = e($row['naam']);
$omschrijving = e($row['omschrijving']);
$datum = e($row['datum']);
$ingeschakeld = e($row['ingeschakeld']);

?>

<div class="modal-header bg-MoBlue">
    <h5 class="modal-title" id="modalLabel"><i class="fa-fw fa-solid fa-gear me-1"></i>Roosterinstellingen (<?= $_SESSION["current"]["rooster_name"] ?>)</h5>
    <button type="button" class="btn text-white fs-5" data-bs-dismiss="modal" aria-label="Sluiten"><i class="fa-fw fa-solid fa-xmark"></i></button>
</div>
<div class="modal-body" id="response">

    <form name="scheduleSettings" id="scheduleSettings">
        <div class="mb-3">
            <label for="inputNaam" class="form-label">Roosternaam</label>
            <input type="text" name="naam" maxlength="255" class="form-control" id="inputNaam" value="<?= $naam; ?>">
        </div>
        <div class="mb-3">
            <label for="inputOmschrijving" placeholder="Interne omschrijving" class="form-label">Omschrijving</label>
            <textarea class="form-control" name="omschrijving" id="inputOmschrijving" rows="3" maxlength="255" placeholder="Interne omschrijving"><?= $omschrijving; ?></textarea>
        </div>
        <div class="mb-3">
            <label for="inputDatum" class="form-label">Datum</label>
            <input type="date" name="datum" class="form-control" id="inputDatum" aria-describedby="inputDatumHelp" value="<?= $datum; ?>">
            <div id="inputDatumHelp" class="form-text">De datum bepaald vanaf welke periode het rooster actief is.</div>
        </div>
        <div class="ms-2 mb-3 form-check form-switch d-flex align-items-center">
            <input class="form-check-input fs-5" type="checkbox" name="ingeschakeld" id="inputIngeschakeld" <?php if ($ingeschakeld == 1) { echo " checked"; $AanUit = "uitschakelen";}else{$AanUit = "inschakelen";} ?>>
            <label class="form-check-label ms-2" for="inputIngeschakeld">Rooster <?=$AanUit?>?</label>
        </div>
    </form>
    
    <div id="errors"></div>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" id="close">Annuleren</button>
    <button type="button" class="btn btn-MoBlue" id="submit">Opslaan</button>
</div>

<!-- Jquery voor afhandelen AJAX POST call -->
<script> 

$(document).ready(function(){

    $('#submit').click(function(){

        let formData = $("#scheduleSettings").serializeArray();
        let jsonData = formData.reduce(function(obj, item) {
            obj[item.name] = item.value;
            return obj;
        }, {});

        $('#submit').attr("disabled", "disabled");
        $('#submit').html('<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Opslaan');

        $.ajax({  
                url:"/motimeflow/ajax/PostScheduleSettings.php",  
                method:"POST",  
                data: $('#scheduleSettings').serialize(),  
                success:function(data)  
                {   
                    $('#response').html(data); 
                    $('#submit').addClass("d-none"); 
                    $('#close').html("Sluiten");
                    $('#roosterTab<?=$_SESSION["current"]["rooster_nmr"]; ?>').html(jsonData.naam);
                    loadOverview();
                    loadSchedule(<?= $_SESSION["current"]["rooster_nmr"]; ?>);
                },
                error:function(request, status, error){
                    $('#errors').html(request.responseText);  
                    $('#submit').removeAttr("disabled");
                    $('#submit').html('Opslaan');
                }
        });
    });
});

</script>