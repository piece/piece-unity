<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * PHP versions 4 and 5
 *
 * Copyright (c) 2006-2007 KUBO Atsuhiro <iteman@users.sourceforge.net>,
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
 * @subpackage Piece_Unity_Plugin_View
 * @copyright  2006-2007 KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    SVN: $Id$
 * @since      File available since Release 0.11.0
 */

require dirname(__FILE__) . '/../../../prepare.php';
require_once 'PHPUnit.php';
require_once 'Piece/Unity/Plugin/View.php';
require_once 'Piece/Unity/Context.php';
require_once 'Piece/Unity/Config.php';
require_once 'Piece/Unity/Error.php';
require_once 'Piece/Unity/Plugin/Factory.php';

// {{{ Piece_Unity_Plugin_ViewTestCase

/**
 * TestCase for Piece_Unity_Plugin_View
 *
 * @package    Piece_Unity
 * @subpackage Piece_Unity_Plugin_View
 * @copyright  2006-2007 KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    Release: @package_version@
 * @since      Class available since Release 0.11.0
 */
class Piece_Unity_Plugin_ViewTestCase extends PHPUnit_TestCase
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
        Piece_Unity_Error::pushCallback(create_function('$error', 'var_dump($error); return ' . PEAR_ERRORSTACK_DIE . ';'));
        $_SERVER['REQUEST_METHOD'] = 'GET';
    }

    function tearDown()
    {
        Piece_Unity_Context::clear();
        Piece_Unity_Plugin_Factory::clearInstances();
        Piece_Unity_Error::popCallback();
    }

    function testSelfNotation()
    {
        $oldScriptName = $_SERVER['SCRIPT_NAME'];
        $_SERVER['SCRIPT_NAME'] = '/foo.php';
        $_SERVER['SERVER_NAME'] = 'example.org';
        $_SERVER['SERVER_PORT'] = '80';

        $context = &Piece_Unity_Context::singleton();
        $context->setView('self://__eventNameKey=goDisplayForm&bar=baz#zip');
        $config = &new Piece_Unity_Config();
        $context->setConfiguration($config);
        $view = &new Piece_Unity_Plugin_View();
        $view->invoke();

        $this->assertEquals('Renderer_Redirection', $config->getExtension('View', 'renderer'));
        $this->assertEquals('http://example.org/foo.php?__eventNameKey=goDisplayForm&bar=baz#zip', $context->getView());

        unset($_SERVER['SERVER_PORT']);
        unset($_SERVER['SERVER_NAME']);
        $_SERVER['SCRIPT_NAME'] = $oldScriptName;
    }

    function testSelfNotationForHTTPS()
    {
        $oldScriptName = $_SERVER['SCRIPT_NAME'];
        $_SERVER['SCRIPT_NAME'] = '/foo.php';
        $_SERVER['SERVER_NAME'] = 'example.org';
        $_SERVER['SERVER_PORT'] = '80';

        $context = &Piece_Unity_Context::singleton();
        $context->setView('selfs://__eventNameKey=goDisplayForm&bar=baz#zip');
        $config = &new Piece_Unity_Config();
        $context->setConfiguration($config);
        $view = &new Piece_Unity_Plugin_View();
        $view->invoke();

        $this->assertEquals('Renderer_Redirection', $config->getExtension('View', 'renderer'));
        $this->assertEquals('https://example.org/foo.php?__eventNameKey=goDisplayForm&bar=baz#zip', $context->getView());

        unset($_SERVER['SERVER_PORT']);
        unset($_SERVER['SERVER_NAME']);
        $_SERVER['SCRIPT_NAME'] = $oldScriptName;
    }

    /**
     * @since Method available since Release 0.12.0
     */
    function testBuiltinElements()
    {
        $config = &new Piece_Unity_Config();
        $context = &Piece_Unity_Context::singleton();
        $context->setConfiguration($config);
        $view = &new Piece_Unity_Plugin_View();
        $view->invoke();
        $viewElement = &$context->getViewElement();
        $elements = $viewElement->getElements();

        $this->assertEquals(9, count($elements));
        $this->assertTrue(array_key_exists('__request', $elements));
        $this->assertTrue(array_key_exists('__session', $elements));
        $this->assertTrue(array_key_exists('__eventNameKey', $elements));
        $this->assertTrue(array_key_exists('__scriptName', $elements));
        $this->assertTrue(array_key_exists('__basePath', $elements));
        $this->assertTrue(array_key_exists('__sessionName', $elements));
        $this->assertTrue(array_key_exists('__sessionID', $elements));
        $this->assertTrue(array_key_exists('__appRootPath', $elements));
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
