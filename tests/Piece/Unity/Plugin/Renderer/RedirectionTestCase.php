<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * PHP versions 4 and 5
 *
 * Copyright (c) 2006-2008 KUBO Atsuhiro <iteman@users.sourceforge.net>,
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
 * @copyright  2006-2008 KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    GIT: $Id$
 * @since      File available since Release 0.6.0
 */

require_once realpath(dirname(__FILE__) . '/../../../../prepare.php');
require_once 'PHPUnit.php';
require_once 'Piece/Unity/Context.php';
require_once 'Piece/Unity/Config.php';
require_once 'Piece/Unity/Error.php';
require_once 'Piece/Unity/Plugin/Factory.php';
require_once 'Piece/Unity/Plugin/Renderer/Redirection.php';

// {{{ Piece_Unity_Plugin_Renderer_RedirectionTestCase

/**
 * Some tests for Piece_Unity_Plugin_Renderer_Redirection.
 *
 * @package    Piece_Unity
 * @copyright  2006-2008 KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    Release: @package_version@
 * @since      Class available since Release 0.6.0
 */
class Piece_Unity_Plugin_Renderer_RedirectionTestCase extends PHPUnit_TestCase
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
    }

    function testRedirection()
    {
        $_SERVER['SERVER_NAME'] = 'example.org';
        $_SERVER['SERVER_PORT'] = '80';
        $context = &Piece_Unity_Context::singleton();
        $context->setView('http://example.org/foo.php');
        $config = &new Piece_Unity_Config();
        $context->setConfiguration($config);
        $redirection = &Piece_Unity_Plugin_Factory::factory('Renderer_Redirection');
        $redirection->invoke();

        $this->assertEquals('http://example.org/foo.php', $redirection->_uri);

        unset($_SERVER['SERVER_NAME']);
        unset($_SERVER['SERVER_PORT']);
    }

    function testRedirectionWithDirectAccessToBackendServer()
    {
        $_SERVER['SERVER_NAME'] = 'foo.example.org';
        $_SERVER['SERVER_PORT'] = '8201';
        $context = &Piece_Unity_Context::singleton();
        $context->setView('http://example.org/foo/bar.php');
        $context->setProxyPath('/foo');
        $config = &new Piece_Unity_Config();
        $context->setConfiguration($config);
        $redirection = &Piece_Unity_Plugin_Factory::factory('Renderer_Redirection');
        $redirection->invoke();

        $this->assertEquals('http://foo.example.org:8201/bar.php',
                            $redirection->_uri
                            );

        unset($_SERVER['SERVER_NAME']);
        unset($_SERVER['SERVER_PORT']);
    }

    function testRedirectionWithProxy()
    {
        $_SERVER['HTTP_X_FORWARDED_FOR'] = '1.2.3.4';
        $_SERVER['HTTP_X_FORWARDED_SERVER'] = 'example.org';
        $_SERVER['SERVER_NAME'] = 'foo.example.org';
        $_SERVER['SERVER_PORT'] = '8201';
        $context = &Piece_Unity_Context::singleton();
        $context->setView('http://example.org/foo/bar.php');
        $context->setProxyPath('/foo');
        $config = &new Piece_Unity_Config();
        $context->setConfiguration($config);
        $redirection = &Piece_Unity_Plugin_Factory::factory('Renderer_Redirection');
        $redirection->invoke();

        $this->assertEquals('http://foo.example.org:8201/bar.php',
                            $redirection->_uri
                            );

        unset($_SERVER['SERVER_NAME']);
        unset($_SERVER['SERVER_PORT']);
        unset($_SERVER['HTTP_X_FORWARDED_FOR']);
    }

    function testRedirectionWithDirectAccessToBackendServerWhenHTTPSProtocolIsGiven()
    {
        $_SERVER['SERVER_NAME'] = 'foo.example.org';
        $_SERVER['SERVER_PORT'] = '8201';
        $context = &Piece_Unity_Context::singleton();
        $context->setView('https://example.org/foo/bar.php');
        $context->setProxyPath('/foo');
        $config = &new Piece_Unity_Config();
        $context->setConfiguration($config);
        $redirection = &Piece_Unity_Plugin_Factory::factory('Renderer_Redirection');
        $redirection->invoke();

        $this->assertEquals('http://foo.example.org:8201/bar.php',
                            $redirection->_uri
                            );

        unset($_SERVER['SERVER_NAME']);
        unset($_SERVER['SERVER_PORT']);
    }

    function testRedirectionWithOtherProxy()
    {
        $_SERVER['HTTP_X_FORWARDED_FOR'] = '1.2.3.4';
        $_SERVER['HTTP_X_FORWARDED_SERVER'] = 'test.example.org';
        $_SERVER['SERVER_NAME'] = 'foo.example.org';
        $_SERVER['SERVER_PORT'] = '8201';
        $context = &Piece_Unity_Context::singleton();
        $context->setView('http://example.org/foo/bar.php');
        $context->setProxyPath('/foo');
        $config = &new Piece_Unity_Config();
        $context->setConfiguration($config);
        $redirection = &new Piece_Unity_Plugin_Renderer_Redirection();
        $redirection->invoke();

        $this->assertEquals('http://foo.example.org:8201/bar.php',
                            $redirection->_uri
                            );

        unset($_SERVER['SERVER_NAME']);
        unset($_SERVER['SERVER_PORT']);
        unset($_SERVER['HTTP_X_FORWARDED_FOR']);
    }

    /**
     * @since Method available since Release 0.11.0
     */
    function testReplaceBuiltinEventNameKeyWithEventNameKey()
    {
        $_SERVER['SERVER_NAME'] = 'example.org';
        $_SERVER['SERVER_PORT'] = '80';
        $context = &Piece_Unity_Context::singleton();
        $context->setView('http://example.org/foo.php?__eventNameKey=bar');
        $config = &new Piece_Unity_Config();
        $context->setConfiguration($config);
        $redirection = &Piece_Unity_Plugin_Factory::factory('Renderer_Redirection');
        $redirection->invoke();

        $this->assertEquals('http://example.org/foo.php?_event=bar',
                            $redirection->_uri
                            );

        unset($_SERVER['SERVER_NAME']);
        unset($_SERVER['SERVER_PORT']);
    }

    /**
     * @since Method available since Release 0.11.0
     */
    function testRedirectionToHTTPS()
    {
        $_SERVER['SERVER_NAME'] = 'example.org';
        $_SERVER['SERVER_PORT'] = '80';
        $context = &Piece_Unity_Context::singleton();
        $context->setView('https://example.org/foo.php');
        $config = &new Piece_Unity_Config();
        $context->setConfiguration($config);
        $redirection = &Piece_Unity_Plugin_Factory::factory('Renderer_Redirection');
        $redirection->invoke();

        $this->assertEquals('https://example.org/foo.php', $redirection->_uri);

        unset($_SERVER['SERVER_NAME']);
        unset($_SERVER['SERVER_PORT']);
    }

    /**
     * @since Method available since Release 1.5.0
     */
    function testShouldSupportSelfNotation()
    {
        $oldScriptName = $_SERVER['SCRIPT_NAME'];
        $_SERVER['SCRIPT_NAME'] = '/foo.php';
        $_SERVER['SERVER_NAME'] = 'example.org';
        $_SERVER['SERVER_PORT'] = '80';

        $context = &Piece_Unity_Context::singleton();
        $context->setView('self://__eventNameKey=goDisplayForm&bar=baz#zip');
        $config = &new Piece_Unity_Config();
        $context->setConfiguration($config);
        $redirection = &Piece_Unity_Plugin_Factory::factory('Renderer_Redirection');
        $redirection->invoke();

        $this->assertEquals('http://example.org/foo.php?__eventNameKey=goDisplayForm&bar=baz#zip', $context->getView());

        unset($_SERVER['SERVER_PORT']);
        unset($_SERVER['SERVER_NAME']);
        $_SERVER['SCRIPT_NAME'] = $oldScriptName;
    }

    /**
     * @since Method available since Release 1.5.0
     */
    function testShouldSupportSelfNotationForHTTPS()
    {
        $oldScriptName = $_SERVER['SCRIPT_NAME'];
        $_SERVER['SCRIPT_NAME'] = '/foo.php';
        $_SERVER['SERVER_NAME'] = 'example.org';
        $_SERVER['SERVER_PORT'] = '80';

        $context = &Piece_Unity_Context::singleton();
        $context->setView('selfs://__eventNameKey=goDisplayForm&bar=baz#zip');
        $config = &new Piece_Unity_Config();
        $context->setConfiguration($config);
        $redirection = &Piece_Unity_Plugin_Factory::factory('Renderer_Redirection');
        $redirection->invoke();

        $this->assertEquals('https://example.org/foo.php?__eventNameKey=goDisplayForm&bar=baz#zip', $context->getView());

        unset($_SERVER['SERVER_PORT']);
        unset($_SERVER['SERVER_NAME']);
        $_SERVER['SCRIPT_NAME'] = $oldScriptName;
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
