<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * PHP version 5
 *
 * Copyright (c) 2006-2009 KUBO Atsuhiro <kubo@iteman.jp>,
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
 * @copyright  2006-2009 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @see        Piece_Flow_Continuation_Server, Piece_Flow
 * @since      File available since Release 0.1.0
 */

// {{{ Piece_Unity_Plugin_Dispatcher_Continuation

/**
 * A dispatcher for stateful application components.
 *
 * This dispatcher starts a new continuation or continues with the current
 * continuation, and returns a view string.
 *
 * @package    Piece_Unity
 * @copyright  2006-2009 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
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
     * @access protected
     */

    /**#@-*/

    /**#@+
     * @access private
     */

    private $_continuationServer;
    private static $_sessionKey = '_continuation';
    private static $_flowIDKey;
    private static $_flowID;

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
     * @throws Stagehand_LegacyError_PEARErrorStack_Exception
     */
    public function invoke()
    {
        $this->_prepareContinuation();

        try {
            $flowExecutionTicket = $this->_continuationServer->invoke($this->context, $this->getConfiguration('bindActionsWithFlowExecution'));
            $viewString = $this->_continuationServer->getView();
        } catch (Stagehand_LegacyError_PEARErrorStack_Exception $e) {
            if ($e->getCode() == PIECE_FLOW_ERROR_FLOW_EXECUTION_EXPIRED) {
                if ($this->getConfiguration('useGCFallback')) {
                    $session = $this->context->getSession();
                    $session->setAttribute('_flowExecutionExpired', true);
                    $this->context->sendHTTPStatus(302);
                    return $this->getConfiguration('gcFallbackURI');
                }
            }

            throw $e;
        }

        $this->context->getViewElement()->setElement('__flowExecutionTicket', $flowExecutionTicket);

        $session = $this->context->getSession();
        $session->setPreloadCallback('_Dispatcher_Continuation_ActionLoader', array(__CLASS__, 'loadAction'));
        foreach (array_keys(Piece_Flow_Action_Factory::getInstances())
                 as $actionClass
                 ) {
            $session->addPreloadClass('_Dispatcher_Continuation_ActionLoader',
                                      $actionClass,
                                      $this->_continuationServer->getActiveFlowID()
                                      );
        }

        if (!$this->getConfiguration('useFlowMappings')) {
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
     */
    public static function getEventName()
    {
        return Piece_Unity_Context::singleton()->getEventName();
    }

    // }}}
    // {{{ getFlowExecutionTicket()

    /**
     * Gets a flow execution ticket.
     *
     * @return string
     */
    public static function getFlowExecutionTicket()
    {
        $request = Piece_Unity_Context::singleton()->getRequest();
        return $request->hasParameter(Piece_Unity_Service_Continuation::getFlowExecutionTicketKey()) ? $request->getParameter(Piece_Unity_Service_Continuation::getFlowExecutionTicketKey()) : null;
    }

    // }}}
    // {{{ getFlowID()

    /**
     * Gets a flow ID.
     *
     * @return string
     */
    public static function getFlowID()
    {
        if (!is_null(self::$_flowID)) {
            return self::$_flowID;
        }

        $request = Piece_Unity_Context::singleton()->getRequest();
        return $request->hasParameter(self::$_flowIDKey) ? $request->getParameter(self::$_flowIDKey) : null;
    }

    // }}}
    // {{{ loadAction()

    /**
     * Loads an action for preventing that the action become an incomplete class.
     *
     * @param string $class
     * @param string $flowID
     */
    public static function loadAction($class, $flowID)
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
     */
    public function publish()
    {
        if (is_null($this->_continuationServer)) {
            $this->_prepareContinuation();
        }

        $this->context->getViewElement()->setElement('__continuation', $this->context->getContinuation());
    }

    /**#@-*/

    /**#@+
     * @access protected
     */

    // }}}
    // {{{ initialize()

    /**
     * Defines and initializes extension points and configuration points.
     *
     * @since Method available since Release 0.6.0
     */
    protected function initialize()
    {
        $this->addConfigurationPoint('actionDirectory');
        $this->addConfigurationPoint('cacheDirectory');
        $this->addConfigurationPoint('flowDefinitions', array()); // deprecated
        $this->addConfigurationPoint('flowExecutionTicketKey',
                                      '_flowExecutionTicket'
                                      );
        $this->addConfigurationPoint('flowNameKey', '_flow'); // deprecated
        $this->addConfigurationPoint('flowName');             // deprecated
        $this->addConfigurationPoint('bindActionsWithFlowExecution', true);
        $this->addConfigurationPoint('enableGC', false);
        $this->addConfigurationPoint('gcExpirationTime', 1440);
        $this->addConfigurationPoint('useGCFallback', false);
        $this->addConfigurationPoint('gcFallbackURL'); // deprecated
        $this->addConfigurationPoint('useFlowMappings', false);
        $this->addConfigurationPoint('flowMappings', array());
        $this->addConfigurationPoint('configDirectory');
        $this->addConfigurationPoint('configExtension', '.flow');
        $this->addConfigurationPoint('useFullFlowNameAsViewPrefix', true);
        $this->addConfigurationPoint('gcFallbackURI', $this->getConfiguration('gcFallbackURL'));

        Piece_Unity_Service_Continuation::setFlowExecutionTicketKey($this->getConfiguration('flowExecutionTicketKey'));
        self::$_flowIDKey = $this->getConfiguration('flowNameKey');

        if ($this->getConfiguration('useFlowMappings')) {
            self::$_flowID = $this->context->getOriginalScriptName();
        } else {
            self::$_flowID = $this->getConfiguration('flowName');
        }

        Piece_Flow_Action_Factory::setActionDirectory($this->getConfiguration('actionDirectory'));

        $viewElement = $this->context->getViewElement();
        $viewElement->setElement('__flowExecutionTicketKey',
                                 Piece_Unity_Service_Continuation::getFlowExecutionTicketKey()
                                 );
        $viewElement->setElement('__flowNameKey', self::$_flowIDKey); // deprecated
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
    private function _createContinuationServer()
    {
        $continuationServer =
            new Piece_Flow_Continuation_Server(false,
                                               $this->getConfiguration('enableGC'),
                                               $this->getConfiguration('gcExpirationTime')
                                               );
        $continuationServer->setCacheDirectory($this->getConfiguration('cacheDirectory'));
        $continuationServer->setEventNameCallback(array(__CLASS__, 'getEventName'));
        $continuationServer->setFlowExecutionTicketCallback(array(__CLASS__, 'getFlowExecutionTicket'));
        $continuationServer->setFlowIDCallback(array(__CLASS__, 'getFlowID'));

        if ($this->getConfiguration('useFlowMappings')) {
            $continuationServer->setConfigDirectory($this->getConfiguration('configDirectory'));
            $continuationServer->setConfigExtension($this->getConfiguration('configExtension'));
            foreach ($this->getConfiguration('flowMappings') as $flowMapping) {
                if (array_key_exists('url', $flowMapping)) {
                    $flowMapping['uri'] = $flowMapping['url'];
                }

                $continuationServer->addFlow($flowMapping['uri'],
                                             $flowMapping['flowName'],
                                             $flowMapping['isExclusive']
                                             );
            }
        } else {
            foreach ($this->getConfiguration('flowDefinitions') as $flowDefinition) {
                $continuationServer->addFlow($flowDefinition['name'],
                                             $flowDefinition['file'],
                                             $flowDefinition['isExclusive']
                                             );
            }
        }

        return $continuationServer;
    }

    // }}}
    // {{{ _prepareContinuation()

    /**
     * Sets the Piece_Flow_Continuation_Service object to $_continuation
     * property and the context.
     *
     * @since Method available since Release 0.9.0
     */
    private function _prepareContinuation()
    {
        $session = $this->context->getSession();
        $continuationServer = $session->getAttribute(self::$_sessionKey);
        if (is_null($continuationServer)) {
            $continuationServer = $this->_createContinuationServer();
            $session->setAttribute(self::$_sessionKey, $continuationServer);
            $session->setPreloadCallback('_Dispatcher_Continuation',
                                         array('Piece_Unity_Plugin_Factory',
                                               'factory')
                                         );
            $session->addPreloadClass('_Dispatcher_Continuation',
                                      'Dispatcher_Continuation'
                                      );
        }

        $this->context->setContinuation($continuationServer->createService());
        $this->_continuationServer = $continuationServer;
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
    private function _prefixFlowNameToViewString($viewString)
    {
        do {
            if (preg_match('!^html:\(.*\)!', $viewString, $matches)) {
                $viewString = $matches[1];
            }

            if (preg_match('!^[^:]+:!', $viewString)) {
                break;
            }

            $flowName = $this->_continuationServer->getActiveFlowSource();
            if ($this->getConfiguration('useFullFlowNameAsViewPrefix')) {
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
