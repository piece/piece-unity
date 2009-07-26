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
class Piece_Unity_Plugin_Dispatcher_Continuation
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

        Piece_Unity_Service_Continuation::setFlowExecutionTicketKey($this->flowExecutionTicketKey);
        self::$_flowID = $this->context->getOriginalScriptName();

        Piece_Flow_Action_Factory::setActionDirectory($this->actionDirectory);

        $viewElement = $this->context->viewElement;
        $viewElement->setElement('__flowExecutionTicketKey',
                                 Piece_Unity_Service_Continuation::getFlowExecutionTicketKey()
                                 );

        try {
            $flowExecutionTicket =
                $this->_continuationServer->invoke(
                    $this->context, $this->bindActionsWithFlowExecution
                                                   );
            $viewString = $this->_continuationServer->getView();
        } catch (Stagehand_LegacyError_PEARErrorStack_Exception $e) {
            if ($e->getCode() == PIECE_FLOW_ERROR_FLOW_EXECUTION_EXPIRED) {
                if ($this->useGCFallback) {
                    $session = $this->context->getSession();
                    $session->setAttribute('_flowExecutionExpired', true);
                    $this->context->sendHTTPStatus(302);
                    return $this->gcFallbackURI;
                }
            }

            throw $e;
        }

        $this->context->viewElement->setElement('__flowExecutionTicket', $flowExecutionTicket);

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

        return $this->_prefixFlowNameToViewString($viewString);
    }

    // }}}
    // {{{ getEventName()

    /**
     * Gets an event name.
     *
     * @return string
     */
    public function getEventName()
    {
        return $this->context->getEventName();
    }

    // }}}
    // {{{ getFlowExecutionTicket()

    /**
     * Gets a flow execution ticket.
     *
     * @return string
     */
    public function getFlowExecutionTicket()
    {
        return $this->context->request->hasParameter(Piece_Unity_Service_Continuation::getFlowExecutionTicketKey()) ? $this->context->request->getParameter(Piece_Unity_Service_Continuation::getFlowExecutionTicketKey()) : null;
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
        return self::$_flowID;
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

        $this->context->viewElement->setElement('__continuation', $this->context->getContinuation());
    }

    /**#@-*/

    /**#@+
     * @access protected
     */

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
                                               $this->enableGC,
                                               $this->gcExpirationTime
                                               );
        $continuationServer->setCacheDirectory($this->cacheDirectory);
        $continuationServer->setFlowIDCallback(array(__CLASS__, 'getFlowID'));
        $continuationServer->setConfigDirectory($this->configDirectory);
        $continuationServer->setConfigExtension($this->configExtension);
        foreach ((array)$this->flowMappings as $flowMapping) {
            if (array_key_exists('url', $flowMapping)) {
                $flowMapping['uri'] = $flowMapping['url'];
            }

            $continuationServer->addFlow($flowMapping['uri'],
                                         $flowMapping['flowName'],
                                         $flowMapping['isExclusive']
                                         );
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

        $continuationServer->setEventNameCallback(array($this, 'getEventName'));
        $continuationServer->setFlowExecutionTicketCallback(array($this, 'getFlowExecutionTicket'));
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
            if ($this->useFullFlowNameAsViewPrefix) {
                $viewString = $flowName . '_' . $viewString;
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
