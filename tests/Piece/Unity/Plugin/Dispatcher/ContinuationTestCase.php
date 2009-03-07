<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * PHP versions 4 and 5
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
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    GIT: $Id$
 * @since      File available since Release 0.1.0
 */

require_once realpath(dirname(__FILE__) . '/../../../../prepare.php');
require_once 'PHPUnit.php';
require_once 'Piece/Unity/Plugin/Dispatcher/Continuation.php';
require_once 'Piece/Unity/Context.php';
require_once 'Cache/Lite/File.php';
require_once 'Piece/Unity/Plugin/Renderer/PHP.php';
require_once 'Piece/Unity/Config.php';
require_once 'Piece/Unity/Error.php';
require_once 'Piece/Unity/Service/Continuation.php';
require_once 'Piece/Unity/HTTPStatus.php';

// {{{ Piece_Unity_Plugin_Dispatcher_ContinuationTestCase

/**
 * Some tests for Piece_Unity_Plugin_Dispatcher_Continuation.
 *
 * @package    Piece_Unity
 * @copyright  2006-2009 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    Release: @package_version@
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

    var $_cacheDirectory;

    /**#@-*/

    /**#@+
     * @access public
     */

    function setUp()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SESSION = array();
        $this->_cacheDirectory = dirname(__FILE__) . '/' . basename(__FILE__, '.php');
    }

    function tearDown()
    {
        unset($_SESSION);
        $cache = &new Cache_Lite_File(array('cacheDir' => "{$this->_cacheDirectory}/",
                                            'masterFile' => '',
                                            'automaticSerialization' => true,
                                            'errorHandlingAPIBreak' => true)
                                      );
        $cache->clean();
        Piece_Unity_Context::clear();
        Piece_Unity_Error::clearErrors();
        unset($_GET['_flowExecutionTicket']);
        unset($_GET['_event']);
        unset($_GET['_flow']);
        unset($_SERVER['REQUEST_METHOD']);
    }

    function testContinuation()
    {
        $_GET['_flow'] = 'Counter';
        $config = &new Piece_Unity_Config();
        $config->setConfiguration('Dispatcher_Continuation', 'actionDirectory', $this->_cacheDirectory);
        $config->setConfiguration('Dispatcher_Continuation', 'cacheDirectory', $this->_cacheDirectory);
        $config->setConfiguration('Dispatcher_Continuation', 'flowDefinitions', array(array('name' => 'Counter', 'file' => "{$this->_cacheDirectory}/Counter.yaml", 'isExclusive' => true)));
        $context = &Piece_Unity_Context::singleton();
        $context->setConfiguration($config);
        $session = &$context->getSession();
        @$session->start();

        $dispatcher = &new Piece_Unity_Plugin_Dispatcher_Continuation();

        $this->assertEquals('Counter', $dispatcher->invoke());

        $continuationServer = &$session->getAttribute(PIECE_UNITY_CONTINUATION_SESSIONKEY);
        $continuationService = &$continuationServer->createService();
        $viewElement = &$context->getViewElement();
        $flowExecutionTicket = $viewElement->getElement('__flowExecutionTicket');

        $this->assertEquals(strtolower('Piece_Flow_Continuation_Server'), strtolower(get_class($continuationServer)));
        $this->assertTrue($continuationService->hasAttribute('counter'));
        $this->assertEquals(0, $continuationService->getAttribute('counter'));

        Piece_Unity_Context::clear();
        $_GET['_event'] = 'increase';
        $_GET['_flowExecutionTicket'] = $flowExecutionTicket;
        $context = &Piece_Unity_Context::singleton();
        $context->setConfiguration($config);
        $dispatcher = &new Piece_Unity_Plugin_Dispatcher_Continuation();
        $session = &$context->getSession();
        @$session->start();
        $session->setAttributeByRef(PIECE_UNITY_CONTINUATION_SESSIONKEY, $continuationServer);
        $dispatcher->invoke();
        $continuationService = &$continuationServer->createService();

        $this->assertEquals(1, $continuationService->getAttribute('counter'));

        $dispatcher->invoke();

        $this->assertEquals(2, $continuationService->getAttribute('counter'));
        $this->assertEquals('Finish', $dispatcher->invoke());

        $continuationService = &$continuationServer->createService();

        $this->assertEquals(3, $continuationService->getAttribute('counter'));
    }

    function testInvalidConfiguration()
    {
        $_GET['_flow'] = 'Counter';
        $config = &new Piece_Unity_Config();
        $config->setConfiguration('Dispatcher_Continuation', 'actionDirectory', $this->_cacheDirectory);
        $config->setConfiguration('Dispatcher_Continuation', 'enableSingleFlowMode', true);
        $config->setConfiguration('Dispatcher_Continuation', 'cacheDirectory', $this->_cacheDirectory);
        $config->setConfiguration('Dispatcher_Continuation', 'flowDefinitions',
                                  array(array('name' => 'Counter', 'file' => "{$this->_cacheDirectory}/Counter.yaml", 'isExclusive' => true),
                                        array('name' => 'SeconfCounter', 'file' => "{$this->_cacheDirectory}/Counter.yaml", 'isExclusive' => true))
                                  );
        $context = &Piece_Unity_Context::singleton();
        $context->setConfiguration($config);
        $session = &$context->getSession();
        @$session->start();
        $dispatcher = &new Piece_Unity_Plugin_Dispatcher_Continuation();
        Piece_Unity_Error::disableCallback();
        $dispatcher->invoke();
        Piece_Unity_Error::enableCallback();

        $this->assertTrue(Piece_Unity_Error::hasErrors());

        $error = Piece_Unity_Error::pop();

        $this->assertEquals(PIECE_UNITY_ERROR_INVALID_CONFIGURATION, $error['code']);
    }

    function testFailreToInvoke()
    {
        $config = &new Piece_Unity_Config();
        $config->setConfiguration('Dispatcher_Continuation', 'actionDirectory', $this->_cacheDirectory);
        $config->setConfiguration('Dispatcher_Continuation', 'enableSingleFlowMode', true);
        $config->setConfiguration('Dispatcher_Continuation', 'cacheDirectory', $this->_cacheDirectory);
        $config->setConfiguration('Dispatcher_Continuation', 'flowDefinitions',
                                  array(array('name' => 'Counter', 'file' => "{$this->_cacheDirectory}/Counter.yaml", 'isExclusive' => false))
                                  );
        $context = &Piece_Unity_Context::singleton();
        $context->setConfiguration($config);
        $session = &$context->getSession();
        @$session->start();

        $dispatcher = &new Piece_Unity_Plugin_Dispatcher_Continuation();

        $this->assertEquals('Counter', $dispatcher->invoke());

        $session = &$context->getSession();
        $continuationServer = &$session->getAttribute(PIECE_UNITY_CONTINUATION_SESSIONKEY);
        $viewElement = &$context->getViewElement();
        $flowExecutionTicket = $viewElement->getElement('__flowExecutionTicket');
        Piece_Unity_Context::clear();
        $_GET['_event'] = 'increase';
        $_GET['_flowExecutionTicket'] = $flowExecutionTicket;
        $context = &Piece_Unity_Context::singleton();
        $context->setConfiguration($config);
        $session = &$context->getSession();
        @$session->start();
        $dispatcher = &new Piece_Unity_Plugin_Dispatcher_Continuation();
        $session = &$context->getSession();
        $session->setAttributeByRef(PIECE_UNITY_CONTINUATION_SESSIONKEY, $continuationServer);
        $dispatcher->invoke();
        $dispatcher->invoke();
        $dispatcher->invoke();
        Piece_Unity_Error::disableCallback();
        $dispatcher->invoke();
        Piece_Unity_Error::enableCallback();

        $this->assertTrue(Piece_Unity_Error::hasErrors());

        $error = Piece_Unity_Error::pop();

        $this->assertEquals(PIECE_UNITY_ERROR_INVOCATION_FAILED, $error['code']);
    }

    function testSettingContinuationServiceObjectAsViewElement()
    {
        $_GET['_bar'] = 'Counter';

        $config = &new Piece_Unity_Config();
        $config->setConfiguration('Dispatcher_Continuation', 'actionDirectory', $this->_cacheDirectory);
        $config->setConfiguration('Dispatcher_Continuation', 'cacheDirectory', $this->_cacheDirectory);
        $config->setConfiguration('Dispatcher_Continuation', 'flowDefinitions', array(array('name' => 'Counter', 'file' => "{$this->_cacheDirectory}/Counter.yaml", 'isExclusive' => true)));
        $config->setConfiguration('Dispatcher_Continuation', 'flowExecutionTicketKey', '_foo');
        $config->setConfiguration('Dispatcher_Continuation', 'flowNameKey', '_bar');
        $config->setConfiguration('Renderer_PHP', 'templateDirectory', $this->_cacheDirectory);
        $context = &Piece_Unity_Context::singleton();
        $context->setConfiguration($config);
        $session = &$context->getSession();
        @$session->start();

        $dispatcher = &new Piece_Unity_Plugin_Dispatcher_Continuation();
        $context->setView($dispatcher->invoke());
        $dispatcher->publish();

        $renderer = &new Piece_Unity_Plugin_Renderer_PHP();
        ob_start();
        $renderer->invoke();
        $buffer = ob_get_contents();
        ob_end_clean();

        $this->assertEquals('OK', $buffer);
    }

    function testMappingURIsToFlows()
    {
        $_GET['_flow'] = 'Foo';
        $config = &new Piece_Unity_Config();
        $config->setConfiguration('Dispatcher_Continuation', 'actionDirectory', $this->_cacheDirectory);
        $config->setConfiguration('Dispatcher_Continuation', 'cacheDirectory', $this->_cacheDirectory);
        $config->setConfiguration('Dispatcher_Continuation', 'flowDefinitions', array(array('name' => 'Counter', 'file' => "{$this->_cacheDirectory}/Counter.yaml", 'isExclusive' => true)));
        $config->setConfiguration('Dispatcher_Continuation', 'flowName', 'Counter');
        $context = &Piece_Unity_Context::singleton();
        $context->setConfiguration($config);
        $session = &$context->getSession();
        @$session->start();

        $dispatcher = &new Piece_Unity_Plugin_Dispatcher_Continuation();
        Piece_Unity_Error::disableCallback();
        $viewString = $dispatcher->invoke();
        Piece_Unity_Error::enableCallback();

        $this->assertEquals('Counter', $viewString);
        $this->assertFalse(Piece_Unity_Error::hasErrors());
    }

    /**
     * @since Method available since Release 0.8.0
     */
    function testSettingResultsAsViewElementAndFlowAttribute()
    {
        $_GET['_flow'] = 'ContinuationValidation';
        $fields = array('first_name' => ' Foo ',
                        'last_name' => ' Bar ',
                        'email' => 'baz@example.org',
                        );
        foreach ($fields as $name => $value) {
            $_GET[$name] = $value;
        }

        $config = &new Piece_Unity_Config();
        $config->setConfiguration('Dispatcher_Continuation', 'actionDirectory', $this->_cacheDirectory);
        $config->setConfiguration('Dispatcher_Continuation', 'cacheDirectory', $this->_cacheDirectory);
        $config->setConfiguration('Dispatcher_Continuation', 'flowDefinitions', array(array('name' => 'ContinuationValidation', 'file' => "{$this->_cacheDirectory}/ContinuationValidation.yaml", 'isExclusive' => true)));
        $context = &Piece_Unity_Context::singleton();
        $context->setConfiguration($config);
        $session = &$context->getSession();
        @$session->start();
        $dispatcher = &new Piece_Unity_Plugin_Dispatcher_Continuation();

        $this->assertEquals('Form', $dispatcher->invoke());

        $session = &$context->getSession();
        $continuationServer = &$session->getAttribute(PIECE_UNITY_CONTINUATION_SESSIONKEY);
        $viewElement = &$context->getViewElement();
        $flowExecutionTicket = $viewElement->getElement('__flowExecutionTicket');

        Piece_Unity_Context::clear();
        $_GET['_event'] = 'validate';
        $_GET['_flowExecutionTicket'] = $flowExecutionTicket;
        $context = &Piece_Unity_Context::singleton();
        $context->setConfiguration($config);
        $session = &$context->getSession();
        @$session->start();
        $session->setAttributeByRef(PIECE_UNITY_CONTINUATION_SESSIONKEY, $continuationServer);
        $validation = &$context->getValidation();
        $validation->setConfigDirectory($this->_cacheDirectory);
        $validation->setCacheDirectory($this->_cacheDirectory);
        $dispatcher = &new Piece_Unity_Plugin_Dispatcher_Continuation();

        $this->assertEquals('Success', $dispatcher->invoke());

        $viewElement = &$context->getViewElement();

        $this->assertTrue($viewElement->hasElement('__ValidationResults'));
        $this->assertEquals($validation->getResults(), $viewElement->getElement('__ValidationResults'));

        $continuationService = &$context->getContinuation();

        $this->assertTrue($continuationService->hasAttribute('__ValidationResults'));
        $this->assertEquals($validation->getResults(), $continuationService->getAttribute('__ValidationResults'));

        $user = &$context->getAttribute('user');
        foreach ($fields as $field => $value) {
            $this->assertEquals(trim($value), $user->$field, $field);
        }

        foreach (array_keys($fields) as $field) {
            unset($_GET[$field]);
        }
    }

    /**
     * @since Method available since Release 1.1.0
     */
    function testFallbackURIShouldBeReturnedWhenFlowExecutionHasExpiredAndGCIsEnabled()
    {
        $_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.1';
        $config = &new Piece_Unity_Config();
        $config->setConfiguration('Dispatcher_Continuation', 'flowName', 'FlowExecutionExpired');
        $config->setConfiguration('Dispatcher_Continuation', 'actionDirectory', $this->_cacheDirectory);
        $config->setConfiguration('Dispatcher_Continuation', 'cacheDirectory', $this->_cacheDirectory);
        $config->setConfiguration('Dispatcher_Continuation', 'flowDefinitions', array(array('name' => 'FlowExecutionExpired', 'file' => "{$this->_cacheDirectory}/FlowExecutionExpired.yaml", 'isExclusive' => false)));
        $config->setConfiguration('Dispatcher_Continuation', 'enableGC', true);
        $config->setConfiguration('Dispatcher_Continuation', 'gcExpirationTime', 1);
        $config->setConfiguration('Dispatcher_Continuation', 'useGCFallback', true);
        $config->setConfiguration('Dispatcher_Continuation', 'gcFallbackURI', 'http://www.example.org/');
        $context = &Piece_Unity_Context::singleton();
        $context->setConfiguration($config);
        $session = &$context->getSession();
        @$session->start();
        $dispatcher = &new Piece_Unity_Plugin_Dispatcher_Continuation();

        $this->assertEquals('Form', $dispatcher->invoke());

        $session = &$context->getSession();
        $continuationServer = &$session->getAttribute(PIECE_UNITY_CONTINUATION_SESSIONKEY);
        $viewElement = &$context->getViewElement();
        $flowExecutionTicket = $viewElement->getElement('__flowExecutionTicket');

        Piece_Unity_Context::clear();
        $_GET['_flowExecutionTicket'] = $flowExecutionTicket;
        $context = &Piece_Unity_Context::singleton();
        $context->setConfiguration($config);
        $session = &$context->getSession();
        @$session->start();
        $session->setAttributeByRef(PIECE_UNITY_CONTINUATION_SESSIONKEY, $continuationServer);
        $dispatcher = &new Piece_Unity_Plugin_Dispatcher_Continuation();
        sleep(2);

        $this->assertEquals('http://www.example.org/', $dispatcher->invoke());
        $this->assertEquals('HTTP/1.1 302 Found',
                            $GLOBALS['PIECE_UNITY_HTTPStatus_SentStatusLine']
                            );
        $this->assertTrue($session->hasAttribute('_flowExecutionExpired'));
        $this->assertTrue($session->getAttribute('_flowExecutionExpired'));

        $session->removeAttribute('_flowExecutionExpired');
        $GLOBALS['PIECE_UNITY_HTTPStatus_SentStatusLine'] = null;
        unset($_SERVER['SERVER_PROTOCOL']);
    }

    /**
     * @since Method available since Release 1.3.0
     */
    function testAnyExceptionsExceptPieceFlowShouldBeRaisedAgain()
    {
        $config = &new Piece_Unity_Config();
        $config->setConfiguration('Dispatcher_Continuation', 'flowName', 'AnyExceptionsExceptPieceFlow');
        $config->setConfiguration('Dispatcher_Continuation', 'actionDirectory', $this->_cacheDirectory);
        $config->setConfiguration('Dispatcher_Continuation', 'cacheDirectory', $this->_cacheDirectory);
        $config->setConfiguration('Dispatcher_Continuation', 'flowDefinitions', array(array('name' => 'AnyExceptionsExceptPieceFlow', 'file' => "{$this->_cacheDirectory}/AnyExceptionsExceptPieceFlow.yaml", 'isExclusive' => false)));
        $context = &Piece_Unity_Context::singleton();
        $context->setConfiguration($config);
        $session = &$context->getSession();
        @$session->start();
        $dispatcher = &new Piece_Unity_Plugin_Dispatcher_Continuation();
        Piece_Unity_Error::disableCallback();
        $dispatcher->invoke();
        Piece_Unity_Error::enableCallback();

        $this->assertTrue(Piece_Unity_Error::hasErrors());

        $error = Piece_Unity_Error::pop();

        $this->assertEquals(PIECE_UNITY_ERROR_INVOCATION_FAILED, $error['code']);
    }

    /**
     * @since Method available since Release 1.3.0
     */
    function testURIToFlowMappingsShouldWorkIfUseFlowMappingsIsTrue()
    {
        $_SERVER['SERVER_NAME'] = 'example.org';
        $_SERVER['SERVER_PORT'] = '80';
        $oldScriptName = $_SERVER['REQUEST_URI'];
        $_SERVER['REQUEST_URI'] = '/entry/new.php';
        $config = &new Piece_Unity_Config();
        $config->setConfiguration('Dispatcher_Continuation', 'actionDirectory', $this->_cacheDirectory);
        $config->setConfiguration('Dispatcher_Continuation', 'configDirectory', $this->_cacheDirectory);
        $config->setConfiguration('Dispatcher_Continuation', 'cacheDirectory', $this->_cacheDirectory);
        $config->setConfiguration('Dispatcher_Continuation', 'useFlowMappings', true);
        $config->setConfiguration('Dispatcher_Continuation', 'useFullFlowNameAsViewPrefix', false);
        $config->setConfiguration('Dispatcher_Continuation',
                                  'flowMappings',
                                  array(array('uri' => '/entry/new.php',
                                              'flowName' => 'Entry_New',
                                              'isExclusive' => false))
                                  );
        $context = &Piece_Unity_Context::singleton();
        $context->setConfiguration($config);
        $session = &$context->getSession();
        @$session->start();
        $dispatcher = &new Piece_Unity_Plugin_Dispatcher_Continuation();
        $viewString = $dispatcher->invoke();

        $this->assertEquals('Entry_New', $viewString);
        $this->assertEquals('bar', $context->getAttribute('foo'));

        $_SERVER['REQUEST_URI'] = $oldScriptName;
        unset($_SERVER['SERVER_PORT']);
        unset($_SERVER['SERVER_NAME']);
    }

    /**
     * @since Method available since Release 1.3.1
     */
    function testURIToFlowMappingsShouldWorkWithReverseProxy()
    {
        $_SERVER['HTTP_X_FORWARDED_FOR'] = '1.2.3.4';
        $_SERVER['HTTP_X_FORWARDED_SERVER'] = 'example.org';
        $_SERVER['SERVER_NAME'] = 'foo.example.org';
        $_SERVER['SERVER_PORT'] = '8201';
        $oldScriptName = $_SERVER['REQUEST_URI'];
        $_SERVER['REQUEST_URI'] = '/entry/new.php';
        $config = &new Piece_Unity_Config();
        $config->setConfiguration('Dispatcher_Continuation', 'actionDirectory', $this->_cacheDirectory);
        $config->setConfiguration('Dispatcher_Continuation', 'configDirectory', $this->_cacheDirectory);
        $config->setConfiguration('Dispatcher_Continuation', 'cacheDirectory', $this->_cacheDirectory);
        $config->setConfiguration('Dispatcher_Continuation', 'useFlowMappings', true);
        $config->setConfiguration('Dispatcher_Continuation',
                                  'flowMappings',
                                  array(array('uri' => '/entry/new.php',
                                              'flowName' => 'Entry_New',
                                              'isExclusive' => false))
                                  );
        $context = &Piece_Unity_Context::singleton();
        $context->setConfiguration($config);
        $context->setProxyPath('/crud');
        $context->setScriptName($context->getProxyPath() . $context->getScriptName());
        $session = &$context->getSession();
        @$session->start();
        $dispatcher = &new Piece_Unity_Plugin_Dispatcher_Continuation();
        Piece_Unity_Error::disableCallback();
        $dispatcher->invoke();
        Piece_Unity_Error::enableCallback();

        $this->assertFalse(Piece_Unity_Error::hasErrors());

        $_SERVER['REQUEST_URI'] = $oldScriptName;
        unset($_SERVER['SERVER_PORT']);
        unset($_SERVER['SERVER_NAME']);
        unset($_SERVER['HTTP_X_FORWARDED_FOR']);
        unset($_SERVER['HTTP_X_FORWARDED_SERVER']);
    }

    /**
     * @since Method available since Release 1.3.1
     */
    function testURIToFlowMappingsShouldWorkWithBackendServerDirectly()
    {
        $_SERVER['SERVER_NAME'] = 'example.org';
        $_SERVER['SERVER_PORT'] = '80';
        $oldScriptName = $_SERVER['REQUEST_URI'];
        $_SERVER['REQUEST_URI'] = '/entry/new.php';
        $config = &new Piece_Unity_Config();
        $config->setConfiguration('Dispatcher_Continuation', 'actionDirectory', $this->_cacheDirectory);
        $config->setConfiguration('Dispatcher_Continuation', 'configDirectory', $this->_cacheDirectory);
        $config->setConfiguration('Dispatcher_Continuation', 'cacheDirectory', $this->_cacheDirectory);
        $config->setConfiguration('Dispatcher_Continuation', 'useFlowMappings', true);
        $config->setConfiguration('Dispatcher_Continuation',
                                  'flowMappings',
                                  array(array('uri' => '/entry/new.php',
                                              'flowName' => 'Entry_New',
                                              'isExclusive' => false))
                                  );
        $context = &Piece_Unity_Context::singleton();
        $context->setConfiguration($config);
        $context->setProxyPath('/crud');
        $session = &$context->getSession();
        @$session->start();
        $dispatcher = &new Piece_Unity_Plugin_Dispatcher_Continuation();
        Piece_Unity_Error::disableCallback();
        $dispatcher->invoke();
        Piece_Unity_Error::enableCallback();

        $this->assertFalse(Piece_Unity_Error::hasErrors());

        $_SERVER['REQUEST_URI'] = $oldScriptName;
        unset($_SERVER['SERVER_PORT']);
        unset($_SERVER['SERVER_NAME']);
    }

    /**
     * @since Method available since Release 1.4.0
     */
    function testFullFlowNameShouldUseAsViewPrefixIfUseFullFlowNameAsViewPrefixIsTrue()
    {
        $_SERVER['SERVER_NAME'] = 'example.org';
        $_SERVER['SERVER_PORT'] = '80';
        $oldScriptName = $_SERVER['REQUEST_URI'];
        $_SERVER['REQUEST_URI'] = '/entry/new.php';
        $config = &new Piece_Unity_Config();
        $config->setConfiguration('Dispatcher_Continuation', 'actionDirectory', $this->_cacheDirectory);
        $config->setConfiguration('Dispatcher_Continuation', 'configDirectory', $this->_cacheDirectory);
        $config->setConfiguration('Dispatcher_Continuation', 'cacheDirectory', $this->_cacheDirectory);
        $config->setConfiguration('Dispatcher_Continuation', 'useFlowMappings', true);
        $config->setConfiguration('Dispatcher_Continuation', 'useFullFlowNameAsViewPrefix', true);
        $config->setConfiguration('Dispatcher_Continuation',
                                  'flowMappings',
                                  array(array('uri' => '/entry/new.php',
                                              'flowName' => 'Entry_New',
                                              'isExclusive' => false))
                                  );
        $context = &Piece_Unity_Context::singleton();
        $context->setConfiguration($config);
        $session = &$context->getSession();
        @$session->start();
        $dispatcher = &new Piece_Unity_Plugin_Dispatcher_Continuation();
        $viewString = $dispatcher->invoke();

        $this->assertEquals('Entry_New_New', $viewString);
        $this->assertEquals('bar', $context->getAttribute('foo'));

        $_SERVER['REQUEST_URI'] = $oldScriptName;
        unset($_SERVER['SERVER_PORT']);
        unset($_SERVER['SERVER_NAME']);
    }

    /**
     * @since Method available since Release 1.5.0
     */
    function testShouldPassThroughAnExceptionRaisedFromAnyPackage()
    {
        $_SERVER['SERVER_NAME'] = 'example.org';
        $_SERVER['SERVER_PORT'] = '80';
        $oldScriptName = $_SERVER['REQUEST_URI'];
        $_SERVER['REQUEST_URI'] = '/exceptions/pass-through.php';
        $config = &new Piece_Unity_Config();
        $config->setConfiguration('Dispatcher_Continuation', 'actionDirectory', $this->_cacheDirectory);
        $config->setConfiguration('Dispatcher_Continuation', 'configDirectory', $this->_cacheDirectory);
        $config->setConfiguration('Dispatcher_Continuation', 'cacheDirectory', $this->_cacheDirectory);
        $config->setConfiguration('Dispatcher_Continuation', 'useFlowMappings', true);
        $config->setConfiguration('Dispatcher_Continuation',
                                  'flowMappings',
                                  array(array('uri' => '/exceptions/pass-through.php',
                                              'flowName' => 'Exceptions_PassThrough',
                                              'isExclusive' => false))
                                  );
        $context = &Piece_Unity_Context::singleton();
        $context->setConfiguration($config);
        $session = &$context->getSession();
        @$session->start();
        $dispatcher = &new Piece_Unity_Plugin_Dispatcher_Continuation();
        $errorStack = &PEAR_ErrorStack::singleton('Exceptions_PassThrough');
        $errorStack->pushCallback(array('Piece_Unity_Error', 'handleError'));
        $dispatcher->invoke();
        $errorStack->popCallback();

        $this->assertTrue($errorStack->hasErrors('exception'));

        $error = $errorStack->pop();

        $this->assertEquals(-1, $error['code']);

        $_SERVER['REQUEST_URI'] = $oldScriptName;
        unset($_SERVER['SERVER_PORT']);
        unset($_SERVER['SERVER_NAME']);
    }

    /**
     * @since Method available since Release 1.5.0
     */
    function testShouldCreateAUriObjectBasedOnTheActiveFlowExecution()
    {
        $_SERVER['SERVER_NAME'] = 'example.org';
        $_SERVER['SERVER_PORT'] = '80';
        $oldScriptName = $_SERVER['REQUEST_URI'];
        $_SERVER['REQUEST_URI'] = '/entry/new.php';
        $config = &new Piece_Unity_Config();
        $config->setConfiguration('Dispatcher_Continuation', 'actionDirectory', $this->_cacheDirectory);
        $config->setConfiguration('Dispatcher_Continuation', 'configDirectory', $this->_cacheDirectory);
        $config->setConfiguration('Dispatcher_Continuation', 'cacheDirectory', $this->_cacheDirectory);
        $config->setConfiguration('Dispatcher_Continuation', 'useFlowMappings', true);
        $config->setConfiguration('Dispatcher_Continuation',
                                  'flowMappings',
                                  array(array('uri' => $_SERVER['REQUEST_URI'],
                                              'flowName' => 'Entry_New',
                                              'isExclusive' => false))
                                  );
        $context = &Piece_Unity_Context::singleton();
        $context->setConfiguration($config);
        $session = &$context->getSession();
        @$session->start();
        $dispatcher = &new Piece_Unity_Plugin_Dispatcher_Continuation();
        $dispatcher->invoke();
        $uri = &$context->getAttribute('uri');

        $this->assertEquals(strtolower('Piece_Unity_URI'),
                            strtolower(get_class($uri))
                            );
        $this->assertRegExp('!^http://example\.org/entry/new\.php\?_flowExecutionTicket=[0-9a-f]{40}&_event=baz$!',
                            $uri->getURI()
                            );

        $_SERVER['REQUEST_URI'] = $oldScriptName;
        unset($_SERVER['SERVER_PORT']);
        unset($_SERVER['SERVER_NAME']);
    }

    /**
     * @since Method available since Release 1.5.0
     */
    function testShouldCreateAUriObjectBasedOnAGivenPathAndFlowExecutionTicket()
    {
        $_SERVER['SERVER_NAME'] = 'example.org';
        $_SERVER['SERVER_PORT'] = '80';
        $oldScriptName = $_SERVER['REQUEST_URI'];
        $_SERVER['REQUEST_URI'] = '/user/authentication.php';
        $config = &new Piece_Unity_Config();
        $config->setConfiguration('Dispatcher_Continuation', 'actionDirectory', $this->_cacheDirectory);
        $config->setConfiguration('Dispatcher_Continuation', 'configDirectory', $this->_cacheDirectory);
        $config->setConfiguration('Dispatcher_Continuation', 'cacheDirectory', $this->_cacheDirectory);
        $config->setConfiguration('Dispatcher_Continuation', 'useFlowMappings', true);
        $config->setConfiguration('Dispatcher_Continuation',
                                  'flowMappings',
                                  array(array('uri' => '/entry/new.php',
                                              'flowName' => 'Entry_New',
                                              'isExclusive' => false),
                                        array('uri' => '/user/authentication.php',
                                              'flowName' => 'Entry_New',
                                              'isExclusive' => true),
                                        )
                                  );
        $context = &Piece_Unity_Context::singleton();
        $context->setConfiguration($config);
        $session = &$context->getSession();
        @$session->start();
        $dispatcher = &new Piece_Unity_Plugin_Dispatcher_Continuation();
        $dispatcher->invoke();
        Piece_Unity_Context::clear();
        $_SERVER['REQUEST_URI'] = '/entry/new.php';
        $_GET['_event'] = null;
        $_GET['_flowExecutionTicket'] = null;
        $context = &Piece_Unity_Context::singleton();
        $context->setConfiguration($config);
        $session = &$context->getSession();
        @$session->start();
        $dispatcher = &new Piece_Unity_Plugin_Dispatcher_Continuation();
        $dispatcher->invoke();
        $uri1 = &$context->getAttribute('uri');
        $uri2 = &Piece_Unity_Service_Continuation::createURI('qux', '/user/authentication.php');

        $this->assertEquals(strtolower('Piece_Unity_URI'),
                            strtolower(get_class($uri1))
                            );
        $this->assertRegExp('!^http://example\.org/entry/new\.php\?_flowExecutionTicket=[0-9a-f]{40}&_event=baz$!',
                            $uri1->getURI()
                            );
        $this->assertEquals(strtolower('Piece_Unity_URI'),
                            strtolower(get_class($uri2))
                            );
        $this->assertRegExp('!^http://example\.org/user/authentication\.php\?_flowExecutionTicket=[0-9a-f]{40}&_event=qux$!',
                            $uri2->getURI()
                            );

        $queryString1 = $uri1->getQueryString();
        $queryString2 = $uri2->getQueryString();

        $this->assertTrue($queryString1['_flowExecutionTicket'] != $queryString2['_flowExecutionTicket']);

        $_SERVER['REQUEST_URI'] = $oldScriptName;
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
