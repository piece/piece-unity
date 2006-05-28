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

// {{{ GLOBALS

$GLOBALS['PIECE_UNITY_Context_Instance'] = null;

// }}}
// {{{ Piece_Unity_Context

/**
 * The context holder for a Piece_Unity application.
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

    /**#@-*/

    /**#@+
     * @access public
     */

    // }}}
    // {{{ singleton()

    /**
     * @return Piece_Unity_Context
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
     * Sets the Piece_Unity_Config object for this context.
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
     * Sets the view string for this context. It will be dispatched to an
     * appropriate renderer.
     *
     * @param string $view
     */
    function setView($view)
    {
        $this->_view = $view;
    }

    // }}}
    // {{{ setRequest()

    /**
     * Sets the Piece_Unity_Request object for this context.
     *
     * @param Piece_Unity_Request &$request
     */
    function setRequest(&$request)
    {
        $this->_request = $request;
    }

    // }}}
    // {{{ getConfiguration()

    /**
     * Gets the Piece_Unity_Config object for this context.
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
     * Gets the view string for this context.
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
     * Gets the Piece_Unity_Request object for this context.
     *
     * @return Piece_Unity_Request
     */
    function &getRequest()
    {
        return $this->_request;
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
