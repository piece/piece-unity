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

require_once 'Piece/Unity/Plugin/Common.php';
require_once 'Piece/Unity/Session.php';
require_once 'Piece/Unity/Plugin/Factory.php';
require_once 'Piece/Unity/Error.php';
require_once 'Piece/Unity/Validation.php';
require_once 'Piece/Unity/URL.php';

// {{{ Piece_Unity_Plugin_KernelConfigurator

/**
 * A configurator which can be used to set appropriate values to Piece_Unity
 * Kernel Classes.
 *
 * @package    Piece_Unity
 * @author     KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @copyright  2006-2007 KUBO Atsuhiro <iteman@users.sourceforge.net>
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
        $this->_context->setProxyPath($this->getConfiguration('proxyPath'));

        /*
         * Preloads Dispatcher_Continuation plug-in for restoring
         * action instances in session.
         */
        Piece_Unity_Plugin_Factory::factory('Dispatcher_Continuation');

        $this->_setAutoloadClasses();
        $this->_setEventName();
        $this->_importPathInfo();
        $this->_setPluginDirectories();
        $this->_configureValidation();
        $this->_setNonSSLableServers();
        $this->_setPluginPrefixes();
        $this->_setValidatorPrefixes();
        $this->_setFilterPrefixes();
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
        $this->_addConfigurationPoint('validationValidatorDirectories', array());
        $this->_addConfigurationPoint('validationFilterDirectories', array());
        $this->_addConfigurationPoint('nonSSLableServers', array());
        $this->_addConfigurationPoint('pluginPrefixes', array());
        $this->_addConfigurationPoint('validationValidatorPrefixes', array());
        $this->_addConfigurationPoint('validationFilterPrefixes', array());
    }

    // }}}
    // {{{ _setPluginPrefixes()

    /**
     * Sets plug-in prefixes.
     *
     * @since Method available since Release 0.11.0
     */
    function _setPluginPrefixes()
    {
        $pluginPrefixes = $this->getConfiguration('pluginPrefixes');
        if (!is_array($pluginPrefixes)) {
            Piece_Unity_Error::pushCallback(create_function('$error', 'return ' . PEAR_ERRORSTACK_PUSHANDLOG . ';'));
            Piece_Unity_Error::push(PIECE_UNITY_ERROR_INVALID_CONFIGURATION,
                                    'Failed to configure the configuration point [ pluginPrefixes ] at the plugin [ ' . __CLASS__ . ' ].',
                                    'warning',
                                    array('plugin' => __CLASS__)
                                    );
            Piece_Unity_Error::popCallback();
            return;
        }

        foreach (array_reverse($pluginPrefixes) as $pluginPrefix) {
            Piece_Unity_Plugin_Factory::addPluginPrefix($pluginPrefix);
        }
    }

    // }}}
    // {{{ _setAutoloadClasses()

    /**
     * Sets autoload classes.
     *
     * @since Method available since Release 0.11.0
     */
    function _setAutoloadClasses()
    {
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
    }

    // }}}
    // {{{ _setEventName()

    /**
     * Sets an event name for the current request.
     *
     * @since Method available since Release 0.11.0
     */
    function _setEventName()
    {
        $eventName = $this->getConfiguration('eventName');
        if (!is_null($eventName)) {
            $this->_context->setEventName($eventName);
        }
    }

    // }}}
    // {{{ _importPathInfo()

    /**
     * Imports PATH_INFO string as parameters.
     *
     * @since Method available since Release 0.11.0
     */
    function _importPathInfo()
    {
        if ($this->getConfiguration('importPathInfo')) {
            $request = &$this->_context->getRequest();
            $request->importPathInfo();
        }
    }

    // }}}
    // {{{ _setPluginDirectories()

    /**
     * Sets plug-in directories.
     *
     * @since Method available since Release 0.11.0
     */
    function _setPluginDirectories()
    {
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
    }

    // }}}
    // {{{ _configureValidation()

    /**
     * Configures some validatoin stuff.
     *
     * @since Method available since Release 0.11.0
     */
    function _configureValidation()
    {
        $validation = &$this->_context->getValidation();
        $validation->setConfigDirectory($this->getConfiguration('validationConfigDirectory'));
        $validation->setCacheDirectory($this->getConfiguration('validationCacheDirectory'));

        $this->_setValidatorDirectories();
        $this->_setFilterDirectories();
    }

    // }}}
    // {{{ _setValidatorDirectories()

    /**
     * Sets validator directories.
     *
     * @since Method available since Release 0.11.0
     */
    function _setValidatorDirectories()
    {
        $validatorDirectories = $this->getConfiguration('validationValidatorDirectories');
        if (!is_array($validatorDirectories)) {
            Piece_Unity_Error::pushCallback(create_function('$error', 'return ' . PEAR_ERRORSTACK_PUSHANDLOG . ';'));
            Piece_Unity_Error::push(PIECE_UNITY_ERROR_INVALID_CONFIGURATION,
                                    'Failed to configure the configuration point [ validationValidatorDirectories ] at the plugin [ ' . __CLASS__ . ' ].',
                                    'warning',
                                    array('plugin' => __CLASS__)
                                    );
            Piece_Unity_Error::popCallback();
            return;
        }

        foreach (array_reverse($validatorDirectories) as $validatorDirectory) {
            Piece_Unity_Validation::addValidatorDirectory($validatorDirectory);
        }
    }

    // }}}
    // {{{ _setFilterDirectories()

    /**
     * Sets filter directories.
     *
     * @since Method available since Release 0.11.0
     */
    function _setFilterDirectories()
    {
        $filterDirectories = $this->getConfiguration('validationFilterDirectories');
        if (!is_array($filterDirectories)) {
            Piece_Unity_Error::pushCallback(create_function('$error', 'return ' . PEAR_ERRORSTACK_PUSHANDLOG . ';'));
            Piece_Unity_Error::push(PIECE_UNITY_ERROR_INVALID_CONFIGURATION,
                                    'Failed to configure the configuration point [ validationFilterDirectories ] at the plugin [ ' . __CLASS__ . ' ].',
                                    'warning',
                                    array('plugin' => __CLASS__)
                                    );
            Piece_Unity_Error::popCallback();
            return;
        }

        foreach (array_reverse($filterDirectories) as $filterDirectory) {
            Piece_Unity_Validation::addFilterDirectory($filterDirectory);
        }
    }

    // }}}
    // {{{ _setNonSSLableServers()

    /**
     * Makes a list of non-SSLable servers.
     *
     * @since Method available since Release 0.11.0
     */
    function _setNonSSLableServers()
    {
        $nonSSLableServers = $this->getConfiguration('nonSSLableServers');
        if (!is_array($nonSSLableServers)) {
            Piece_Unity_Error::pushCallback(create_function('$error', 'return ' . PEAR_ERRORSTACK_PUSHANDLOG . ';'));
            Piece_Unity_Error::push(PIECE_UNITY_ERROR_INVALID_CONFIGURATION,
                                    'Failed to configure the configuration point [ nonSSLableServers ] at the plugin [ ' . __CLASS__ . ' ].',
                                    'warning',
                                    array('plugin' => __CLASS__)
                                    );
            Piece_Unity_Error::popCallback();
            return;
        }

        foreach ($nonSSLableServers as $nonSSLableServer) {
            Piece_Unity_URL::addNonSSLableServer($nonSSLableServer);
        }
    }

    // }}}
    // {{{ _setValidatorPrefixes()

    /**
     * Sets validator prefixes.
     *
     * @since Method available since Release 0.11.0
     */
    function _setValidatorPrefixes()
    {
        $validatorPrefixes = $this->getConfiguration('validationValidatorPrefixes');
        if (!is_array($validatorPrefixes)) {
            Piece_Unity_Error::pushCallback(create_function('$error', 'return ' . PEAR_ERRORSTACK_PUSHANDLOG . ';'));
            Piece_Unity_Error::push(PIECE_UNITY_ERROR_INVALID_CONFIGURATION,
                                    'Failed to configure the configuration point [ validationValidatorPrefixes ] at the plugin [ ' . __CLASS__ . ' ].',
                                    'warning',
                                    array('plugin' => __CLASS__)
                                    );
            Piece_Unity_Error::popCallback();
            return;
        }

        foreach (array_reverse($validatorPrefixes) as $validatorPrefix) {
            Piece_Unity_Validation::addValidatorPrefix($validatorPrefix);
        }
    }

    // }}}
    // {{{ _setFilterPrefixes()

    /**
     * Sets filter prefixes.
     *
     * @since Method available since Release 0.11.0
     */
    function _setFilterPrefixes()
    {
        $filterPrefixes = $this->getConfiguration('validationFilterPrefixes');
        if (!is_array($filterPrefixes)) {
            Piece_Unity_Error::pushCallback(create_function('$error', 'return ' . PEAR_ERRORSTACK_PUSHANDLOG . ';'));
            Piece_Unity_Error::push(PIECE_UNITY_ERROR_INVALID_CONFIGURATION,
                                    'Failed to configure the configuration point [ validationFilterPrefixes ] at the plugin [ ' . __CLASS__ . ' ].',
                                    'warning',
                                    array('plugin' => __CLASS__)
                                    );
            Piece_Unity_Error::popCallback();
            return;
        }

        foreach (array_reverse($filterPrefixes) as $filterPrefix) {
            Piece_Unity_Validation::addFilterPrefix($filterPrefix);
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
