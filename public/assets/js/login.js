$(document).ready(function() {
    let $errorInput = $("#err");
    let errorMessage = $errorInput.val();
    if(errorMessage != ""){
        toastr.error(errorMessage);
    }
})
