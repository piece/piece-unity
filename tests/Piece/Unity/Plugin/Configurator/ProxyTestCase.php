<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * PHP versions 4 and 5
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
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    GIT: $Id$
 * @since      File available since Release 0.12.0
 */

require_once realpath(dirname(__FILE__) . '/../../../../prepare.php');
require_once 'PHPUnit.php';
require_once 'Piece/Unity/Plugin/Configurator/Proxy.php';
require_once 'Piece/Unity/Config.php';
require_once 'Piece/Unity/Context.php';
require_once 'Piece/Unity/Error.php';

// {{{ Piece_Unity_Plugin_Configurator_ProxyTestCase

/**
 * Some tests for Piece_Unity_Plugin_Configurator_Proxy.
 *
 * @package    Piece_Unity
 * @copyright  2007-2009 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    Release: @package_version@
 * @since      Class available since Release 0.12.0
 */
class Piece_Unity_Plugin_Configurator_ProxyTestCase extends PHPUnit_TestCase
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

    function tearDown()
    {
        Piece_Unity_Context::clear();
        Piece_Unity_Error::clearErrors();
    }

    function testSetProxyPath()
    {
        $config = &new Piece_Unity_Config();
        $config->setConfiguration('Configurator_Proxy', 'proxyPath', '/foo/bar');
        $context = &Piece_Unity_Context::singleton();
        $context->setConfiguration($config);

        $configurator = &new Piece_Unity_Plugin_Configurator_Proxy();
        $configurator->invoke();

        $this->assertEquals('/foo/bar', $context->getProxyPath());
    }

    function testProxy()
    {
        $previousScriptName = $_SERVER['REQUEST_URI'];
        $_SERVER['REQUEST_URI'] = '/baz/qux.php';
        $_SERVER['HTTP_X_FORWARDED_FOR'] = '1.2.3.4';
        $previousSessionCookiePath = ini_get('session.cookie_path');
        ini_set('session.cookie_path', '/baz');

        $config = &new Piece_Unity_Config();
        $config->setConfiguration('Configurator_Proxy', 'proxyPath', '/bar');
        $context = &Piece_Unity_Context::singleton();
        $context->setConfiguration($config);
        $context->setAppRootPath('/foo');

        $interceptor = &new Piece_Unity_Plugin_Configurator_Proxy();
        $interceptor->invoke();

        $this->assertEquals('/bar/baz', $context->getBasePath());
        $this->assertEquals('/bar/baz/qux.php', $context->getScriptName());
        $this->assertEquals('/bar/baz', ini_get('session.cookie_path'));
        $this->assertEquals('/bar/foo', $context->getAppRootPath());

        ini_set('session.cookie_path', $previousSessionCookiePath);
        unset($_SERVER['HTTP_X_FORWARDED_FOR']);
        $_SERVER['REQUEST_URI'] = $previousScriptName;
    }

    function testNonProxy()
    {
        $previousScriptName = $_SERVER['REQUEST_URI'];
        $_SERVER['REQUEST_URI'] = '/baz/qux.php';

        $config = &new Piece_Unity_Config();
        $config->setConfiguration('Configurator_Proxy', 'proxyPath', '/bar');
        $context = &Piece_Unity_Context::singleton();
        $context->setConfiguration($config);
        $context->setAppRootPath('/foo');

        $interceptor = &new Piece_Unity_Plugin_Configurator_Proxy();
        $interceptor->invoke();

        $this->assertEquals('/baz', $context->getBasePath());
        $this->assertEquals('/baz/qux.php', $context->getScriptName());
        $this->assertEquals('/foo', $context->getAppRootPath());

        $_SERVER['REQUEST_URI'] = $previousScriptName;
    }

    function testAdjustingSessionCookiePathToOff()
    {
        $_SERVER['HTTP_X_FORWARDED_FOR'] = '1.2.3.4';
        $previousSessionCookiePath = ini_get('session.cookie_path');
        ini_set('session.cookie_path', '/bar');

        $config = &new Piece_Unity_Config();
        $config->setConfiguration('Configurator_Proxy', 'proxyPath', '/foo');
        $config->setConfiguration('Configurator_Proxy', 'adjustSessionCookiePath', false);
        $context = &Piece_Unity_Context::singleton();
        $context->setConfiguration($config);

        $interceptor = &new Piece_Unity_Plugin_Configurator_Proxy();
        $interceptor->invoke();

        $this->assertEquals('/bar', ini_get('session.cookie_path'));

        ini_set('session.cookie_path', $previousSessionCookiePath);
        unset($_SERVER['HTTP_X_FORWARDED_FOR']);
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
