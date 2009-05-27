<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * PHP version 5
 *
 * Copyright (c) 2007-2009 KUBO Atsuhiro <kubo@iteman.jp>,
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
 * @copyright  2007-2009 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    GIT: $Id$
 * @since      File available since Release 1.2.0
 */

// {{{ Piece_Unity_Plugin_ControllerTest

/**
 * Some tests for Piece_Unity_Plugin_Controller.
 *
 * @package    Piece_Unity
 * @copyright  2007-2009 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    Release: @package_version@
 * @since      Class available since Release 1.2.0
 */
class Piece_Unity_Plugin_ControllerTest extends Piece_Unity_PHPUnit_TestCase
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

    /* public function setUp() */
    /* { */
    /*     parent::setUp(); */
    /*     $_SERVER['REQUEST_METHOD'] = 'GET'; */
    /*     $this->_exclusiveDirectory = ; */
    /* } */

    /* function tearDown() */
    /* { */
    /*     unset($_SESSION); */
    /*     Piece_Unity_Context::clear(); */
    /*     unset($_GET['_event']); */
    /*     unset($_SERVER['REQUEST_METHOD']); */
    /* } */

    /**
     * @test
     */
    public function overwriteTheViewWithAnArbitraryViewFromAnAction()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_GET['_event'] = 'ControllerTestCaseSpecifyingArbitraryViewInActionShouldWork';
        /* $oldScriptName = $_SERVER['REQUEST_URI']; */
        $_SERVER['REQUEST_URI'] = '/foo.php';
        $_SERVER['SERVER_NAME'] = 'example.org';
        $_SERVER['SERVER_PORT'] = '80';
        $config = new Piece_Unity_Config();
        $config->setExtension('Controller', 'dispatcher', 'Dispatcher_Simple');
        $config->setConfiguration('Dispatcher_Simple', 'actionDirectory', dirname(__FILE__) . '/' . basename(__FILE__, '.php'));
        $context = Piece_Unity_Context::singleton();
        $context->setConfiguration($config);
        $controller = new Piece_Unity_Plugin_Controller();
        $controller->invoke();

        $this->assertEquals('http://example.org/', $context->getView());

        /* $_SERVER['REQUEST_URI'] = $oldScriptName; */
        /* unset($_SERVER['SERVER_PORT']); */
        /* unset($_SERVER['SERVER_NAME']); */
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