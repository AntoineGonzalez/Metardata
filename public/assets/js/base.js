$(document).ready(function() {
    let path = window.location.href
    let pathParts = path.split("/")
    let currentPage = pathParts[pathParts.length - 1]
    let linkFound = false
    /* refresh active item */
    $(".menu-item").each(function(index) {
        let linkName = $(this).attr("href")
        if(linkName == currentPage) {
            linkFound = true
            $(this).addClass("active")
        } else {
            $(this).removeClass("active")
        }
    })

    if(!linkFound) {
        $($(".menu-item").get(0)).addClass("active")
    }
})

function w3_open() {
  $(".sidenav").css("display", "block")
  $("body").removeClass("horizontal-header")
  $("body").addClass("vertical-header")
}

function w3_close() {
  $(".sidenav").css("display", "none")
  $("body").removeClass("vertical-header")
  $("body").addClass("horizontal-header")
}

function displayMenu(x) {
  x.classList.toggle("change");
  if($(".sidenav").is(":visible")) {
      w3_close()
  } else {
      w3_open()
  }
}
