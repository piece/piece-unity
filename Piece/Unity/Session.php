<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * PHP versions 4 and 5
 *
 * Copyright (c) 2006, KUBO Atsuhiro <iteman@users.sourceforge.net>
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
 * @link       http://iteman.typepad.jp/piece/
 * @since      File available since Release 0.2.0
 */

// {{{ GLOBALS

$GLOBALS['PIECE_UNITY_Session_Autoload_Classes'] = array();

// {{{ Piece_Unity_Session

/**
 * The session state storage for Piece_Unity package.
 *
 * @package    Piece_Unity
 * @author     KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @copyright  2006 KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    Release: @package_version@
 * @link       http://iteman.typepad.jp/piece/
 * @since      Class available since Release 0.2.0
 */
class Piece_Unity_Session
{

    // {{{ properties

    /**#@+
     * @access public
     */

    /**#@-*/

    /**#@+
     * @access private
     */

    var $_attributes;

    /**#@-*/

    /**#@+
     * @access public
     */

    // }}}
    // {{{ setAttribute()

    /**
     * Sets an attribute for the current session state.
     *
     * @param string $name
     * @param mixed  $value
     */
    function setAttribute($name, $value)
    {
        $this->_attributes[$name] = $value;
    }

    // }}}
    // {{{ setAttributeByRef()

    /**
     * Sets an attribute by reference for the current session state.
     *
     * @param string $name
     * @param mixed  &$value
     */
    function setAttributeByRef($name, &$value)
    {
        $this->_attributes[$name] = &$value;
    }

    // }}}
    // {{{ hasAttribute()

    /**
     * Returns whether the current session state has an attribute with a
     * given name.
     *
     * @param string $name
     * @return boolean
     */
    function hasAttribute($name)
    {
        return array_key_exists($name, $this->_attributes);
    }

    // }}}
    // {{{ getAttribute()

    /**
     * Gets an attribute for the current session state.
     *
     * @param string $name
     * @return mixed
     */
    function &getAttribute($name)
    {
        $attribute = &$this->_attributes[$name];
        return $attribute;
    }

    // }}}
    // {{{ addAutoloadClass()

    /**
     * Adds a autoload class for restoring sessions safely.
     *
     * @param string $class
     * @static
     */
    function addAutoloadClass($class)
    {
        if (!in_array($class, $GLOBALS['PIECE_UNITY_Session_Autoload_Classes'])) {
            $GLOBALS['PIECE_UNITY_Session_Autoload_Classes'][] = $class;
        }
    }

    // }}}
    // {{{ start()

    /**
     * Starts a new session or restores a session if it already exists, and
     * binds the attribute holder to the $_SESSION superglobal array.
     */
    function start()
    {
        foreach ($GLOBALS['PIECE_UNITY_Session_Autoload_Classes'] as $class) {
            if (version_compare(phpversion(), '5.0.0', '<')) {
                $found = class_exists($class);
            } else {
                $found = class_exists($class, false);
            }

            if ($found) {
                continue;
            }

            $file = str_replace('_', '/', $class) . '.php';
            if (!@include_once($file)) {
                Piece_Unity_Error::pushCallback(create_function('$error', 'return ' . PEAR_ERRORSTACK_PUSHANDLOG . ';'));
                Piece_Unity_Error::push(PIECE_UNITY_ERROR_NOT_FOUND,
                                        "The class file [ $file ] not found or was not readable.",
                                        'warning'
                                        );
                Piece_Unity_Error::popCallback();
            }
        }

        @session_start();
        $this->_attributes = &$_SESSION;
    }

    // }}}
    // {{{ removeAttribute()

    /**
     * Removes an attribute from the current session state.
     *
     * @param string $name
     */
    function removeAttribute($name)
    {
        unset($this->_attributes[$name]);
    }

    // }}}
    // {{{ clearAttributes()

    /**
     * Removes all attributes from the current session state.
     */
    function clearAttributes()
    {
        $this->_attributes = array();
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
