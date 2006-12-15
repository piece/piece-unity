<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * PHP versions 4 and 5
 *
 * Copyright (c) 2006 KUBO Atsuhiro <iteman@users.sourceforge.net>,
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
 * @link       http://piece-framework.com/piece-unity/
 * @since      File available since Release 0.9.0
 */

require_once 'Net/URL.php';
require_once 'Piece/Unity/Context.php';
require_once 'Piece/Unity/Error.php';

// {{{ GLOBALS

$GLOBALS['PIECE_UNITY_URL_NonSSLableServers'] = array();

// }}}
// {{{ Piece_Unity_URL

/**
 * A utility which is used to create the appropriate absolute URL from a
 * relative/absolute URL.
 *
 * @package    Piece_Unity
 * @author     KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @copyright  2006 KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    Release: @package_version@
 * @link       http://piece-framework.com/piece-unity/
 * @since      Class available since Release 0.9.0
 */
class Piece_Unity_URL
{

    // {{{ properties

    /**#@+
     * @access public
     */

    /**#@-*/

    /**#@+
     * @access private
     */

    var $_url;
    var $_isExternal;

    /**#@-*/

    /**#@+
     * @access public
     */

    // }}}
    // {{{ constructor

    /**
     * Initializes a Net_URL object if the path is given.
     *
     * @param string  $path
     * @param boolean $isExternal
     */
    function Piece_Unity_URL($path = null, $isExternal = false)
    {
        $this->_isExternal = $isExternal;
        if (!is_null($path)) {
            $this->initialize($path);
        }
    }

    // }}}
    // {{{ getQueryString()

    /**
     * Gets the query string of a URL.
     *
     * @return boolean
     */
    function getQueryString()
    {
        if (is_null($this->_url)) {
            Piece_Unity_Error::pushCallback(create_function('$error', 'return ' . PEAR_ERRORSTACK_PUSHANDLOG . ';'));
            Piece_Unity_Error::push(PIECE_UNITY_ERROR_INVALID_OPERATION,
                                    __FUNCTION__ . ' method must be called after initializing.',
                                    'warning'
                                    );
            Piece_Unity_Error::popCallback();
            return;
        }

        return $this->_url->querystring;
    }

    // }}}
    // {{{ addQueryString()

    /**
     * Adds a name/value pair to the query string.
     *
     * @param string $name
     * @param string $value
     */
    function addQueryString($name, $value)
    {
        if (is_null($this->_url)) {
            Piece_Unity_Error::pushCallback(create_function('$error', 'return ' . PEAR_ERRORSTACK_PUSHANDLOG . ';'));
            Piece_Unity_Error::push(PIECE_UNITY_ERROR_INVALID_OPERATION,
                                    __FUNCTION__ . ' method must be called after initializing.',
                                    'warning'
                                    );
            Piece_Unity_Error::popCallback();
            return;
        }

        $this->_url->addQueryString($name, $value);
    }

    // }}}
    // {{{ getURL()

    /**
     * Gets the absolute URL.
     *
     * @param boolean $useSSL
     * @return string
     */
    function getURL($useSSL = false)
    {
        if (is_null($this->_url)) {
            Piece_Unity_Error::pushCallback(create_function('$error', 'return ' . PEAR_ERRORSTACK_PUSHANDLOG . ';'));
            Piece_Unity_Error::push(PIECE_UNITY_ERROR_INVALID_OPERATION,
                                    __FUNCTION__ . ' method must be called after initializing.',
                                    'warning'
                                    );
            Piece_Unity_Error::popCallback();
            return;
        }

        if (!$this->_isExternal) {
            $context = &Piece_Unity_Context::singleton();
            if (!$context->usingProxy()) {
                if ($_SERVER['SERVER_PORT'] != 80 && $_SERVER['SERVER_PORT'] != 443) {
                    return $this->_url->getURL();
                }
            }
        }

        if (version_compare(phpversion(), '5.0.0', '<')) {
            $url = $this->_url;
        } else {
            $url = clone($this->_url);
        }

        if (!$useSSL) {
            $url->protocol = 'http';
            $url->port= '80';
        } else {
            if (!in_array($url->host, $GLOBALS['PIECE_UNITY_URL_NonSSLableServers'])) {
                $url->protocol = 'https';
                $url->port= '443';
            } else {
                $url->protocol = 'http';
                $url->port= '80';
            }
        }

        return $url->getURL();
    }

    // }}}
    // {{{ create()

    /**
     * A utility to get the appropriate absolute URL immediately.
     *
     * This method cannot use to create external URLs.
     *
     * @param string $path
     * @return string
     */
    function create($path)
    {
        $url = &new Piece_Unity_URL($path);
        return $url->getURL();
    }

    // }}}
    // {{{ initialize()

    /**
     * Creates a Net_URL object with the given path, and replaces some pieces
     * of a URL when the URL is not external.
     *
     * @param string  $path
     */
    function initialize($path)
    {
        $this->_url = &new Net_URL($path);

        if (!$this->_isExternal) {
            $context = &Piece_Unity_Context::singleton();
            if ($context->usingProxy()) {
                if ($this->_url->host != $_SERVER['HTTP_X_FORWARDED_SERVER']) {
                    $this->_url->host = $_SERVER['HTTP_X_FORWARDED_SERVER'];
                    $this->_url->protocol = 'http';
                }

                $this->_url->port = 80;
            } else {
                if ($_SERVER['SERVER_PORT'] != 443) {
                    $this->_url->protocol = 'http';
                } else {
                    $this->_url->protocol = 'https';
                }

                $this->_url->host = $_SERVER['SERVER_NAME'];
                $this->_url->port = $_SERVER['SERVER_PORT'];
                $this->_url->path = preg_replace('!^' . $context->getProxyPath() . '!', '', $this->_url->path);
            }
        }
    }

    // }}}
    // {{{ createSSL()

    /**
     * A utility to get the appropriate HTTPS URL immediately.
     *
     * This method cannot use to create external URLs.
     *
     * @param string $path
     * @return string
     */
    function createSSL($path)
    {
        $url = &new Piece_Unity_URL($path);
        return $url->getURL(true);
    }

    // }}}
    // {{{ addNonSSLableServer()

    /**
     * Adds a server name which is forced to be non-SSL request.
     *
     * @param string $serverName
     * @static
     */
    function addNonSSLableServer($serverName)
    {
        $GLOBALS['PIECE_UNITY_URL_NonSSLableServers'][] = $serverName;
    }

    // }}}
    // {{{ clearNonSSLableServers()

    /**
     * Clears the list of non-SSLable servers.
     *
     * @static
     */
    function clearNonSSLableServers()
    {
        $GLOBALS['PIECE_UNITY_URL_NonSSLableServers'] = array();
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
