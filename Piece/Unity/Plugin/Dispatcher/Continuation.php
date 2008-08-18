<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * PHP versions 4 and 5
 *
 * Copyright (c) 2006-2008 KUBO Atsuhiro <iteman@users.sourceforge.net>,
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
 * @copyright  2006-2008 KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    SVN: $Id$
 * @see        Piece_Flow_Continuation_Server, Piece_Flow
 * @since      File available since Release 0.1.0
 */

require_once 'Piece/Unity/Plugin/Common.php';
require_once 'Piece/Flow/Continuation/Server.php';
require_once 'Piece/Flow/Action/Factory.php';
require_once 'Piece/Unity/Context.php';
require_once 'Piece/Flow/Error.php';
require_once 'Piece/Unity/Error.php';
require_once 'Piece/Unity/Service/Continuation.php';

// {{{ constants

define('PIECE_UNITY_CONTINUATION_SESSIONKEY', '_continuation');

// }}}
// {{{ GLOBALS

$GLOBALS['PIECE_UNITY_Continuation_FlowIDKey'] = null;
$GLOBALS['PIECE_UNITY_Continuation_FlowID'] = null;

// }}}
// {{{ Piece_Unity_Plugin_Dispatcher_Continuation

/**
 * A dispatcher for stateful application components.
 *
 * This dispatcher starts a new continuation or continues with the current
 * continuation, and returns a view string.
 *
 * @package    Piece_Unity
 * @copyright  2006-2008 KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    Release: @package_version@
 * @see        Piece_Flow_Continuation_Server, Piece_Flow
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

    var $_continuationServer;

    /**#@-*/

    /**#@+
     * @access public
     */

    // }}}
    // {{{ invoke()

    /**
     * Invokes the plugin specific code.
     *
     * @return string
     * @throws PIECE_UNITY_ERROR_INVALID_CONFIGURATION
     * @throws PIECE_UNITY_ERROR_INVOCATION_FAILED
     */
    function invoke()
    {
        $this->_prepareContinuation();
        if (Piece_Unity_Error::hasErrors()) {
            return;
        }

        Piece_Flow_Error::disableCallback();
        $flowExecutionTicket = $this->_continuationServer->invoke($this->_context, $this->_getConfiguration('bindActionsWithFlowExecution'));
        Piece_Flow_Error::enableCallback();
        if (Piece_Flow_Error::hasErrors()) {
            $error = Piece_Flow_Error::pop();
            if ($error['code'] == PIECE_FLOW_ERROR_FLOW_EXECUTION_EXPIRED) {
                if ($this->_getConfiguration('useGCFallback')) {
                    return $this->_getConfiguration('gcFallbackURL');
                }
            }

            Piece_Unity_Error::push(PIECE_UNITY_ERROR_INVOCATION_FAILED,
                                    "Failed to invoke the plugin [ {$this->_name} ] for any reasons.",
                                    'exception',
                                    array(),
                                    $error
                                    );
            return;
        }

        Piece_Flow_Error::disableCallback();
        $viewString = $this->_continuationServer->getView();
        Piece_Flow_Error::enableCallback();
        if (Piece_Flow_Error::hasErrors()) {
            Piece_Unity_Error::push(PIECE_UNITY_ERROR_INVOCATION_FAILED,
                                    "Failed to invoke the plugin [ {$this->_name} ] for any reasons.",
                                    'exception',
                                    array(),
                                    Piece_Flow_Error::pop()
                                    );
            return;
        }

        $viewElement = &$this->_context->getViewElement();
        $viewElement->setElement('__flowExecutionTicket', $flowExecutionTicket);

        $session = &$this->_context->getSession();
        $session->setPreloadCallback('_Dispatcher_Continuation_ActionLoader', array(__CLASS__, 'loadAction'));
        foreach (array_keys(Piece_Flow_Action_Factory::getInstances())
                 as $actionClass
                 ) {
            $session->addPreloadClass('_Dispatcher_Continuation_ActionLoader',
                                      $actionClass,
                                      $this->_continuationServer->getActiveFlowID()
                                      );
        }

        if (!$this->_getConfiguration('useFlowMappings')) {
            return $viewString;
        }

        return $this->_prefixFlowNameToViewString($viewString);
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
        return $request->hasParameter(Piece_Unity_Service_Continuation::getFlowExecutionTicketKey()) ? $request->getParameter(Piece_Unity_Service_Continuation::getFlowExecutionTicketKey()) : null;
    }

    // }}}
    // {{{ getFlowID()

    /**
     * Gets a flow ID.
     *
     * @return string
     * @static
     */
    function getFlowID()
    {
        if (!is_null($GLOBALS['PIECE_UNITY_Continuation_FlowID'])) {
            return $GLOBALS['PIECE_UNITY_Continuation_FlowID'];
        }

        $context = &Piece_Unity_Context::singleton();
        $request = &$context->getRequest();
        return $request->hasParameter($GLOBALS['PIECE_UNITY_Continuation_FlowIDKey']) ? $request->getParameter($GLOBALS['PIECE_UNITY_Continuation_FlowIDKey']) : null;
    }

    // }}}
    // {{{ loadAction()

    /**
     * Loads an action for preventing that the action become an incomplete class.
     *
     * @param string $class
     * @param string $flowID
     * @static
     */
    function loadAction($class, $flowID)
    {
        if ($flowID == Piece_Unity_Plugin_Dispatcher_Continuation::getFlowID()) {
            Piece_Flow_Action_Factory::load($class);
        }
    }

    // }}}
    // {{{ publish()

    /**
     * Publishes the Piece_Flow_Continuation_Service object as a view element if it
     * exists.
     *
     * @since Method available since Release 0.9.0
     * @throws PIECE_UNITY_ERROR_INVALID_CONFIGURATION
     */
    function publish()
    {
        if (is_null($this->_continuationServer)) {
            $this->_prepareContinuation();
            if (Piece_Unity_Error::hasErrors()) {
                return;
            }
        }

        $continuationService = &$this->_context->getContinuation();
        $viewElement = &$this->_context->getViewElement();
        $viewElement->setElementByRef('__continuation', $continuationService);
    }

    /**#@-*/

    /**#@+
     * @access private
     */

    // }}}
    // {{{ _createContinuationServer()

    /**
     * Creates a new Piece_Flow_Continuation_Server object and configure it.
     *
     * @return Piece_Flow_Continuation_Server
     * @throws PIECE_UNITY_ERROR_INVALID_CONFIGURATION
     */
    function &_createContinuationServer()
    {
        $continuationServer =
            &new Piece_Flow_Continuation_Server($this->_getConfiguration('enableSingleFlowMode'),
                                                $this->_getConfiguration('enableGC'),
                                                $this->_getConfiguration('gcExpirationTime')
                                                );
        $continuationServer->setCacheDirectory($this->_getConfiguration('cacheDirectory'));
        $continuationServer->setEventNameCallback(array(__CLASS__, 'getEventName'));
        $continuationServer->setFlowExecutionTicketCallback(array(__CLASS__, 'getFlowExecutionTicket'));
        $continuationServer->setFlowIDCallback(array(__CLASS__, 'getFlowID'));

        if ($this->_getConfiguration('useFlowMappings')) {
            $continuationServer->setConfigDirectory($this->_getConfiguration('configDirectory'));
            $continuationServer->setConfigExtension($this->_getConfiguration('configExtension'));
            foreach ($this->_getConfiguration('flowMappings') as $flowMapping) {
                Piece_Flow_Error::disableCallback();
                $continuationServer->addFlow($flowMapping['url'],
                                             $flowMapping['flowName'],
                                             $flowMapping['isExclusive']
                                             );
                Piece_Flow_Error::enableCallback();
                if (Piece_Flow_Error::hasErrors()) {
                    Piece_Unity_Error::push(PIECE_UNITY_ERROR_INVALID_CONFIGURATION,
                                            "Failed to configure the plugin [ {$this->_name}.",
                                            'exception',
                                            array(),
                                            Piece_Flow_Error::pop()
                                            );
                    $return = null;
                    return $return;
                }
            }
        } else {
            foreach ($this->_getConfiguration('flowDefinitions') as $flowDefinition) {
                Piece_Flow_Error::disableCallback();
                $continuationServer->addFlow($flowDefinition['name'],
                                             $flowDefinition['file'],
                                             $flowDefinition['isExclusive']
                                             );
                Piece_Flow_Error::enableCallback();
                if (Piece_Flow_Error::hasErrors()) {
                    Piece_Unity_Error::push(PIECE_UNITY_ERROR_INVALID_CONFIGURATION,
                                            "Failed to configure the plugin [ {$this->_name}.",
                                            'exception',
                                            array(),
                                            Piece_Flow_Error::pop()
                                            );
                    $return = null;
                    return $return;
                }
            }
        }

        return $continuationServer;
    }

    // }}}
    // {{{ _initialize()

    /**
     * Defines and initializes extension points and configuration points.
     *
     * @since Method available since Release 0.6.0
     */
    function _initialize()
    {
        $this->_addConfigurationPoint('actionDirectory');
        $this->_addConfigurationPoint('enableSingleFlowMode', false); // deprecated
        $this->_addConfigurationPoint('cacheDirectory');
        $this->_addConfigurationPoint('flowDefinitions', array()); // deprecated
        $this->_addConfigurationPoint('flowExecutionTicketKey',
                                      '_flowExecutionTicket'
                                      );
        $this->_addConfigurationPoint('flowNameKey', '_flow'); // deprecated
        $this->_addConfigurationPoint('flowName');             // deprecated
        $this->_addConfigurationPoint('bindActionsWithFlowExecution', true);
        $this->_addConfigurationPoint('enableGC', false);
        $this->_addConfigurationPoint('gcExpirationTime', 1440);
        $this->_addConfigurationPoint('useGCFallback', false);
        $this->_addConfigurationPoint('gcFallbackURL');
        $this->_addConfigurationPoint('useFlowMappings', false);
        $this->_addConfigurationPoint('flowMappings', array());
        $this->_addConfigurationPoint('configDirectory');
        $this->_addConfigurationPoint('configExtension', '.flow');
        $this->_addConfigurationPoint('useFullFlowNameAsViewPrefix', true);

        Piece_Unity_Service_Continuation::setFlowExecutionTicketKey($this->_getConfiguration('flowExecutionTicketKey'));
        $GLOBALS['PIECE_UNITY_Continuation_FlowIDKey'] =
            $this->_getConfiguration('flowNameKey');

        if ($this->_getConfiguration('useFlowMappings')) {
            $GLOBALS['PIECE_UNITY_Continuation_FlowID'] = $_SERVER['SCRIPT_NAME'];
        } else {
            $GLOBALS['PIECE_UNITY_Continuation_FlowID'] =
                $this->_getConfiguration('flowName');
        }

        Piece_Flow_Action_Factory::setActionDirectory($this->_getConfiguration('actionDirectory'));

        $viewElement = &$this->_context->getViewElement();
        $viewElement->setElement('__flowExecutionTicketKey',
                                 Piece_Unity_Service_Continuation::getFlowExecutionTicketKey()
                                 );
        $viewElement->setElement('__flowNameKey', // deprecated
                                 $GLOBALS['PIECE_UNITY_Continuation_FlowIDKey']
                                 );
    }

    // }}}
    // {{{ _prepareContinuation()

    /**
     * Sets the Piece_Flow_Continuation_Service object to $_continuation
     * property and the context.
     *
     * @since Method available since Release 0.9.0
     * @throws PIECE_UNITY_ERROR_INVALID_CONFIGURATION
     */
    function _prepareContinuation()
    {
        $session = &$this->_context->getSession();
        $continuationServer = &$session->getAttribute(PIECE_UNITY_CONTINUATION_SESSIONKEY);
        if (is_null($continuationServer)) {
            $continuationServer = &$this->_createContinuationServer();
            if (Piece_Unity_Error::hasErrors()) {
                return;
            }

            $session->setAttributeByRef(PIECE_UNITY_CONTINUATION_SESSIONKEY,
                                        $continuationServer
                                        );
            $session->setPreloadCallback('_Dispatcher_Continuation',
                                         array('Piece_Unity_Plugin_Factory',
                                               'factory')
                                         );
            $session->addPreloadClass('_Dispatcher_Continuation',
                                      'Dispatcher_Continuation'
                                      );
        }

        $continuationService = &$continuationServer->createService();
        $this->_context->setContinuation($continuationService);
        $this->_continuationServer = &$continuationServer;
    }

    // }}}
    // {{{ _prefixFlowNameToViewString()

    /**
     * Prefixes the active flow name to a given view string.
     *
     * @param string $viewString
     * @return string
     * @since Method available since Release 1.5.0
     */
    function _prefixFlowNameToViewString($viewString)
    {
        do {
            if (preg_match('!^html:\(.*\)!', $viewString, $matches)) {
                $viewString = $matches[1];
            }

            if (preg_match('!^[^:]+:!', $viewString)) {
                break;
            }

            $flowName = $this->_continuationServer->getActiveFlowSource();
            if ($this->_getConfiguration('useFullFlowNameAsViewPrefix')) {
                $viewString = "{$flowName}_{$viewString}";
                break;
            }

            $positionOfUnderscore = strrpos($flowName, '_');
            if ($positionOfUnderscore) {
                $viewString = substr($flowName, 0, $positionOfUnderscore + 1) .
                    $viewString;
            }
        } while (false);

        return $viewString;
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
