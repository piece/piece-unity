<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * PHP versions 4 and 5
 *
 * Copyright (c) 2006, KUBO Atsuhiro <iteman@users.sourceforge.net>
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 *     * Redistributions of source code must retain the above copyright
 *       notice, this list of conditions and the following disclaimer.
 *     * Redistributions in binary form must reproduce the above copyright
 *       notice, this list of conditions and the following disclaimer in the
 *       documentation and/or other materials provided with the distribution.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @package    Piece_Unity
 * @author     KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @copyright  2006 KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    SVN: $Id$
 * @link       http://iteman.typepad.jp/piece/
 * @see        Piece_Flow_Continuation, Piece_Flow, Piece_Flow_Error
 * @since      File available since Release 0.1.0
 */

require_once 'Piece/Unity/Plugin/Common.php';
require_once 'Piece/Flow/Continuation.php';

// {{{ GLOBALS

$GLOBALS['PIECE_UNITY_Continuation_Session_Key'] = null;
$GLOBALS['PIECE_UNITY_Continuation_FlowExecutionTicket_Key'] = null;
$GLOBALS['PIECE_UNITY_Continuation_FlowName_Key'] = null;
$GLOBALS['PIECE_UNITY_Continuation_FlowName'] = null;

// }}}
// {{{ Piece_Unity_Plugin_Dispatcher_Continuation

/**
 * A dispatcher which dispatches requests to the continuation server based on
 * Piece_Flow.
 *
 * @package    Piece_Unity
 * @author     KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @copyright  2006 KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    Release: @package_version@
 * @link       http://iteman.typepad.jp/piece/
 * @see        Piece_Flow_Continuation, Piece_Flow, Piece_Flow_Error
 * @since      Class available since Release 0.1.0
 */
class Piece_Unity_Plugin_Dispatcher_Continuation extends Piece_Unity_Plugin_Common
{

    // {{{ properties

    /**#@+
     * @access public
     */

    /**#@-*/

    /**#@+
     * @access private
     */

    /**#@-*/

    /**#@+
     * @access public
     */

    // }}}
    // {{{ constructor

    /**
     * Defines extension points and configuration points for the plugin.
     */
    function Piece_Unity_Plugin_Dispatcher_Continuation()
    {
        parent::Piece_Unity_Plugin_Common();
        $this->_addConfigurationPoint('actionDirectory');
        $this->_addConfigurationPoint('enableSingleFlowMode', false);
        $this->_addConfigurationPoint('cacheDirectory');
        $this->_addConfigurationPoint('flowDefinitions', array());
        $this->_addConfigurationPoint('sessionKey', strtolower(__CLASS__));
        $this->_addConfigurationPoint('flowExecutionTicketKey', '_flowExecutionTicket');
        $this->_addConfigurationPoint('flowNameKey', '_flow');
        $this->_addConfigurationPoint('flowName');
    }

    // }}}
    // {{{ invoke()

    /**
     * Invokes the plugin specific code.
     *
     * Starts a new continuation or continues with the current continuation,
     * and returns a view string.
     *
     * @return string
     * @throws PIECE_UNITY_ERROR_INVALID_CONFIGURATION
     * @throws PIECE_UNITY_ERROR_INVOCATION_FAILED
     */
    function invoke()
    {
        $this->_initialize();

        $session = &$this->_context->getSession();
        $continuation = &$session->getAttribute($GLOBALS['PIECE_UNITY_Continuation_Session_Key']);
        if (is_null($continuation)) {
            $continuation = &$this->_createContinuation();
            if (Piece_Unity_Error::hasErrors('exception')) {
                return;
            }

            $session->setAttributeByRef($GLOBALS['PIECE_UNITY_Continuation_Session_Key'], $continuation);
        }

        $continuation->invoke($this->_context);
        if (Piece_Flow_Error::hasErrors('exception')) {
            Piece_Unity_Error::push(PIECE_UNITY_ERROR_INVOCATION_FAILED,
                                    'Failed to invoke the plugin [ ' . __CLASS__ . ' ].',
                                    'exception',
                                    array('plugin' => __CLASS__),
                                    Piece_Flow_Error::pop()
                                    );
            return;
        }

        Piece_Unity_Error::pushCallback(create_function('$error', 'return ' . PEAR_ERRORSTACK_PUSHANDLOG . ';'));
        $view = $continuation->getView();
        Piece_Unity_Error::popCallback();
        if (Piece_Flow_Error::hasErrors('exception')) {
            Piece_Unity_Error::push(PIECE_UNITY_ERROR_INVOCATION_FAILED,
                                    'Failed to invoke the plugin [ ' . __CLASS__ . ' ].',
                                    'exception',
                                    array('plugin' => __CLASS__),
                                    Piece_Flow_Error::pop()
                                    );
            return;
        }

        $this->_setViewElements($continuation);

        return $view;
    }

    // }}}
    // {{{ getEventName()

