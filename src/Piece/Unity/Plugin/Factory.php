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

// {{{ Piece_Unity_Plugin_Factory

/**
 * A factory class for creating plugin objects.
 *
 * @package    Piece_Unity
 * @copyright  2006-2009 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @since      Class available since Release 0.1.0
 */
class Piece_Unity_Plugin_Factory
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

    private static $_pluginInstances = array();
    private static $_pluginDirectories = array();
    private static $_pluginPrefixes = array();

    /**#@-*/

    /**#@+
     * @access public
     */

    // }}}
    // {{{ factory()

    /**
     * Creates a plugin object from the plugin directories.
     *
     * @param string $pluginName
     * @return mixed
     * @throws Piece_Unity_Exception
     */
    public static function factory($pluginName)
    {
        if (!array_key_exists($pluginName, self::$_pluginInstances)) {
            $found = false;
            foreach (self::$_pluginPrefixes as $prefixAlias) {
                $pluginClass = self::_getPluginClass($pluginName, $prefixAlias);
                if (Piece_Unity_ClassLoader::loaded($pluginClass)) {
                    $found = true;
                    break;
                }
            }

            if (!$found) {
                foreach (self::$_pluginDirectories as $pluginDirectory) {
                    foreach (self::$_pluginPrefixes as $prefixAlias) {
                        $pluginClass = self::_getPluginClass($pluginName, $prefixAlias);

                        try {
                            Piece_Unity_ClassLoader::load($pluginClass, $pluginDirectory);
                        } catch (Piece_Unity_ClassLoader_NotFoundException $e) {
                            continue;
                        }

                        if (Piece_Unity_ClassLoader::loaded($pluginClass)) {
                            $found = true;
                            break 2;
                        }
                    }
                }

                if (!$found) {
                    throw new Piece_Unity_Exception(
                        'The plugin [ ' .
                        $pluginName .
                        ' ] is not found in the following directories: ' .
                        implode(', ', self::$_pluginDirectories)
                                                    );
                }
            }

            $plugin = new $pluginClass($prefixAlias);
            if (!$plugin instanceof Piece_Unity_Plugin_Common) {
                throw new Piece_Unity_Exception(
                    'The plugin [ ' .
                    $pluginName .
                    ' ] must be subclass of Piece_Unity_Plugin_Common'
                                                );
            }

            self::$_pluginInstances[$pluginName] = $plugin;
        }

        return self::$_pluginInstances[$pluginName];
    }

    // }}}
    // {{{ addPluginDirectory()

    /**
     * Adds a plugin directory.
     *
     * @param string $pluginDirectory
     */
    public static function addPluginDirectory($pluginDirectory)
    {
        array_unshift(self::$_pluginDirectories, realpath($pluginDirectory));
    }

    // }}}
    // {{{ initializePluginDirectories()

    /**
     * Clears the plug-in paths.
     *
     * @since Method available since Release 2.0.0dev1
     */
    public static function initializePluginDirectories()
    {
        self::$_pluginDirectories = array();
        self::addPluginDirectory(realpath(dirname(__FILE__) . '/../../..'));
    }

    // }}}
    // {{{ clearInstances()

    /**
     * Initializes the plug-in instances.
     */
    public static function clearInstances()
    {
        self::$_pluginInstances = array();
    }

    // }}}
    // {{{ addPluginPrefix()

    /**
     * Adds a prefix for a plug-in.
     *
     * @param string $pluginPrefix
     */
    public static function addPluginPrefix($pluginPrefix)
    {
        array_unshift(self::$_pluginPrefixes, $pluginPrefix);
    }

    // }}}
    // {{{ initializePluginPrefixes()

    /**
     * Initializes the plug-in prefixes.
     *
     * @since Method available since Release 2.0.0dev1
     */
    public static function initializePluginPrefixes()
    {
        self::$_pluginPrefixes = array();
        self::addPluginPrefix('Piece_Unity_Plugin');
    }

    /**#@-*/

    /**#@+
     * @access protected
     */

    /**#@-*/

    /**#@+
     * @access private
     */

    // }}}
    // {{{ _getPluginClass()

    /**
     * Gets the class name for a given plug-in name with a prefix alias.
     *
     * @param string $pluginName
     * @param string $prefixAlias
     * @return string
     */
    private static function _getPluginClass($pluginName, $prefixAlias)
    {
        if ($prefixAlias) {
            return $prefixAlias .  '_' . $pluginName;
        } else {
            return $pluginName;
        }
    }

    /**#@-*/

    // }}}
}

// }}}

Piece_Unity_Plugin_Factory::initializePluginDirectories();
Piece_Unity_Plugin_Factory::initializePluginPrefixes();

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
