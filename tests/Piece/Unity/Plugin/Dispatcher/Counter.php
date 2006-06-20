<?php
if (isset($__continuation) && is_a($__continuation, 'Piece_Flow_Continuation')
    && isset($__flowExecutionTicketKey) && is_string($__flowExecutionTicketKey)
    && isset($__flowNameKey) && is_string($__flowNameKey)
    && isset($__flowExecutionTicket) && is_string($__flowExecutionTicket)
    ) {
    print 'OK';
} else {
    print 'NG';
}
?>
