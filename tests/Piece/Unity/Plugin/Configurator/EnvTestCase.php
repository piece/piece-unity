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

require_once realpath(dirname(__FILE__) . '/../../../../prepare.php');
require_once 'PHPUnit.php';
require_once 'Piece/Unity/Plugin/Configurator/Env.php';
require_once 'Piece/Unity/Context.php';
require_once 'Piece/Unity/Config.php';
require_once 'Piece/Unity/URI.php';
require_once 'Piece/Unity/Error.php';

// {{{ Piece_Unity_Plugin_Configurator_EnvTestCase

/**
 * Some tests for Piece_Unity_Plugin_Configurator_Env.
 *
 * @package    Piece_Unity
 * @copyright  2007-2008 KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    Release: @package_version@
 * @since      Class available since Release 0.11.0
 */
class Piece_Unity_Plugin_Configurator_EnvTestCase extends PHPUnit_TestCase
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
        $config->setConfiguration('Configurator_Env', 'proxyPath', '/foo/bar');
        $context = &Piece_Unity_Context::singleton();
        $context->setConfiguration($config);

        $configurator = &new Piece_Unity_Plugin_Configurator_Env();
        $configurator->invoke();

        $this->assertEquals('/foo/bar', $context->getProxyPath());
    }

    function testSetNonSSLableServers()
    {
        $_SERVER['SERVER_NAME'] = 'example.org';
        $_SERVER['SERVER_PORT'] = '80';
        $config = &new Piece_Unity_Config();
        $config->setConfiguration('Configurator_Env', 'nonSSLableServers', array('example.org'));
        $context = &Piece_Unity_Context::singleton();
        $context->setConfiguration($config);

        $configurator = &new Piece_Unity_Plugin_Configurator_Env();
        $configurator->invoke();

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
