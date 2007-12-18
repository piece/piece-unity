// $Id$

function ahah(url, target, delay, content) {
    if (window.XMLHttpRequest) {
        req = new XMLHttpRequest();
    } else if (window.ActiveXObject) {
        req = new ActiveXObject('Microsoft.XMLHTTP');
    }

    if (req != undefined) {
        req.onreadystatechange = function() { ahahDone(url, target, delay, content); };
        req.open('POST', url, true);
        req.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
        req.setRequestHeader('Accept','application/x-piece-html-fragment');
        req.send(content);
    }
}  

function ahahDone(url, target, delay, content) {
    if (req.readyState == 4) { // only if req is "loaded"
        if (req.status == 200) { // only if "OK"
            document.getElementById(target).innerHTML = req.responseText;
        } else {
            document.getElementById(target).innerHTML = "AHAH error:\n" + req.statusText;
        }

        if (delay != undefined) {
            setTimeout('ahah(url, target, delay, content)', delay); // resubmit after delay
            //server should ALSO delay before responding
        }
    }
}

function sendAHAHRequest(sender, target, delay) {
    if (sender.form) {
        var form = sender.form;
        var data = [
            encodeURIComponent(sender.name) + '=' + encodeURIComponent(sender.value)
        ];

        for (var i=0; i < form.length; i++) {
            var el = form[i];
            if (el.type == 'submit' || el.type == 'button' || el.type == 'reset') {
                continue;
            }

            if (el.type == 'radio' && !el.checked) {
                continue;
            }

            data[data.length] = encodeURIComponent(el.name) + '=' + encodeURIComponent(el.value);
        }

        ahah(form.action, target, delay, data.join('&'));
    } else if (sender.href) {
        ahah(sender.href, target, delay);
    }

    return false;
}
