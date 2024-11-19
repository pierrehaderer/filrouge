function openVideoGuide() {
    openInNewTab('todo.html');
}
function toastSuccess(message) {
    var toast = "<div class='toast toast-success' id='toast'><div class='d-flex'><div class='toast-body'>" + message + "</div><button type='button' class='btn-close me-2 m-auto' data-bs-dismiss='toast'></button></div></div>";
    $(".toast-modal-container").html("");
    $(".toast-container").html(toast);
    $(".toast").toast("show");
}

function toastWarning(message) {
    var toast = "<div class='toast toast-warning' id='toast' data-bs-autohide='false'><div class='d-flex'><div class='toast-body'>" + message + "</div><button type='button' class='btn-close me-2 m-auto' data-bs-dismiss='toast'></button></div></div>";
    $(".toast-modal-container").html("");
    $(".toast-container").html(toast);
    $(".toast").toast("show");
}

function toastError(message) {
    var toast = "<div class='toast toast-error' id='toast' data-bs-autohide='false'><div class='d-flex'><div class='toast-body'>" + message + "</div><button type='button' class='btn-close me-2 m-auto' data-bs-dismiss='toast'></button></div></div>";
    $(".toast-modal-container").html("");
    $(".toast-container").html(toast);
    $(".toast").toast("show");
}

function toastHide() {
    $(".toast").toast("hide");
}

function redirect(address) {
    window.location.href = address;
}

function redirectTableauDesScores(tableau, cleSejour) {
    if (tableau == "READY_PLAYER_ONE") {
        redirect("index.html?sejour=" + cleSejour);
    } else if (tableau == "HOGWARTS_SOLO") {
        redirect("hogwarts_solo.html?sejour=" + cleSejour);
    } else if (tableau == "HOGWARTS_TEAM") {
        redirect("hogwarts_team.html?sejour=" + cleSejour);
    }
}

function openInNewTab(url) {
  window.open(url, '_blank').focus();
}

function getHttpParam(param) {
    var urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has(param)) {
        return urlParams.get(param);
    }
    return "";
}

function escapeQuote(param) {
    return param.replace(/'/g, "&rsquo;");
}

function activateTooltips() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
}

function loadOverlay() {
  $(".overlay").show(0);
}

function unloadOverlay() {
  $(".overlay").hide(0);
}
