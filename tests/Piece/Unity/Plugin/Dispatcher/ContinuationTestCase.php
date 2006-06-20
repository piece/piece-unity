<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * PHP versions 4 and 5
 *
 * Copyright (c) 2006, KUBO Atsuhiro <iteman2002@yahoo.co.jp>
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
 * @author     KUBO Atsuhiro <iteman2002@yahoo.co.jp>
 * @copyright  2006 KUBO Atsuhiro <iteman2002@yahoo.co.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    SVN: $Id$
 * @link       http://iteman.typepad.jp/piece/
 * @see        Piece_Unity_Plugin_Dispatcher_Continuation
 * @since      File available since Release 0.1.0
 */

require_once 'PHPUnit.php';
require_once 'Piece/Unity/Plugin/Dispatcher/Continuation.php';
require_once 'Piece/Unity/Context.php';
require_once 'Cache/Lite/File.php';
require_once 'Piece/Unity/Plugin/Renderer/PHP.php';

// {{{ Piece_Unity_Plugin_Dispatcher_ContinuationTestCase

/**
 * TestCase for Piece_Unity_Plugin_Dispatcher_Continuation
 *
 * @package    Piece_Unity
 * @author     KUBO Atsuhiro <iteman2002@yahoo.co.jp>
 * @copyright  2006 KUBO Atsuhiro <iteman2002@yahoo.co.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    Release: @package_version@
 * @link       http://iteman.typepad.jp/piece/
 * @see        Piece_Unity_Plugin_Dispatcher_Continuation
 * @since      Class available since Release 0.1.0
 */
