/**
 * Created by ralphy on 21/09/17.
 */
function main() {
    var aSpoofed = Array.prototype.slice.call(document.querySelectorAll('.spoofed'), 0);
    aSpoofed.forEach(function(a) {
        var sMail = spoof(a.getAttribute('data-spoof'), Math.E, true);
        a.setAttribute('href', 'mailto:' + sMail);
        a.innerHTML += sMail;
        a.removeAttribute('data-spoof');
    });
}

window.addEventListener('load', main);