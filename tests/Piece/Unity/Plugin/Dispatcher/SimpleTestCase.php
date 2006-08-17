<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * PHP versions 4 and 5
 *
 * Copyright (c) 2006, KUBO Atsuhiro <iteman@users.sourceforge.net>
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
 * @link       http://iteman.typepad.jp/piece/
 * @see        Piece_Unity_Plugin_Dispatcher_Simple
 * @since      File available since Release 0.1.0
 */

require_once 'PHPUnit.php';
require_once 'Piece/Unity/Plugin/Dispatcher/Simple.php';
require_once 'Piece/Unity/Config.php';

// {{{ Piece_Unity_Plugin_Dispatcher_SimpleTestCase

/**
 * TestCase for Piece_Unity_Plugin_Dispatcher_Simple
 *
 * @package    Piece_Unity
 * @author     KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @copyright  2006 KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    Release: @package_version@
 * @link       http://iteman.typepad.jp/piece/
 * @see        Piece_Unity_Plugin_Dispatcher_Simple
 * @since      Class available since Release 0.1.0
 */
class Piece_Unity_Plugin_Dispatcher_SimpleTestCase extends PHPUnit_TestCase
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
        $_SERVER['REQUEST_METHOD'] = 'GET';
    }

    function tearDown()
    {
        Piece_Unity_Context::clear();
        unset($_GET['_event']);
        unset($_SERVER['REQUEST_METHOD']);
    }

    function testDispatchingWithoutAction()
    {
        $_GET['_event'] = 'foo';
        $config = &new Piece_Unity_Config();
        $context = &Piece_Unity_Context::singleton();
        $context->setConfiguration($config);
        $dispatcher = &new Piece_Unity_Plugin_Dispatcher_Simple();

        $this->assertEquals('foo', $dispatcher->invoke());
    }

    function testDispatchingWithAction()
    {
        $_GET['_event'] = 'SimpleExample';
        $GLOBALS['actionCalled'] = false;

        $this->assertEquals('SimpleExample', $this->_dispatch());
        $this->assertTrue($GLOBALS['actionCalled']);

        unset($GLOBALS['actionCalled']);
    }

    function testRelativePathVulnerability()
    {
        $_GET['_event'] = '../RelativePathVulnerability';
        $GLOBALS['actionCalled'] = false;
        $GLOBALS['RelativePathVulnerabilityActionLoaded'] = false;

        $this->assertEquals('../RelativePathVulnerability', $this->_dispatch());
        $this->assertFalse($GLOBALS['actionCalled']);
        $this->assertFalse($GLOBALS['RelativePathVulnerabilityActionLoaded']);

        unset($GLOBALS['actionCalled']);
        unset($GLOBALS['RelativePathVulnerabilityActionLoaded']);
    }

    /**#@-*/

    /**#@+
     * @access private
     */

    function _dispatch()
    {
        $config = &new Piece_Unity_Config();
        $config->setConfiguration('Dispatcher_Simple', 'actionDirectory', dirname(__FILE__));
        $context = &Piece_Unity_Context::singleton();
        $context->setConfiguration($config);
        $dispatcher = &new Piece_Unity_Plugin_Dispatcher_Simple();
        return $dispatcher->invoke();
    }

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
