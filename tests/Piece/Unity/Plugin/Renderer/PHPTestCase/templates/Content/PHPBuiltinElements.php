<?php
if (isset($__request) && is_a($__request, 'Piece_Unity_Request')
    && isset($__session) && is_a($__session, 'Piece_Unity_Session')
    && isset($__eventNameKey) && is_string($__eventNameKey)
    && isset($__scriptName) && is_string($__scriptName)
    && isset($__basePath) && is_string($__basePath)
    && isset($__sessionName) && is_string($__sessionName)
    && isset($__sessionID) && is_string($__sessionID)
    && isset($__url) && is_a($__url, 'Piece_Unity_URL')
    ) {
    print 'OK';
}
?>
