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
 * @see        Piece_Unity_URL
 * @since      File available since Release 0.9.0
 */

require_once 'PHPUnit.php';
require_once 'Piece/Unity/URL.php';
require_once 'Piece/Unity/Context.php';

// {{{ Piece_Unity_URLTestCase

/**
 * TestCase for Piece_Unity_URL
 *
 * @package    Piece_Unity
 * @author     KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @copyright  2006 KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    Release: @package_version@
 * @link       http://piece-framework.com/piece-unity/
 * @see        Piece_Unity_URL
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

    function tearDown()
    {
        Piece_Unity_Context::clear();
    }

    function testExternalURL()
    {
        $url = &new Piece_Unity_URL('http://example.org/foo/bar/baz.php', true);

        $this->assertEquals('https://example.org/foo/bar/baz.php', $url->getURL(true));
        $this->assertEquals('http://example.org/foo/bar/baz.php', $url->getURL(false));
        $this->assertEquals('http://example.org/foo/bar/baz.php', $url->getURL());
    }

    function testInternalURLWithAbsolutePath()
    {
        $_SERVER['SERVER_NAME'] = 'example.org';
        $_SERVER['SERVER_PORT'] = '80';
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

        $this->assertEquals('http://foo.example.org:8201/bar/baz.php', $url->getURL(false));
        $this->assertEquals('http://foo.example.org:8201/bar/baz.php', $url->getURL());

        unset($_SERVER['SERVER_PORT']);
        unset($_SERVER['SERVER_NAME']);
    }

    function testCreateDirectly()
    {
        $this->assertEquals('https://example.org/foo/bar/baz.php',
                            Piece_Unity_URL::createURL('http://example.org/foo/bar/baz.php', true, true)
                            );

        $_SERVER['SERVER_NAME'] = 'example.org';
        $_SERVER['SERVER_PORT'] = '80';

        $this->assertEquals('https://example.org/foo/bar/baz.php',
                            Piece_Unity_URL::createURL('http://example.com/foo/bar/baz.php', false, true)
                            );

        $url = &new Piece_Unity_URL();

        $this->assertEquals('https://example.org/foo/bar/baz.php',
                            $url->createURL('http://example.com/foo/bar/baz.php', false, true)
                            );

        unset($_SERVER['SERVER_PORT']);
        unset($_SERVER['SERVER_NAME']);
    }

    function testInvalidOperations()
    {
        $url = &new Piece_Unity_URL();
        $url->getQueryString();
        $url->addQueryString('foo', 'bar');
        $url->getURL();

        $this->assertTrue(Piece_Unity_Error::hasErrors('warning'));

        $error = Piece_Unity_Error::pop();

        $this->assertEquals(PIECE_UNITY_ERROR_INVALID_OPERATION, $error['code']);

        $error = Piece_Unity_Error::pop();

        $this->assertEquals(PIECE_UNITY_ERROR_INVALID_OPERATION, $error['code']);

        $error = Piece_Unity_Error::pop();

        $this->assertEquals(PIECE_UNITY_ERROR_INVALID_OPERATION, $error['code']);

        $error = Piece_Unity_Error::pop();

        $this->assertNull($error);
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
