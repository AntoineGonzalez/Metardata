import PictureMap from './customElements/map.class.js'

customElements.define('picture-map', PictureMap)

let $addToShopButton = $("#addToShop");
let $removeFromShopButton = $("#removeToShop");

$addToShopButton.on("click", function() {
    let id = $("#picture").attr("picture-id");
    addItem(id).then(function(res) {
        res = JSON.parse(res)
        if(res.status === 200) {
            toastr.success('Ajouté au panier !')
        } else if(res.status === 208) {
            toastr.info('Déjà dans le panier')
        }
    }).catch(function(err) {
        toastr.error('Oups..Quelque chose s\'est mal passé')
    })
})

$removeFromShopButton.on("click", function() {
    let id = $("#picture").attr("picture-id");
    removeItem(id).then(function(res) {
        res = JSON.parse(res)
        if(res.status === 200) {
            toastr.error('Retiré du panier !')
        } else if(res.status === 404) {
            toastr.info('L\'image n\'est pas dans le panier')
        }
    }).catch(function(e) {
        toastr.error('Oups..Quelque chose s\'est mal passé')
    })
})

function removeItem(id) {
    return new Promise(function(resolve, reject) {
        $.ajax({
            url: "?a=removeToShop",
            type:"POST",
            data: { pictureId:id },
            success: function(result){
                resolve(result);
            },
            error: function(error){
                reject(error)
            }
        });
    })
}

function addItem(id) {
    return new Promise(function(resolve, reject) {
        $.ajax({
            url: "?a=addToShop",
            type:"POST",
            data: { pictureId:id },
            success: function(result){
                resolve(result);
            },
            error: function(error){
                reject(error)
            }
        });
    })
}

function removePicture(){
    let id = $("#picture").attr("picture-id");
    $.ajax({
        url: "?a=deletePicture&pictureId="+id,
        type:"DELETE",
        success: function(result){
            let res = JSON.parse(result);
            if(res.status==200){
                window.location.href="gallery?refresh";
            }
        },
        error: function(error){
            console.error(error);
        }
    });
}
let $formContainer = $(".metadata-form-container");
let $formTemplate = $(".metadata-form-template");
let $removeButton = $("#suppr-btn");
let $modifButton = $("#modif-btn");

let clone = $formTemplate.contents().clone().hide();
$formContainer.hide()
$formContainer.append(clone);
let newData={};
newData.path= $("#picture img").attr("src");

function toggleForm(){
    let $form = $(".collapsible-content");
    let $inputs = $form.find("input, textarea");
    let $main = $("main");

    if($form.is(":visible")){
        $inputs.each( function (){
            $(this).attr("disabled","");
        });
        $form.hide();
        $formContainer.hide()
    }else{
        $inputs.each( function (){
            $(this).removeAttr("disabled");
            $(this).change( function (){
                newData[$(this).attr("name")] = $(this).val();
            });
        });
        let id = $("#picture").attr("picture-id");
        $.ajax({
            url: "?a=getIptcForPicture&pictureId="+id,
            type:"GET",
            success: function(result){
                let res = JSON.parse(result);
                if(res.status==200){
                    $inputs.each( function(){
                        $(this).val(res.iptc[$(this).attr("name")]);
                    });
                }
            },
            error: function(error){
                console.error(error);
            }
        });

        $form.show();
        $formContainer.show()
    }
}

function updatePictureData(){
    let updatedData = []
    updatedData.push(newData);
    $.ajax({
        url: `?a=saveMetadata`,
        type: "POST",
        data: { data:updatedData },
        success: function(result){
            result = JSON.parse(result)
            if(result.status === 200) {
                window.location.reload(true);
            }
        },
        error: function(err) {
            console.log(err);
        }
    });
}

if(!$removeButton.length == 0 ){
    $removeButton.on("click", e => {
        e.preventDefault()
        removePicture();
    });
}

if(!$modifButton.length == 0 ){
    $modifButton.on("click", e => {
        e.preventDefault()
        toggleForm();
    });
}

let $submitModif = $(".update-picture-data");
$submitModif.on("click", e => {
    e.preventDefault()
    updatePictureData();
});
