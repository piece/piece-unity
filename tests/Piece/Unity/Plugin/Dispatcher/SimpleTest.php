<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * PHP version 5
 *
 * Copyright (c) 2006-2007, 2009 KUBO Atsuhiro <kubo@iteman.jp>,
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
 * @copyright  2006-2007, 2009 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @since      File available since Release 0.1.0
 */

require_once 'Piece/Right/Validator/Factory.php';
require_once 'Piece/Right/Env.php';
require_once 'Piece/Right/Config/Factory.php';

// {{{ Piece_Unity_Plugin_Dispatcher_SimpleTest

/**
 * Some tests for Piece_Unity_Plugin_Dispatcher_Simple.
 *
 * @package    Piece_Unity
 * @copyright  2006-2007, 2009 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @since      Class available since Release 0.1.0
 */
class Piece_Unity_Plugin_Dispatcher_SimpleTest extends Piece_Unity_PHPUnit_TestCase
{

    // {{{ properties

    /**#@+
     * @access public
     */

    /**#@-*/

    /**#@+
     * @access protected
     */

    protected $serviceName = 'Piece_Unity_Plugin_Dispatcher_Simple';

    /**#@-*/

    /**#@+
     * @access private
     */

    /**#@-*/

    /**#@+
     * @access public
     */

    public function setUp()
    {
        parent::setUp();
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $this->cacheDirectory = dirname(__FILE__) . '/' . basename(__FILE__, '.php');
    }

    /**
     * @test
     */
    public function dispatchTheRequestToTheViewDirectly()
    {
        $_GET['_event'] = 'foo';
        $this->initializeContext();
        $dispatcher = $this->config->instantiateFeature($this->serviceName);
        $viewString = $dispatcher->invoke();

        $this->assertEquals('foo', $viewString);
    }

    /**
     * @test
     */
    public function dispatchTheRequestToTheAction()
    {
        $_GET['_event'] = 'SimpleExample';
        $GLOBALS['actionCalled'] = false;
        $this->initializeContext();
        $this->config->lazyAddExtension($this->serviceName, 'actionDirectory', $this->cacheDirectory);
        $dispatcher = $this->config->instantiateFeature($this->serviceName);
        $viewString = $dispatcher->invoke();

        $this->assertEquals('SimpleExample', $viewString);
        $this->assertTrue($GLOBALS['actionCalled']);
    }

    /**
     * @test
     */
    public function removeRelativePathsFromTheEventName()
    {
        $_GET['_event'] = '../RelativePathVulnerability';
        $GLOBALS['actionCalled'] = false;
        $GLOBALS['RelativePathVulnerabilityActionLoaded'] = false;
        $this->initializeContext();
        $this->config->lazyAddExtension($this->serviceName, 'actionDirectory', $this->cacheDirectory);
        $dispatcher = $this->config->instantiateFeature($this->serviceName);
        $viewString = $dispatcher->invoke();

        $this->assertEquals('../RelativePathVulnerability', $viewString);
        $this->assertFalse($GLOBALS['actionCalled']);
        $this->assertFalse($GLOBALS['RelativePathVulnerabilityActionLoaded']);
    }

    /**
     * @test
     * @since Method available since Release 0.8.0
     */
    public function setAValidationResultAsAViewElement()
    {
        $_GET['_event'] = 'SimpleValidation';
        $fields = array('first_name' => ' Foo ',
                        'last_name' => ' Bar ',
                        'email' => 'baz@example.org',
                        );
        foreach ($fields as $name => $value) {
            $_GET[$name] = $value;
        }

        $this->initializeContext();
        $validation = $this->context->getValidation();
        $validation->setConfigDirectory($this->cacheDirectory);
        $validation->setCacheDirectory($this->cacheDirectory);
        $this->config->lazyAddExtension($this->serviceName, 'actionDirectory', $this->cacheDirectory);
        $dispatcher = $this->config->instantiateFeature($this->serviceName);
        $dispatcher->invoke();

        $viewElement = $this->context->getViewElement();

        $this->assertTrue($viewElement->hasElement('__ValidationResults'));
        $this->assertEquals($validation->getResults(), $viewElement->getElement('__ValidationResults'));

        $user = $this->context->getAttribute('user');
        foreach ($fields as $field => $value) {
            $this->assertEquals(trim($value), $user->$field, $field);
        }
    }

    /**
     * @test
     * @since Method available since Release 1.2.0
     */
    public function useTheDefaultEventIfTheGivenEventNameIsEmpty()
    {
        $_GET['_event'] = '';
        $this->initializeContext();
        $this->config->lazyAddExtension($this->serviceName, 'useDefaultEvent', true);
        $this->config->lazyAddExtension($this->serviceName, 'defaultEventName', 'Index');
        $dispatcher = $this->config->instantiateFeature($this->serviceName);
        $viewString = $dispatcher->invoke();

        $this->assertEquals('Index', $viewString);
        $this->assertEquals('Index', $this->context->getEventName());
    }

    /**
     * @test
     * @since Method available since Release 1.2.0
     */
    public function useTheDefaultEventIfTheGivenEventNameIsNull()
    {
        $_GET['_event'] = null;
        $this->initializeContext();
        $this->config->lazyAddExtension($this->serviceName, 'useDefaultEvent', true);
        $this->config->lazyAddExtension($this->serviceName, 'defaultEventName', 'Index');
        $dispatcher = $this->config->instantiateFeature($this->serviceName);
        $viewString = $dispatcher->invoke();

        $this->assertEquals('Index', $viewString);
        $this->assertEquals('Index', $this->context->getEventName());
    }

    /**
     * @test
     * @since Method available since Release 1.2.0
     */
    public function notUseTheDefaultEventIfTheGivenEventNameIsNotEmptyOrNull()
    {
        $_GET['_event'] = 'Foo';
        $this->initializeContext();
        $this->config->lazyAddExtension($this->serviceName, 'useDefaultEvent', true);
        $this->config->lazyAddExtension($this->serviceName, 'defaultEventName', 'Index');
        $dispatcher = $this->config->instantiateFeature($this->serviceName);
        $viewString = $dispatcher->invoke();

        $this->assertEquals('Foo', $viewString);
        $this->assertEquals('Foo', $this->context->getEventName());
    }

    /**
     * @test
     * @since Method available since Release 1.2.0
     */
    public function notUseTheDefaultEventIfTheOptionIsDisabled()
    {
        $_GET['_event'] = 'Foo';
        $this->initializeContext();
        $this->config->lazyAddExtension($this->serviceName, 'useDefaultEvent', false);
        $this->config->lazyAddExtension($this->serviceName, 'defaultEventName', 'Index');
        $dispatcher = $this->config->instantiateFeature($this->serviceName);
        $viewString = $dispatcher->invoke();

        $this->assertEquals('Foo', $viewString);
        $this->assertEquals('Foo', $this->context->getEventName());
    }

    /**
     * @test
     * @since Method available since Release 1.2.0
     */
    public function returnAnyViewStringWhichShouldBeRendered()
    {
        $_GET['_event'] = 'ActionShouldBeAbleToReturnViewString';
        $this->initializeContext();
        $this->config->lazyAddExtension($this->serviceName, 'actionDirectory', $this->cacheDirectory);
        $dispatcher = $this->config->instantiateFeature($this->serviceName);
        $viewString = $dispatcher->invoke();
        $eventName = $this->context->getEventName();

        $this->assertNotEquals($viewString, $eventName);
        $this->assertEquals('Foo', $viewString);
        $this->assertEquals('ActionShouldBeAbleToReturnViewString', $eventName);
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
