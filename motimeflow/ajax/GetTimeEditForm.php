<?php
session_start();
if(!isset($_SESSION["user"])) {
    include $_SERVER["DOCUMENT_ROOT"] . "/motimeflow/inc/login.php";
    exit();
}
include $_SERVER["DOCUMENT_ROOT"] . "/motimeflow/components/config.php";

$query = "SELECT * FROM moTimeflow_tijden_v2 WHERE id = " . e($_GET["id"]) . " AND klant_id = " . $_SESSION["user"]["klant_nmr"];;
$stmt = $conn->prepare($query);
$stmt->execute();
$row = $stmt->fetch(PDO::FETCH_ASSOC);

$pauzeTijd = e($row['pauzeTijd']);
$weekDagen = e($row['weekDagen']);
$pulsDuur = e($row['pulsDuur']);
$omschrijving = e($row['omschrijving']);
$relayNummer = e($row['relayNummer']);
$aanuit = e($row['aanuit']);
?>

<div class="modal-header bg-MoBlue">
    <h5 class="modal-title" id="modalLabel"><i class="fa-fw fa-solid fa-pen-to-square me-1"></i>Tijd bewerken</h5>
    <button type="button" class="btn text-white fs-5" data-bs-dismiss="modal" aria-label="Sluiten"><i class="fa-fw fa-solid fa-xmark"></i></button>
</div>
<div class="modal-body" id="response">

    <div id="responseFullPage">
        <form name="timeEdit" id="timeEdit">
            <table class="table" id="dynamic_field">
                <thead>
                    <tr>
                        <th>Dagen</th>
                        <th class="text-center" scope="col">Tijd</th>
                        <th class="text-center" scope="col">Actie</th>
                        <th class="text-center" scope="col">Omschrijving</th>
                        <th class="text-center" scope="col">Relayuitgang</th>
                    </tr>
                </thead>
                <tr class="align-middle" id="editForm">
                    <td>

                    <?php

                        $weekDagenArray = array_map('intval', explode(',', $weekDagen));

                        for ($i = 1; $i <= 7; $i++) {
                            echo '<input type="checkbox" id="'.$days[date($i)].'" name="'.$days[date($i)].'|'.$i.'" ';

                            if (in_array($i, $weekDagenArray)) { echo " checked"; }

                            echo '> <label for="'.$days[date($i)].'">'.$days[date($i)].'</label><br>';
                            
                        }

                    ?>

                    </td>

                    <td class="text-center">
                        <input value="<?= substr($row['pauzeTijd'], 0, -3) ?>" id="tijd" type="time" name="tijd[]" placeholder="Tijd" class="form-control">
                    </td>

                    <td class="text-center">
                        <div class="d-flex justify-content-center">
                            <select class="form-select w-75 pulsSelect" name="aanuit[]" aria-label="Selecteer een actie...">
                                <option <?php if ($aanuit == 0) { echo "selected"; } ?> value="0">Selecteer een actie...</option>
                                <option <?php if ($aanuit == 1) { echo "selected"; } ?> value="1">Continu aan</option>
                                <option <?php if ($aanuit == 2) { echo "selected"; } ?> value="2">Continu uit</option>
                                <option <?php if ($aanuit == 3) { echo "selected"; } ?> value="3">Aantal seconden</option>
                            </select>
                        </div>
                        <div class="d-flex justify-content-center">
                            <input value="<?= substr($pulsDuur, 0, -1) ?>" class="mt-2 form-control w-50 pulsduur" style="<?php if ($aanuit == 3) { echo "display: block;"; } else { echo "display: none;"; } ?>" type="number" min="1" max="25" name="pulsduur[]" placeholder="1 t/m 25 sec">
                        </div>
                    </td>

                    <td class="text-center">
                        <textarea style="resize: none;" id="omschrijving" name="omschrijving[]" placeholder="Omschrijving" maxlength="255" class="form-control"><?= $omschrijving; ?></textarea>
                    </td>

                    <td class="text-center">
                        <select class="form-select" id="relaynummer" name="relaynummer[]" aria-label="Relaynummer">
                            <?php
                            for($u=0; $u < $_SESSION["user"]["relay_nmr"]; $u++) {

                                if ($u + 1 == $relayNummer) {
                                    echo'<option selected value="' . $u + 1 . '">' . $u + 1 . '</option>';
                                } else {
                                    echo'<option value="' . $u + 1 . '">' . $u + 1 . '</option>';
                                }
                                
                            }
                            ?>
                        </select>
                    </td>

                </tr>
            </table>
        </form>
        <div id="responseNotification" class="mt-2"></div>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" id="close">Annuleren</button>
    <button type="button" id="submit" class="btn btn-MoBlue">Opslaan</button>
</div>

<script> 
$(document).ready(function(){  

    $(document).on("click", ".pulsSelect", function() {
        if ($(this).val() !== "3") {
            $(this).closest("td").find(".pulsduur").css("display", "none");
        } else {
            $(this).closest("td").find(".pulsduur").css("display", "block");
        }
    });

    
    $('#submit').click(function(){

        $('#submit').attr("disabled", "disabled");
        $('#submit').html('<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Opslaan');

        $.ajax({  
                url:"/motimeflow/ajax/PostTimeEditForm.php?id=<?= $_GET["id"]; ?>",  
                method:"POST",  
                data:$('#timeEdit').serialize(),  
                success:function(data)  
                {   
                    $('#response').html(data); 
                    $('#submit').addClass("d-none"); 
                    $('#close').html("Sluiten");
                    loadOverview();
                    loadSchedule(<?= $_SESSION["current"]["rooster_nmr"]; ?>);
                },
                error:function(request, status, error){
                    $('#responseNotification').html(request.responseText);  
                    $('#submit').removeAttr("disabled");
                    $('#submit').html('Opslaan');
                }
        });
    });
});
</script>