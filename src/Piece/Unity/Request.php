<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * PHP version 5
 *
 * Copyright (c) 2006-2007, 2009 KUBO Atsuhiro <kubo@iteman.jp>,
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
 * @copyright  2006-2007, 2009 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    Release: @package_version@
 * @since      File available since Release 0.1.0
 */

// {{{ Piece_Unity_Request

/**
 * The parameter holder for client request data.
 *
 * @package    Piece_Unity
 * @copyright  2006-2007, 2009 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    Release: @package_version@
 * @since      Class available since Release 0.1.0
 */
class Piece_Unity_Request
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

    private $_parameters = array();

    /**#@-*/

    /**#@+
     * @access public
     */

    // }}}
    // {{{ __construct()

    /**
     * Imports client request data correspoinding to the request method.
     */
    public function __construct()
    {
        if (@$_SERVER['REQUEST_METHOD'] == 'GET') {
            $this->_parameters = $_GET;
        } elseif (@$_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->_parameters = $_POST;

            foreach ($_FILES as $name => $value) {
                $this->setParameter($name, $value);
            }
        }
    }

    // }}}
    // {{{ setParameter()

    /**
     * Sets a parameter for this request.
     *
     * @param string $name
     * @param mixed  $value
     */
    public function setParameter($name, $value)
    {
        $this->_parameters[$name] = $value;
    }

    // }}}
    // {{{ hasParameter()

    /**
     * Returns whether this request has a parameter with a given name.
     *
     * @param string $name
     * @return boolean
     */
    public function hasParameter($name)
    {
        return array_key_exists($name, $this->_parameters);
    }

    // }}}
    // {{{ getParameter()

    /**
     * Gets a parameter for this request.
     *
     * @param string $name
     * @return mixed
     */
    public function getParameter($name)
    {
        return $this->_parameters[$name];
    }

    // }}}
    // {{{ getParameters()

    /**
     * Gets all parameters for this request.
     *
     * @return array
     */
    public function getParameters()
    {
        return $this->_parameters;
    }

    // }}}
    // {{{ importPathInfo()

    /**
     * Imports PATH_INFO string as parameters.
     *
     * @param boolean $importPathInfo
     * @since Method available since Release 0.4.0
     */
    public function importPathInfo()
    {
        $pathInfo = self::getPathInfo();
        if (is_null($pathInfo)) {
            return;
        }

        $pathInfoParameters = explode('/', trim($pathInfo, '/'));
        for ($i = 0, $count = count($pathInfoParameters); $i < $count; $i += 2) {
            $this->_parameters[ $pathInfoParameters[$i] ] = @$pathInfoParameters[$i + 1];
        }
    }

    // }}}
    // {{{ getPathInfo()

    /**
     * Gets PATH_INFO string.
     *
     * @return string
     * @since Method available since Release 1.7.1
     */
    public static function getPathInfo()
    {
        if (PHP_SAPI != 'cgi') {
            if (array_key_exists('PATH_INFO', $_SERVER)) {
                return $_SERVER['PATH_INFO'];
            }
        }

        if (array_key_exists('ORIG_PATH_INFO', $_SERVER)) {
            return $_SERVER['ORIG_PATH_INFO'];
        }
    }

    /**#@-*/

    /**#@+
     * @access protected
     */

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
