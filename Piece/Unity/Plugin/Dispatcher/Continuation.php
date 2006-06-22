<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * PHP versions 4 and 5
 *
 * Copyright (c) 2006, KUBO Atsuhiro <iteman2002@yahoo.co.jp>
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
 * @author     KUBO Atsuhiro <iteman2002@yahoo.co.jp>
 * @copyright  2006 KUBO Atsuhiro <iteman2002@yahoo.co.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    SVN: $Id$
 * @link       http://iteman.typepad.jp/piece/
 * @since      File available since Release 0.1.0
 */

require_once 'Piece/Unity/Plugin/Common.php';
require_once 'Piece/Flow/Continuation.php';

// {{{ GLOBALS

$GLOBALS['PIECE_UNITY_Continuation_Session_Key'] = null;
$GLOBALS['PIECE_UNITY_Continuation_FlowExecutionTicket_Key'] = null;
$GLOBALS['PIECE_UNITY_Continuation_FlowName_Key'] = null;

// }}}
// {{{ Piece_Unity_Plugin_Dispatcher_Continuation

/**
 * A dispatcher which dispatches requests to the continuation server based on
 * Piece_Flow.
 *
 * @package    Piece_Unity
 * @author     KUBO Atsuhiro <iteman2002@yahoo.co.jp>
 * @copyright  2006 KUBO Atsuhiro <iteman2002@yahoo.co.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    Release: @package_version@
 * @link       http://iteman.typepad.jp/piece/
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
        $this->_addConfigurationPoint('actionDirectory', null);
        $this->_addConfigurationPoint('enableSingleFlowMode', false);
        $this->_addConfigurationPoint('cacheDirectory', null);
        $this->_addConfigurationPoint('flowDefinitions', array());
        $this->_addConfigurationPoint('sessionKey', strtolower(__CLASS__));
        $this->_addConfigurationPoint('flowExecutionTicketKey', '_flowExecutionTicket');
        $this->_addConfigurationPoint('flowNameKey', '_flow');
    }

    // }}}
    // {{{ invoke()

    /**
     * Invokes the plugin specific code.
     *
     * Starts a new continuation or continues with the current continuation,
     * and returns a view string.
     *
     * @throws PIECE_UNITY_ERROR_INVALID_CONFIGURATION
     * @throws PIECE_UNITY_ERROR_INVOCATION_FAILED
     * @return string
     */
    function invoke()
    {
        $GLOBALS['PIECE_UNITY_Continuation_Session_Key'] = $this->getConfiguration('sessionKey');
        $GLOBALS['PIECE_UNITY_Continuation_FlowExecutionTicket_Key'] = $this->getConfiguration('flowExecutionTicketKey');
        $GLOBALS['PIECE_UNITY_Continuation_FlowName_Key'] = $this->getConfiguration('flowNameKey');
        Piece_Flow_Continuation::setActionDirectory($this->getConfiguration('actionDirectory'));

        $session = &$this->_context->getSession();
        $continuation = &$session->getAttribute($GLOBALS['PIECE_UNITY_Continuation_Session_Key']);
        if (is_null($continuation)) {
            $continuation = &new Piece_Flow_Continuation($this->getConfiguration('enableSingleFlowMode'));
            $continuation->setCacheDirectory($this->getConfiguration('cacheDirectory'));
            $continuation->setEventNameCallback(array(__CLASS__, 'getEventName'));
            $continuation->setFlowExecutionTicketCallback(array(__CLASS__, 'getFlowExecutionTicket'));
            $continuation->setFlowNameCallback(array(__CLASS__, 'getFlowName'));

            foreach ($this->getConfiguration('flowDefinitions') as $flowDefinition) {
                Piece_Unity_Error::pushCallback(create_function('$error', 'return ' . PEAR_ERRORSTACK_PUSHANDLOG . ';'));
                $result = $continuation->addFlow($flowDefinition['name'],
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
                    return;
                }
            }

            $session->setAttributeByRef($GLOBALS['PIECE_UNITY_Continuation_Session_Key'], $continuation);
        }

        Piece_Unity_Error::pushCallback(create_function('$error', 'return ' . PEAR_ERRORSTACK_PUSHANDLOG . ';'));
        $continuation->invoke($this->_context);
        Piece_Unity_Error::popCallback();
        if (Piece_Flow_Error::hasErrors('exception')) {
            $error = Piece_Flow_Error::pop();
            if ($error['code'] == PIECE_FLOW_ERROR_FLOW_NAME_NOT_GIVEN) {
                return false;
            }

            Piece_Unity_Error::push(PIECE_UNITY_ERROR_INVOCATION_FAILED,
                                    'Failed to invoke the plugin [ ' . __CLASS__ . ' ].',
                                    'exception',
                                    array('plugin' => __CLASS__),
                                    $error
                                    );
            return;
        }

        Piece_Unity_Error::pushCallback(create_function('$error', 'return ' . PEAR_ERRORSTACK_PUSHANDLOG . ';'));
        $view = $continuation->getView();
        Piece_Unity_Error::popCallback();
        if (Piece_Flow_Error::hasErrors('exception')) {
            $error = Piece_Flow_Error::pop();
            Piece_Unity_Error::push(PIECE_UNITY_ERROR_INVOCATION_FAILED,
                                    'Failed to invoke the plugin [ ' . __CLASS__ . ' ].',
                                    'exception',
                                    array('plugin' => __CLASS__),
                                    $error
                                    );
            return;
        }

        /*
         * Sets the Piece_Flow_Continuation object, the flow execution ticket
         * key, the flow name key, and the current flow execution ticket as a
         * built-in view elements.
         */
        $viewElement = &$this->_context->getViewElement();
        $viewElement->setElementByRef('__continuation', $continuation);
        $viewElement->setElement('__flowExecutionTicketKey', $GLOBALS['PIECE_UNITY_Continuation_FlowExecutionTicket_Key']);
        $viewElement->setElement('__flowNameKey', $GLOBALS['PIECE_UNITY_Continuation_FlowName_Key']);
        $viewElement->setElement('__flowExecutionTicket', $continuation->getCurrentFlowExecutionTicket());

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
        return $context->getEvent();
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
        $context = &Piece_Unity_Context::singleton();
        $request = &$context->getRequest();
        return $request->hasParameter($GLOBALS['PIECE_UNITY_Continuation_FlowName_Key']) ? $request->getParameter($GLOBALS['PIECE_UNITY_Continuation_FlowName_Key']) : null;
    }

    /**#@-*/

    /**#@+
     * @access private
     */
 
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
