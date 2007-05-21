<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * PHP versions 4 and 5
 *
 * Copyright (c) 2006-2007 KUBO Atsuhiro <iteman@users.sourceforge.net>,
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
 * @copyright  2006-2007 KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    SVN: $Id$
 * @link       http://piece-framework.com/piece-unity/
 * @since      File available since Release 0.1.0
 */

require_once 'Piece/Unity/Request.php';
require_once 'Piece/Unity/ViewElement.php';
require_once 'Piece/Unity/Session.php';
require_once 'Piece/Unity/Validation.php';

// {{{ GLOBALS

$GLOBALS['PIECE_UNITY_Context_Instance'] = null;

// }}}
// {{{ Piece_Unity_Context

/**
 * The application context holder for Piece_Unity applications.
 *
 * @package    Piece_Unity
 * @author     KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @copyright  2006-2007 KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    Release: @package_version@
 * @link       http://piece-framework.com/piece-unity/
 * @since      Class available since Release 0.1.0
 */
class Piece_Unity_Context
{

    // {{{ properties

    /**#@+
     * @access public
     */

    /**#@-*/

    /**#@+
     * @access private
     */

    var $_config;
    var $_view;
    var $_request;
    var $_viewElement;
    var $_eventName;
    var $_session;
    var $_eventNameImported = false;
    var $_eventNameKey = '_event';
    var $_scriptName;
    var $_basePath = '';
    var $_attributes = array();
    var $_proxyPath;
    var $_continuation;
    var $_validation;
    var $_proxyMeasures = array('HTTP_X_FORWARDED_FOR',
                                'HTTP_X_FORWARDED',
                                'HTTP_FORWARDED_FOR',
                                'HTTP_FORWARDED',
                                'HTTP_VIA',
                                'HTTP_X_COMING_FROM',
                                'HTTP_COMING_FROM'
                                );

    /**#@-*/

    /**#@+
     * @access public
     */

    // }}}
    // {{{ singleton()

    /**
     * Returns the Piece_Unity_Context instance if exists. If not exists, a
     * new instance of the Piece_Unity_Context class will be created and
     * returned.
     *
     * @return Piece_Unity_Context
     * @static
     */
    function &singleton()
    {
        if (is_null($GLOBALS['PIECE_UNITY_Context_Instance'])) {
            $GLOBALS['PIECE_UNITY_Context_Instance'] = &new Piece_Unity_Context();
        }

        return $GLOBALS['PIECE_UNITY_Context_Instance'];
    }

    // }}}
    // {{{ setConfiguration()

    /**
     * Sets a Piece_Unity_Config object.
     *
     * @param Piece_Unity_Config &$config
     */
    function setConfiguration(&$config)
    {
        $this->_config = &$config;
    }

    // }}}
    // {{{ setView()

    /**
     * Sets a view string. It will be dispatched to an appropriate renderer.
     *
     * @param string $view
     */
    function setView($view)
    {
        $this->_view = $view;
    }

    // }}}
    // {{{ getConfiguration()

    /**
     * Gets the Piece_Unity_Config object.
     *
     * @return Piece_Unity_Config
     */
    function &getConfiguration()
    {
        return $this->_config;
    }

    // }}}
    // {{{ getView()

    /**
     * Gets the view string.
     *
     * @return string
     */
    function getView()
    {
        return $this->_view;
    }

    // }}}
    // {{{ getRequest()

    /**
     * Gets the Piece_Unity_Request object.
     *
     * @return Piece_Unity_Request
     */
    function &getRequest()
    {
        return $this->_request;
    }

    // }}}
    // {{{ getViewElement()

    /**
     * Gets the Piece_Unity_ViewElement object.
     *
     * @return Piece_Unity_ViewElement
     */
    function &getViewElement()
    {
        return $this->_viewElement;
    }

    // }}}
    // {{{ getEventName()

    /**
     * Gets the event name.
     *
     * @return string
     */
    function getEventName()
    {
        if (!$this->_eventNameImported) {
            $this->_importEventNameFromSubmit();
        }

        if (!$this->_eventNameImported) {
            $this->_importEventNameFromRequest();
        }

        return $this->_eventName;
    }

    // }}}
    // {{{ clear()

