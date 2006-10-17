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
 * @author     KUMAKURA Yousuke <kumatch@users.sourceforge.net>
 * @copyright  2006 KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    SVN: $Id$
 * @link       http://piece-framework.com/piece-unity/
 * @since      File available since Release 0.5.0
 */

require_once 'Piece/Unity/Plugin/Common.php';

// {{{ Piece_Unity_Plugin_Interceptor_Authentication

/**
 * @package    Piece_Unity
 * @author     KUMAKURA Yousuke <kumatch@users.sourceforge.net>
 * @copyright  2006 KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    Release: @package_version@
 * @link       http://piece-framework.com/piece-unity/
 * @since      Class available since Release 0.7.0
 */
class Piece_Unity_Plugin_Interceptor_Authentication extends Piece_Unity_Plugin_Common
{

    // {{{ properties

    /**#@+
     * @access public
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
     * @return boolean
     */
    function invoke()
    {
        $services = $this->getConfiguration('services');
        $scriptName = $this->_context->getScriptName();

        foreach ($services as $service) {
            if (in_array($scriptName, $service['entries'])) {
                $class  = $service['guard']['class'];
                $method = $service['guard']['method'];
                if (is_bool($this->_isStartingService($class, $method))
                    && !$this->_isStartingService($class, $method)
                    ) {
                    $this->_context->setView($service['url']);
                    break;
                }
            }
        }
    }

    /**#@-*/

    /**#@+
     * @access private
     */

    // }}}
    // {{{ _isStartingService()

    /**
     * Is Starting Service ? Check by classes method.
     *
     * @since Method available since Release 0.7.0
     */
    function _isStartingService($class, $method)
    {
        $isStartingService = false;

        $guardDirectory = $this->getConfiguration('guardDirectory');
        if (is_null($guardDirectory)) {
            return $isStartingService;
        }

        $file = "$guardDirectory/" . str_replace('_', '/', $class) . '.php';
        if (is_readable($file)) {
            if (!include_once $file) {
                Piece_Unity_Error::push(PIECE_UNITY_ERROR_NOT_FOUND,
                                        "The guard file [ $file ] not found or was not readable."
                                        );
                return $isStartingService;
            }

            if (version_compare(phpversion(), '5.0.0', '<')) {
                $result = class_exists($class);
            } else {
                $result = class_exists($class, false);
            }

            if ($result) {
                $action = &new $class();
                if (method_exists($action, $method)) {
                    $isStartingService = $action->$method();
                } else {
                    Piece_Unity_Error::push(PIECE_UNITY_ERROR_NOT_FOUND,
                                            "The guard method [ $class::$method ] not found."
                                            );
                }
            }
        }

        return $isStartingService;
    }

    // }}}
    // {{{ _initialize()

    /**
     * Defines and initializes extension points and configuration points.
     *
     * @since Method available since Release 0.6.0
     */
    function _initialize()
    {
        $this->_addConfigurationPoint('services', array());
        $this->_addConfigurationPoint('guardDirectory');
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
