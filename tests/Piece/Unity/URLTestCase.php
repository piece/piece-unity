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
 * @version    SVN: $Id$
 * @since      File available since Release 0.9.0
 */

require_once realpath(dirname(__FILE__) . '/../../prepare.php');
require_once 'PHPUnit.php';
require_once 'Piece/Unity/URL.php';
require_once 'Piece/Unity/Context.php';
require_once 'Piece/Unity/Error.php';
require_once 'PEAR/ErrorStack.php';

// {{{ Piece_Unity_URLTestCase

/**
 * Some tests for Piece_Unity_URL.
 *
 * @package    Piece_Unity
 * @copyright  2006-2008 KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    Release: @package_version@
 * @since      Class available since Release 0.9.0
 */
class Piece_Unity_URLTestCase extends PHPUnit_TestCase
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

    function setUp()
    {
        PEAR_ErrorStack::setDefaultCallback(create_function('$error', 'var_dump($error); return ' . PEAR_ERRORSTACK_DIE . ';'));
    }

    function tearDown()
    {
        Piece_Unity_Context::clear();
        Piece_Unity_Error::clearErrors();
    }

    function testInternalURLWithAbsolutePath()
    {
        $_SERVER['SERVER_NAME'] = 'example.org';
        $_SERVER['SERVER_PORT'] = '80';
        $url = &new Piece_Unity_URL('http://example.com/foo/bar/baz.php', false);

        $this->assertEquals('https://example.org/foo/bar/baz.php', $url->getURL(true));
        $this->assertEquals('http://example.org/foo/bar/baz.php', $url->getURL(false));
        $this->assertEquals('http://example.org/foo/bar/baz.php', $url->getURL());

        $_SERVER['SERVER_PORT'] = '443';
        $url = &new Piece_Unity_URL('http://example.com/foo/bar/baz.php', false);

        $this->assertEquals('https://example.org/foo/bar/baz.php', $url->getURL(true));
        $this->assertEquals('http://example.org/foo/bar/baz.php', $url->getURL(false));
        $this->assertEquals('http://example.org/foo/bar/baz.php', $url->getURL());

        unset($_SERVER['SERVER_PORT']);
        unset($_SERVER['SERVER_NAME']);
    }

    function testInternalURLWithRelativePath()
    {
        $_SERVER['SERVER_NAME'] = 'example.org';
        $_SERVER['SERVER_PORT'] = '80';
        $url = &new Piece_Unity_URL('/foo/bar/baz.php', false);

        $this->assertEquals('https://example.org/foo/bar/baz.php', $url->getURL(true));
        $this->assertEquals('http://example.org/foo/bar/baz.php', $url->getURL(false));
        $this->assertEquals('http://example.org/foo/bar/baz.php', $url->getURL());

        $_SERVER['SERVER_PORT'] = '443';
        $url = &new Piece_Unity_URL('/foo/bar/baz.php', false);

        $this->assertEquals('https://example.org/foo/bar/baz.php', $url->getURL(true));
        $this->assertEquals('http://example.org/foo/bar/baz.php', $url->getURL(false));
        $this->assertEquals('http://example.org/foo/bar/baz.php', $url->getURL());

        unset($_SERVER['SERVER_PORT']);
        unset($_SERVER['SERVER_NAME']);
    }

    function testInternalURLWithProxy()
    {
        $_SERVER['HTTP_X_FORWARDED_FOR'] = '1.2.3.4';
        $_SERVER['HTTP_X_FORWARDED_SERVER'] = 'example.org';
        $_SERVER['SERVER_NAME'] = 'foo.example.org';
        $_SERVER['SERVER_PORT'] = '8201';
        $context = &Piece_Unity_Context::singleton();
        $context->setProxyPath('/foo');
        $url = &new Piece_Unity_URL('http://example.com/foo/bar/baz.php', false);

        $this->assertEquals('https://example.org/foo/bar/baz.php', $url->getURL(true));
        $this->assertEquals('http://example.org/foo/bar/baz.php', $url->getURL(false));
        $this->assertEquals('http://example.org/foo/bar/baz.php', $url->getURL());

        $url = &new Piece_Unity_URL('https://example.com/foo/bar/baz.php', false);

        $this->assertEquals('https://example.org/foo/bar/baz.php', $url->getURL(true));
        $this->assertEquals('http://example.org/foo/bar/baz.php', $url->getURL(false));
        $this->assertEquals('http://example.org/foo/bar/baz.php', $url->getURL());

        unset($_SERVER['SERVER_PORT']);
        unset($_SERVER['SERVER_NAME']);
        unset($_SERVER['HTTP_X_FORWARDED_FOR']);
        unset($_SERVER['HTTP_X_FORWARDED_SERVER']);
    }

    function testInternalURLWithDirectAccessToBackendServer()
    {
        $_SERVER['SERVER_NAME'] = 'foo.example.org';
        $_SERVER['SERVER_PORT'] = '8201';
        $context = &Piece_Unity_Context::singleton();
        $context->setProxyPath('/foo');

        $url = &new Piece_Unity_URL('http://example.com/foo/bar/baz.php', false);

        $this->assertEquals('http://foo.example.org:8201/bar/baz.php', $url->getURL(true));
        $this->assertEquals('http://foo.example.org:8201/bar/baz.php', $url->getURL(false));
        $this->assertEquals('http://foo.example.org:8201/bar/baz.php', $url->getURL());

        $url = &new Piece_Unity_URL('https://example.com/foo/bar/baz.php', false);

        $this->assertEquals('http://foo.example.org:8201/bar/baz.php', $url->getURL(true));
        $this->assertEquals('http://foo.example.org:8201/bar/baz.php', $url->getURL(false));
        $this->assertEquals('http://foo.example.org:8201/bar/baz.php', $url->getURL());

        unset($_SERVER['SERVER_PORT']);
        unset($_SERVER['SERVER_NAME']);
    }

    function testCreateDirectly()
    {
        $_SERVER['SERVER_NAME'] = 'example.org';
        $_SERVER['SERVER_PORT'] = '80';

        $this->assertEquals('https://example.org/foo/bar/baz.php',
                            Piece_Unity_URL::createSSL('http://example.com/foo/bar/baz.php')
                            );
        $this->assertEquals('https://example.org/foo/bar/baz.php',
                            Piece_Unity_URL::createSSL('/foo/bar/baz.php')
                            );
        $this->assertEquals('http://example.org/foo/bar/baz.php',
                            Piece_Unity_URL::create('http://example.com/foo/bar/baz.php')
                            );
        $this->assertEquals('http://example.org/foo/bar/baz.php',
                            Piece_Unity_URL::create('/foo/bar/baz.php')
                            );

        $url = &new Piece_Unity_URL();

        $this->assertEquals('https://example.org/foo/bar/baz.php',
                            $url->createSSL('http://example.com/foo/bar/baz.php')
                            );

        unset($_SERVER['SERVER_PORT']);
        unset($_SERVER['SERVER_NAME']);
    }

    function testInvalidOperations()
    {
        $url = &new Piece_Unity_URL();
        Piece_Unity_Error::disableCallback();
        $url->getQueryString();
        Piece_Unity_Error::enableCallback();

        $this->assertTrue(Piece_Unity_Error::hasErrors());

        $error = Piece_Unity_Error::pop();

        $this->assertEquals(PIECE_UNITY_ERROR_INVALID_OPERATION, $error['code']);

        Piece_Unity_Error::disableCallback();
        $url->addQueryString('foo', 'bar');
        Piece_Unity_Error::enableCallback();

        $error = Piece_Unity_Error::pop();

        $this->assertEquals(PIECE_UNITY_ERROR_INVALID_OPERATION, $error['code']);

        Piece_Unity_Error::disableCallback();
        $url->getURL();
        Piece_Unity_Error::enableCallback();

        $error = Piece_Unity_Error::pop();

        $this->assertEquals(PIECE_UNITY_ERROR_INVALID_OPERATION, $error['code']);

        $error = Piece_Unity_Error::pop();

        $this->assertNull($error);
    }

    function testNonSSLableServers()
    {
        $_SERVER['SERVER_NAME'] = 'example.org';
        $_SERVER['SERVER_PORT'] = '80';
        Piece_Unity_URL::addNonSSLableServer('example.org');

        $this->assertEquals('http://example.org/foo/bar/baz.php',
                            Piece_Unity_URL::createSSL('http://example.com/foo/bar/baz.php')
                            );
        $this->assertEquals('http://example.org/foo/bar/baz.php',
                            Piece_Unity_URL::createSSL('/foo/bar/baz.php')
                            );

        $_SERVER['SERVER_PORT'] = '443';

        $this->assertEquals('http://example.org/foo/bar/baz.php',
                            Piece_Unity_URL::create('https://example.com/foo/bar/baz.php')
                            );
        $this->assertEquals('http://example.org/foo/bar/baz.php',
                            Piece_Unity_URL::create('/foo/bar/baz.php')
                            );

        Piece_Unity_URL::clearNonSSLableServers();
        unset($_SERVER['SERVER_PORT']);
        unset($_SERVER['SERVER_NAME']);
    }

    function testNonSSLableServersWithProxy()
    {
        $_SERVER['HTTP_X_FORWARDED_FOR'] = '1.2.3.4';
        $_SERVER['HTTP_X_FORWARDED_SERVER'] = 'example.org';
        $_SERVER['SERVER_NAME'] = 'foo.example.org';
        $_SERVER['SERVER_PORT'] = '8201';
        $context = &Piece_Unity_Context::singleton();
        $context->setProxyPath('/foo');
        Piece_Unity_URL::addNonSSLableServer('example.org');

        $this->assertEquals('http://example.org/foo/bar/baz.php',
                            Piece_Unity_URL::createSSL('http://example.com/foo/bar/baz.php')
                            );
        $this->assertEquals('http://example.org/foo/bar/baz.php',
                            Piece_Unity_URL::createSSL('/foo/bar/baz.php')
                            );
        $this->assertEquals('http://example.org/foo/bar/baz.php',
                            Piece_Unity_URL::create('https://example.com/foo/bar/baz.php')
                            );
        $this->assertEquals('http://example.org/foo/bar/baz.php',
                            Piece_Unity_URL::create('/foo/bar/baz.php')
                            );

        Piece_Unity_URL::clearNonSSLableServers();
        unset($_SERVER['SERVER_PORT']);
        unset($_SERVER['SERVER_NAME']);
        unset($_SERVER['HTTP_X_FORWARDED_FOR']);
        unset($_SERVER['HTTP_X_FORWARDED_SERVER']);
    }

    /**
     * @since Method available since Release 0.11.0
     */
    function testCannotRedirectWithEZwebMobilePhone()
    {
        $_SERVER['HTTP_VIA'] = '1.2.3.4';
        $_SERVER['SERVER_NAME'] = 'example.org';
        $_SERVER['SERVER_PORT'] = '80';

        $this->assertEquals('http://example.org/foo/bar/baz.php',
                            Piece_Unity_URL::create('http://example.com/foo/bar/baz.php')
                            );

        unset($_SERVER['SERVER_PORT']);
        unset($_SERVER['SERVER_NAME']);
        unset($_SERVER['HTTP_VIA']);
    }

    /**
     * @since Method available since Release 1.2.0
     */
    function testDomainStringShouldNotBeReplacedIfExternalURLIsGiven()
    {
        $url = &new Piece_Unity_URL('http://example.org/foo/bar/baz.php', true);

        $this->assertEquals('http://example.org/foo/bar/baz.php', $url->getURL(false));
        $this->assertEquals('http://example.org/foo/bar/baz.php', $url->getURL(true));

        $url = &new Piece_Unity_URL('http://example.org:80/foo/bar/baz.php', true);

        $this->assertEquals('http://example.org/foo/bar/baz.php', $url->getURL(false));

        $url = &new Piece_Unity_URL('http://example.org:443/foo/bar/baz.php', true);

        $this->assertEquals('http://example.org:443/foo/bar/baz.php', $url->getURL(false));

        $url = &new Piece_Unity_URL('http://example.org:8201/foo/bar/baz.php', true);

        $this->assertEquals('http://example.org:8201/foo/bar/baz.php', $url->getURL(false));

        $url = &new Piece_Unity_URL('http://example.org:8202/foo/bar/baz.php', true);

        $this->assertEquals('http://example.org:8202/foo/bar/baz.php', $url->getURL(false));

        $url = &new Piece_Unity_URL('https://example.org/foo/bar/baz.php', true);

        $this->assertEquals('https://example.org/foo/bar/baz.php', $url->getURL(false));
        $this->assertEquals('https://example.org/foo/bar/baz.php', $url->getURL(true));
    }

    /**
     * @since Method available since Release 1.2.0
     */
    function testPathInfoInURLShouldBeKept()
    {
        $url = &new Piece_Unity_URL('http://example.org/foo.php/bar/baz', true);

        $this->assertEquals('http://example.org/foo.php/bar/baz', $url->getURL());
    }

    /**
     * @since Method available since Release 1.2.0
     */
    function testPathInfoInURLShouldNotBeEncodedAutomatically()
    {

        $url = &new Piece_Unity_URL('http://example.org/foo.php/bar/%E4%B9%85%E4%BF%9D%E6%95%A6%E5%95%93', true);

        $this->assertEquals('http://example.org/foo.php/bar/%E4%B9%85%E4%BF%9D%E6%95%A6%E5%95%93', $url->getURL());
    }

    /**
     * @since Method available since Release 1.2.0
     */
    function testQueryStringInURLShouldNotBeEncodedAutomatically()
    {
        $url = &new Piece_Unity_URL('http://example.org/foo.php?bar=%E4%B9%85%E4%BF%9D%E6%95%A6%E5%95%93', true);

        $this->assertEquals('http://example.org/foo.php?bar=%E4%B9%85%E4%BF%9D%E6%95%A6%E5%95%93', $url->getURL());
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
?>
