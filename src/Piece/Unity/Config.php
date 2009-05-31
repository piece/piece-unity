<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * PHP version 5
 *
 * Copyright (c) 2006-2007, 2009 KUBO Atsuhiro <kubo@iteman.jp>,
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
 * @copyright  2006-2007, 2009 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @since      File available since Release 0.1.0
 */

// {{{ Piece_Unity_Config

/**
 * The configuration container for Piece_Unity applications.
 *
 * @package    Piece_Unity
 * @copyright  2006-2007, 2009 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @since      Class available since Release 0.1.0
 */
class Piece_Unity_Config
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

    private $_extensions = array();
    private $_configurations = array();
    private $_extensionPoints = array();
    private $_configurationPoints = array();

    /**#@-*/

    /**#@+
     * @access public
     */

    // }}}
    // {{{ setExtension()

    /**
     * Sets the extension to the extension point of the plugin.
     *
     * @param string $plugin
     * @param string $extensionPoint
     * @param string $extension
     */
    public function setExtension($plugin, $extensionPoint, $extension)
    {
        $this->_extensions[ strtolower($plugin) ][ strtolower($extensionPoint) ] = $extension;
    }

    // }}}
    // {{{ setConfiguration()

    /**
     * Sets the configuration to the configuration point of the plugin.
     *
     * @param string $plugin
     * @param string $configurationPoint
     * @param string $configuration
     */
    public function setConfiguration($plugin, $configurationPoint, $configuration)
    {
        $this->_configurations[ strtolower($plugin) ][ strtolower($configurationPoint) ] = $configuration;
    }

    // }}}
    // {{{ getExtensionDefinition()

    /**
     * Gets the definition of the extension for the extension point of the plugin.
     *
     * @param string $plugin
     * @param string $extensionPoint
     * @return string
     */
    public function getExtensionDefinition($plugin, $extensionPoint)
    {
        $plugin = strtolower($plugin);
        $extensionPoint = strtolower($extensionPoint);
        if (!array_key_exists($plugin, $this->_extensions)) {
            return;
        }

        if (!array_key_exists($extensionPoint, $this->_extensions[$plugin])) {
            return;
        }

        return $this->_extensions[$plugin][$extensionPoint];
    }

    // }}}
    // {{{ getConfigurationDefinition()

    /**
     * Gets the definition of the configuration for the configuration point of
     * the plugin.
     *
     * @param string $plugin
     * @param string $configurationPoint
     * @return string
     */
    public function getConfigurationDefinition($plugin, $configurationPoint)
    {
        $plugin = strtolower($plugin);
        $configurationPoint = strtolower($configurationPoint);
        if (!array_key_exists($plugin, $this->_configurations)) {
            return;
        }

        if (!array_key_exists($configurationPoint, $this->_configurations[$plugin])) {
            return;
        }

        return $this->_configurations[$plugin][$configurationPoint];
    }

    // }}}
    // {{{ merge()

    /**
     * Merges the given configuretion into the existing configuration.
     *
     * @param Piece_Unity_Config $config
     */
    public function merge(Piece_Unity_Config $config)
    {
        $extensions = $config->getExtensions();
        array_walk($extensions, array($this, 'mergeExtensions'));

        $configurations = $config->getConfigurations();
        array_walk($configurations, array($this, 'mergeConfigurations'));
    }

    // }}}
    // {{{ mergeExtensions()

    /**
     * A callback that will be called by array_walk() function in merge() method.
     *
     * @param string $extensions
     * @param string $plugin
     */
    public function mergeExtensions($extensions, $plugin)
    {
        foreach ($extensions as $extensionPoint => $extension) {
            $this->_extensions[ strtolower($plugin) ][ strtolower($extensionPoint) ] = $extension;
        }
    }

    // }}}
    // {{{ mergeConfigurations()

    /**
     * A callback that will be called by array_walk() function in merge() method.
     *
     * @param string $configurations
     * @param string $plugin
     */
    public function mergeConfigurations($configurations, $plugin)
    {
        foreach ($configurations as $configurationPoint => $configuration) {
            $this->_configurations[ strtolower($plugin) ][ strtolower($configurationPoint) ] = $configuration;
        }
    }

    // }}}
    // {{{ getExtensions()

    /**
     * Gets the array of extensions.
     *
     * @return array
     */
    public function getExtensions()
    {
        return $this->_extensions;
    }

    // }}}
    // {{{ getConfigurations()

    /**
     * Gets the array of configurations.
     *
     * @return array
     */
    public function getConfigurations()
    {
        return $this->_configurations;
    }

    // }}}
    // {{{ validateExtensionPoints()

    /**
     * Validates whether all extension points in the current configuration can be
     * found in the given plug-in.
     *
     * @param string $plugin
     * @throws Piece_Unity_Exception
     * @since Method available since Release 1.1.0
     */
    public function validateExtensionPoints($plugin)
    {
        $plugin = strtolower($plugin);
        if (!array_key_exists($plugin, $this->_extensions)) {
            return;
        }

        foreach (array_keys($this->_extensions[$plugin]) as $extensionPoint) {
            if (!array_key_exists($extensionPoint, $this->_extensionPoints[$plugin])) {
                throw new Piece_Unity_Exception('The extension point [ ' .
                                                $extensionPoint .
                                                ' ] is not found in the plug-in [ ' .
                                                $plugin .
                                                ' ]'
                                                );
            }
        }
    }

    // }}}
    // {{{ validateConfigurationPoints()

    /**
     * Validates whether all configuration points in the current configuration can be
     * found in the given plug-in.
     *
     * @param string $plugin
     * @throws Piece_Unity_Exception
     * @since Method available since Release 1.1.0
     */
    public function validateConfigurationPoints($plugin)
    {
        $plugin = strtolower($plugin);
        if (!array_key_exists($plugin, $this->_configurations)) {
            return;
        }

        foreach (array_keys($this->_configurations[$plugin]) as $configurationPoint) {
            if (!array_key_exists($configurationPoint, $this->_configurationPoints[$plugin])) {
                throw new Piece_Unity_Exception('The configuration point [ ' .
                                                $configurationPoint .
                                                ' ] is not found in the plug-in [ ' .
                                                $plugin .
                                                ' ]'
                                                );
            }
        }
    }

    // }}}
    // {{{ addExtensionPoint()

    /**
     * Adds the extension point to the plugin, and sets the default value for
     * the extension point to the given value.
     *
     * @param string $plugin
     * @param string $extensionPoint
     * @param string $default
     * @since Method available since Release 2.0.0dev1
     */
    public function addExtensionPoint($plugin, $extensionPoint, $default = null)
    {
        $this->_extensionPoints[ strtolower($plugin) ][ strtolower($extensionPoint) ] = $default;
    }

    // }}}
    // {{{ addConfigurationPoint()

    /**
     * Adds the configuration point to the plugin, and sets the default value
     * for the configuration point to the given value.
     *
     * @param string $plugin
     * @param string $configurationPoint
     * @param string $default
     * @since Method available since Release 2.0.0dev1
     */
    public function addConfigurationPoint($plugin, $configurationPoint, $default = null)
    {
        $this->_configurationPoints[ strtolower($plugin) ][ strtolower($configurationPoint) ] = $default;
    }

    // }}}
    // {{{ getExtension()

    /**
     * Gets the extension of the given extension point.
     *
     * @param string $plugin
     * @param string $extensionPoint
     * @return mixed
     * @throws Piece_Unity_Exception
     * @since Method available since Release 2.0.0dev1
     */
    public function getExtension($plugin, $extensionPoint)
    {
        $plugin = strtolower($plugin);
        if (!array_key_exists($plugin, $this->_extensionPoints)) {
            throw new Piece_Unity_Exception(
                'The plug-in [ ' .
                $plugin .
                ' ] is not found in the configuration repository'
                                            );
        }

        $extensionPoint = strtolower($extensionPoint);
        if (!array_key_exists($extensionPoint, $this->_extensionPoints[$plugin])) {
            throw new Piece_Unity_Exception(
                'The extension point [ ' .
                $extensionPoint .
                ' ] is not found in the plug-in [ ' .
                $plugin .
                ' ]'
                                            );
        }

        $extension = $this->getExtensionDefinition($plugin, $extensionPoint);
        if (is_null($extension)) {
            $extension = $this->_extensionPoints[$plugin][$extensionPoint];
        }

        if (!$extension || is_array($extension)) {
            return $extension;
        }

        return Piece_Unity_Plugin_Factory::factory($extension);
    }

    // }}}
    // {{{ getConfiguration()

    /**
     * Gets the configuration of the given configuration point.
     *
     * @param string $plugin
     * @param string $configurationPoint
     * @return string
     * @throws Piece_Unity_Exception
     * @since Method available since Release 2.0.0dev1
     */
    public function getConfiguration($plugin, $configurationPoint)
    {
        $plugin = strtolower($plugin);
        if (!array_key_exists($plugin, $this->_configurationPoints)) {
            throw new Piece_Unity_Exception(
                'The plug-in [ ' .
                $plugin .
                ' ] is not found in the configuration repository'
                                            );
        }

        $configurationPoint = strtolower($configurationPoint);
        if (!array_key_exists($configurationPoint, $this->_configurationPoints[$plugin])) {
            throw new Piece_Unity_Exception(
                'The configuration point [ ' .
                $configurationPoint .
                ' ] is not found in the plug-in [ ' .
                $plugin .
                ' ]'
                                            );
        }

        $configuration =
            $this->getConfigurationDefinition($plugin, $configurationPoint);
        if (is_null($configuration)) {
            $configuration =
                $this->_configurationPoints[$plugin][$configurationPoint];
        }

        return $configuration;
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