class Piece_Unity_Plugin_Dispatcher_ContinuationTestCase extends PHPUnit_TestCase
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
        Piece_Unity_Session_Factory::setMode('Mock');
        $_SERVER['REQUEST_METHOD'] = 'GET';
    }

    function tearDown()
    {
        $cache = &new Cache_Lite_File(array('cacheDir' => dirname(__FILE__) . '/',
                                            'masterFile' => '',
                                            'automaticSerialization' => true,
                                            'errorHandlingAPIBreak' => true)
                                      );
        $cache->clean();
        $context = &Piece_Unity_Context::singleton();
        $context->clear();
        Piece_Unity_Error::clearErrors();
        unset($_GET['_flowExecutionTicket']);
        unset($_GET['_event']);
        unset($_GET['_flow']);
        unset($_SERVER['REQUEST_METHOD']);
        Piece_Unity_Error::popCallback();
     }

    function testContinuation()
    {
        $_GET['_flow'] = 'Counter';

        $config = &new Piece_Unity_Config();
        $config->setConfiguration('Dispatcher_Continuation', 'actionDirectory', dirname(__FILE__));
        $config->setConfiguration('Dispatcher_Continuation', 'cacheDirectory', dirname(__FILE__));
        $config->setConfiguration('Dispatcher_Continuation', 'flowDefinitions', array(array('name' => 'Counter', 'file' => dirname(__FILE__) . '/Counter.yaml', 'isExclusive' => true)));
        $context = &Piece_Unity_Context::singleton();
        $context->setConfiguration($config);

        $dispatcher = &new Piece_Unity_Plugin_Dispatcher_Continuation();
        $dispatcher->invoke();

        $this->assertEquals('Counter', $context->getView());

        $session = &$context->getSession();
        $continuation = &$session->getAttribute($GLOBALS['PIECE_UNITY_Continuation_Session_Key']);
        $flowExecutionTicket = $continuation->getCurrentFlowExecutionTicket();

        $this->assertTrue(is_a($continuation, 'Piece_Flow_Continuation'));
        $this->assertTrue($continuation->hasAttribute('counter'));
        $this->assertEquals(0, $continuation->getAttribute('counter'));

        $context->clear();
        $_GET['_event'] = 'increase';
        $_GET['_flowExecutionTicket'] = $flowExecutionTicket;
        unset($_GET['_flow']);
        $dispatcher = &new Piece_Unity_Plugin_Dispatcher_Continuation();
        $context = &Piece_Unity_Context::singleton();
        $session = &$context->getSession();
        $session->setAttributeByRef($GLOBALS['PIECE_UNITY_Continuation_Session_Key'], $continuation);
        $context->setConfiguration($config);
        $dispatcher->invoke();

        $this->assertEquals(1, $continuation->getAttribute('counter'));

        $dispatcher->invoke();

        $this->assertEquals(2, $continuation->getAttribute('counter'));

        $dispatcher->invoke();

        $this->assertEquals(3, $continuation->getAttribute('counter'));
        $this->assertEquals('Finish', $context->getView());
    }

    function testInvalidConfiguration()
    {
        Piece_Unity_Error::pushCallback(create_function('$error', 'return ' . PEAR_ERRORSTACK_PUSHANDLOG . ';'));
        $_GET['_flow'] = 'Counter';

        $config = &new Piece_Unity_Config();
        $config->setConfiguration('Dispatcher_Continuation', 'actionDirectory', dirname(__FILE__));
        $config->setConfiguration('Dispatcher_Continuation', 'enableSingleFlowMode', true);
        $config->setConfiguration('Dispatcher_Continuation', 'cacheDirectory', dirname(__FILE__));
        $config->setConfiguration('Dispatcher_Continuation', 'flowDefinitions',
                                  array(array('name' => 'Counter', 'file' => dirname(__FILE__) . '/Counter.yaml', 'isExclusive' => true),
                                        array('name' => 'SeconfCounter', 'file' => dirname(__FILE__) . '/Counter.yaml', 'isExclusive' => true))
                                  );
        $context = &Piece_Unity_Context::singleton();
        $context->setConfiguration($config);

        $dispatcher = &new Piece_Unity_Plugin_Dispatcher_Continuation();
        $dispatcher->invoke();

        $this->assertTrue(Piece_Unity_Error::hasErrors('exception'));

        $error = Piece_Unity_Error::pop();

        $this->assertEquals(PIECE_UNITY_ERROR_INVALID_CONFIGURATION, $error['code']);

        Piece_Unity_Error::popCallback();
    }

    function testFailreToInvoke()
    {
        Piece_Unity_Error::pushCallback(create_function('$error', 'return ' . PEAR_ERRORSTACK_PUSHANDLOG . ';'));
        $_GET['_flow'] = 'Counter';

        $config = &new Piece_Unity_Config();
        $config->setConfiguration('Dispatcher_Continuation', 'actionDirectory', dirname(__FILE__));
        $config->setConfiguration('Dispatcher_Continuation', 'enableSingleFlowMode', true);
        $config->setConfiguration('Dispatcher_Continuation', 'cacheDirectory', dirname(__FILE__));
        $config->setConfiguration('Dispatcher_Continuation', 'flowDefinitions',
                                  array(array('name' => 'Counter', 'file' => dirname(__FILE__) . '/Counter.yaml', 'isExclusive' => false))
                                  );
        $context = &Piece_Unity_Context::singleton();
        $context->setConfiguration($config);

        $dispatcher = &new Piece_Unity_Plugin_Dispatcher_Continuation();
        $dispatcher->invoke();

        $this->assertEquals('Counter', $context->getView());

        $session = &$context->getSession();
        $continuation = &$session->getAttribute($GLOBALS['PIECE_UNITY_Continuation_Session_Key']);
        $flowExecutionTicket = $continuation->getCurrentFlowExecutionTicket();
        $context->clear();
        $_GET['_event'] = 'increase';
        $_GET['_flowExecutionTicket'] = $flowExecutionTicket;
        unset($_GET['_flow']);
        $dispatcher = &new Piece_Unity_Plugin_Dispatcher_Continuation();
        $context = &Piece_Unity_Context::singleton();
        $session = &$context->getSession();
        $session->setAttributeByRef($GLOBALS['PIECE_UNITY_Continuation_Session_Key'], $continuation);
        $context->setConfiguration($config);
        $dispatcher->invoke();
        $dispatcher->invoke();
        $dispatcher->invoke();
        $dispatcher->invoke();

        $this->assertTrue(Piece_Unity_Error::hasErrors('exception'));

        $error = Piece_Unity_Error::pop();

        $this->assertEquals(PIECE_UNITY_ERROR_INVOCATION_FAILED, $error['code']);

        Piece_Unity_Error::popCallback();
    }

    function testSettingContinuationObjectAsViewElement()
    {
        $_GET['_bar'] = 'Counter';

        $config = &new Piece_Unity_Config();
        $config->setConfiguration('Dispatcher_Continuation', 'actionDirectory', dirname(__FILE__));
        $config->setConfiguration('Dispatcher_Continuation', 'cacheDirectory', dirname(__FILE__));
        $config->setConfiguration('Dispatcher_Continuation', 'flowDefinitions', array(array('name' => 'Counter', 'file' => dirname(__FILE__) . '/Counter.yaml', 'isExclusive' => true)));
        $config->setConfiguration('Dispatcher_Continuation', 'flowExecutionTicketKey', '_foo');
        $config->setConfiguration('Dispatcher_Continuation', 'flowNameKey', '_bar');
        $config->setConfiguration('Renderer_PHP', 'templateDirectory', dirname(__FILE__));
        $context = &Piece_Unity_Context::singleton();
        $context->setConfiguration($config);

        $dispatcher = &new Piece_Unity_Plugin_Dispatcher_Continuation();
        $dispatcher->invoke();

        $renderer = &new Piece_Unity_Plugin_Renderer_PHP();
        ob_start();
        $renderer->invoke();
        $buffer = ob_get_contents();
        ob_end_clean();

        $this->assertEquals('OK', $buffer);
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
