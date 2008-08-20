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
require_once 'Piece/Unity/URI.php';
require_once 'Piece/Unity/Context.php';
require_once 'Piece/Unity/Error.php';

// {{{ Piece_Unity_URITestCase

/**
 * Some tests for Piece_Unity_URI.
 *
 * @package    Piece_Unity
 * @copyright  2006-2008 KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    Release: @package_version@
 * @since      Class available since Release 0.9.0
 */
class Piece_Unity_URITestCase extends PHPUnit_TestCase
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

    function testInternalURIWithAbsolutePath()
    {
        $_SERVER['SERVER_NAME'] = 'example.org';
        $_SERVER['SERVER_PORT'] = '80';
        $uri = &new Piece_Unity_URI('http://example.com/foo/bar/baz.php', false);

        $this->assertEquals('https://example.org/foo/bar/baz.php', $uri->getURI(true));
        $this->assertEquals('http://example.org/foo/bar/baz.php', $uri->getURI(false));
        $this->assertEquals('http://example.org/foo/bar/baz.php', $uri->getURI());

        $_SERVER['SERVER_PORT'] = '443';
        $uri = &new Piece_Unity_URI('http://example.com/foo/bar/baz.php', false);

        $this->assertEquals('https://example.org/foo/bar/baz.php', $uri->getURI(true));
        $this->assertEquals('http://example.org/foo/bar/baz.php', $uri->getURI(false));
        $this->assertEquals('http://example.org/foo/bar/baz.php', $uri->getURI());

        unset($_SERVER['SERVER_PORT']);
        unset($_SERVER['SERVER_NAME']);
    }

    function testInternalURIWithRelativePath()
    {
        $_SERVER['SERVER_NAME'] = 'example.org';
        $_SERVER['SERVER_PORT'] = '80';
        $uri = &new Piece_Unity_URI('/foo/bar/baz.php', false);

        $this->assertEquals('https://example.org/foo/bar/baz.php', $uri->getURI(true));
        $this->assertEquals('http://example.org/foo/bar/baz.php', $uri->getURI(false));
        $this->assertEquals('http://example.org/foo/bar/baz.php', $uri->getURI());

        $_SERVER['SERVER_PORT'] = '443';
        $uri = &new Piece_Unity_URI('/foo/bar/baz.php', false);

        $this->assertEquals('https://example.org/foo/bar/baz.php', $uri->getURI(true));
        $this->assertEquals('http://example.org/foo/bar/baz.php', $uri->getURI(false));
        $this->assertEquals('http://example.org/foo/bar/baz.php', $uri->getURI());

        unset($_SERVER['SERVER_PORT']);
        unset($_SERVER['SERVER_NAME']);
    }

    function testInternalURIWithProxy()
    {
        $_SERVER['HTTP_X_FORWARDED_FOR'] = '1.2.3.4';
        $_SERVER['HTTP_X_FORWARDED_SERVER'] = 'example.org';
        $_SERVER['SERVER_NAME'] = 'foo.example.org';
        $_SERVER['SERVER_PORT'] = '8201';
        $context = &Piece_Unity_Context::singleton();
        $context->setProxyPath('/foo');
        $uri = &new Piece_Unity_URI('http://example.com/foo/bar/baz.php', false);

        $this->assertEquals('https://example.org/foo/bar/baz.php', $uri->getURI(true));
        $this->assertEquals('http://example.org/foo/bar/baz.php', $uri->getURI(false));
        $this->assertEquals('http://example.org/foo/bar/baz.php', $uri->getURI());

        $uri = &new Piece_Unity_URI('https://example.com/foo/bar/baz.php', false);

        $this->assertEquals('https://example.org/foo/bar/baz.php', $uri->getURI(true));
        $this->assertEquals('http://example.org/foo/bar/baz.php', $uri->getURI(false));
        $this->assertEquals('http://example.org/foo/bar/baz.php', $uri->getURI());

        unset($_SERVER['SERVER_PORT']);
        unset($_SERVER['SERVER_NAME']);
        unset($_SERVER['HTTP_X_FORWARDED_FOR']);
        unset($_SERVER['HTTP_X_FORWARDED_SERVER']);
    }

    function testInternalURIWithDirectAccessToBackendServer()
    {
        $_SERVER['SERVER_NAME'] = 'foo.example.org';
        $_SERVER['SERVER_PORT'] = '8201';
        $context = &Piece_Unity_Context::singleton();
        $context->setProxyPath('/foo');

        $uri = &new Piece_Unity_URI('http://example.com/foo/bar/baz.php', false);

        $this->assertEquals('http://foo.example.org:8201/bar/baz.php', $uri->getURI(true));
        $this->assertEquals('http://foo.example.org:8201/bar/baz.php', $uri->getURI(false));
        $this->assertEquals('http://foo.example.org:8201/bar/baz.php', $uri->getURI());

        $uri = &new Piece_Unity_URI('https://example.com/foo/bar/baz.php', false);

        $this->assertEquals('http://foo.example.org:8201/bar/baz.php', $uri->getURI(true));
        $this->assertEquals('http://foo.example.org:8201/bar/baz.php', $uri->getURI(false));
        $this->assertEquals('http://foo.example.org:8201/bar/baz.php', $uri->getURI());

        unset($_SERVER['SERVER_PORT']);
        unset($_SERVER['SERVER_NAME']);
    }

    function testCreateDirectly()
    {
        $_SERVER['SERVER_NAME'] = 'example.org';
        $_SERVER['SERVER_PORT'] = '80';

        $this->assertEquals('https://example.org/foo/bar/baz.php',
                            Piece_Unity_URI::createSSL('http://example.com/foo/bar/baz.php')
                            );
        $this->assertEquals('https://example.org/foo/bar/baz.php',
                            Piece_Unity_URI::createSSL('/foo/bar/baz.php')
                            );
        $this->assertEquals('http://example.org/foo/bar/baz.php',
                            Piece_Unity_URI::create('http://example.com/foo/bar/baz.php')
                            );
        $this->assertEquals('http://example.org/foo/bar/baz.php',
                            Piece_Unity_URI::create('/foo/bar/baz.php')
                            );

        $uri = &new Piece_Unity_URI();

        $this->assertEquals('https://example.org/foo/bar/baz.php',
                            $uri->createSSL('http://example.com/foo/bar/baz.php')
                            );

        unset($_SERVER['SERVER_PORT']);
        unset($_SERVER['SERVER_NAME']);
    }

    function testInvalidOperations()
    {
        $uri = &new Piece_Unity_URI();
        Piece_Unity_Error::disableCallback();
        $uri->getQueryString();
        Piece_Unity_Error::enableCallback();

        $this->assertTrue(Piece_Unity_Error::hasErrors());

        $error = Piece_Unity_Error::pop();

        $this->assertEquals(PIECE_UNITY_ERROR_INVALID_OPERATION, $error['code']);

        Piece_Unity_Error::disableCallback();
        $uri->addQueryString('foo', 'bar');
        Piece_Unity_Error::enableCallback();

        $error = Piece_Unity_Error::pop();

        $this->assertEquals(PIECE_UNITY_ERROR_INVALID_OPERATION, $error['code']);

        Piece_Unity_Error::disableCallback();
        $uri->getURI();
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
        Piece_Unity_URI::addNonSSLableServer('example.org');

        $this->assertEquals('http://example.org/foo/bar/baz.php',
                            Piece_Unity_URI::createSSL('http://example.com/foo/bar/baz.php')
                            );
        $this->assertEquals('http://example.org/foo/bar/baz.php',
                            Piece_Unity_URI::createSSL('/foo/bar/baz.php')
                            );

        $_SERVER['SERVER_PORT'] = '443';

        $this->assertEquals('http://example.org/foo/bar/baz.php',
                            Piece_Unity_URI::create('https://example.com/foo/bar/baz.php')
                            );
        $this->assertEquals('http://example.org/foo/bar/baz.php',
                            Piece_Unity_URI::create('/foo/bar/baz.php')
                            );

        Piece_Unity_URI::clearNonSSLableServers();
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
        Piece_Unity_URI::addNonSSLableServer('example.org');

        $this->assertEquals('http://example.org/foo/bar/baz.php',
                            Piece_Unity_URI::createSSL('http://example.com/foo/bar/baz.php')
                            );
        $this->assertEquals('http://example.org/foo/bar/baz.php',
                            Piece_Unity_URI::createSSL('/foo/bar/baz.php')
                            );
        $this->assertEquals('http://example.org/foo/bar/baz.php',
                            Piece_Unity_URI::create('https://example.com/foo/bar/baz.php')
                            );
        $this->assertEquals('http://example.org/foo/bar/baz.php',
                            Piece_Unity_URI::create('/foo/bar/baz.php')
                            );

        Piece_Unity_URI::clearNonSSLableServers();
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
                            Piece_Unity_URI::create('http://example.com/foo/bar/baz.php')
                            );

        unset($_SERVER['SERVER_PORT']);
        unset($_SERVER['SERVER_NAME']);
        unset($_SERVER['HTTP_VIA']);
    }

    /**
     * @since Method available since Release 1.2.0
     */
    function testDomainStringShouldNotBeReplacedIfExternalURIIsGiven()
    {
        $uri = &new Piece_Unity_URI('http://example.org/foo/bar/baz.php', true);

        $this->assertEquals('http://example.org/foo/bar/baz.php', $uri->getURI(false));
        $this->assertEquals('http://example.org/foo/bar/baz.php', $uri->getURI(true));

        $uri = &new Piece_Unity_URI('http://example.org:80/foo/bar/baz.php', true);

        $this->assertEquals('http://example.org/foo/bar/baz.php', $uri->getURI(false));

        $uri = &new Piece_Unity_URI('http://example.org:443/foo/bar/baz.php', true);

        $this->assertEquals('http://example.org:443/foo/bar/baz.php', $uri->getURI(false));

        $uri = &new Piece_Unity_URI('http://example.org:8201/foo/bar/baz.php', true);

        $this->assertEquals('http://example.org:8201/foo/bar/baz.php', $uri->getURI(false));

        $uri = &new Piece_Unity_URI('http://example.org:8202/foo/bar/baz.php', true);

        $this->assertEquals('http://example.org:8202/foo/bar/baz.php', $uri->getURI(false));

        $uri = &new Piece_Unity_URI('https://example.org/foo/bar/baz.php', true);

        $this->assertEquals('https://example.org/foo/bar/baz.php', $uri->getURI(false));
        $this->assertEquals('https://example.org/foo/bar/baz.php', $uri->getURI(true));
    }

    /**
     * @since Method available since Release 1.2.0
     */
    function testPathInfoInURIShouldBeKept()
    {
        $uri = &new Piece_Unity_URI('http://example.org/foo.php/bar/baz', true);

        $this->assertEquals('http://example.org/foo.php/bar/baz', $uri->getURI());
    }

    /**
     * @since Method available since Release 1.2.0
     */
    function testPathInfoInURIShouldNotBeEncodedAutomatically()
    {

        $uri = &new Piece_Unity_URI('http://example.org/foo.php/bar/%E4%B9%85%E4%BF%9D%E6%95%A6%E5%95%93', true);

        $this->assertEquals('http://example.org/foo.php/bar/%E4%B9%85%E4%BF%9D%E6%95%A6%E5%95%93', $uri->getURI());
    }

    /**
     * @since Method available since Release 1.2.0
     */
    function testQueryStringInURIShouldNotBeEncodedAutomatically()
    {
        $uri = &new Piece_Unity_URI('http://example.org/foo.php?bar=%E4%B9%85%E4%BF%9D%E6%95%A6%E5%95%93', true);

        $this->assertEquals('http://example.org/foo.php?bar=%E4%B9%85%E4%BF%9D%E6%95%A6%E5%95%93', $uri->getURI());
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
