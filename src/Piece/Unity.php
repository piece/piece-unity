<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * PHP versions 4 and 5
 *
 * Copyright (c) 2006-2008 KUBO Atsuhiro <iteman@users.sourceforge.net>,
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
 * @copyright  2006-2008 KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    SVN: $Id$
 * @since      File available since Release 0.1.0
 */

require_once 'Piece/Unity/Context.php';
require_once 'Piece/Unity/Error.php';
require_once 'Piece/Unity/Config/Factory.php';
require_once 'Piece/Unity/Plugin/Factory.php';

// {{{ GLOBALS

$GLOBALS['PIECE_UNITY_Root_Plugin'] = 'Root';
$GLOBALS['PIECE_UNITY_ConfigurationCallback'] = null;

// }}}
// {{{ Piece_Unity

/**
 * A single entry point for Piece_Unity applications.
 *
 * @package    Piece_Unity
 * @copyright  2006-2008 KUBO Atsuhiro <iteman@users.sourceforge.net>
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

    var $_config;

    /**#@-*/

    /**#@+
     * @access public
     */

    // }}}
    // {{{ constructor

    /**
     * Initializes an object. If one or more arguments are given, the constructor
     * configures the runtime.
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
        if (func_num_args()) {
            $this->configure($configDirectory, $cacheDirectory, $dynamicConfig);
        }
    }

    // }}}
    // {{{ dispatch()

    /**
     * Dispatches a request.
     *
     * @throws PIECE_UNITY_ERROR_INVALID_OPERATION
     */
    function dispatch()
    {
        if (is_null($this->_config)) {
            Piece_Unity_Error::push(PIECE_UNITY_ERROR_INVALID_OPERATION,
                                    __FUNCTION__ . ' method must be called after calling configure().'
                                    );
            return;
        }

        $root = &Piece_Unity_Plugin_Factory::factory($GLOBALS['PIECE_UNITY_Root_Plugin']);
        if (Piece_Unity_Error::hasErrors()) {
            return;
        }

        $root->invoke();
    }

    // }}}
    // {{{ setConfiguration()

    /**
     * Sets the configuration to the configuration point of the plugin.
     *
     * @param string $plugin
     * @param string $configurationPoint
     * @param string $configuration
     * @throws PIECE_UNITY_ERROR_INVALID_OPERATION
     * @since Method available since Release 1.1.0
     */
    function setConfiguration($plugin, $configurationPoint, $configuration)
    {
        if (is_null($this->_config)) {
            Piece_Unity_Error::push(PIECE_UNITY_ERROR_INVALID_OPERATION,
                                    __FUNCTION__ . ' method must be called after calling configure().'
                                    );
            return;
        }

        $this->_config->setConfiguration($plugin, $configurationPoint, $configuration);
    }

    // }}}
    // {{{ setExtension()

    /**
     * Sets the extension to the extension point of the plugin.
     *
     * @param string $plugin
     * @param string $extensionPoint
     * @param string $extension
     * @throws PIECE_UNITY_ERROR_INVALID_OPERATION
     * @since Method available since Release 1.1.0
     */
    function setExtension($plugin, $extensionPoint, $extension)
    {
        if (is_null($this->_config)) {
            Piece_Unity_Error::push(PIECE_UNITY_ERROR_INVALID_OPERATION,
                                    __FUNCTION__ . ' method must be called after calling configure().'
                                    );
            return;
        }

        $this->_config->setExtension($plugin, $extensionPoint, $extension);
    }

    // }}}
    // {{{ createRuntime()

    /**
     * Creates the Piece_Unity object for the current request, and invokes the given
     * callback for any configuration.
     *
     * @param callback $callback
     * @return Piece_Unity
     * @since Method available since Release 1.5.0
     */
    function &createRuntime($callback = null)
    {
        $runtime = &new Piece_Unity();
        if (!is_null($callback)) {
            call_user_func_array($callback, array(&$runtime));
        } else {
            if (!is_null($GLOBALS['PIECE_UNITY_ConfigurationCallback'])) {
                call_user_func_array($GLOBALS['PIECE_UNITY_ConfigurationCallback'],
                                     array(&$runtime)
                                     );
            }
        }

        return $runtime;
    }

    // }}}
    // {{{ configure()

    /**
     * Configures the application.
     *
     * First this method tries to load a configuration from a configuration file in
     * the given configration directory using Piece_Unity_Config_Factory::factory().
     * This method creates a new object if the load failed.
     * Second this method merges the given configuretion into the loaded
     * configuration.
     * Finally this method sets the configuration to the current context.
     *
     * @param string             $configDirectory
     * @param string             $cacheDirectory
     * @param Piece_Unity_Config $dynamicConfig
     * @since Method available since Release 1.5.0
     */
    function configure($configDirectory = null,
                       $cacheDirectory = null,
                       $dynamicConfig = null
                       )
    {
        $this->_config =
            &Piece_Unity_Config_Factory::factory($configDirectory, $cacheDirectory);
        if (is_a($dynamicConfig, 'Piece_Unity_Config')) {
            $this->_config->merge($dynamicConfig);
        }

        $context = &Piece_Unity_Context::singleton();
        $context->setConfiguration($this->_config);
    }

    // }}}
    // {{{ setConfigurationCallback()

    /**
     * Sets the given callback as the callback for Piece_Unity::createRuntime().
     *
     * @param callback $callback
     * @throws PIECE_UNITY_ERROR_UNEXPECTED_VALUE
     * @since Method available since Release 1.7.0
     */
    function setConfigurationCallback($callback)
    {
        $GLOBALS['PIECE_UNITY_ConfigurationCallback'] = $callback;
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
