let $removeToShopButtons = $(".removeToShop");

$removeToShopButtons.each(function() {
    $(this).on("click", function() {
        let id = $(this).attr("picture-id");
        $.ajax({
            url: "?a=removeToShop",
            type:"POST",
            data:  {pictureId:id },
            success: function(result){
                let products = $(".product")
                products.each(function() {
                    if($(this).attr("picture-id") == id){
                        // remove next divider
                        $(this).next().remove();
                        $(this).remove();
                        $(".total").text(`Total : ${$(".product").length * 2.50}€`)
                        if($(".product").length == 0) {
                            $(".process-paiement-btn").hide()
                        }
                    }
                });
            },
            error: function(error){
                console.error(error);
            }
        });
    });
});

$(document).ready(function() {
    $(".process-paiement-btn").on("click", function(e) {
        e.preventDefault()
        $(".removeToShop").each(function() {
            e.preventDefault()
            $(this).attr("disabled", "disabled")
            $(this).css("background-color", "grey")
            $(this).off()
        })
        $(".mail-container").show()

        $(".mail-adress-added").on("click", function(e) {
            e.preventDefault()
            let mailValue = $(".mail-adress").val()
            let firstname = $(".firstname").val()

            if(validateEmail(mailValue)) {
                $(".selected-mail").text("Mail sélectionné : "+mailValue)
                $(".selected-mail").css("color", "green")

                $.ajax({
                    url: `?a=getPaiementItems&mail=${mailValue}&firstname=${firstname}`,
                    type: "GET",
                    success: function(result) {
                        let res = JSON.parse(result)
                        if(res.status == 200) {
                            $(".cards-logos").html(res.message)
                        } else {
                            let $errorMsg = $("<p>", {
                                text: "Une erreur est survenue..",
                                class: "warning"
                            });
                            $(".cards-logos").html($errorMsg)
                        }
                        $(".paiements").show()
                    },
                    error: function() {
                        console.log(err)
                    }
                });
            } else {
                toastr.error("L'adresse mail n'est pas conforme")
            }
        })
    })


})

function validateEmail(email) {
    let re = /\S+@\S+\.\S+/;
    return re.test(email);
}
