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

// {{{ Piece_Unity_Session_Preload

/**
 * A class *pre*loader for restoring objects in session.
 *
 * @package    Piece_Unity
 * @copyright  2006-2009 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @since      Class available since Release 0.9.0
 */
class Piece_Unity_Session_Preload
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

    private $_services = array();

    /**#@-*/

    /**#@+
     * @access public
     */

    // }}}
    // {{{ __wakeup

    /**
     * Preloads classes by service specific callback.
     */
    public function __wakeup()
    {
        foreach (array_keys($this->_services) as $service) {
            foreach ($this->_services[$service]['classes'] as $class => $id) {
                call_user_func($this->_services[$service]['callback'], $class, $id);
            }
        }
    }

    // }}}
    // {{{ addClass()

    /**
     * Adds a class for preload to the given service.
     *
     * @param string $service
     * @param string $class
     * @param string $id
     */
    public function addClass($service, $class, $id = null)
    {
        $this->_initializeService($service);
        $this->_services[$service]['classes'][$class] = $id;
    }

    // }}}
    // {{{ setCallback()

    /**
     * Sets a callback for preload to the given service.
     *
     * @param string   $service
     * @param callback $callback
     */
    public function setCallback($service, $callback)
    {
        $this->_initializeService($service);
        $this->_services[$service]['callback'] = $callback;
    }

    /**#@-*/

    /**#@+
     * @access private
     */

    // }}}
    // {{{ _initializeService()

    /**
     * Initializes the structure for a given service.
     *
     * @param string   $service
     */
    private function _initializeService($service)
    {
        if (!array_key_exists($service, $this->_services)) {
            $this->_services[$service] = array('callback' => null,
                                               'classes' => array()
                                               );
        }
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
