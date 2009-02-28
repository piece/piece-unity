<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * PHP version 5
 *
 * Copyright (c) 2007-2009 KUBO Atsuhiro <iteman@users.sourceforge.net>,
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
 * @copyright  2007-2009 KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    GIT: $Id$
 * @since      File available since Release 0.12.0
 */

// {{{ Piece_Unity_Plugin_Configurator_AppRootTest

/**
 * Some tests for Piece_Unity_Plugin_Configurator_AppRoot.
 *
 * @package    Piece_Unity
 * @copyright  2007-2009 KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    Release: @package_version@
 * @since      Class available since Release 0.12.0
 */
class Piece_Unity_Plugin_Configurator_AppRootTest extends PHPUnit_Framework_TestCase
{

    // {{{ properties

    /**#@+
     * @access public
     */

    /**#@-*/

    /**#@+
     * @access protected
     */

    protected $backupGlobals = false;

    /**#@-*/
    
    /**#@+
     * @access private
     */

    /**#@-*/

    /**#@+
     * @access public
     */

    public function tearDown()
    {
        Piece_Unity_Context::clear();
        Piece_Unity_Error::clearErrors();
    }

    public function testAppRoot()
    {
        $appRoot = realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR . '..');
        $config = new Piece_Unity_Config();
        $config->setConfiguration('Configurator_AppRoot', 'appRoot', $appRoot);
        $context = Piece_Unity_Context::singleton();
        $context->setConfiguration($config);
        $configurator = new Piece_Unity_Plugin_Configurator_AppRoot();
        $configurator->invoke();

        $this->assertEquals($appRoot, getcwd());
    }

    public function testAppRootWithNonExistingDirectory()
    {
        $appRoot = '/foo/bar';
        $config = new Piece_Unity_Config();
        $config->setConfiguration('Configurator_AppRoot', 'appRoot', $appRoot);
        $context = Piece_Unity_Context::singleton();
        $context->setConfiguration($config);
        $configurator = new Piece_Unity_Plugin_Configurator_AppRoot();
        Piece_Unity_Error::disableCallback();
        @$configurator->invoke();
        Piece_Unity_Error::enableCallback();

        $this->assertTrue(Piece_Unity_Error::hasErrors());

        $error = Piece_Unity_Error::pop();

        $this->assertEquals(PIECE_UNITY_ERROR_INVOCATION_FAILED, $error['code']);
    }

    public function testAppRootPath()
    {
        $appRootPath = '/foo/bar';
        $config = new Piece_Unity_Config();
        $config->setConfiguration('Configurator_AppRoot', 'appRootPath', $appRootPath);
        $context = Piece_Unity_Context::singleton();
        $context->setConfiguration($config);
        $configurator = new Piece_Unity_Plugin_Configurator_AppRoot();
        $configurator->invoke();

        $this->assertEquals($appRootPath, $context->getAppRootPath());
    }

    /**#@-*/

    /**#@+
     * @access protected
     */

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
