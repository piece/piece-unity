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

// {{{ Piece_Unity_Config

/**
 * The configuration container for Piece_Unity applications.
 *
 * @package    Piece_Unity
 * @author     KUBO Atsuhiro <iteman2002@yahoo.co.jp>
 * @copyright  2006 KUBO Atsuhiro <iteman2002@yahoo.co.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    Release: @package_version@
 * @link       http://iteman.typepad.jp/piece/
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
    var $_configurationDirectory;
    var $_cacheDirectory;
    var $_mergedExtensions = array();
    var $_mergedConfigurations = array();

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
    // {{{ setConfigurationDirectory()

    /**
     * Sets the configuration directory.
     *
     * @param string $configDirectory
     */
    function setConfigurationDirectory($configDirectory)
    {
        $this->_configurationDirectory = $configDirectory;
    }

    // }}}
    // {{{ setCacheDirectory()

    /**
     * Sets the cache directory.
     *
     * @param string $cacheDirectory
     */
    function setCacheDirectory($cacheDirectory)
    {
        $this->_cacheDirectory = $cacheDirectory;
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
            return null;
        }

        if (!array_key_exists($extensionPoint, $this->_extensions[$plugin])) {
            return null;
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
            return null;
        }

        if (!array_key_exists($configurationPoint, $this->_configurations[$plugin])) {
            return null;
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
     * A callback that will be called by array_walk in merge method.
     *
     * @param string $value
     * @param string $key
     */
    function mergeExtensions($value, $plugin)
    {
        foreach ($value as $extensionPoint => $extension) {
            $this->_extensions[ strtolower($plugin) ][ strtolower($extensionPoint) ] = $extension;
            $this->_mergedExtensions[ strtolower($plugin) ][ strtolower($extensionPoint) ] = $extension;
        }
    }

    // }}}
    // {{{ mergeConfigurations()

    /**
     * A callback that will be called by array_walk in merge method.
     *
     * @param string $value
     * @param string $key
     */
    function mergeConfigurations($value, $plugin)
    {
        foreach ($value as $configurationPoint => $configuration) {
            $this->_configurations[ strtolower($plugin) ][ strtolower($configurationPoint) ] = $configuration;
            $this->_mergedConfigurations[ strtolower($plugin) ][ strtolower($configurationPoint) ] = $configuration;
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
    // {{{ getConfigurationDirectory()

    /**
     * Gets the configuration directory.
     *
     * @return string
     */
    function getConfigurationDirectory()
    {
        return $this->_configurationDirectory;
    }

    // }}}
    // {{{ getCacheDirectory()

    /**
     * Gets the cache directory.
     *
     * @return string
     */
    function getCacheDirectory()
    {
        return $this->_cacheDirectory;
    }

    // }}}
    // {{{ getError()

    /**
     * Gets an error on loading a configuration file.
     *
     * @return array
     * @see Piece_Unity_Config::setError()
     */
    function getError()
    {
        return $this->_error;
    }

    // }}}
    // {{{ isMergedExtensionPoint()

    /**
     * Returns whether the given extension point is merged with a dynamic configration.
     *
     * @param string $plugin
     * @param string $extensionPoint
     * @return boolean
     */
    function isMergedExtensionPoint($plugin, $extensionPoint)
    {
        if (array_key_exists(strtolower($plugin), $this->_mergedExtensions)
            && array_key_exists(strtolower($extensionPoint), $this->_mergedExtensions[ strtolower($plugin) ])
            ) {
            return true;
        }

        return false;
    }

    // }}}
    // {{{ isMergedConfigurationPoint()

    /**
     * Returns whether the given configuration point is merged with a dynamic configration.
     *
     * @param string $plugin
     * @param string $configurationPoint
     * @return boolean
     */
    function isMergedConfigurationPoint($plugin, $configurationPoint)
    {
        if (array_key_exists(strtolower($plugin), $this->_mergedConfigurations)
            && array_key_exists(strtolower($configurationPoint), $this->_mergedConfigurations[ strtolower($plugin) ])
            ) {
            return true;
        }

        return false;
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
?>
