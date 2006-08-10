<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * PHP versions 4 and 5
 *
 * Copyright (c) 2006, KUBO Atsuhiro <iteman@users.sourceforge.net>
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
 * @author     KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @copyright  2006 KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    SVN: $Id$
 * @link       http://iteman.typepad.jp/piece/
 * @since      File available since Release 0.1.0
 */

require_once 'Piece/Unity/Error.php';

// {{{ GLOBALS

$GLOBALS['PIECE_UNITY_Plugin_Instances'] = array();
$GLOBALS['PIECE_UNITY_Plugin_Directories'] = array(dirname(__FILE__) . '/../../..');

// }}}
// {{{ Piece_Unity_Plugin_Factory

/**
 * A factory class for creating plugin objects.
 *
 * @package    Piece_Unity
 * @author     KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @copyright  2006 KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    Release: @package_version@
 * @link       http://iteman.typepad.jp/piece/
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
     * @access private
     */

    /**#@-*/

    /**#@+
     * @access public
     * @static
     */

    // }}}
    // {{{ factory()

    /**
     * Creates a plugin object from the plugin directories.
     *
     * @param string $plugin
     * @return mixed
     * @throws PIECE_UNITY_ERROR_NOT_FOUND
     * @throws PIECE_UNITY_ERROR_INVALID_PLUGIN
     */
    function &factory($plugin)
    {
        $plugin = "Piece_Unity_Plugin_$plugin";
        if (!array_key_exists($plugin, $GLOBALS['PIECE_UNITY_Plugin_Instances'])) {
            $found = false;
            $numberOfDirectories = count($GLOBALS['PIECE_UNITY_Plugin_Directories']);
            for ($i = 0; $i < $numberOfDirectories; ++$i) {
                $found = Piece_Unity_Plugin_Factory::_load($plugin, $GLOBALS['PIECE_UNITY_Plugin_Directories'][$i]);
                if ($found) {
                    break;
                }
            }

            if (!$found) {
                Piece_Unity_Error::push(PIECE_UNITY_ERROR_NOT_FOUND,
                                        "The plugin [ $plugin ] not found in the following directories:\n" .
                                        implode("\n", $GLOBALS['PIECE_UNITY_Plugin_Directories'])
                                        );
                $return = null;
                return $return;
            }

            $instance = &new $plugin();
            if (!is_a($instance, 'Piece_Unity_Plugin_Common')) {
                Piece_Unity_Error::push(PIECE_UNITY_ERROR_INVALID_PLUGIN,
                                        "The plugin [ $plugin ] is invalid."
                                        );
                $return = null;
                return $return;
            }

            $GLOBALS['PIECE_UNITY_Plugin_Instances'][$plugin] = &$instance;
        }

        return $GLOBALS['PIECE_UNITY_Plugin_Instances'][$plugin];
    }

    // }}}
    // {{{ addPluginDirectory()

    /**
     * Adds a plugin directory.
     *
     * @param string $pluginDirectory
     */
    function addPluginDirectory($pluginDirectory)
    {
        array_unshift($GLOBALS['PIECE_UNITY_Plugin_Directories'], $pluginDirectory);
    }

    /**#@-*/

    /**#@+
     * @access private
     */

    // }}}
    // {{{ _load()

    /**
     * Loads a plugin corresponding to the given plugin name.
     *
     * @param string $plugin
     * @param string $pluginDirectory
     * @return boolean
     * @static
     */
    function _load($plugin, $pluginDirectory)
    {
        $file = "$pluginDirectory/" . str_replace('_', '/', $plugin) . '.php';

        if (!file_exists($file)) {
            Piece_Unity_Error::pushCallback(create_function('$error', 'return ' . PEAR_ERRORSTACK_PUSHANDLOG . ';'));
            Piece_Unity_Error::push(PIECE_UNITY_ERROR_NOT_FOUND,
                                    "The plugin file [ $file ] for the class [ $plugin ] not found.",
                                    'warning'
                                    );
            Piece_Unity_Error::popCallback();
            return false;
        }

        if (!is_readable($file)) {
            Piece_Unity_Error::pushCallback(create_function('$error', 'return ' . PEAR_ERRORSTACK_PUSHANDLOG . ';'));
            Piece_Unity_Error::push(PIECE_UNITY_ERROR_NOT_READABLE,
                                    "The plugin file [ $file ] was not readable.",
                                    'warning'
                                    );
            Piece_Unity_Error::popCallback();
            return false;
        }

        if (!include_once $file) {
            Piece_Unity_Error::pushCallback(create_function('$error', 'return ' . PEAR_ERRORSTACK_PUSHANDLOG . ';'));
            Piece_Unity_Error::push(PIECE_UNITY_ERROR_NOT_FOUND,
                                    "The plugin file [ $file ] not found or was not readable.",
                                    'warning'
                                    );
            Piece_Unity_Error::popCallback();
            return false;
        }

        if (version_compare(phpversion(), '5.0.0', '<')) {
            $found = class_exists($plugin);
        } else {
            $found = class_exists($plugin, false);
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