    /**
     * Removed a single instance safely.
     *
     * @static
     */
    function clear()
    {
        unset($GLOBALS['PIECE_UNITY_Context_Instance']);
        $GLOBALS['PIECE_UNITY_Context_Instance'] = null;
    }

    // }}}
    // {{{ getSession()

    /**
     * Gets the session state object.
     *
     * @return mixed
     */
    function &getSession()
    {
        return $this->_session;
    }

    // }}}
    // {{{ setEventNameKey()

    /**
     * Sets a key which represents the event name parameter.
     *
     * @param string $eventNameKey
     */
    function setEventNameKey($eventNameKey)
    {
        $this->_eventNameKey = $eventNameKey;
    }

    // }}}
    // {{{ getEventNameKey()

    /**
     * Gets the key which represents the event name parameter.
     *
     * @return string
     */
    function getEventNameKey()
    {
        return $this->_eventNameKey;
    }

    // }}}
    // {{{ setEventName()

    /**
     * Sets an event name for the current request.
     *
     * @param string $eventName
     */
    function setEventName($eventName)
    {
        $this->_eventNameImported = true;
        $this->_eventName = $eventName;
    }

    // }}}
    // {{{ getScriptName()

    /**
     * Gets the script name of the current request.
     *
     * @return string
     */
    function getScriptName()
    {
        return $this->_scriptName;
    }

    // }}}
    // {{{ getBasePath()

    /**
     * Gets the base path of the current request.
     *
     * @return string
     */
    function getBasePath()
    {
        return $this->_basePath;
    }

    // }}}
    // {{{ setScriptName()

    /**
     * Sets the script name of the current request.
     *
     * @param string $scriptName
     * @since Method available since Release 0.5.0
     */
    function setScriptName($scriptName)
    {
        $this->_scriptName = $scriptName;
    }

    // }}}
    // {{{ setBasePath()

    /**
     * Sets the base path of the current request.
     *
     * @param string $basePath
     * @since Method available since Release 0.5.0
     */
    function setBasePath($basePath)
    {
        $this->_basePath = $basePath;
    }

    // }}}
    // {{{ setAttribute()

    /**
     * Sets an attribute for the current request.
     *
     * @param string $name
     * @param mixed  $value
     * @since Method available since Release 0.6.0
     */
    function setAttribute($name, $value)
    {
        $this->_attributes[$name] = $value;
    }

    // }}}
    // {{{ setAttributeByRef()

    /**
     * Sets an attribute by reference for the current request.
     *
     * @param string $name
     * @param mixed  &$value
     * @since Method available since Release 0.6.0
     */
    function setAttributeByRef($name, &$value)
    {
        $this->_attributes[$name] = &$value;
    }

    // }}}
    // {{{ hasAttribute()

    /**
     * Returns whether the current request has an attribute with a given name.
     *
     * @param string $name
     * @return boolean
     * @since Method available since Release 0.6.0
     */
    function hasAttribute($name)
    {
        return array_key_exists($name, $this->_attributes);
    }

    // }}}
    // {{{ getAttribute()

    /**
     * Gets an attribute for the current request.
     *
     * @param string $name
     * @return mixed
     * @since Method available since Release 0.6.0
     */
    function &getAttribute($name)
    {
        return $this->_attributes[$name];
    }

    // }}}
    // {{{ removeAttribute()

    /**
     * Removes an attribute from the current request.
     *
     * @param string $name
     * @since Method available since Release 0.6.0
     */
    function removeAttribute($name)
    {
        unset($this->_attributes[$name]);
    }

    // }}}
    // {{{ clearAttributes()

    /**
     * Removes all attributes from the current request.
     *
     * @since Method available since Release 0.6.0
     */
    function clearAttributes()
    {
        $this->_attributes = array();
    }

    // }}}
    // {{{ setProxyPath()

    /**
     * Sets the proxy path if the application uses proxy servers.
     *
     * @param string $proxyPath
     * @since Method available since Release 0.6.0
     */
    function setProxyPath($proxyPath)
    {
        $this->_proxyPath = $proxyPath;
    }

    // }}}
    // {{{ getProxyPath()

    /**
     * Gets the proxy path of the application.
     *
     * @return string
     * @since Method available since Release 0.6.0
     */
    function getProxyPath()
    {
        return $this->_proxyPath;
    }