    /**
     * Gets an event name.
     *
     * @return string
     * @static
     */
    function getEventName()
    {
        $context = &Piece_Unity_Context::singleton();
        return $context->getEventName();
    }

    // }}}
    // {{{ getFlowExecutionTicket()

    /**
     * Gets a flow execution ticket.
     *
     * @return string
     * @static
     */
    function getFlowExecutionTicket()
    {
        $context = &Piece_Unity_Context::singleton();
        $request = &$context->getRequest();
        return $request->hasParameter($GLOBALS['PIECE_UNITY_Continuation_FlowExecutionTicket_Key']) ? $request->getParameter($GLOBALS['PIECE_UNITY_Continuation_FlowExecutionTicket_Key']) : null;
    }

    // }}}
    // {{{ getFlowName()

    /**
     * Gets a flow name.
     *
     * @return string
     * @static
     */
    function getFlowName()
    {
        if (!is_null($GLOBALS['PIECE_UNITY_Continuation_FlowName'])) {
            return $GLOBALS['PIECE_UNITY_Continuation_FlowName'];
        }

        $context = &Piece_Unity_Context::singleton();
        $request = &$context->getRequest();
        return $request->hasParameter($GLOBALS['PIECE_UNITY_Continuation_FlowName_Key']) ? $request->getParameter($GLOBALS['PIECE_UNITY_Continuation_FlowName_Key']) : null;
    }

    /**#@-*/

    /**#@+
     * @access private
     */

    // }}}
    // {{{ _createContinuation()

    /**
     * Creates a new Piece_Flow_Continuation object and configure it.
     *
     * @return Piece_Flow_Continuation
     * @throws PIECE_UNITY_ERROR_INVALID_CONFIGURATION
     */
    function &_createContinuation()
    {
        $continuation = &new Piece_Flow_Continuation($this->getConfiguration('enableSingleFlowMode'));
        $continuation->setCacheDirectory($this->getConfiguration('cacheDirectory'));
        $continuation->setEventNameCallback(array(__CLASS__, 'getEventName'));
        $continuation->setFlowExecutionTicketCallback(array(__CLASS__, 'getFlowExecutionTicket'));
        $continuation->setFlowNameCallback(array(__CLASS__, 'getFlowName'));

        foreach ($this->getConfiguration('flowDefinitions') as $flowDefinition) {
            Piece_Unity_Error::pushCallback(create_function('$error', 'return ' . PEAR_ERRORSTACK_PUSHANDLOG . ';'));
            $continuation->addFlow($flowDefinition['name'],
                                   $flowDefinition['file'],
                                   $flowDefinition['isExclusive']
                                   );
            Piece_Unity_Error::popCallback();
            if (Piece_Flow_Error::hasErrors('exception')) {
                Piece_Unity_Error::push(PIECE_UNITY_ERROR_INVALID_CONFIGURATION,
                                        'Failed to configure the plugin [ ' . __CLASS__ . ' ].',
                                        'exception',
                                        array('plugin' => __CLASS__),
                                        Piece_Flow_Error::pop()
                                        );
                $return = null;
                return $return;
            }
        }

        return $continuation;
    }

    // }}}
    // {{{ _initialize()

    /**
     * Initialize the global variables in the class and the action directory
     * for the current request.
     */
    function _initialize()
    {
        $GLOBALS['PIECE_UNITY_Continuation_Session_Key'] = $this->getConfiguration('sessionKey');
        $GLOBALS['PIECE_UNITY_Continuation_FlowExecutionTicket_Key'] = $this->getConfiguration('flowExecutionTicketKey');
        $GLOBALS['PIECE_UNITY_Continuation_FlowName_Key'] = $this->getConfiguration('flowNameKey');
        $GLOBALS['PIECE_UNITY_Continuation_FlowName'] = $this->getConfiguration('flowName');
        Piece_Flow_Continuation::setActionDirectory($this->getConfiguration('actionDirectory'));
    }

    // }}}
    // {{{ _setViewElements()

    /**
     * Sets the Piece_Flow_Continuation object, the flow execution ticket
     * key, the flow name key, and the current flow execution ticket as a
     * built-in view elements.
     *
     * @param Piece_Flow_Continuation &$continuation
     */
    function _setViewElements(&$continuation)
    {
        $viewElement = &$this->_context->getViewElement();
        $viewElement->setElementByRef('__continuation', $continuation);
        $viewElement->setElement('__flowExecutionTicketKey', $GLOBALS['PIECE_UNITY_Continuation_FlowExecutionTicket_Key']);
        $viewElement->setElement('__flowNameKey', $GLOBALS['PIECE_UNITY_Continuation_FlowName_Key']);
        $viewElement->setElement('__flowExecutionTicket', $continuation->getCurrentFlowExecutionTicket());
    }

    /**#@-*/

    // }}}
}

// }}}

/*
 * Local Variables:
 * mode: php
 * coding: iso-8859-1
 * tab-width: 4
 * c-basic-offset: 4
 * c-hanging-comment-ender-p: nil
 * indent-tabs-mode: nil
 * End:
 */
?>
