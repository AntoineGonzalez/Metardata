$(document).ready(function() {
    //pagination init
    updatePagination(1)
})


$('.back-btn').on('click', function() {
    getPreviousPictures().then(function(pictures) {
        updatePictures(pictures);
    }).catch(console.error)
})

$('.next-btn').on('click', function() {
    getNextPictures().then(function(pictures) {
        updatePictures(pictures)
    }).catch(console.error)
})

function updatePictures(pictures) {
    $(".picture-ctn").html("")

    pictures.forEach(function(picture) {

        let $pic = $("<picture>")
        let $source = $("<source>", {
            srcset: picture.jsonPath
        })
        let $img = $("<img>", {
            src: picture.jsonPath
        })

        $pic.on("click", function() {
            window.location.href = `picture/${picture.jsonId}`;
        })

        $pic.append($source, $img)
        $(".picture-ctn").append($pic)
    })
}

function getNextPictures() {
    return new Promise(function(resolve, reject) {
        $.ajax({
            url: `?a=getNextPictures`,
            type: "GET",
            success: function(result){
                let obj = JSON.parse(result)
                if(obj.code === 200) {
                    resolve(obj.pictures)
                    updatePagination(obj.page)
                } else {
                    reject(obj.message)
                }

            },
            error: function(err) {
                console.log("error")
                console.log(err)
                reject(err)
            }
        })
    })
}

function getPreviousPictures() {
    return new Promise(function(resolve, reject) {
        $.ajax({
            url: `?a=getPreviousPictures`,
            type: "GET",
            success: function(result){
                let obj = JSON.parse(result)
                if(obj.code === 200) {
                    resolve(obj.pictures)
                    updatePagination(obj.page)
                } else {
                    reject(obj.message)
                }
            },
            error: function(err) {
                reject(err)
            }
        })
    })
}

function getPicturePage(index) {
    $.ajax({
        url: `?a=getPicturePage&index=${index}`,
        type: "GET",
        success: function(result){
            let obj = JSON.parse(result)
            if(obj.code === 200) {
                updatePictures(obj.pictures)
                updatePagination(obj.page)
            } else {
                console.log(result)
            }
        },
        error: function(err) {
            console.log(err)
        }
    })

}

function updatePagination(activePage) {
    let $pagination = $(".pagination")
    $pagination.find("a").removeClass("active")
    $pagination.find(`.page-${activePage}`).addClass("active")
}
