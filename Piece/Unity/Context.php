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

require_once 'Piece/Unity/Request.php';
require_once 'Piece/Unity/ViewElement.php';
require_once 'Piece/Unity/Session/Factory.php';

// {{{ GLOBALS

$GLOBALS['PIECE_UNITY_Context_Instance'] = null;

// }}}
// {{{ Piece_Unity_Context

/**
 * The context holder for Piece_Unity applications.
 *
 * @package    Piece_Unity
 * @author     KUBO Atsuhiro <iteman2002@yahoo.co.jp>
 * @copyright  2006 KUBO Atsuhiro <iteman2002@yahoo.co.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    Release: @package_version@
 * @link       http://iteman.typepad.jp/piece/
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
    var $_event;
    var $_session;

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
    // {{{ getEvent()

    /**
     * Gets the event name.
     *
     * @return string
     */
    function getEvent()
    {
        return $this->_event;
    }

    // }}}
    // {{{ clear()

    /**
     * Clears all properties.
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

        foreach ($this->_request->getParameters() as $key => $value) {
            if (preg_match('/^event_([a-zA-Z_]+)$/', $key, $matches)) {
                $this->_event = $matches[1];
            }
        }
        if (is_null($this->_event)) {
            $this->_event = $this->_request->hasParameter('event') ? $this->_request->getParameter('event') : null;
        }

        $this->_viewElement = &new Piece_Unity_ViewElement();
        $this->_session = &Piece_Unity_Session_Factory::factory();
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
