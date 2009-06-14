<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * PHP version 5
 *
 * Copyright (c) 2007-2009 KUBO Atsuhiro <kubo@iteman.jp>,
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
 * @copyright  2007-2009 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @since      File available since Release 0.12.0
 */

// {{{ Piece_Unity_Plugin_Configurator_ProxyTest

/**
 * Some tests for Piece_Unity_Plugin_Configurator_Proxy.
 *
 * @package    Piece_Unity
 * @copyright  2007-2009 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @since      Class available since Release 0.12.0
 */
class Piece_Unity_Plugin_Configurator_ProxyTest extends Piece_Unity_PHPUnit_TestCase
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

    /**#@-*/

    /**#@+
     * @access public
     */

    /**
     * @test
     */
    public function setTheProxyPath()
    {
        Piece_Unity_Context::singleton()->setConfiguration(new Piece_Config());
        $config = Piece_Unity_Context::singleton()->getConfiguration();
        $config->defineService('Piece_Unity_Plugin_Configurator_Proxy');
        $config->queueExtension('Piece_Unity_Plugin_Configurator_Proxy', 'proxyPath', '/foo/bar');
        $config->instantiateFeature('Piece_Unity_Plugin_Configurator_Proxy')
               ->configure();

        $this->assertEquals('/foo/bar', Piece_Unity_Context::singleton()->getProxyPath());
    }

    /**
     * @test
     */
    public function adjustTheProxyPathForAProxy()
    {
        $_SERVER['REQUEST_URI'] = '/baz/qux.php';
        $_SERVER['HTTP_X_FORWARDED_FOR'] = '1.2.3.4';
        $oldSessionCookiePath = ini_get('session.cookie_path');
        ini_set('session.cookie_path', '/baz');

        $context = Piece_Unity_Context::singleton();
        $context->setConfiguration(new Piece_Config());
        $context->setAppRootPath('/foo');
        $config = $context->getConfiguration();
        $config->defineService('Piece_Unity_Plugin_Configurator_Proxy');
        $config->queueExtension('Piece_Unity_Plugin_Configurator_Proxy', 'proxyPath', '/bar');
        $config->instantiateFeature('Piece_Unity_Plugin_Configurator_Proxy')
               ->configure();

        $this->assertEquals('/bar/baz', $context->getBasePath());
        $this->assertEquals('/bar/baz/qux.php', $context->getScriptName());
        $this->assertEquals('/bar/baz', ini_get('session.cookie_path'));
        $this->assertEquals('/bar/foo', $context->getAppRootPath());

        ini_set('session.cookie_path', $oldSessionCookiePath);
    }

    /**
     * @test
     */
    public function adjustTheProxyPathForDirectAccessToABackendServer()
    {
        $_SERVER['REQUEST_URI'] = '/baz/qux.php';

        $context = Piece_Unity_Context::singleton();
        $context->setConfiguration(new Piece_Config());
        $context->setAppRootPath('/foo');
        $config = $context->getConfiguration();
        $config->defineService('Piece_Unity_Plugin_Configurator_Proxy');
        $config->queueExtension('Piece_Unity_Plugin_Configurator_Proxy', 'proxyPath', '/bar');
        $config->instantiateFeature('Piece_Unity_Plugin_Configurator_Proxy')
               ->configure();

        $this->assertEquals('/baz', $context->getBasePath());
        $this->assertEquals('/baz/qux.php', $context->getScriptName());
        $this->assertEquals('/foo', $context->getAppRootPath());
    }

    /**
     * @test
     */
    public function notAdjustTheSessionCookiePathByConfiguration()
    {
        $oldSessionCookiePath = ini_get('session.cookie_path');
        ini_set('session.cookie_path', '/bar');

        Piece_Unity_Context::singleton()->setConfiguration(new Piece_Config());
        $config = Piece_Unity_Context::singleton()->getConfiguration();
        $config->defineService('Piece_Unity_Plugin_Configurator_Proxy');
        $config->queueExtension('Piece_Unity_Plugin_Configurator_Proxy', 'proxyPath', '/foo');
        $config->queueExtension('Piece_Unity_Plugin_Configurator_Proxy', 'adjustSessionCookiePath', false);
        $config->instantiateFeature('Piece_Unity_Plugin_Configurator_Proxy')
               ->configure();

        $this->assertEquals('/bar', ini_get('session.cookie_path'));

        ini_set('session.cookie_path', $oldSessionCookiePath);
    }

    /**#@-*/

    /**#@+
     * @access protected
     */

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
