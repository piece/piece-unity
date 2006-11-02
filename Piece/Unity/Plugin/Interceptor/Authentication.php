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
 * @since      File available since Release 0.9.0
 */

require_once 'Piece/Unity/Plugin/Common.php';

// {{{ Piece_Unity_Plugin_Interceptor_Authentication

/**
 * An interceptor to control the access to protected resources on Piece_Unity
 * applications.
 *
 * @package    Piece_Unity
 * @author     KUMAKURA Yousuke <kumatch@users.sourceforge.net>
 * @copyright  2006 KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    Release: @package_version@
 * @link       http://piece-framework.com/piece-unity/
 * @since      Class available since Release 0.9.0
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
    var $_useCallback = false;
    var $_callbackKey = 'callback';

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
     * @throws PIECE_UNITY_ERROR_INVALID_CONFIGURATION
     * @throws PIECE_UNITY_ERROR_NOT_FOUND
     */
    function invoke()
    {
        $scriptName = $this->_context->getScriptName();
        foreach ($this->getConfiguration('services') as $service) {
            if (in_array($scriptName, $service['resources'])) {
                $guardDirectory = $this->getConfiguration('guardDirectory');
                if (is_null($guardDirectory)) {
                    Piece_Unity_Error::push(PIECE_UNITY_ERROR_INVALID_CONFIGURATION,
                                            'The guard directory not specified.'
                                            );

                    return;
                }

                $found = $this->_load($service['guard']['class'], $guardDirectory);
                if (!$found) {
                    Piece_Unity_Error::push(PIECE_UNITY_ERROR_NOT_FOUND,
                                            "The gaurd [ {$service['guard']['class']} ] not found in the directory [ $guardDirectory ]."
                                            );
                    return;

                }

                $guard = &new $service['guard']['class']();
                if (!method_exists($guard, $service['guard']['method'])) {
                    Piece_Unity_Error::push(PIECE_UNITY_ERROR_NOT_FOUND,
                                            "The guard method [ {$service['guard']['class']}::{$service['guard']['method']} ] not found."
                                            );
                    return;
                }

                if (!$guard->$service['guard']['method']($this->_context)) {
                    if (isset($service['useCallback'])) {
                        $useCallback = $service['useCallback'];
                    } else {
                        $useCallback = $this->_useCallback;
                    }

                    if (isset($service['callbackKey'])) {
                        $callbackKey = $service['callbackKey'];
                    } else {
                        $callbackKey = $this->_callbackKey;
                    }
                    
                    if ($useCallback === true) {
                        $url = "{$service['url']}?{$callbackKey}={$scriptName}";
                    } else {
                        $url = $service['url'];
                    }

                    $this->_context->setView($url);
                    return false;
                }
            }
        }

        return true;
    }

    /**#@-*/

    /**#@+
     * @access private
     */

    // }}}
    // {{{ _initialize()

    /**
     * Defines and initializes extension points and configuration points.
     */
    function _initialize()
    {
        $this->_addConfigurationPoint('services', array());
        $this->_addConfigurationPoint('guardDirectory');
    }

    // }}}
    // {{{ _load()

    /**
     * Loads a guard class corresponding to the given class name.
     *
     * @param string $guard
     * @param string $guardDirectory
     * @return boolean
     * @static
     */
    function _load($guard, $guardDirectory)
    {
        $file = "$guardDirectory/" . str_replace('_', '/', $guard) . '.php';

        if (!file_exists($file)) {
            Piece_Unity_Error::pushCallback(create_function('$error', 'return ' . PEAR_ERRORSTACK_PUSHANDLOG . ';'));
            Piece_Unity_Error::push(PIECE_UNITY_ERROR_NOT_FOUND,
                                    "The guard file [ $file ] for the class [ $guard ] not found.",
                                    'warning'
                                    );
            Piece_Unity_Error::popCallback();
            return false;
        }

        if (!is_readable($file)) {
            Piece_Unity_Error::pushCallback(create_function('$error', 'return ' . PEAR_ERRORSTACK_PUSHANDLOG . ';'));
            Piece_Unity_Error::push(PIECE_UNITY_ERROR_NOT_READABLE,
                                    "The guard file [ $file ] was not readable.",
                                    'warning'
                                    );
            Piece_Unity_Error::popCallback();
            return false;
        }

        if (!include_once $file) {
            Piece_Unity_Error::pushCallback(create_function('$error', 'return ' . PEAR_ERRORSTACK_PUSHANDLOG . ';'));
            Piece_Unity_Error::push(PIECE_UNITY_ERROR_NOT_FOUND,
                                    "The guard file [ $file ] not found or was not readable.",
                                    'warning'
                                    );
            Piece_Unity_Error::popCallback();
            return false;
        }

        if (version_compare(phpversion(), '5.0.0', '<')) {
            $found = class_exists($guard);
        } else {
            $found = class_exists($guard, false);
        }

        return $found;
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
