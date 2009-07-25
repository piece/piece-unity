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
 * @since      File available since Release 0.9.0
 */

// {{{ Piece_Unity_URI

/**
 * A utility which is used to create the appropriate absolute URI from
 * a relative/absolute URI.
 *
 * @package    Piece_Unity
 * @copyright  2006-2009 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @since      Class available since Release 0.9.0
 */
class Piece_Unity_URI
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

    private $_url;
    private $_isExternal = false;
    private $_isRedirection = false;
    private $path;

    /**#@-*/

    /**#@+
     * @access public
     */

    // }}}
    // {{{ getQueryString()

    /**
     * Gets the query string of a URI.
     *
     * @return string
     */
    public function getQueryString()
    {
        if (is_null($this->_url)) {
            $this->initialize();
        }

        return $this->_url->getQuery();
    }

    // }}}
    // {{{ setQueryVariable()

    /**
     * Adds a name/value pair to the query string.
     *
     * @param string $name
     * @param string $value
     */
    public function setQueryVariable($name, $value)
    {
        if (is_null($this->_url)) {
            $this->initialize();
        }

        $this->_url->setQueryVariable($name, $value);
    }

    // }}}
    // {{{ removeQueryVariable()

    /**
     * Removes a name/value pair from the query string.
     *
     * @param string $name
     * @since Method available since Release 0.11.0
     */
    public function removeQueryVariable($name)
    {
        if (is_null($this->_url)) {
            $this->initialize();
        }

        $this->_url->unsetQueryVariable($name);
    }

    // }}}
    // {{{ getQueryVariables()

    /**
     * @return array
     * @since Method available since Release 2.0.0dev1
     */
    public function getQueryVariables()
    {
        if (is_null($this->_url)) {
            $this->initialize();
        }

        return $this->_url->getQueryVariables();
    }

    // }}}
    // {{{ getURI()

    /**
     * Gets the absolute URI.
     * The standard port of the URI scheme is set when using reverse-proxy.
     *
     * @param string $scheme The scheme for the URI. The scheme MUST be one of:
     *                       https, http, or pass (default).
     * @return string
     */
    public function getURI($scheme = 'pass')
    {
        if (is_null($this->_url)) {
            $this->initialize();
        }

        if ($this->_isExternal) {
            return $this->_url->getURL();
        }

        if (!$this->_isRedirection
            && Stagehand_HTTP_ServerEnv::usingProxy()
            && array_key_exists('HTTP_X_FORWARDED_SERVER', $_SERVER)
            ) {
            if ($this->_url->getHost() != $_SERVER['HTTP_X_FORWARDED_SERVER']) {
                $this->_url->setHost($_SERVER['HTTP_X_FORWARDED_SERVER']);
            }
        } else {
            $this->_url->setHost($_SERVER['SERVER_NAME']);
            $this->_url->setPort($_SERVER['SERVER_PORT']);
            $this->_url->setPath($this->context->removeProxyPath($this->_url->getPath()));
        }

        $url = clone($this->_url);

        if (!in_array($scheme, array('https', 'http', 'pass'))) {
            $scheme = 'pass';
        }

        if ($scheme == 'pass') {
            $scheme = $this->_url->getScheme();
            $port = $this->_url->getPort();
        }

        if ($scheme == 'https') {
            $url->setScheme('https');
            $port = 443;
        } elseif ($scheme == 'http') {
            $url->setScheme('http');
            $port = 80;
        } else {
            $url->setScheme(Stagehand_HTTP_ServerEnv::isSecure() ? 'https' : 'http');
        }

        if ((Stagehand_HTTP_ServerEnv::usingProxy() && !$this->_isRedirection)
            || Stagehand_HTTP_ServerEnv::isRunningOnStandardPort()
            ) {
            $url->setPort($port);
        }

        return $url->getNormalizedURL();
    }

    // }}}
    // {{{ create()

    /**
     * A utility to get the appropriate absolute URI immediately.
     *
     * This method cannot use to create external URIs.
     *
     * @param string $path
     * @param string $scheme The scheme for the URI. The scheme MUST be one of:
     *                       https, http, or pass (default).
     * @return string
     */
    public function create($path, $scheme = 'pass')
    {
        $this->setPath($path);
        $this->initialize();
        return $this->getURI($scheme);
    }

    // }}}
    // {{{ setIsRedirection()

    /**
     * @param boolean $isRedirection
     * @since Method available since Release 2.0.0dev1
     */
    public function setIsRedirection($isRedirection)
    {
        $this->_isRedirection = $isRedirection;
    }

    // }}}
    // {{{ setIsExternal()

    /**
     * @param boolean $isExternal
     * @since Method available since Release 2.0.0dev1
     */
    public function setIsExternal($isExternal)
    {
        $this->_isExternal = $isExternal;
    }

    // }}}
    // {{{ setPath()

    /**
     * @param string $path
     * @since Method available since Release 2.0.0dev1
     */
    public function setPath($path)
    {
        $this->path = $path;
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
    // {{{ initialize()

    /**
     * Creates a Net_URL object with the given path, and replaces some pieces of
     * a URI when the URI is not external.
     *
     * @throws Piece_Unity_URI_PathNotSpecifiedException
     */
    private function initialize()
    {
        if (is_null($this->path)) {
            throw new Piece_Unity_URI_PathNotSpecifiedException(
                'The path must be specified'
                                                                );
        }

        $path = $this->path;

        if (!$this->_isExternal
            && !preg_match('/^https?/', $this->path)
            && !Stagehand_HTTP_ServerEnv::usingProxy()
            ) {
            $path = $this->context->getAppRootPath() . $path;
        }

        $this->_url = new Net_URL2($path);
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
