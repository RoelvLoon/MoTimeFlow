// // Na het laden van de pagina:
// $(document).ready(function() {
//     loadOverview();
//     loadSchedule(1);
// });

// Buttons
$(document).on("click", "#refreshOverview", function() {
    loadOverview()
});

$(document).on("click", "#settingsSchedule", function() {
    openModal("modal", "/motimeflow/ajax/GetScheduleSettings.php")
});

$(document).on("click", "#clearTimes", function() {
    openModal("modal", "/motimeflow/ajax/GetClearTimes.php", "modal-xl")
});

$(document).on("click", "#addTimeToSchedule", function() {
    openModal("modal", "/motimeflow/ajax/GetTimeForm.php", "modal-xl")
});

$(document).on("click", "#editTimeButton", function() {
    openModal("modal", "/motimeflow/ajax/GetTimeEditForm.php?id=" + $(this).attr("data-id"), "modal-xl")
});

$(document).on("click", "#deleteTimeButton", function() {
    openModal("modal", "/motimeflow/ajax/GetTimeDelete.php?id=" + $(this).attr("data-id"))
});

$(document).on("click", ".schedule_button", function() {
    $(".schedule_button").removeClass("active");
    $(".schedule_button").removeAttr("aria-current");

    $(this).addClass("active");
    $(this).attr("aria-current", "page");

    loadSchedule($(this).attr("data-schedule"));
});

// Functies
async function loadOverview() {
    $("#overview").html(generateSpinner("MoBlue", $("#overview").height()));
    $("#overview").load("/motimeflow/ajax/GetOverview.php");
}

async function loadSchedule(schedule) {
    $("#schedules").html(generateSpinner("MoBlue", $("#schedules").height()));
    $("#schedules").load("/motimeflow/ajax/GetSchedule.php?id=" + schedule);
}

async function openModal(id, ajax, width = "") {

    // Geef het modal de gewenste grootte, daarvoor moeten eerst alle andere classes verwijderd worden (indien die er zijn)
    $("#" + id + " div").removeClass("modal-sm")
    $("#" + id + " div").removeClass("modal-lg")
    $("#" + id + " div").removeClass("modal-xl")

    $("#" + id + " div").addClass(width)

    // Initialiseer de Bootstrap modal
    let modal = new bootstrap.Modal(document.getElementById(id));

    // Open het modal
    modal.show();

    // Zet een laadscherm in het modal
    $("#" + id + " div div.modal-content").html('<div class="modal-header bg-MoBlue"><h5 class="modal-title" id="modalLabel">Laden...</h5>    <button type="button" class="btn text-white fs-5" data-bs-dismiss="modal" aria-label="Sluiten"><i class="fa-fw fa-solid fa-xmark"></i></button></div><div class="modal-body">' + generateSpinner() + '</div>');

    // Laad de content van het modal in via AJAX.
    $("#" + id + " div div.modal-content").load(ajax);
    
}

function generateSpinner(color = "MoBlue", height = "auto") {

    if (height <= 128) {
        height = "auto";
    } else {
        height = height + "px";
    }

    return `<div style="height: ${height};" class="d-flex justify-content-center align-items-center">
                <div class="spinner-border text-${color} m-5" role="status">
                    <span class="visually-hidden">Laden..</span>
                </div>
            </div>`;
} 

function startCountdown(eventId, endTime) {
    let countdown = $("#" + eventId);
    let interval = setInterval(function() {
        let currentTime = new Date().getTime();
        let distance = endTime - currentTime;
        
        // let days = Math.floor(distance / (1000 * 60 * 60 * 24));
        let hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        let minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        let seconds = Math.floor((distance % (1000 * 60)) / 1000);
        
        // countdown.html(days + "d " + hours + "u " + minutes + "m " + seconds + "s ");
        countdown.html('<i class="fa-fw fa-regular fa-hourglass-half me-1"></i>' + hours + "u " + minutes + "m " + seconds + "s ");
        
        if (distance < 0) {
            clearInterval(interval);
            countdown.html('<i class="fa-fw fa-solid fa-check me-1"></i>Verstreken');

            let countdownBlock = $(countdown).closest("tr").find(".bg-MoBlue");

            $(countdownBlock).addClass("bg-MoGreen").removeClass("bg-MoBlue");
            
        }
    }, 1000);
}