<?php
if (isset($__request) && is_a($__request, 'Piece_Unity_Request')
    && isset($__session) && is_a($__session, 'Piece_Unity_Session')
    && isset($__eventNameKey) && is_string($__eventNameKey)
    && isset($__baseURLPath) && is_string($__baseURLPath)
    ) {
    print 'OK';
}
?>
