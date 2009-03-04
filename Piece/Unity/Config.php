<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * PHP versions 4 and 5
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
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    GIT: $Id$
 * @since      File available since Release 0.1.0
 */

// {{{ Piece_Unity_Config

/**
 * The configuration container for Piece_Unity applications.
 *
 * @package    Piece_Unity
 * @copyright  2006-2007, 2009 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
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
     * @access private
     */

    var $_extensions = array();
    var $_configurations = array();

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
    function setExtension($plugin, $extensionPoint, $extension)
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
    function setConfiguration($plugin, $configurationPoint, $configuration)
    {
        $this->_configurations[ strtolower($plugin) ][ strtolower($configurationPoint) ] = $configuration;
    }

    // }}}
    // {{{ getExtension()

    /**
     * Gets the extension for the extension point of the plugin.
     *
     * @param string $plugin
     * @param string $extensionPoint
     * @return string
     */
    function getExtension($plugin, $extensionPoint)
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
    // {{{ getConfiguration()

    /**
     * Gets the configuration for the configuration point of the plugin.
     *
     * @param string $plugin
     * @param string $configurationPoint
     * @return string
     */
    function getConfiguration($plugin, $configurationPoint)
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
     * @param Piece_Unity_Config &$config
     */
    function merge(&$config)
    {
        $extensions = $config->getExtensions();
        array_walk($extensions, array(&$this, 'mergeExtensions'));

        $configurations = $config->getConfigurations();
        array_walk($configurations, array(&$this, 'mergeConfigurations'));
    }

    // }}}
    // {{{ mergeExtensions()

    /**
     * A callback that will be called by array_walk() function in merge()
     * method.
     *
     * @param string $extensions
     * @param string $plugin
     */
    function mergeExtensions($extensions, $plugin)
    {
        foreach ($extensions as $extensionPoint => $extension) {
            $this->_extensions[ strtolower($plugin) ][ strtolower($extensionPoint) ] = $extension;
        }
    }

    // }}}
    // {{{ mergeConfigurations()

    /**
     * A callback that will be called by array_walk() function in merge()
     * method.
     *
     * @param string $configurations
     * @param string $plugin
     */
    function mergeConfigurations($configurations, $plugin)
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
    function getExtensions()
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
    function getConfigurations()
    {
        return $this->_configurations;
    }

    // }}}
    // {{{ validateExtensionPoints()

    /**
     * Validates whether all extension points in the current configuration
     * can be found in the given plug-in.
     *
     * @param string $plugin
     * @param array  $extensionPoints
     * @throws PIECE_UNITY_ERROR_NOT_FOUND
     * @since Method available since Release 1.1.0
     */
    function validateExtensionPoints($plugin, $extensionPoints)
    {
        $plugin = strtolower($plugin);
        if (!array_key_exists($plugin, $this->_extensions)) {
            return;
        }

        foreach (array_keys($this->_extensions[$plugin]) as $extensionPoint) {
            if (!in_array($extensionPoint, $extensionPoints)) {
                Piece_Unity_Error::push(PIECE_UNITY_ERROR_NOT_FOUND,
                                        "The extension point [ $extensionPoint ] is not found in the plug-in [ $plugin ]."
                                        );
                return;
            }
        }
    }

    // }}}
    // {{{ validateConfigurationPoints()

    /**
     * Validates whether all configuration points in the current
     * configuration can be found in the given plug-in.
     *
     * @param string $plugin
     * @param array  $configurationPoints
     * @throws PIECE_UNITY_ERROR_NOT_FOUND
     * @since Method available since Release 1.1.0
     */
    function validateConfigurationPoints($plugin, $configurationPoints)
    {
        $plugin = strtolower($plugin);
        if (!array_key_exists($plugin, $this->_configurations)) {
            return;
        }

        foreach (array_keys($this->_configurations[$plugin]) as $configurationPoint) {
            if (!in_array($configurationPoint, $configurationPoints)) {
                Piece_Unity_Error::push(PIECE_UNITY_ERROR_NOT_FOUND,
                                        "The configuration point [ $configurationPoint ] is not found in the plug-in [ $plugin ]."
                                        );
                return;
            }
        }
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
