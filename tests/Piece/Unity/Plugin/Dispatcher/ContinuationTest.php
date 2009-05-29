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
 * @since      File available since Release 0.1.0
 */

require_once 'Piece/Flow/Continuation/Server.php';
require_once 'Piece/Flow/Env.php';
require_once 'Piece/Right/Config/Factory.php';
require_once 'Piece/Right/Validator/Factory.php';

// {{{ Piece_Unity_Plugin_Dispatcher_ContinuationTest

/**
 * Some tests for Piece_Unity_Plugin_Dispatcher_Continuation.
 *
 * @package    Piece_Unity
 * @copyright  2006-2009 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @since      Class available since Release 0.1.0
 */
class Piece_Unity_Plugin_Dispatcher_ContinuationTest extends Piece_Unity_PHPUnit_TestCase
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

    private $_exclusiveDirectory;

    /**#@-*/

    /**#@+
     * @access public
     */

    public function setUp()
    {
        parent::setUp();
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SESSION = array();
        $this->_exclusiveDirectory = dirname(__FILE__) . '/' . basename(__FILE__, '.php');
    }

    public function tearDown()
    {
        $cache = new Cache_Lite_File(array('cacheDir' => $this->_exclusiveDirectory . '/',
                                            'masterFile' => '',
                                            'automaticSerialization' => true,
                                            'errorHandlingAPIBreak' => true)
                                      );
        $cache->clean();
    }

    /**
     * @test
     */
    public function continueTheExistingFlowExecution()
    {
        $_GET['_flow'] = 'Counter';
        $config = new Piece_Unity_Config();
        $config->setConfiguration('Dispatcher_Continuation', 'actionDirectory', $this->_exclusiveDirectory);
        $config->setConfiguration('Dispatcher_Continuation', 'cacheDirectory', $this->_exclusiveDirectory);
        $config->setConfiguration('Dispatcher_Continuation', 'flowDefinitions', array(array('name' => 'Counter', 'file' => $this->_exclusiveDirectory . '/Counter.yaml', 'isExclusive' => true)));
        $context = Piece_Unity_Context::singleton();
        $context->setConfiguration($config);
        $session = $context->getSession();
        @$session->start();

        $dispatcher = new Piece_Unity_Plugin_Dispatcher_Continuation();

        $this->assertEquals('Counter', $dispatcher->invoke());

        $continuationServer = $session->getAttribute($this->readAttribute('Piece_Unity_Plugin_Dispatcher_Continuation', '_sessionKey'));
        $continuationService = $continuationServer->createService();
        $viewElement = $context->getViewElement();
        $flowExecutionTicket = $viewElement->getElement('__flowExecutionTicket');

        $this->assertType('Piece_Flow_Continuation_Server', $continuationServer);
        $this->assertTrue($continuationService->hasAttribute('counter'));
        $this->assertEquals(0, $continuationService->getAttribute('counter'));

        Piece_Unity_Context::clear();
        $_GET['_event'] = 'increase';
        $_GET['_flowExecutionTicket'] = $flowExecutionTicket;
        $context = Piece_Unity_Context::singleton();
        $context->setConfiguration($config);
        $dispatcher = new Piece_Unity_Plugin_Dispatcher_Continuation();
        $session = $context->getSession();
        @$session->start();
        $session->setAttribute($this->readAttribute('Piece_Unity_Plugin_Dispatcher_Continuation', '_sessionKey'), $continuationServer);
        $dispatcher->invoke();
        $continuationService = $continuationServer->createService();

        $this->assertEquals(1, $continuationService->getAttribute('counter'));

        $dispatcher->invoke();

        $this->assertEquals(2, $continuationService->getAttribute('counter'));
        $this->assertEquals('Finish', $dispatcher->invoke());

        $continuationService = $continuationServer->createService();

        $this->assertEquals(3, $continuationService->getAttribute('counter'));
    }

    /**
     * @test
     * @expectedException Stagehand_LegacyError_PEARErrorStack_Exception
     */
    public function raiseAnExceptionIfTheConfigurationIsInvalid()
    {
        $_GET['_flow'] = 'Counter';
        $config = new Piece_Unity_Config();
        $config->setConfiguration('Dispatcher_Continuation', 'actionDirectory', $this->_exclusiveDirectory);
        $config->setConfiguration('Dispatcher_Continuation', 'enableSingleFlowMode', true);
        $config->setConfiguration('Dispatcher_Continuation', 'cacheDirectory', $this->_exclusiveDirectory);
        $config->setConfiguration('Dispatcher_Continuation', 'flowDefinitions',
                                  array(array('name' => 'Counter', 'file' => $this->_exclusiveDirectory . '/Counter.yaml', 'isExclusive' => true),
                                        array('name' => 'SeconfCounter', 'file' => $this->_exclusiveDirectory . '/Counter.yaml', 'isExclusive' => true))
                                  );
        $context = Piece_Unity_Context::singleton();
        $context->setConfiguration($config);
        @$context->getSession()->start();
        $dispatcher = new Piece_Unity_Plugin_Dispatcher_Continuation();
        $dispatcher->invoke();
    }

    /**
     * @test
     */
    public function raiseAnExceptionIfTheContinuationServerWasAlreadyShutdown()
    {
        $config = new Piece_Unity_Config();
        $config->setConfiguration('Dispatcher_Continuation', 'actionDirectory', $this->_exclusiveDirectory);
        $config->setConfiguration('Dispatcher_Continuation', 'enableSingleFlowMode', true);
        $config->setConfiguration('Dispatcher_Continuation', 'cacheDirectory', $this->_exclusiveDirectory);
        $config->setConfiguration('Dispatcher_Continuation', 'flowDefinitions',
                                  array(array('name' => 'Counter', 'file' => $this->_exclusiveDirectory . '/Counter.yaml', 'isExclusive' => false))
                                  );
        $context = Piece_Unity_Context::singleton();
        $context->setConfiguration($config);
        @$context->getSession()->start();

        $dispatcher = new Piece_Unity_Plugin_Dispatcher_Continuation();

        $this->assertEquals('Counter', $dispatcher->invoke());

        $continuationServer = $context->getSession()->getAttribute($this->readAttribute('Piece_Unity_Plugin_Dispatcher_Continuation', '_sessionKey'));
        $viewElement = $context->getViewElement();
        $flowExecutionTicket = $viewElement->getElement('__flowExecutionTicket');
        Piece_Unity_Context::clear();
        $_GET['_event'] = 'increase';
        $_GET['_flowExecutionTicket'] = $flowExecutionTicket;
        $context = Piece_Unity_Context::singleton();
        $context->setConfiguration($config);
        @$context->getSession()->start();
        $dispatcher = new Piece_Unity_Plugin_Dispatcher_Continuation();
        $context->getSession()->setAttribute($this->readAttribute('Piece_Unity_Plugin_Dispatcher_Continuation', '_sessionKey'), $continuationServer);
        $dispatcher->invoke();
        $dispatcher->invoke();
        $dispatcher->invoke();

        try {
            $dispatcher->invoke();
            $this->fail('An expected exception has not been raised');
        } catch (Stagehand_LegacyError_PEARErrorStack_Exception $e) {
        }
    }

    /**
     * @test
     */
    public function setAContinuationServiceObjectAsAViewElement()
    {
        $_GET['_bar'] = 'Counter';

        $config = new Piece_Unity_Config();
        $config->setConfiguration('Dispatcher_Continuation', 'actionDirectory', $this->_exclusiveDirectory);
        $config->setConfiguration('Dispatcher_Continuation', 'cacheDirectory', $this->_exclusiveDirectory);
        $config->setConfiguration('Dispatcher_Continuation', 'flowDefinitions', array(array('name' => 'Counter', 'file' => $this->_exclusiveDirectory . '/Counter.yaml', 'isExclusive' => true)));
        $config->setConfiguration('Dispatcher_Continuation', 'flowExecutionTicketKey', '_foo');
        $config->setConfiguration('Dispatcher_Continuation', 'flowNameKey', '_bar');
        $config->setConfiguration('Renderer_PHP', 'templateDirectory', $this->_exclusiveDirectory);
        $context = Piece_Unity_Context::singleton();
        $context->setConfiguration($config);
        @$context->getSession()->start();

        $dispatcher = new Piece_Unity_Plugin_Dispatcher_Continuation();
        $context->setView($dispatcher->invoke());
        $dispatcher->publish();

        $renderer = new Piece_Unity_Plugin_Renderer_PHP();
        ob_start();
        $renderer->render();
        $buffer = ob_get_contents();
        ob_end_clean();

        $this->assertEquals('OK', $buffer);
    }

    /**
     * @test
     */
    public function mapAUriToAFlowByFlowDefinitions()
    {
        $_GET['_flow'] = 'Foo';
        $config = new Piece_Unity_Config();
        $config->setConfiguration('Dispatcher_Continuation', 'actionDirectory', $this->_exclusiveDirectory);
        $config->setConfiguration('Dispatcher_Continuation', 'cacheDirectory', $this->_exclusiveDirectory);
        $config->setConfiguration('Dispatcher_Continuation', 'flowDefinitions', array(array('name' => 'Counter', 'file' => $this->_exclusiveDirectory . '/Counter.yaml', 'isExclusive' => true)));
        $config->setConfiguration('Dispatcher_Continuation', 'flowName', 'Counter');
        $context = Piece_Unity_Context::singleton();
        $context->setConfiguration($config);
        @$context->getSession()->start();

        $dispatcher = new Piece_Unity_Plugin_Dispatcher_Continuation();
        $viewString = $dispatcher->invoke();

        $this->assertEquals('Counter', $viewString);
    }

    /**
     * @test
     * @since Method available since Release 0.8.0
     */
    public function setResultObjectsAsViewElementsAndFlowAttributes()
    {
        $_GET['_flow'] = 'ContinuationValidation';
        $fields = array('first_name' => ' Foo ',
                        'last_name' => ' Bar ',
                        'email' => 'baz@example.org',
                        );
        foreach ($fields as $name => $value) {
            $_GET[$name] = $value;
        }

        $config = new Piece_Unity_Config();
        $config->setConfiguration('Dispatcher_Continuation', 'actionDirectory', $this->_exclusiveDirectory);
        $config->setConfiguration('Dispatcher_Continuation', 'cacheDirectory', $this->_exclusiveDirectory);
        $config->setConfiguration('Dispatcher_Continuation', 'flowDefinitions', array(array('name' => 'ContinuationValidation', 'file' => $this->_exclusiveDirectory . '/ContinuationValidation.yaml', 'isExclusive' => true)));
        $context = Piece_Unity_Context::singleton();
        $context->setConfiguration($config);
        @$context->getSession()->start();
        $dispatcher = new Piece_Unity_Plugin_Dispatcher_Continuation();

        $this->assertEquals('Form', $dispatcher->invoke());

        $continuationServer = $context->getSession()->getAttribute($this->readAttribute('Piece_Unity_Plugin_Dispatcher_Continuation', '_sessionKey'));
        $flowExecutionTicket = $context->getViewElement()->getElement('__flowExecutionTicket');

        Piece_Unity_Context::clear();
        $_GET['_event'] = 'validate';
        $_GET['_flowExecutionTicket'] = $flowExecutionTicket;
        $context = Piece_Unity_Context::singleton();
        $context->setConfiguration($config);
        $session = $context->getSession();
        @$session->start();
        $session->setAttribute($this->readAttribute('Piece_Unity_Plugin_Dispatcher_Continuation', '_sessionKey'), $continuationServer);
        $validation = $context->getValidation();
        $validation->setConfigDirectory($this->_exclusiveDirectory);
        $validation->setCacheDirectory($this->_exclusiveDirectory);
        $dispatcher = new Piece_Unity_Plugin_Dispatcher_Continuation();

        $this->assertEquals('Success', $dispatcher->invoke());

        $viewElement = $context->getViewElement();

        $this->assertTrue($viewElement->hasElement('__ValidationResults'));
        $this->assertEquals($validation->getResults(), $viewElement->getElement('__ValidationResults'));

        $continuationService = $context->getContinuation();

        $this->assertTrue($continuationService->hasAttribute('__ValidationResults'));
        $this->assertEquals($validation->getResults(), $continuationService->getAttribute('__ValidationResults'));

        $user = $context->getAttribute('user');
        foreach ($fields as $field => $value) {
            $this->assertEquals(trim($value), $user->$field, $field);
        }
    }

    /**
     * @test
     * @since Method available since Release 1.1.0
     */
    public function returnTheFallbackUriWhenTheFlowExecutionHasExpiredIfGcIsEnabled()
    {
        $_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.1';
        $config = new Piece_Unity_Config();
        $config->setConfiguration('Dispatcher_Continuation', 'flowName', 'FlowExecutionExpired');
        $config->setConfiguration('Dispatcher_Continuation', 'actionDirectory', $this->_exclusiveDirectory);
        $config->setConfiguration('Dispatcher_Continuation', 'cacheDirectory', $this->_exclusiveDirectory);
        $config->setConfiguration('Dispatcher_Continuation', 'flowDefinitions', array(array('name' => 'FlowExecutionExpired', 'file' => $this->_exclusiveDirectory . '/FlowExecutionExpired.yaml', 'isExclusive' => false)));
        $config->setConfiguration('Dispatcher_Continuation', 'enableGC', true);
        $config->setConfiguration('Dispatcher_Continuation', 'gcExpirationTime', 1);
        $config->setConfiguration('Dispatcher_Continuation', 'useGCFallback', true);
        $config->setConfiguration('Dispatcher_Continuation', 'gcFallbackURI', 'http://www.example.org/');
        $context = Piece_Unity_Context::singleton();
        $context->setConfiguration($config);
        @$context->getSession()->start();
        $dispatcher = new Piece_Unity_Plugin_Dispatcher_Continuation();

        $this->assertEquals('Form', $dispatcher->invoke());

        $continuationServer = $context->getSession()->getAttribute($this->readAttribute('Piece_Unity_Plugin_Dispatcher_Continuation', '_sessionKey'));
        $flowExecutionTicket = $context->getViewElement()->getElement('__flowExecutionTicket');

        Piece_Unity_Context::clear();
        $_GET['_flowExecutionTicket'] = $flowExecutionTicket;
        $context = Piece_Unity_Context::singleton();
        $context->setConfiguration($config);
        $session = $context->getSession();
        @$session->start();
        $session->setAttribute($this->readAttribute('Piece_Unity_Plugin_Dispatcher_Continuation', '_sessionKey'), $continuationServer);
        $dispatcher = new Piece_Unity_Plugin_Dispatcher_Continuation();
        sleep(2);

        $this->assertEquals('http://www.example.org/', $dispatcher->invoke());
        $this->assertEquals('HTTP/1.1 302 Found',
                            $this->readAttribute('Stagehand_HTTP_Status', '_sentStatusLine')
                            );
        $this->assertTrue($session->hasAttribute('_flowExecutionExpired'));
        $this->assertTrue($session->getAttribute('_flowExecutionExpired'));
    }

    /**
     * @test
     * @since Method available since Release 1.3.0
     */
    public function mapAUriToAFlowByFlowMappings()
    {
        $_SERVER['SERVER_NAME'] = 'example.org';
        $_SERVER['SERVER_PORT'] = '80';
        $_SERVER['REQUEST_URI'] = '/entry/new.php';
        $config = new Piece_Unity_Config();
        $config->setConfiguration('Dispatcher_Continuation', 'actionDirectory', $this->_exclusiveDirectory);
        $config->setConfiguration('Dispatcher_Continuation', 'configDirectory', $this->_exclusiveDirectory);
        $config->setConfiguration('Dispatcher_Continuation', 'cacheDirectory', $this->_exclusiveDirectory);
        $config->setConfiguration('Dispatcher_Continuation', 'useFlowMappings', true);
        $config->setConfiguration('Dispatcher_Continuation', 'useFullFlowNameAsViewPrefix', false);
        $config->setConfiguration('Dispatcher_Continuation',
                                  'flowMappings',
                                  array(array('uri' => '/entry/new.php',
                                              'flowName' => 'Entry_New',
                                              'isExclusive' => false))
                                  );
        $context = Piece_Unity_Context::singleton();
        $context->setConfiguration($config);
        @$context->getSession()->start();
        $dispatcher = new Piece_Unity_Plugin_Dispatcher_Continuation();
        $viewString = $dispatcher->invoke();

        $this->assertEquals('Entry_New', $viewString);
        $this->assertEquals('bar', $context->getAttribute('foo'));
    }

    /**
     * @test
     * @since Method available since Release 1.3.1
     */
    public function mapAUriToAFlowByFlowMappingsIfABackendServerIsAccessedViaReverseProxy()
    {
        $_SERVER['HTTP_X_FORWARDED_FOR'] = '1.2.3.4';
        $_SERVER['HTTP_X_FORWARDED_SERVER'] = 'example.org';
        $_SERVER['SERVER_NAME'] = 'foo.example.org';
        $_SERVER['SERVER_PORT'] = '8201';
        $_SERVER['REQUEST_URI'] = '/entry/new.php';
        $config = new Piece_Unity_Config();
        $config->setConfiguration('Dispatcher_Continuation', 'actionDirectory', $this->_exclusiveDirectory);
        $config->setConfiguration('Dispatcher_Continuation', 'configDirectory', $this->_exclusiveDirectory);
        $config->setConfiguration('Dispatcher_Continuation', 'cacheDirectory', $this->_exclusiveDirectory);
        $config->setConfiguration('Dispatcher_Continuation', 'useFlowMappings', true);
        $config->setConfiguration('Dispatcher_Continuation',
                                  'flowMappings',
                                  array(array('uri' => '/entry/new.php',
                                              'flowName' => 'Entry_New',
                                              'isExclusive' => false))
                                  );
        $context = Piece_Unity_Context::singleton();
        $context->setConfiguration($config);
        $context->setProxyPath('/crud');
        $context->setScriptName($context->getProxyPath() . $context->getScriptName());
        @$context->getSession()->start();
        $dispatcher = new Piece_Unity_Plugin_Dispatcher_Continuation();
        $dispatcher->invoke();
    }

    /**
     * @test
     * @since Method available since Release 1.3.1
     */
    public function mapAUriToAFlowByFlowMappingsIfABackendServerIsAccessedDirectly()
    {
        $_SERVER['SERVER_NAME'] = 'example.org';
        $_SERVER['SERVER_PORT'] = '80';
        $_SERVER['REQUEST_URI'] = '/entry/new.php';
        $config = new Piece_Unity_Config();
        $config->setConfiguration('Dispatcher_Continuation', 'actionDirectory', $this->_exclusiveDirectory);
        $config->setConfiguration('Dispatcher_Continuation', 'configDirectory', $this->_exclusiveDirectory);
        $config->setConfiguration('Dispatcher_Continuation', 'cacheDirectory', $this->_exclusiveDirectory);
        $config->setConfiguration('Dispatcher_Continuation', 'useFlowMappings', true);
        $config->setConfiguration('Dispatcher_Continuation',
                                  'flowMappings',
                                  array(array('uri' => '/entry/new.php',
                                              'flowName' => 'Entry_New',
                                              'isExclusive' => false))
                                  );
        $context = Piece_Unity_Context::singleton();
        $context->setConfiguration($config);
        $context->setProxyPath('/crud');
        @$context->getSession()->start();
        $dispatcher = new Piece_Unity_Plugin_Dispatcher_Continuation();
        $dispatcher->invoke();
    }

    /**
     * @test
     * @since Method available since Release 1.4.0
     */
    public function useTheFullFlowNameAsTheViewPrefixIfUseFullFlowNameAsViewPrefixIsTrue()
    {
        $_SERVER['SERVER_NAME'] = 'example.org';
        $_SERVER['SERVER_PORT'] = '80';
        $_SERVER['REQUEST_URI'] = '/entry/new.php';
        $config = new Piece_Unity_Config();
        $config->setConfiguration('Dispatcher_Continuation', 'actionDirectory', $this->_exclusiveDirectory);
        $config->setConfiguration('Dispatcher_Continuation', 'configDirectory', $this->_exclusiveDirectory);
        $config->setConfiguration('Dispatcher_Continuation', 'cacheDirectory', $this->_exclusiveDirectory);
        $config->setConfiguration('Dispatcher_Continuation', 'useFlowMappings', true);
        $config->setConfiguration('Dispatcher_Continuation', 'useFullFlowNameAsViewPrefix', true);
        $config->setConfiguration('Dispatcher_Continuation',
                                  'flowMappings',
                                  array(array('uri' => '/entry/new.php',
                                              'flowName' => 'Entry_New',
                                              'isExclusive' => false))
                                  );
        $context = Piece_Unity_Context::singleton();
        $context->setConfiguration($config);
        @$context->getSession()->start();
        $dispatcher = new Piece_Unity_Plugin_Dispatcher_Continuation();
        $viewString = $dispatcher->invoke();

        $this->assertEquals('Entry_New_New', $viewString);
        $this->assertEquals('bar', $context->getAttribute('foo'));
    }

    /**
     * @test
     * @expectedException Piece_Unity_Plugin_Dispatcher_ContinuationTest_Exceptions_PassThrough_Exception
     * @since Method available since Release 1.5.0
     */
    public function passThroughAnExceptionRaisedFromAnyPackage()
    {
        $_SERVER['SERVER_NAME'] = 'example.org';
        $_SERVER['SERVER_PORT'] = '80';
        $_SERVER['REQUEST_URI'] = '/exceptions/pass-through.php';
        $config = new Piece_Unity_Config();
        $config->setConfiguration('Dispatcher_Continuation', 'actionDirectory', $this->_exclusiveDirectory);
        $config->setConfiguration('Dispatcher_Continuation', 'configDirectory', $this->_exclusiveDirectory);
        $config->setConfiguration('Dispatcher_Continuation', 'cacheDirectory', $this->_exclusiveDirectory);
        $config->setConfiguration('Dispatcher_Continuation', 'useFlowMappings', true);
        $config->setConfiguration('Dispatcher_Continuation',
                                  'flowMappings',
                                  array(array('uri' => '/exceptions/pass-through.php',
                                              'flowName' => 'Exceptions_PassThrough',
                                              'isExclusive' => false))
                                  );
        $context = Piece_Unity_Context::singleton();
        $context->setConfiguration($config);
        @$context->getSession()->start();
        $dispatcher = new Piece_Unity_Plugin_Dispatcher_Continuation();
        $dispatcher->invoke();
    }

    /**
     * @test
     * @since Method available since Release 1.5.0
     */
    public function createAUriObjectBasedOnTheActiveFlowExecution()
    {
        $_SERVER['SERVER_NAME'] = 'example.org';
        $_SERVER['SERVER_PORT'] = '80';
        $_SERVER['REQUEST_URI'] = '/entry/new.php';
        $config = new Piece_Unity_Config();
        $config->setConfiguration('Dispatcher_Continuation', 'actionDirectory', $this->_exclusiveDirectory);
        $config->setConfiguration('Dispatcher_Continuation', 'configDirectory', $this->_exclusiveDirectory);
        $config->setConfiguration('Dispatcher_Continuation', 'cacheDirectory', $this->_exclusiveDirectory);
        $config->setConfiguration('Dispatcher_Continuation', 'useFlowMappings', true);
        $config->setConfiguration('Dispatcher_Continuation',
                                  'flowMappings',
                                  array(array('uri' => $_SERVER['REQUEST_URI'],
                                              'flowName' => 'Entry_New',
                                              'isExclusive' => false))
                                  );
        $context = Piece_Unity_Context::singleton();
        $context->setConfiguration($config);
        @$context->getSession()->start();
        $dispatcher = new Piece_Unity_Plugin_Dispatcher_Continuation();
        $dispatcher->invoke();
        $uri = $context->getAttribute('uri');

        $this->assertType('Piece_Unity_URI', $uri);
        $this->assertRegExp('!^http://example\.org/entry/new\.php\?_flowExecutionTicket=[0-9a-f]{40}&_event=baz$!',
                            $uri->getURI()
                            );
    }

    /**
     * @test
     * @since Method available since Release 1.5.0
     */
    public function createAUriObjectBasedOnAGivenPathAndFlowExecutionTicket()
    {
        $_SERVER['SERVER_NAME'] = 'example.org';
        $_SERVER['SERVER_PORT'] = '80';
        $_SERVER['REQUEST_URI'] = '/user/authentication.php';
        $config = new Piece_Unity_Config();
        $config->setConfiguration('Dispatcher_Continuation', 'actionDirectory', $this->_exclusiveDirectory);
        $config->setConfiguration('Dispatcher_Continuation', 'configDirectory', $this->_exclusiveDirectory);
        $config->setConfiguration('Dispatcher_Continuation', 'cacheDirectory', $this->_exclusiveDirectory);
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
        $context = Piece_Unity_Context::singleton();
        $context->setConfiguration($config);
        @$context->getSession()->start();
        $dispatcher = new Piece_Unity_Plugin_Dispatcher_Continuation();
        $dispatcher->invoke();
        Piece_Unity_Context::clear();
        $_SERVER['REQUEST_URI'] = '/entry/new.php';
        $_GET['_event'] = null;
        $_GET['_flowExecutionTicket'] = null;
        $context = Piece_Unity_Context::singleton();
        $context->setConfiguration($config);
        @$context->getSession()->start();
        $dispatcher = new Piece_Unity_Plugin_Dispatcher_Continuation();
        $dispatcher->invoke();
        $uri1 = $context->getAttribute('uri');
        $uri2 = Piece_Unity_Service_Continuation::createURI('qux', '/user/authentication.php');

        $this->assertType('Piece_Unity_URI', $uri1);
        $this->assertRegExp('!^http://example\.org/entry/new\.php\?_flowExecutionTicket=[0-9a-f]{40}&_event=baz$!',
                            $uri1->getURI()
                            );
        $this->assertType('Piece_Unity_URI', $uri2);
        $this->assertRegExp('!^http://example\.org/user/authentication\.php\?_flowExecutionTicket=[0-9a-f]{40}&_event=qux$!',
                            $uri2->getURI()
                            );

        $queryVariables1 = $uri1->getQueryVariables();
        $queryVariables2 = $uri2->getQueryVariables();

        $this->assertNotEquals($queryVariables1['_flowExecutionTicket'],
                               $queryVariables2['_flowExecutionTicket']
                               );
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
