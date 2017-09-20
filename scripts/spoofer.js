/**
 * Created by ralphy on 20/09/17.
 */

/**
 * Will spoof/unspoof any text
 * @param sText {string} text to be ciphered
 * @param fKey {number} the seed key
 * @param bReverse {boolean}
 * @return {string}
 */
function spoof(sText, fKey, bReverse) {
    var fRnd = fKey, n;
    var MAX_CHAR = 126;
    var MIN_CHAR = 32;
    var NB_CHARS = MAX_CHAR - MIN_CHAR + 1;
    var sResult = '', c, cx;
    for (var i = 0, l = sText.length; i < l; ++i) {
        fRnd = Math.sin(fRnd);
        n = parseFloat('0.' + fRnd.toString().substr(6, 6)) * NB_CHARS | 0;
        c = sText.charCodeAt(i) - MIN_CHAR;
        if (bReverse) {
            cx = (c + NB_CHARS - n) % NB_CHARS;
        } else {
            cx = (c + n) % NB_CHARS;
        }
        sResult += String.fromCharCode(cx + MIN_CHAR);
    }
    return sResult;
}
