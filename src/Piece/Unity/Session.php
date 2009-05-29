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
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    Release: @package_version@
 * @since      File available since Release 0.2.0
 */

// {{{ Piece_Unity_Session

/**
 * The session state storage for Piece_Unity package.
 *
 * @package    Piece_Unity
 * @copyright  2006-2009 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    Release: @package_version@
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
     * @access protected
     */

    /**#@-*/

    /**#@+
     * @access private
     */

    private $_preload;
    private $_attributes;
    private static $_autoloadClasses = array();

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
    public function setAttribute($name, $value)
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
    public function setAttributeByRef($name, &$value)
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
    public function hasAttribute($name)
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
    public function &getAttribute($name)
    {
        return $this->_attributes[$name];
    }

    // }}}
    // {{{ addAutoloadClass()

    /**
     * Adds a autoload class for restoring sessions safely.
     *
     * @param string $class
     */
    public static function addAutoloadClass($class)
    {
        if (!in_array($class, self::$_autoloadClasses)) {
            self::$_autoloadClasses[] = $class;
        }
    }

    // }}}
    // {{{ start()

    /**
     * Starts a new session or restores a session if it already exists, and
     * binds the attribute holder to the $_SESSION superglobal array.
     *
     * @throws Piece_Unity_Exception
     */
    public function start()
    {
        foreach (self::$_autoloadClasses as $class) {
            if (Piece_Unity_ClassLoader::loaded($class)) {
                continue;
            }

            Piece_Unity_ClassLoader::load($class);
            if (!Piece_Unity_ClassLoader::loaded($class)) {
                throw new Piece_Unity_Exception(
                    'The class [ ' .
                    $class .
                    ' ] is not found in the loaded file'
                                                );
            }
        }

        session_start();

        $this->_attributes = &$_SESSION;

        if ($this->hasAttribute('_Piece_Unity_Session_Preload')) {
            $this->_preload = $this->getAttribute('_Piece_Unity_Session_Preload');
        } else {
            $this->_preload = new Piece_Unity_Session_Preload();
            $this->setAttribute('_Piece_Unity_Session_Preload', $this->_preload);
        }
    }

    // }}}
    // {{{ removeAttribute()

    /**
     * Removes an attribute from the current session state.
     *
     * @param string $name
     */
    public function removeAttribute($name)
    {
        unset($this->_attributes[$name]);
    }

    // }}}
    // {{{ clearAttributes()

    /**
     * Removes all attributes from the current session state.
     */
    public function clearAttributes()
    {
        $this->_attributes = array();
    }

    // }}}
    // {{{ addPreloadClass()

    /**
     * Adds a class for preload to the given service.
     *
     * @param string $service
     * @param string $class
     * @param string $id
     */
    public function addPreloadClass($service, $class, $id = null)
    {
        if (is_null($this->_preload)) {
            return;
        }

        $this->_preload->addClass($service, $class, $id);
    }

    // }}}
    // {{{ setPreloadCallback()

    /**
     * Sets a callback for preload to the given service.
     *
     * @param string   $service
     * @param callback $callback
     */
    public function setPreloadCallback($service, $callback)
    {
        if (is_null($this->_preload)) {
            return;
        }

        $this->_preload->setCallback($service, $callback);
    }

    // }}}
    // {{{ restart()

    /**
     * Destroys the existing session and starts a new session.
     *
     * @link http://www.php.net/session_destroy
     * @since Method available since Release 1.6.0
     */
    public function restart()
    {
        session_destroy();
        $this->start();
        session_regenerate_id();
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
