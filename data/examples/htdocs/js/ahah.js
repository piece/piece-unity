// $Id$

function ahah(url, target, delay) {
    if (window.XMLHttpRequest) {
        req = new XMLHttpRequest();
    } else if (window.ActiveXObject) {
        req = new ActiveXObject('Microsoft.XMLHTTP');
    }

    if (req != undefined) {
        req.onreadystatechange = function() { ahahDone(url, target, delay); };
        req.open('GET', url, true);
        req.send(null);
    }
}  

function ahahDone(url, target, delay) {
    if (req.readyState == 4) { // only if req is "loaded"
        if (req.status == 200) { // only if "OK"
            document.getElementById(target).innerHTML = req.responseText;
        } else {
            document.getElementById(target).innerHTML = "AHAH error:\n" + req.statusText;
        }

        if (delay != undefined) {
            setTimeout('ahah(url, target, delay)', delay); // resubmit after delay
            //server should ALSO delay before responding
        }
    }
}
