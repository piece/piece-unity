<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * PHP versions 4 and 5
 *
 * Copyright (c) 2006 KUBO Atsuhiro <iteman@users.sourceforge.net>,
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
 * @link       http://piece-framework.com/piece-unity/
 * @since      File available since Release 0.1.0
 */

require_once 'Piece/Unity/Plugin/Common.php';
require_once 'Piece/Unity/Session.php';
require_once 'Piece/Unity/Plugin/Factory.php';
require_once 'Piece/Unity/Error.php';

// {{{ Piece_Unity_Plugin_KernelConfigurator

/**
 * @package    Piece_Unity
 * @author     KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @copyright  2006 KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    Release: @package_version@
 * @link       http://piece-framework.com/piece-unity/
 * @since      Class available since Release 0.1.0
 */
class Piece_Unity_Plugin_KernelConfigurator extends Piece_Unity_Plugin_Common
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
    // {{{ invoke()

    /**
     * Invokes the plugin specific code.
     */
    function invoke()
    {
        $this->_context->setEventNameKey($this->getConfiguration('eventNameKey'));

        $autoloadClasses = $this->getConfiguration('autoloadClasses');
        if (!is_array($autoloadClasses)) {
            Piece_Unity_Error::pushCallback(create_function('$error', 'return ' . PEAR_ERRORSTACK_PUSHANDLOG . ';'));
            Piece_Unity_Error::push(PIECE_UNITY_ERROR_INVALID_CONFIGURATION,
                                    'Failed to configure the configuration point [ autoloadClasses ] at the plugin [ ' . __CLASS__ . ' ].',
                                    'warning',
                                    array('plugin' => __CLASS__)
                                    );
            Piece_Unity_Error::popCallback();
            $autoloadClasses = array();
        }

        $autoloadClasses[] = 'Piece_Flow_Continuation';
        $autoloadClasses[] = 'Piece_Right_Results';
        foreach ($autoloadClasses as $autoloadClass) {
            Piece_Unity_Session::addAutoloadClass($autoloadClass);
        }

        $eventName = $this->getConfiguration('eventName');
        if (!is_null($eventName)) {
            $this->_context->setEventName($eventName);
        }

        $importPathInfo = $this->getConfiguration('importPathInfo');
        if ($importPathInfo) {
            $request = &$this->_context->getRequest();
            $request->importPathInfo();
        }

        $pluginDirectories = $this->getConfiguration('pluginDirectories');
        if (!is_array($pluginDirectories)) {
            Piece_Unity_Error::pushCallback(create_function('$error', 'return ' . PEAR_ERRORSTACK_PUSHANDLOG . ';'));
            Piece_Unity_Error::push(PIECE_UNITY_ERROR_INVALID_CONFIGURATION,
                                    'Failed to configure the configuration point [ pluginDirectories ] at the plugin [ ' . __CLASS__ . ' ].',
                                    'warning',
                                    array('plugin' => __CLASS__)
                                    );
            Piece_Unity_Error::popCallback();
            return;
        }

        foreach (array_reverse($pluginDirectories) as $pluginDirectory) {
            Piece_Unity_Plugin_Factory::addPluginDirectory($pluginDirectory);
        }

        $this->_context->setProxyPath($this->getConfiguration('proxyPath'));

        $validation = &$this->_context->getValidation();
        $validation->setConfigDirectory($this->getConfiguration('validationConfigDirectory'));
        $validation->setCacheDirectory($this->getConfiguration('validationCacheDirectory'));
    }

    /**#@-*/

    /**#@+
     * @access private
     */

    // }}}
    // {{{ _initialize()

    /**
     * Defines and initializes extension points and configuration points.
     *
     * @since Method available since Release 0.6.0
     */
    function _initialize()
    {
        $this->_addConfigurationPoint('eventNameKey', '_event');
        $this->_addConfigurationPoint('autoloadClasses', array());
        $this->_addConfigurationPoint('eventName');
        $this->_addConfigurationPoint('importPathInfo', false);
        $this->_addConfigurationPoint('pluginDirectories', array());
        $this->_addConfigurationPoint('proxyPath');
        $this->_addConfigurationPoint('validationConfigDirectory');
        $this->_addConfigurationPoint('validationCacheDirectory');
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
