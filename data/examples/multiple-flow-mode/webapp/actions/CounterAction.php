<?php
class CounterAction
{
    function initialize(&$flow, $event, &$context)
    {
        $flow->setAttribute('counter', 0);
    }

    function increase(&$flow, $event, &$context)
    {
        $flow->setAttribute('counter', $flow->getAttribute('counter') + 1);
    }

    function reached(&$flow, $event, &$context)
    {
        return $flow->getAttribute('counter') >= 10;
    }
}
?>
