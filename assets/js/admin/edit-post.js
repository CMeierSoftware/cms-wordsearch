const element = document.querySelector(".cmsws-shortcode");
if (element != null) {
    element.onclick = function () {
        document.execCommand("copy");
    };

    element.addEventListener("copy", function (event) {
        event.preventDefault();
        if (event.clipboardData) {
            event.clipboardData.setData("text/plain", element.textContent);
        }
        var tooltipText = document.querySelector(".cmsws-tooltip-copy");

        tooltipText.style.visibility = "visible";
        tooltipText.style.opacity = "1";

        setTimeout(function () {
            tooltipText.style.visibility = "hidden";
            tooltipText.style.opacity = "0";
        }, 2000);
    });
}

