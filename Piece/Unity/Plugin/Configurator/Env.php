<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * PHP versions 4 and 5
 *
 * Copyright (c) 2007-2008 KUBO Atsuhiro <iteman@users.sourceforge.net>,
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
 * @copyright  2007-2008 KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    SVN: $Id$
 * @since      File available since Release 0.11.0
 */

require_once 'Piece/Unity/Plugin/Common.php';
require_once 'Piece/Unity/URI.php';
require_once 'Piece/Unity/Error.php';

// {{{ Piece_Unity_Plugin_Configurator_Env

/**
 * A configurator for env stuff.
 *
 * @package    Piece_Unity
 * @copyright  2007-2008 KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    Release: @package_version@
 * @since      Class available since Release 0.11.0
 */
class Piece_Unity_Plugin_Configurator_Env extends Piece_Unity_Plugin_Common
{

    // {{{ properties

    /**#@+
     * @access public
     */

    /**#@-*/

    /**#@+
     * @access private
     */

    var $_requiredEnvHandlers = array('Configurator_EnvHandler_PieceFlow',
                                      'Configurator_EnvHandler_PieceRight'
                                      );

    /**#@-*/

    /**#@+
     * @access public
     */

    // }}}
    // {{{ invoke()

    /**
     * Invokes the plugin specific code.
     *
     * @throws PIECE_UNITY_ERROR_INVALID_CONFIGURATION
     * @throws PIECE_UNITY_ERROR_NOT_FOUND
     * @throws PIECE_UNITY_ERROR_INVALID_PLUGIN
     * @throws PIECE_UNITY_ERROR_CANNOT_READ
     */
    function invoke()
    {
        $proxyPath = $this->_getConfiguration('proxyPath');
        if (!is_null($proxyPath)) {
            $this->_context->setProxyPath($proxyPath);
        }

        $this->_setNonSSLableServers();

        $envHandlers = &$this->_getExtension('envHandlers');
        if (!is_array($envHandlers)) {
            Piece_Unity_Error::push(PIECE_UNITY_ERROR_INVALID_CONFIGURATION,
                                    "The value of the extension point [ envHandlers ] on the plug-in [ {$this->_name} ] should be an array."
                                    );
            return;
        }

        foreach (array_merge($this->_requiredEnvHandlers, $envHandlers) as $extension) {
            $envHandler = &Piece_Unity_Plugin_Factory::factory($extension);
            if (Piece_Unity_Error::hasErrors()) {
                return;
            }

            $envHandler->invoke(Piece_Unity_Env::isProduction());
            if (Piece_Unity_Error::hasErrors()) {
                return;
            }
        }
    }

    /**#@-*/

    /**#@+
     * @access private
     */

    // }}}
    // {{{ _initialize()

    /**
     * Defines and initializes extension points and configuration points.
     */
    function _initialize()
    {
        $this->_addConfigurationPoint('proxyPath');
        $this->_addConfigurationPoint('nonSSLableServers', array());
        $this->_addExtensionPoint('envHandlers', array());
    }

    // }}}
    // {{{ _setNonSSLableServers()

    /**
     * Makes a list of non-SSLable servers.
     *
     * @throws PIECE_UNITY_ERROR_INVALID_CONFIGURATION
     */
    function _setNonSSLableServers()
    {
        $nonSSLableServers = $this->_getConfiguration('nonSSLableServers');
        if (!is_array($nonSSLableServers)) {
            Piece_Unity_Error::push(PIECE_UNITY_ERROR_INVALID_CONFIGURATION,
                                    "The value of the configuration point [ nonSSLableServers ] on the plug-in [ {$this->_name} ] should be an array."
                                    );
            return;
        }

        foreach ($nonSSLableServers as $nonSSLableServer) {
            Piece_Unity_URI::addNonSSLableServer($nonSSLableServer);
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
