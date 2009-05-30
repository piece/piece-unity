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
 * @since      File available since Release 0.1.0
 */

// {{{ Piece_Unity

/**
 * A single entry point for Piece_Unity applications.
 *
 * @package    Piece_Unity
 * @copyright  2006-2009 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @since      Class available since Release 0.1.0
 */
class Piece_Unity
{

    // {{{ constants

    const ROOT_PLUGIN = 'Root';

    // }}}
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

    private $_config;
    private static $_configurationCallback;

    /**#@-*/

    /**#@+
     * @access public
     */

    // }}}
    // {{{ __construct()

    /**
     * Initializes an object. If one or more arguments are given, the constructor
     * configures the runtime.
     *
     * @param string             $configDirectory
     * @param string             $cacheDirectory
     * @param Piece_Unity_Config $dynamicConfig
     */
    public function __construct($configDirectory = null,
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
     * @throws Piece_Unity_Exception
     */
    public function dispatch()
    {
        if (is_null($this->_config)) {
            throw new Piece_Unity_Exception(
                __METHOD__ .
                ' method must be called after calling configure().'
                                            );
        }

        Piece_Unity_Plugin_Factory::factory(self::ROOT_PLUGIN)->invoke();
    }

    // }}}
    // {{{ setConfiguration()

    /**
     * Sets the configuration to the configuration point of the plugin.
     *
     * @param string $plugin
     * @param string $configurationPoint
     * @param string $configuration
     * @throws Piece_Unity_Exception
     * @since Method available since Release 1.1.0
     */
    public function setConfiguration($plugin, $configurationPoint, $configuration)
    {
        if (is_null($this->_config)) {
            throw new Piece_Unity_Exception(
                __METHOD__ .
                ' method must be called after calling configure().'
                                            );
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
     * @throws Piece_Unity_Exception
     * @since Method available since Release 1.1.0
     */
    public function setExtension($plugin, $extensionPoint, $extension)
    {
        if (is_null($this->_config)) {
            throw new Piece_Unity_Exception(
                __METHOD__ .
                ' method must be called after calling configure().'
                                            );
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
    public function createRuntime($callback = null)
    {
        $runtime = new self;
        if (!is_null($callback)) {
            call_user_func_array($callback, array($runtime));
        } else {
            if (!is_null(self::$_configurationCallback)) {
                call_user_func_array(self::$_configurationCallback,
                                     array($runtime)
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
    public function configure($configDirectory = null,
                              $cacheDirectory = null,
                              $dynamicConfig = null
                              )
    {
        $this->_config =
            Piece_Unity_Config_Factory::factory($configDirectory, $cacheDirectory);
        if ($dynamicConfig instanceof Piece_Unity_Config) {
            $this->_config->merge($dynamicConfig);
        }

        Piece_Unity_Context::singleton()->setConfiguration($this->_config);
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
    public static function setConfigurationCallback($callback)
    {
        self::$_configurationCallback = $callback;
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
