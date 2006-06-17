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

require_once 'Piece/Unity/Error.php';

// {{{ GLOBALS

$GLOBALS['PIECE_UNITY_Plugin_Instances'] = array();
$GLOBALS['PIECE_UNITY_Plugin_Paths'] = array(dirname(__FILE__) . '/../../..');

// }}}
// {{{ Piece_Unity_Plugin_Factory

/**
 * A factory class for creating plugin objects.
 *
 * @package    Piece_Unity
 * @author     KUBO Atsuhiro <iteman2002@yahoo.co.jp>
 * @copyright  2006 KUBO Atsuhiro <iteman2002@yahoo.co.jp>
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
     */

    // }}}
    // {{{ factory()

    /**
     * Creates a plugin object from a configuration file or a cache.
     *
     * @param string $plugin
     * @return mixed
     * @throws PEAR_ErrorStack
     * @static
     */
    function &factory($plugin)
    {
        $plugin = "Piece_Unity_Plugin_$plugin";
        if (!array_key_exists($plugin, $GLOBALS['PIECE_UNITY_Plugin_Instances'])) {
            Piece_Unity_Error::pushCallback(create_function('$error', 'return ' . PEAR_ERRORSTACK_PUSHANDLOG . ';'));
            foreach ($GLOBALS['PIECE_UNITY_Plugin_Paths'] as $pluginPath) {
                Piece_Unity_Plugin_Factory::_load($plugin, $pluginPath);

                if (version_compare(phpversion(), '5.0.0', '<')) {
                    $found = class_exists($plugin);
                } else {
                    $found = class_exists($plugin, false);
                }

                if ($found) {
                    break;
                }
            }
            Piece_Unity_Error::popCallback();

            if (!$found) {
                $error = Piece_Unity_Error::raiseError(PIECE_UNITY_ERROR_NOT_FOUND,
                                                       "The plugin [ $plugin ] not found in the directories.\n" .
                                                       implode("\n", $GLOBALS['PIECE_UNITY_Plugin_Paths'])
                                                       );
                return $error;
            }

            $instance = &new $plugin();
            if (!is_a($instance, 'Piece_Unity_Plugin_Common')) {
                $error = Piece_Unity_Error::raiseError(PIECE_UNITY_ERROR_INVALID_PLUGIN,
                                                       "The plugin [ $plugin ] was invalid."
                                                       );
                return $error;
            }

            $GLOBALS['PIECE_UNITY_Plugin_Instances'][$plugin] = &$instance;
        }

        return $GLOBALS['PIECE_UNITY_Plugin_Instances'][$plugin];
    }

    // }}}
    // {{{ addPluginPath()

    /**
     * Adds a plugin path.
     *
     * @param string $pluginPath
     */
    function addPluginPath($pluginPath)
    {
        array_unshift($GLOBALS['PIECE_UNITY_Plugin_Paths'], $pluginPath);
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
     * @param string $pluginPath
     * @throws PEAR_ErrorStack
     * @static
     */
    function _load($plugin, $pluginPath)
    {
        $file = realpath("$pluginPath/" . str_replace('_', '/', $plugin) . '.php');

        if (!$file) {
            return Piece_Unity_Error::raiseError(PIECE_UNITY_ERROR_NOT_FOUND,
                                                 "The plugin file for the class [ $plugin ] not found."
                                                 );
        }

        if (!is_readable($file)) {
            return Piece_Unity_Error::raiseError(PIECE_UNITY_ERROR_NOT_READABLE,
                                                 "The plugin file [ $file ] was not readable."
                                                 );
        }

        if (!include_once $file) {
            return Piece_Unity_Error::raiseError(PIECE_UNITY_ERROR_NOT_FOUND,
                                                 "The plugin file [ $file ] not found or was not readable."
                                                 );
        }
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
