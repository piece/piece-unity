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
     * @param string $featureName
     */
    public function __construct($featureName)
    {
        $this->_name = $featureName;
        $this->context = Piece_Unity_Context::singleton();
        $this->initialize();
    }

    // }}}
    // {{{ getName()

    /**
     * Gets the name of the plug-in.
     */
    public function getName()
    {
        return $this->_name;
    }

    /**#@-*/

    /**#@+
     * @access protected
     */

    // }}}
    // {{{ initialize()

    /**
     * Defines and initializes extension points and configuration points.
     *
     * @since Method available since Release 0.6.0
     */
    protected function initialize() {}

    // }}}
    // {{{ addExtensionPoint()

    /**
     * Adds the extension point to the plugin, and sets the default value for
     * the extension point to the given value.
     *
     * @param string  $extensionPoint
     * @param boolean $isOptional
     * @param boolean $isMultiple
     * @param array   $defaltValues
     */
    protected function addExtensionPoint(
        $extensionPoint,
        $isOptional = false,
        $isMultiple = false,
        $defaultValues = array()
                                         )
    {
        $config = $this->context->getConfiguration();
        if (!$config->hasExtensionPoint($this->getName(), $extensionPoint)) {
            $config->defineServicePoint($this->getName(),
                                        $extensionPoint,
                                        $isOptional,
                                        $isMultiple,
                                        $defaultValues
                                        );
        }
    }

    // }}}
    // {{{ addConfigurationPoint()

    /**
     * Adds the configuration point to the plugin, and sets the default value
     * for the configuration point to the given value.
     *
     * @param string  $configurationPoint
     * @param boolean $isOptional
     * @param boolean $isMultiple
     * @param array   $defaltValues
     */
    protected function addConfigurationPoint(
        $configurationPoint,
        $isOptional = false,
        $isMultiple = false,
        $defaultValues = array()
                                             )
    {
        $config = $this->context->getConfiguration();
        if (!$config->hasExtensionPoint($this->getName(), $configurationPoint)) {
            $config->defineValuePoint($this->getName(),
                                      $configurationPoint,
                                      $isOptional,
                                      $isMultiple,
                                      $defaultValues
                                      );
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
