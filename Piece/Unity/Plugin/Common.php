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
 * @author     KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @copyright  2006-2007 KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    SVN: $Id$
 * @link       http://piece-framework.com/piece-unity/
 * @since      File available since Release 0.1.0
 */

require_once 'Piece/Unity/Context.php';
require_once 'Piece/Unity/Plugin/Factory.php';

// {{{ Piece_Unity_Plugin_Common

/**
 * The base class for Piece_Unity plug-ins.
 *
 * @package    Piece_Unity
 * @author     KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @copyright  2006-2007 KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    Release: @package_version@
 * @link       http://piece-framework.com/piece-unity/
 * @since      Class available since Release 0.1.0
 */
class Piece_Unity_Plugin_Common
{

    // {{{ properties

    /**#@+
     * @access public
     */

    /**#@-*/

    /**#@+
     * @access private
     */

    var $_context;
    var $_extensionPoints = array();
    var $_configurationPoints = array();
    var $_name;

    /**#@-*/

    /**#@+
     * @access public
     */

    // }}}
    // {{{ constructor

    /**
     * Sets a single instance of Piece_Unity_Context class to a plugin, and
     * defines extension points and configuration points for the plugin.
     * And also the plug-in name is set.
     *
     * @param string $prefix
     */
    function Piece_Unity_Plugin_Common($prefix = 'Piece_Unity_Plugin')
    {
        if (strlen($prefix)) {
            $this->_name = str_replace(strtolower("{$prefix}_"), '', strtolower(get_class($this)));
        } else {
            $this->_name = strtolower(get_class($this));
        }

        $this->_context = &Piece_Unity_Context::singleton();
        $this->_initialize();
    }

    // }}}
    // {{{ invoke()

    /**
     * Invokes the plugin specific code.
     *
     * @abstract
     */
    function invoke() {}

    // }}}
    // {{{ getExtension()

    /**
     * Gets the extension of the given extension point.
     *
     * @param string $extensionPoint
     * @return mixed
     * @throws PIECE_UNITY_ERROR_NOT_FOUND
     * @throws PIECE_UNITY_ERROR_INVALID_PLUGIN
     */
    function &getExtension($extensionPoint)
    {
        $config = &$this->_context->getConfiguration();
        $extension = $config->getExtension($this->_name, strtolower($extensionPoint));
        if (is_null($extension)) {
            $extension = $this->_extensionPoints[ strtolower($extensionPoint) ];
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
     * @param string $configurationPoint
     * @return string
     */
    function getConfiguration($configurationPoint)
    {
        $config = &$this->_context->getConfiguration();
        $configuration = $config->getConfiguration($this->_name, strtolower($configurationPoint));
        if (is_null($configuration)) {
            $configuration = $this->_configurationPoints[ strtolower($configurationPoint) ];
        }

        return $configuration;
    }

    /**#@-*/

    /**#@+
     * @access private
     */

    // }}}
    // {{{ _addExtensionPoint()

    /**
     * Adds the extension point to the plugin, and sets the default value for
     * the extension point to the given value.
     *
     * @param string $extensionPoint
     * @param string $default
     */
    function _addExtensionPoint($extensionPoint, $default = null)
    {
        $this->_extensionPoints[ strtolower($extensionPoint) ] = $default;
    }

    // }}}
    // {{{ _addConfigurationPoint()

    /**
     * Adds the configuration point to the plugin, and sets the default value
     * for the configuration point to the given value.
     *
     * @param string $configurationPoint
     * @param string $default
     */
    function _addConfigurationPoint($configurationPoint, $default = null)
    {
        $this->_configurationPoints[ strtolower($configurationPoint) ] = $default;
    }

    // }}}
    // {{{ _initialize()

    /**
     * Defines and initializes extension points and configuration points.
     *
     * @since Method available since Release 0.6.0
     */
    function _initialize() {}

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
