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

// {{{ Piece_Unity_Plugin_Common

/**
 * The base class for Piece_Unity plug-ins.
 *
 * @package    Piece_Unity
 * @copyright  2006-2009 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @since      Class available since Release 0.1.0
 */
abstract class Piece_Unity_Plugin_Common
{

    // {{{ properties

    /**#@+
     * @access public
     */

    /**#@-*/

    /**#@+
     * @access protected
     */

    protected $context;

    /**#@-*/

    /**#@+
     * @access private
     */

    private $_extensionPoints = array();
    private $_configurationPoints = array();
    private $_name;

    /**#@-*/

    /**#@+
     * @access public
     */

    // }}}
    // {{{ __construct()

    /**
     * Sets a single instance of Piece_Unity_Context class to a plugin, and defines
     * extension points and configuration points for the plugin. And also the plug-in
     * name is set.
     *
     * @param string $prefix
     */
    public function __construct($prefix = 'Piece_Unity_Plugin')
    {
        if (strlen($prefix)) {
            $this->_name = str_replace(strtolower("{$prefix}_"), '', strtolower(get_class($this)));
        } else {
            $this->_name = strtolower(get_class($this));
        }

        $this->_context = Piece_Unity_Context::singleton();

        $this->initialize();

        $config = $this->_context->getConfiguration();
        $config->validateExtensionPoints($this->_name, array_keys($this->_extensionPoints));
        $config->validateConfigurationPoints($this->_name, array_keys($this->_configurationPoints));
    }

    /**#@-*/

    /**#@+
     * @access protected
     */

    // }}}
    // {{{ addExtensionPoint()

    /**
     * Adds the extension point to the plugin, and sets the default value for
     * the extension point to the given value.
     *
     * @param string $extensionPoint
     * @param string $default
     */
    protected function addExtensionPoint($extensionPoint, $default = null)
    {
        $this->_extensionPoints[ strtolower($extensionPoint) ] = $default;
    }

    // }}}
    // {{{ addConfigurationPoint()

    /**
     * Adds the configuration point to the plugin, and sets the default value
     * for the configuration point to the given value.
     *
     * @param string $configurationPoint
     * @param string $default
     */
    protected function addConfigurationPoint($configurationPoint, $default = null)
    {
        $this->_configurationPoints[ strtolower($configurationPoint) ] = $default;
    }

    // }}}
    // {{{ initialize()

    /**
     * Defines and initializes extension points and configuration points.
     *
     * @since Method available since Release 0.6.0
     */
    protected function initialize() {}

    // }}}
    // {{{ getExtension()

    /**
     * Gets the extension of the given extension point.
     *
     * @param string $extensionPoint
     * @return mixed
     * @throws Piece_Unity_Exception
     * @since Method available since Release 0.12.0
     */
    protected function getExtension($extensionPoint)
    {
        $extensionPoint = strtolower($extensionPoint);
        if (!array_key_exists($extensionPoint, $this->_extensionPoints)) {
            throw new Piece_Unity_Exception("The configuration point  [ $extensionPoint ] is not found in the plug-in [ {$this->_name} ]");
        }

        $extension = $this->_context->getConfiguration()
                                    ->getExtension($this->_name, $extensionPoint);
        if (is_null($extension)) {
            $extension = $this->_extensionPoints[$extensionPoint];
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
     * @throws Piece_Unity_Exception
     * @since Method available since Release 0.12.0
     */
    protected function getConfiguration($configurationPoint)
    {
        $configurationPoint = strtolower($configurationPoint);
        if (!array_key_exists($configurationPoint, $this->_configurationPoints)) {
            throw new Piece_Unity_Exception("The configuration point  [ $configurationPoint ] is not found in the plug-in [ {$this->_name} ]");
        }

        $configuration = $this->_context->getConfiguration()
                                        ->getConfiguration($this->_name,
                                                           $configurationPoint
                                                           );
        if (is_null($configuration)) {
            $configuration = $this->_configurationPoints[$configurationPoint];
        }

        return $configuration;
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