    // }}}
    // {{{ usingProxy()

    /**
     * Returns whether the application is accessed via reverse proxies.
     *
     * @return boolean
     */
    function usingProxy()
    {
        foreach ($this->_proxyMeasures as $proxyMeasure) {
            if (array_key_exists($proxyMeasure, $_SERVER)) {
                return true;
            }
        }

        return false;
    }

    // }}}
    // {{{ setContinuation()

    /**
     * Sets the Piece_Flow_Continuation object for the current session.
     *
     * @param Piece_Flow_Continuation &$continuation
     * @since Method available since Release 0.6.0
     */
    function setContinuation(&$continuation)
    {
        $this->_continuation = &$continuation;
    }

    // }}}
    // {{{ getContinuation()

    /**
     * Gets the Piece_Flow_Continuation object for the current session.
     *
     * @return Piece_Flow_Continuation
     * @since Method available since Release 0.6.0
     */
    function &getContinuation()
    {
        return $this->_continuation;
    }

    // }}}
    // {{{ getValidation()

    /**
     * Gets the Piece_Unity_Validation object for the current request.
     *
     * @return Piece_Unity_Validation
     * @since Method available since Release 0.7.0
     */
    function &getValidation()
    {
        return $this->_validation;
    }

    // }}}
    // {{{ getRemoteAddr()

    /**
     * Gets an IP address (or IP addresses) of the client making the request.
     *
     * @return string
     * @since Method available since Release 0.12.0
     */
    function getRemoteAddr()
    {
        if ($this->usingProxy()) {
            foreach ($this->_proxyMeasures as $proxyMeasure) {
                if (array_key_exists($proxyMeasure, $_SERVER)) {
                    return $_SERVER[$proxyMeasure];
                }
            }
        } else {
            return $_SERVER['REMOTE_ADDR'];
        }
    }

    /**#@-*/

    /**#@+
     * @access private
     */

    // }}}
    // {{{ constructor

    /**
     * Creates a Piece_Unity_Request object and sets an event to the context.
     */
    function Piece_Unity_Context()
    {
        $this->_request = &new Piece_Unity_Request();
        $this->_viewElement = &new Piece_Unity_ViewElement();
        $this->_session = &new Piece_Unity_Session();
        $this->_scriptName = str_replace('//', '/', $_SERVER['SCRIPT_NAME']);

        $positionOfSlash = strrpos($this->_scriptName, '/');
        if ($positionOfSlash) {
            $this->_basePath = substr($this->_scriptName, 0, $positionOfSlash);
        }

        $this->_validation = &new Piece_Unity_Validation();
    }

    // }}}
    // {{{ _importEventNameFromSubmit()

    /**
     * Imports an event name from the submit by a submit or an image.
     *
     * @since Method available since Release 0.9.0
     */
    function _importEventNameFromSubmit()
    {
        $xFound = false;
        $yFound = false;
        foreach ($this->_request->getParameters() as $key => $value) {
            if (preg_match("/^{$this->_eventNameKey}_(.+)$/", $key, $matches)) {
                $eventName = $matches[1];
                $lastTwoBytes = substr($matches[1], -2);
                if ($lastTwoBytes == '_x') {
                    $xFound = true;
                    $xEventName = substr($matches[1], 0, -2);
                    if ($yFound) {
                        break;
                    }
                } elseif ($lastTwoBytes == '_y') {
                    $yFound = true;
                    $yEventName = substr($matches[1], 0, -2);
                    if ($xFound) {
                        break;
                    }
                } else {
                    $this->setEventName($eventName);
                    return;
                }
            }
        }

        if ($xFound || $yFound) {
            if ($xFound && $yFound && $xEventName == $yEventName) {
                $this->setEventName($xEventName);
            } else {
                $this->setEventName($eventName);
            }
        }
    }

    // }}}
    // {{{ _importEventNameFromRequest()

    /**
     * Imports an event name from the request parameters.
     *
     * @since Method available since Release 0.9.0
     */
    function _importEventNameFromRequest()
    {
        $eventName = $this->_request->hasParameter($this->_eventNameKey) ? $this->_request->getParameter($this->_eventNameKey) : null;
        $this->setEventName($eventName);
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
