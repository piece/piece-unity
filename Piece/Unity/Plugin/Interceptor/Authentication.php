<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * PHP versions 4 and 5
 *
 * Copyright (c) 2006-2007 KUBO Atsuhiro <iteman@users.sourceforge.net>,
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
 * @subpackage Piece_Unity_Plugin_Interceptor_Authentication
 * @copyright  2006-2007 KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    SVN: $Id$
 * @since      File available since Release 0.9.0
 */

require_once 'Piece/Unity/Plugin/Common.php';
require_once 'Piece/Unity/Error.php';
require_once 'Piece/Unity/ClassLoader.php';

// {{{ Piece_Unity_Plugin_Interceptor_Authentication

/**
 * An interceptor to control the access to resources which can be accessed
 * only by authenticated users.
 *
 * @package    Piece_Unity
 * @subpackage Piece_Unity_Plugin_Interceptor_Authentication
 * @copyright  2006-2007 KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    Release: @package_version@
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
     * @throws PIECE_UNITY_ERROR_NOT_READABLE
     * @throws PIECE_UNITY_ERROR_CANNOT_READ
     */
    function invoke()
    {
        foreach ($this->_getConfiguration('services') as $service) {
            if (!$this->_isProtectedResource(@$service['resources'])) {
                continue;
            }

            $guardDirectory = $this->_getConfiguration('guardDirectory');
            if (is_null($guardDirectory)) {
                Piece_Unity_Error::push(PIECE_UNITY_ERROR_INVALID_CONFIGURATION,
                                        'The guard directory does not specified.',
                                        'exception',
                                        array('plugin' => __CLASS__)
                                        );

                return;
            }

            $guardClass = @$service['guard']['class'];
            if (is_null($guardClass) || !strlen($guardClass)) {
                Piece_Unity_Error::push(PIECE_UNITY_ERROR_INVALID_CONFIGURATION,
                                        'The guard class does not specified.',
                                        'exception',
                                        array('plugin' => __CLASS__)
                                        );
                return;
            }

            $guardMethod = @$service['guard']['method'];
            if (is_null($guardMethod) || !strlen($guardMethod)) {
                Piece_Unity_Error::push(PIECE_UNITY_ERROR_INVALID_CONFIGURATION,
                                        'The guard method does not specified.',
                                        'exception',
                                        array('plugin' => __CLASS__)
                                        );
                return;
            }

            if (!Piece_Unity_ClassLoader::loaded($guardClass)) {
                Piece_Unity_ClassLoader::load($guardClass, $guardDirectory);
                if (Piece_Unity_Error::hasErrors('exception')) {
                    return;
                }

                if (!Piece_Unity_ClassLoader::loaded($guardClass)) {
                    Piece_Unity_Error::push(PIECE_UNITY_ERROR_NOT_FOUND,
                                            "The guard class [ $guardClass ] not found in the guard directory [ $guardDirectory ].",
                                            'exception',
                                            array('plugin' => __CLASS__)
                                            );
                    return;
                }
            }

            $guard = &new $guardClass();
            if (!method_exists($guard, $guardMethod)) {
                Piece_Unity_Error::push(PIECE_UNITY_ERROR_NOT_FOUND,
                                        "The guard method [ $guardClass::$guardMethod ] not found.",
                                        'exception',
                                        array('plugin' => __CLASS__)
                                        );
                return;
            }

            if (!$guard->$guardMethod($this->_context)) {
                $this->_context->setView($this->_getServiceURL(@$service['url'],
                                                               @$service['useCallback'],
                                                               @$service['callbackKey'])
                                         );
                return false;
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
    // {{{ _isProtectedResource()

    /**
     * Returns whether the current resource is protected or not.
     *
     * @param array $resources
     * @return boolean
     */
    function _isProtectedResource($resources)
    {
        if (!is_array($resources)) {
            return false;
        }

        if ($this->_context->usingProxy()) {
            $path = $this->_context->getProxyPath();
            if (!is_null($path)) {
                for ($i = 0; $i < count($resources); ++$i) {
                    $resources[$i] = str_replace('//', '/', $path . $resources[$i]);
                }
            }
        }

        return in_array($this->_context->getScriptName(), $resources);
    }

    // }}}
    // {{{ _getServiceURL()

    /**
     * Gets the appropriate URL for an authentication service.
     *
     * @param string  $url
     * @param boolean $useCallback
     * @param string  $callbackKey
     * @return string
     */
    function _getServiceURL($url, $useCallback, $callbackKey)
    {
        if (is_null($useCallback)) {
            return $url;
        }

        if (!$useCallback) {
            return $url;
        }

        if (!(array_key_exists('QUERY_STRING', $_SERVER) && strlen($_SERVER['QUERY_STRING']))) {
            $query = '';
        } else {
            $query = "?{$_SERVER['QUERY_STRING']}";
        }

        if (!array_key_exists('PATH_INFO', $_SERVER)) {
            $pathInfo = '';
        } else {
            $pathInfo = $_SERVER['PATH_INFO'];
        }

        if (is_null($callbackKey)) {
            $callbackKey = 'callback';
        }

        return "$url?$callbackKey=" . htmlentities(rawurlencode($this->_context->getScriptName() . "$pathInfo$query"));
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
