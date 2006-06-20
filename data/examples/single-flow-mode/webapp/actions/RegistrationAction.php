<?php
class RegistrationAction
{
    function validate(&$flow, $event, &$context)
    {
        $request = &$context->getRequest();
        $flow->setAttribute('firstName', $request->getParameter('firstName'));
        $flow->setAttribute('lastName', $request->getParameter('lastName'));

        return 'succeed';
    }

    function register(&$flow, $event, &$context)
    {
        return 'succeed';
    }
}
?>
