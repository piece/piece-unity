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
 * @since      File available since Release 0.9.0
 */

// {{{ Piece_Unity_URITest

/**
 * Some tests for Piece_Unity_URI.
 *
 * @package    Piece_Unity
 * @copyright  2006-2009 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @since      Class available since Release 0.9.0
 */
class Piece_Unity_URITest extends Piece_Unity_PHPUnit_TestCase
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
    public function buildAnInternalUriFromTheGivenAbsolutePath()
    {
        $_SERVER['SERVER_NAME'] = 'example.org';
        $_SERVER['SERVER_PORT'] = '80';
        $uri = new Piece_Unity_URI('http://example.com/foo/bar/baz.php');

        $this->assertEquals('https://example.org/foo/bar/baz.php', $uri->getURI('https'));
        $this->assertEquals('http://example.org/foo/bar/baz.php', $uri->getURI('http'));
        $this->assertEquals('http://example.org/foo/bar/baz.php', $uri->getURI());

        $_SERVER['SERVER_PORT'] = '443';
        $uri = new Piece_Unity_URI('http://example.com/foo/bar/baz.php');

        $this->assertEquals('https://example.org/foo/bar/baz.php', $uri->getURI('https'));
        $this->assertEquals('http://example.org/foo/bar/baz.php', $uri->getURI('http'));
        $this->assertEquals('http://example.org/foo/bar/baz.php', $uri->getURI());
    }

    /**
     * @test
     */
    public function buildAnInternalUriFromTheGivenRelativePath()
    {
        $_SERVER['SERVER_NAME'] = 'example.org';
        $_SERVER['SERVER_PORT'] = '80';
        $uri = new Piece_Unity_URI('/foo/bar/baz.php');

        $this->assertEquals('https://example.org/foo/bar/baz.php', $uri->getURI('https'));
        $this->assertEquals('http://example.org/foo/bar/baz.php', $uri->getURI('http'));
        $this->assertEquals('http://example.org/foo/bar/baz.php', $uri->getURI());

        $_SERVER['SERVER_PORT'] = '443';
        $_SERVER['HTTPS'] = 'on';
        $uri = new Piece_Unity_URI('/foo/bar/baz.php');

        $this->assertEquals('https://example.org/foo/bar/baz.php', $uri->getURI('https'));
        $this->assertEquals('http://example.org/foo/bar/baz.php', $uri->getURI('http'));
        $this->assertEquals('https://example.org/foo/bar/baz.php', $uri->getURI());
    }

    /**
     * @test
     */
    public function buildAnInternalUriForAProxy()
    {
        $_SERVER['HTTP_X_FORWARDED_FOR'] = '1.2.3.4';
        $_SERVER['HTTP_X_FORWARDED_SERVER'] = 'example.org';
        $_SERVER['SERVER_NAME'] = 'foo.example.org';
        $_SERVER['SERVER_PORT'] = '8201';
        $this->context->setProxyPath('/foo');
        $uri = new Piece_Unity_URI('http://example.com/foo/bar/baz.php');

        $this->assertEquals('https://example.org/foo/bar/baz.php', $uri->getURI('https'));
        $this->assertEquals('http://example.org/foo/bar/baz.php', $uri->getURI('http'));
        $this->assertEquals('http://example.org/foo/bar/baz.php', $uri->getURI());

        $uri = new Piece_Unity_URI('https://example.com/foo/bar/baz.php');

        $this->assertEquals('https://example.org/foo/bar/baz.php', $uri->getURI('https'));
        $this->assertEquals('http://example.org/foo/bar/baz.php', $uri->getURI('http'));
        $this->assertEquals('https://example.org/foo/bar/baz.php', $uri->getURI());
    }

    /**
     * @test
     */
    public function buildAnInternalUriForDirectAccessToABackendServer()
    {
        $_SERVER['SERVER_NAME'] = 'foo.example.org';
        $_SERVER['SERVER_PORT'] = '8201';
        $this->context->setProxyPath('/foo');

        $uri = new Piece_Unity_URI('http://example.com/foo/bar/baz.php');

        $this->assertEquals('https://foo.example.org:8201/bar/baz.php', $uri->getURI('https'));
        $this->assertEquals('http://foo.example.org:8201/bar/baz.php', $uri->getURI('http'));
        $this->assertEquals('http://foo.example.org:8201/bar/baz.php', $uri->getURI());

        $uri = new Piece_Unity_URI('https://example.com/foo/bar/baz.php');

        $this->assertEquals('https://foo.example.org:8201/bar/baz.php', $uri->getURI('https'));
        $this->assertEquals('http://foo.example.org:8201/bar/baz.php', $uri->getURI('http'));
        $this->assertEquals('https://foo.example.org:8201/bar/baz.php', $uri->getURI());
    }

    /**
     * @test
     */
    public function buildAnInternalUriDirectly()
    {
        $_SERVER['SERVER_NAME'] = 'example.org';
        $_SERVER['SERVER_PORT'] = '80';

        $this->assertEquals('https://example.org/foo/bar/baz.php',
                            Piece_Unity_URI::create('http://example.com/foo/bar/baz.php', 'https')
                            );
        $this->assertEquals('https://example.org/foo/bar/baz.php',
                            Piece_Unity_URI::create('/foo/bar/baz.php', 'https')
                            );
        $this->assertEquals('http://example.org/foo/bar/baz.php',
                            Piece_Unity_URI::create('http://example.com/foo/bar/baz.php')
                            );
        $this->assertEquals('http://example.org/foo/bar/baz.php',
                            Piece_Unity_URI::create('/foo/bar/baz.php')
                            );

        $uri = new Piece_Unity_URI();

        $this->assertEquals('https://example.org/foo/bar/baz.php',
                            $uri->create('http://example.com/foo/bar/baz.php', 'https')
                            );
    }

    /**
     * @test
     * @expectedException Piece_Unity_Exception
     */
    public function raiseAnExceptionWhenGetquerystringIsCalledBeforeInitializing()
    {
        $uri = new Piece_Unity_URI();
        $uri->getQueryString();
    }

    /**
     * @test
     * @expectedException Piece_Unity_Exception
     */
    public function raiseAnExceptionWhenAddquerystringIsCalledBeforeInitializing()
    {
        $uri = new Piece_Unity_URI();
        $uri->setQueryVariable('foo', 'bar');
    }

    /**
     * @test
     * @expectedException Piece_Unity_Exception
     */
    public function raiseAnExceptionWhenGeturiIsCalledBeforeInitializing()
    {
        $uri = new Piece_Unity_URI();
        $uri->getURI();
    }

    /**
     * @test
     * @since Method available since Release 0.11.0
     */
    public function buildAnInternalUrlForEzwebMobilePhones()
    {
        $_SERVER['HTTP_VIA'] = '1.2.3.4';
        $_SERVER['SERVER_NAME'] = 'example.org';
        $_SERVER['SERVER_PORT'] = '80';

        $this->assertEquals('http://example.org/foo/bar/baz.php',
                            Piece_Unity_URI::create('http://example.com/foo/bar/baz.php')
                            );
    }

    /**
     * @test
     * @since Method available since Release 1.2.0
     */
    public function buildAnExternalUri()
    {
        $uri = new Piece_Unity_URI('http://example.org/foo/bar/baz.php');
        $uri->setIsExternal(true);

        $this->assertEquals('http://example.org/foo/bar/baz.php', $uri->getURI(false));
        $this->assertEquals('http://example.org/foo/bar/baz.php', $uri->getURI(true));

        $uri = new Piece_Unity_URI('http://example.org:80/foo/bar/baz.php');
        $uri->setIsExternal(true);

        $this->assertEquals('http://example.org:80/foo/bar/baz.php', $uri->getURI(false));

        $uri = new Piece_Unity_URI('http://example.org:443/foo/bar/baz.php');
        $uri->setIsExternal(true);

        $this->assertEquals('http://example.org:443/foo/bar/baz.php', $uri->getURI(false));

        $uri = new Piece_Unity_URI('http://example.org:8201/foo/bar/baz.php');
        $uri->setIsExternal(true);

        $this->assertEquals('http://example.org:8201/foo/bar/baz.php', $uri->getURI(false));

        $uri = new Piece_Unity_URI('http://example.org:8202/foo/bar/baz.php');
        $uri->setIsExternal(true);

        $this->assertEquals('http://example.org:8202/foo/bar/baz.php', $uri->getURI(false));

        $uri = new Piece_Unity_URI('https://example.org/foo/bar/baz.php');
        $uri->setIsExternal(true);

        $this->assertEquals('https://example.org/foo/bar/baz.php', $uri->getURI(false));
        $this->assertEquals('https://example.org/foo/bar/baz.php', $uri->getURI(true));
    }

    /**
     * @test
     * @since Method available since Release 1.2.0
     */
    public function KeepPathinfoInTheUri()
    {
        $uri = new Piece_Unity_URI('http://example.org/foo.php/bar/baz');
        $uri->setIsExternal(true);

        $this->assertEquals('http://example.org/foo.php/bar/baz', $uri->getURI());
    }

    /**
     * @test
     * @since Method available since Release 1.2.0
     */
    public function NotEncodeThePathinfoInTheUriAutomatically()
    {
        $uri = new Piece_Unity_URI('http://example.org/foo.php/bar/%E4%B9%85%E4%BF%9D%E6%95%A6%E5%95%93');
        $uri->setIsExternal(true);

        $this->assertEquals('http://example.org/foo.php/bar/%E4%B9%85%E4%BF%9D%E6%95%A6%E5%95%93', $uri->getURI());
    }

    /**
     * @test
     * @since Method available since Release 1.2.0
     */
    public function NotEncodeTheQueryStringInTheUriAutomatically()
    {
        $uri = new Piece_Unity_URI('http://example.org/foo.php?bar=%E4%B9%85%E4%BF%9D%E6%95%A6%E5%95%93');
        $uri->setIsExternal(true);

        $this->assertEquals('http://example.org/foo.php?bar=%E4%B9%85%E4%BF%9D%E6%95%A6%E5%95%93', $uri->getURI());
    }

    /**
     * @test
     * @since Method available since Release 1.7.0
     */
    public function passTheGivenProtocolAsIs()
    {
        $_SERVER['SERVER_NAME'] = 'example.org';
        $_SERVER['SERVER_PORT'] = '80';
        $uri = new Piece_Unity_URI('https://example.com/foo/bar.php');

        $this->assertEquals('https://example.org/foo/bar.php', $uri->getURI('pass'));

        $_SERVER['SERVER_PORT'] = '443';
        $uri = new Piece_Unity_URI('https://example.com/foo/bar.php');

        $this->assertEquals('https://example.org/foo/bar.php', $uri->getURI('pass'));

        $_SERVER['SERVER_PORT'] = '8080';
        $uri = new Piece_Unity_URI('https://example.com/foo/bar.php');

        $this->assertEquals('https://example.org:8080/foo/bar.php', $uri->getURI('pass'));

        $_SERVER['SERVER_PORT'] = '8443';
        $uri = new Piece_Unity_URI('https://example.com/foo/bar.php');

        $this->assertEquals('https://example.org:8443/foo/bar.php', $uri->getURI('pass'));
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
