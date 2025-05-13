<?php
session_start();
if(!isset($_SESSION["user"])) {
    include $_SERVER["DOCUMENT_ROOT"] . "/motimeflow/inc/login.php";
    exit();
}
include $_SERVER["DOCUMENT_ROOT"] . "/motimeflow/components/config.php"; 
?>

<div class="modal-header bg-MoBlue">
    <h5 class="modal-title" id="modalLabel"><i class="fa-fw fa-solid fa-pen-to-square me-1"></i>Tijd toevoegen in rooster <?= $_SESSION["current"]["rooster_name"] ?></h5>
    <button type="button" class="btn text-white fs-5" data-bs-dismiss="modal" aria-label="Sluiten"><i class="fa-fw fa-solid fa-xmark"></i></button>
</div>
<div class="modal-body" id="response">

    <div id="responseFullPage">
        <form name="timeAdd" id="timeAdd">
            <table class="table" id="dynamic_field">
                <thead>
                    <tr>
                        <th>Dagen</th>
                        <th class="text-center" scope="col">Tijd</th>
                        <th class="text-center" scope="col">Actie</th>
                        <th class="text-center" scope="col">Omschrijving</th>
                        <th class="text-center" scope="col">Relayuitgang</th>
                        <th></th>
                    </tr>
                </thead>
                <tr class="align-middle" id="addForm">
                    <td>
                        <input type="checkbox" id="mo" name="maandag|1">
                        <label for="mo">maandag</label><br>
                        <input type="checkbox" id="di" name="dinsdag|2">
                        <label for="di">dinsdag</label><br>
                        <input type="checkbox" id="wo" name="woensdag|3">
                        <label for="wo">woensdag</label><br>
                        <input type="checkbox" id="do" name="donderdag|4">
                        <label for="do">donderdag</label><br>
                        <input type="checkbox" id="vr" name="vrijdag|5">
                        <label for="vr">vrijdag</label><br>
                        <input type="checkbox" id="za" name="zaterdag|6">
                        <label for="za">zaterdag</label><br>
                        <input type="checkbox" id="zo" name="zondag|7">
                        <label for="zo">zondag</label><br>
                    </td>

                    <td class="text-center">
                        <input id="tijd" type="time" name="tijd[]" placeholder="Tijd" class="form-control">
                    </td>

                    <td class="text-center">
                        <div class="d-flex justify-content-center">
                            <select class="form-select w-75 pulsSelect" name="aanuit[]" aria-label="Selecteer een actie...">
                                <option selected value="0">Selecteer een actie...</option>
                                <option value="1">Continu aan</option>
                                <option value="2">Continu uit</option>
                                <option value="3">Aantal seconden</option>
                            </select>
                        </div>
                        <div class="d-flex justify-content-center">
                            <input value="0" class="mt-2 form-control w-50 pulsduur" style="display: none;" type="number" min="1" max="25" name="pulsduur[]" placeholder="1 t/m 25 sec">
                        </div>
                    </td>

                    <td class="text-center">
                        <textarea style="resize: none;" id="omschrijving" name="omschrijving[]" placeholder="Omschrijving" maxlength="255" class="form-control"></textarea>
                    </td>

                    <td class="text-center">
                        <select class="form-select" id="relaynummer" name="relaynummer[]" aria-label="Relaynummer">
                            <?php
                            for($u=0; $u < $_SESSION["user"]["relay_nmr"]; $u++) {

                                echo'<option value="' . $u + 1 . '">' . $u + 1 . '</option>';
                                
                            }
                            ?>
                        </select>
                    </td>

                </tr>
            </table>
            <table class="w-100">
                <tr>
                    <td>
                        <button type="button" name="add" id="add" class="btn btn-outline-MoGreen w-100"><i class="fa-solid fa-plus"></i></button>
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
// jQuery voor extra rij met tijden

$(document).ready(function(){  

    let i = 1;

    let addForm = $('#addForm').html();

    $('#add').click(function(){  
        i++;
        $('#dynamic_field').append(`<tr id="row${i}"><td></td>${addForm.slice(952)}<td><button type="button" id="${i}" class="btn btn-outline-danger btn_remove" id="row1"><i class="fa-fw fa-solid fa-trash"></i></button></td></tr>`);  
    });

    $(document).on("click", ".btn_remove", function(){  
        var button_id = $(this).attr("id");   
        $(`#row${button_id}`).remove();
        i--;
    });
    
    $(document).on("click", ".pulsSelect", function() {
        if ($(this).val() !== "3") {
            $(this).closest("td").find(".pulsduur").css("display", "none");
        } else {
            $(this).closest("td").find(".pulsduur").css("display", "block");
            $(this).closest("td").find(".pulsduur").val("");
        }
    });

    
    $('#submit').click(function(){

        $('#submit').attr("disabled", "disabled");
        $('#submit').html('<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Opslaan');

        $.ajax({  
                url:"/motimeflow/ajax/PostTimeForm.php",  
                method:"POST",  
                data:$('#timeAdd').serialize(),  
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