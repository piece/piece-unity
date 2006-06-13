<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * PHP versions 4 and 5
 *
 * Copyright (c) 2006, KUBO Atsuhiro <iteman2002@yahoo.co.jp>
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
 * @author     KUBO Atsuhiro <iteman2002@yahoo.co.jp>
 * @copyright  2006 KUBO Atsuhiro <iteman2002@yahoo.co.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    SVN: $Id$
 * @link       http://iteman.typepad.jp/piece/
 * @since      File available since Release 0.1.0
 */

require_once 'Piece/Unity/Context.php';
require_once 'Piece/Unity/PluginInvoker.php';
require_once 'Piece/Unity/Error.php';
require_once 'Piece/Unity/Config/Factory.php';
require_once 'Piece/Unity/Request.php';
require_once 'Piece/Unity/ViewElement.php';

// {{{ constants

define('PIECE_UNITY_ROOT_PLUGIN', 'Root');

// }}}
// {{{ Piece_Unity

/**
 * @package    Piece_Unity
 * @author     KUBO Atsuhiro <iteman2002@yahoo.co.jp>
 * @copyright  2006 KUBO Atsuhiro <iteman2002@yahoo.co.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    Release: @package_version@
 * @since      Class available since Release 0.1.0
 */
class Piece_Unity
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
    // {{{ constructor

    /**
     * Configures the application.
     *
     * @param string             $configDirectory
     * @param string             $cacheDirectory
     * @param Piece_Unity_Config $dynamicConfig
     */
    function Piece_Unity($configDirectory = null,
                         $cacheDirectory = null,
                         $dynamicConfig = null
                         )
    {
        $this->_configure($configDirectory, $cacheDirectory, $dynamicConfig);
    }

    // }}}
    // {{{ dispatch()

    /**
     * Dispatches a request.
     */
    function dispatch()
    {
        $request = &new Piece_Unity_Request();
        $context = &Piece_Unity_Context::singleton();
        $context->setRequest($request);
        $viewElement = &new Piece_Unity_ViewElement();
        $context = &Piece_Unity_Context::singleton();
        $context->setViewElement($viewElement);
        Piece_Unity_PluginInvoker::invoke(PIECE_UNITY_ROOT_PLUGIN);
    }

    /**#@-*/

    /**#@+
     * @access private
     */

    // }}}
    // {{{ _configure()

    /**
     * Configures the application.
     *
     * First this method tries to load a configuration from a configuration
     * file in the given configration directory using
     * Piece_Unity_Config_Factory::factory method. The method creates a new
     * object if the load failed.
     * Second this method merges the given configuretion into the loaded
     * configuration.
     * Finally this method sets the configuration to the current context.
     *
     * @param string             $configDirectory
     * @param string             $cacheDirectory
     * @param Piece_Unity_Config $dynamicConfig
     */
    function _configure($configDirectory = null,
                        $cacheDirectory = null,
                        $dynamicConfig = null
                        )
    {
        Piece_Unity_Error::pushCallback(create_function('$error', 'return ' . PEAR_ERRORSTACK_PUSHANDLOG . ';'));

        $config = &Piece_Unity_Config_Factory::factory($configDirectory, $cacheDirectory);

        Piece_Unity_Error::popCallback();

        $config->setConfigurationDirectory($configDirectory);
        $config->setCacheDirectory($cacheDirectory);

        if (Piece_Unity_Error::hasErrors()) {
            $stack = &Piece_Unity_Error::getErrorStack();
            $config->setError($stack->pop());
        }

        if (is_a($dynamicConfig, 'Piece_Unity_Config')) {
            $config->merge($dynamicConfig);
        }

        $context = &Piece_Unity_Context::singleton();
        $context->setConfiguration($config);
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
