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
 * @since      File available since Release 0.6.0
 */

// {{{ Piece_Unity_Plugin_Renderer_RedirectionTest

/**
 * Some tests for Piece_Unity_Plugin_Renderer_Redirection.
 *
 * @package    Piece_Unity
 * @copyright  2006-2009 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @since      Class available since Release 0.6.0
 */
class Piece_Unity_Plugin_Renderer_RedirectionTest extends Piece_Unity_PHPUnit_TestCase
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
    public function redirect()
    {
        $_SERVER['SERVER_NAME'] = 'example.org';
        $_SERVER['SERVER_PORT'] = '80';
        $context = Piece_Unity_Context::singleton();
        $context->setView('http://example.org/foo.php');
        $context->setConfiguration(new Piece_Unity_Config());
        $redirection = Piece_Unity_Plugin_Factory::factory('Renderer_Redirection');
        $redirection->render();

        $this->assertAttributeEquals('http://example.org/foo.php',
                                     '_sentURI',
                                     $redirection
                                     );
    }

    /**
     * @test
     */
    public function redirectForDirectAccessToABackendServer()
    {
        $_SERVER['SERVER_NAME'] = 'foo.example.org';
        $_SERVER['SERVER_PORT'] = '8201';
        $context = Piece_Unity_Context::singleton();
        $context->setView('http://example.org/foo/bar.php');
        $context->setProxyPath('/foo');
        $context->setConfiguration(new Piece_Unity_Config());
        $redirection = Piece_Unity_Plugin_Factory::factory('Renderer_Redirection');
        $redirection->render();

        $this->assertEquals('http://foo.example.org:8201/bar.php',
                            $this->readAttribute($redirection, '_sentURI')
                            );
    }

    /**
     * @test
     */
    public function redirectForAProxy()
    {
        $_SERVER['HTTP_X_FORWARDED_FOR'] = '1.2.3.4';
        $_SERVER['HTTP_X_FORWARDED_SERVER'] = 'example.org';
        $_SERVER['SERVER_NAME'] = 'foo.example.org';
        $_SERVER['SERVER_PORT'] = '8201';
        $context = Piece_Unity_Context::singleton();
        $context->setView('http://example.org/foo/bar.php');
        $context->setProxyPath('/foo');
        $context->setConfiguration(new Piece_Unity_Config());
        $redirection = Piece_Unity_Plugin_Factory::factory('Renderer_Redirection');
        $redirection->render();

        $this->assertAttributeEquals('http://foo.example.org:8201/bar.php',
                                     '_sentURI',
                                     $redirection
                                     );
    }

    /**
     * @test
     */
    public function redirectForDirectAccessToABackendServerWhenHttpsProtocolIsGiven()
    {
        $_SERVER['SERVER_NAME'] = 'foo.example.org';
        $_SERVER['SERVER_PORT'] = '8201';
        $context = Piece_Unity_Context::singleton();
        $context->setView('https://example.org/foo/bar.php');
        $context->setProxyPath('/foo');
        $context->setConfiguration(new Piece_Unity_Config());
        $redirection = Piece_Unity_Plugin_Factory::factory('Renderer_Redirection');
        $redirection->render();

        $this->assertAttributeEquals('https://foo.example.org:8201/bar.php',
                                     '_sentURI',
                                     $redirection
                                     );
    }

    /**
     * @test
     */
    public function redirectForOtherProxy()
    {
        $_SERVER['HTTP_X_FORWARDED_FOR'] = '1.2.3.4';
        $_SERVER['HTTP_X_FORWARDED_SERVER'] = 'test.example.org';
        $_SERVER['SERVER_NAME'] = 'foo.example.org';
        $_SERVER['SERVER_PORT'] = '8201';
        $context = Piece_Unity_Context::singleton();
        $context->setView('http://example.org/foo/bar.php');
        $context->setProxyPath('/foo');
        $context->setConfiguration(new Piece_Unity_Config());
        $redirection = new Piece_Unity_Plugin_Renderer_Redirection();
        $redirection->render();

        $this->assertAttributeEquals('http://foo.example.org:8201/bar.php',
                                     '_sentURI',
                                     $redirection
                                     );
    }

    /**
     * @test
     * @since Method available since Release 0.11.0
     */
    public function replaceTheEventNameKeyVariableWithTheActualEventNameKey()
    {
        $_SERVER['SERVER_NAME'] = 'example.org';
        $_SERVER['SERVER_PORT'] = '80';
        $context = Piece_Unity_Context::singleton();
        $context->setView('http://example.org/foo.php?__eventNameKey=bar');
        $context->setConfiguration(new Piece_Unity_Config());
        $redirection = Piece_Unity_Plugin_Factory::factory('Renderer_Redirection');
        $redirection->render();

        $this->assertAttributeEquals('http://example.org/foo.php?_event=bar',
                                     '_sentURI',
                                     $redirection
                                     );
    }

    /**
     * @test
     * @since Method available since Release 0.11.0
     */
    public function redirectToHttps()
    {
        $_SERVER['SERVER_NAME'] = 'example.org';
        $_SERVER['SERVER_PORT'] = '80';
        $context = Piece_Unity_Context::singleton();
        $context->setView('https://example.org/foo.php');
        $context->setConfiguration(new Piece_Unity_Config());
        $redirection = Piece_Unity_Plugin_Factory::factory('Renderer_Redirection');
        $redirection->render();

        $this->assertAttributeEquals('https://example.org/foo.php',
                                     '_sentURI',
                                     $redirection
                                     );
    }

    /**
     * @test
     * @since Method available since Release 1.5.0
     */
    public function supportSelfNotation()
    {
        $_SERVER['REQUEST_URI'] = '/foo.php';
        $_SERVER['SERVER_NAME'] = 'example.org';
        $_SERVER['SERVER_PORT'] = '80';

        $context = Piece_Unity_Context::singleton();
        $context->setView('self://__eventNameKey=goDisplayForm&bar=baz#zip');
        $context->setConfiguration(new Piece_Unity_Config());
        $redirection = Piece_Unity_Plugin_Factory::factory('Renderer_Redirection');
        $redirection->render();

        $this->assertEquals('http://example.org/foo.php?__eventNameKey=goDisplayForm&bar=baz#zip', $context->getView());
    }

    /**
     * @test
     * @since Method available since Release 1.5.0
     */
    public function supportSelfNotationForHttps()
    {
        $_SERVER['REQUEST_URI'] = '/foo.php';
        $_SERVER['SERVER_NAME'] = 'example.org';
        $_SERVER['SERVER_PORT'] = '80';

        $context = Piece_Unity_Context::singleton();
        $context->setView('selfs://__eventNameKey=goDisplayForm&bar=baz#zip');
        $context->setConfiguration(new Piece_Unity_Config());
        $redirection = Piece_Unity_Plugin_Factory::factory('Renderer_Redirection');
        $redirection->render();

        $this->assertEquals('https://example.org/foo.php?__eventNameKey=goDisplayForm&bar=baz#zip', $context->getView());
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
