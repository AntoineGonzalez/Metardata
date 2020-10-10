$(document).ready(function() {
    let $loader = $(".upload-completed")
    let $percent = $(".percent")

    $("#send-pictures-btn").on('click', function(e) {
        e.preventDefault()

        let files = $("#pictures")[0].files;

        if(files.length > 0) {
            $("#send-pictures-btn").attr("disabled", "disabled")
            $("#send-pictures-btn").css("background-color", "grey")
            $('.drop-files input[type="file"]').attr("disabled", "disabled")

            $loader.show()

            let formData = new FormData();

            for(let i = 0; i < files.length; i++) {
                formData.append("attachment[]", files[i])
            }

            var xhr = new XMLHttpRequest();

            xhr.open('POST', '?a=uploadPicture');

            xhr.setRequestHeader('X-Requested-With', 'xmlhttprequest')

            xhr.addEventListener('load', function(e) {
                let response = JSON.parse(e.target.response)
                if(response.status === 200) {
                    toastr.success('Fichier(s) uploadés avec succès!')
                    displayForms(response)
                } else {
                    toastr.error(`Oups...quelque chose s'est mal passé..`)
                }

                $percent.text("")
                $loader.hide()
            });

            xhr.upload.addEventListener('progress', function(e) {
                let progress = Math.trunc((e.loaded / e.total) * 100);
                $percent.text(progress+"%")
            });

            xhr.send(formData);  // Upload du fichier…
        } else {
            toastr.info(`Aucun fichier selectionné`)
        }
    })
});

function displayForms(metadata) {
    let $formTemplate = $(".metadata-form-template")
    let $formContainer = $(".metadata-forms")
    let updatedData = []
    let currentUpdating = 0

    metadata.picturesAdded.forEach(function(picture, index) {
        let newData = {}
        newData.path = picture.data["SourceFile"]

        let clone = $formTemplate.contents().clone().hide()
        let $button  = $("<button>", { class: "collapsible", text: `Fichier ${index+1} - ${picture.filename}`})
        let iptc     = picture.data["IPTC"]
        if(iptc) {
            let fields = clone.find('input, textarea')

            for(let i = 0; i < fields.length; i++) {
                $(fields[i]).change(function(e) {
                    newData[$(this).attr("name")] = $(this).val()
                })
                let fieldvalue = iptc[fields[i].name]
                if(fieldvalue) fields[i].value = fieldvalue
            }

            let updateButton = $(clone[1]).find(".update-picture-data")

            $button.on("click", function() {
                clone.toggle("fast", function() {
                    $(".collapsible-content").each(function(i) {
                        if(index !== i) {
                            $(this).hide()
                        }
                    })
                })
            })

            $(updateButton).on("click", function(e) {
                e.preventDefault()

                let updating = $(this).attr("updating")
                let $i = $(this).find("i")

                if(updating == "true") {
                    for(let i = 0; i < fields.length; i++) {
                        fields[i].disabled = "disabled"
                    }
                    currentUpdating = currentUpdating - 1

                    $i.removeClass("fa-check")
                    $i.addClass("fa-edit")


                    $(this).html(`${$i[0].outerHTML} Modifier`)
                    $(this).attr("updating", "false")
                    $(this).css("background-color", "#e57d2b")
                } else {
                    for(let i = 0; i < fields.length; i++) {
                        fields[i].disabled = ""
                    }

                    currentUpdating = currentUpdating +1

                    $i.removeClass("fa-edit")
                    $i.addClass("fa-check")

                    $(this).html(`${$i[0].outerHTML} Valider`)
                    $(this).attr("updating", "true")
                    $(this).css("background-color", "#28a745")
                }
            })
            $formContainer.append($button, clone)
            updatedData.push(newData)
        }
    })

    $(".validate-metadata").on("click", function(e) {
        if(currentUpdating === 0) {
            $(".validate-loader").show()
            saveMetadata(updatedData).then(function() {
                // redirect to detail
                window.location.href = `gallery`
            }).catch(function() {
                toastr.error(`Oups...quelque chose s'est mal passé..`)
            })
        } else {
            if(currentUpdating > 1) {
                toastr.error(`${currentUpdating} modifications sont encore en cours !`)
            } else {
                toastr.error(`${currentUpdating} modification est encore en cours !`)
            }
        }
    })
    $(".validate-metadata").show()
}

function saveMetadata(metadata) {
    return new Promise(function(resolve, reject) {
        $.ajax({
            url: `?a=saveMetadata`,
            type: "POST",
            data: { data: metadata },
            success: function(result){
                result = JSON.parse(result)
                if(result.status === 200) {
                    resolve()
                } else {
                    reject()
                }
            },
            error: function(err) {
                reject(err)
            }
        })
    })
}
