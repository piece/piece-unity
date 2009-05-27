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
 * @version    GIT: $Id$
 * @since      File available since Release 0.1.0
 */

// {{{ Piece_Unity_Plugin_Dispatcher_Simple

/**
 * A dispatcher for stateless application components.
 *
 * This plug-in invokes the action corresponding to an event if it exists,
 * and returns an event name as a view string.
 *
 * @package    Piece_Unity
 * @copyright  2006-2009 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    Release: @package_version@
 * @since      Class available since Release 0.1.0
 */
class Piece_Unity_Plugin_Dispatcher_Simple extends Piece_Unity_Plugin_Common
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
     * @throws Piece_Unity_Exception
     */
    public function invoke()
    {
        $eventName = $this->context->getEventName();

        if ($this->getConfiguration('useDefaultEvent')) {
            if (is_null($eventName) || !strlen($eventName)) {
                $eventName = $this->getConfiguration('defaultEventName');
                $this->context->setEventName($eventName);
            }
        }

        $class = str_replace('.', '', $eventName . 'Action');

        $actionDirectory = $this->getConfiguration('actionDirectory');
        if (is_null($actionDirectory)) {
            return $eventName;
        }

        if (!Piece_Unity_ClassLoader::loaded($class)) {
            try {
                Piece_Unity_ClassLoader::load($class, $actionDirectory);
            } catch (Piece_Unity_ClassLoader_NotFoundException $e) {
                return $eventName;
            }

            if (!Piece_Unity_ClassLoader::loaded($class)) {
                throw new Piece_Unity_Exception('The class [ ' .
                                                $class .
                                                ' ] is not found in the loaded file'
                                                );
            }
        }

        $action = new $class();
        if (!method_exists($action, 'invoke')) {
            throw new Piece_Unity_Exception('The method invoke() is not found in the class [ ' .
                                            $class .
                                            ' ]'
                                            );
        }

        $viewString = $action->invoke($this->context);
        if (is_null($viewString)) {
            return $eventName;
        } else {
            return $viewString;
        }
    }

    /**#@-*/

    /**#@+
     * @access protected
     */

    // }}}
    // {{{ initialize()

    /**
     * Defines and initializes extension points and configuration points.
     *
     * @since Method available since Release 0.6.0
     */
    protected function initialize()
    {
        $this->addConfigurationPoint('actionDirectory');
        $this->addConfigurationPoint('useDefaultEvent', false);
        $this->addConfigurationPoint('defaultEventName');
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
