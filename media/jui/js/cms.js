/**
 * Sets the HTML of the container-collapse element
 */
function setcollapse(url, name, height) {
    if (!document.getElementById('modal-' + name)) {
        document.getElementById('container-collapse').innerHTML = '<div class="collapse fade" id="modal-' + name + '"><iframe class="iframe" src="' + url + '" height="'+ height + '" width="100%"></iframe></div>';
    }
}
