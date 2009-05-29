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

// {{{ Piece_Unity_ContextTest

/**
 * TestCase for Piece_Unity_Context
 *
 * @package    Piece_Unity
 * @copyright  2006-2007, 2009 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @since      Class available since Release 0.1.0
 */
class Piece_Unity_ContextTest extends Piece_Unity_PHPUnit_TestCase
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

    private $_httpStatus;

    /**#@-*/

    /**#@+
     * @access public
     */

    public function setUp()
    {
        parent::setUp();
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_GET['_event'] = 'foo';
    }

    /**
     * @test
     */
    public function setTheView()
    {
        $context = Piece_Unity_Context::singleton();
        $context->setView('foo');

        $this->assertEquals('foo', $context->getView());
    }

    /**
     * @test
     */
    public function initializeProperties()
    {
        $context = Piece_Unity_Context::singleton();

        $this->assertType('Piece_Unity_Request', $context->getRequest());
        $this->assertEquals('foo', $context->getEventName());
        $this->assertType('Piece_Unity_ViewElement', $context->getViewElement());
        $this->assertType('Piece_Unity_Session', $context->getSession());
    }

    /**
     * @test
     */
    public function importTheEventNameFromRequestParameters()
    {
        $_GET['_event_bar'] = null;

        $this->assertEquals('bar',
                            Piece_Unity_Context::singleton()->getEventName()
                            );
    }

    /**
     * @test
     */
    public function setTheEventNameKey()
    {
        $_GET['_foo'] = 'bar';

        $context = Piece_Unity_Context::singleton();
        $context->setEventNameKey('_foo');

        $this->assertEquals('bar', $context->getEventName());
    }

    /**
     * @test
     */
    public function importTheEventNameFromRequestParametersWithAGivenEventNameKey()
    {
        $_GET['_foo'] = 'bar';
        $_GET['_foo_baz'] = null;

        $context = Piece_Unity_Context::singleton();
        $context->setEventNameKey('_foo');

        $this->assertEquals('baz', $context->getEventName());
    }

    /**
     * @test
     */
    public function getTheEventNameKey()
    {
        $context = Piece_Unity_Context::singleton();
        $context->setEventNameKey('_foo');

        $this->assertEquals('_foo', $context->getEventNameKey());
    }

    /**
     * @test
     */
    public function setTheEventNameBySeteventname()
    {
        $context = Piece_Unity_Context::singleton();
        $context->setEventName('bar');

        $this->assertEquals('bar', $context->getEventName());
    }

    /**
     * @test
     */
    public function getTheBasePathOfTheRequestUri()
    {
        $_SERVER['REQUEST_URI'] = '/path/to/foo.php';

        $this->assertEquals('/path/to',
                            Piece_Unity_Context::singleton()->getBasePath()
                            );
    }

    /**
     * @test
     * @since Method available since Release 0.5.0
     */
    public function setTheBasePathOfTheRequestUri()
    {
        $_SERVER['REQUEST_URI'] = '/path/to/foo.php';

        $context = Piece_Unity_Context::singleton();

        $this->assertEquals('/path/to', $context->getBasePath());

        $context->setBasePath('/path/to/foo/bar');

        $this->assertEquals('/path/to/foo/bar', $context->getBasePath());
    }

    /**
     * @test
     * @since Method available since Release 0.5.0
     */
    public function setTheBasePathOfTheRequestUriOnWindows()
    {
        $_SERVER['REQUEST_URI'] = '//path/to/foo.php';

        $this->assertEquals('/path/to',
                            Piece_Unity_Context::singleton()->getBasePath()
                            );
    }

    /**
     * @test
     * @since Method available since Release 0.6.0
     */
    public function setAnAttribute()
    {
        $context = Piece_Unity_Context::singleton();
        $context->setAttribute('foo', 'bar');

        $this->assertTrue($context->hasAttribute('foo'));
        $this->assertEquals('bar', $context->getAttribute('foo'));
    }

    /**
     * @test
     * @since Method available since Release 0.6.0
     */
    public function setAnAttributeByReference()
    {
        $foo1 = array('bar' => 'baz');
        $context = Piece_Unity_Context::singleton();
        $context->setAttributeByRef('foo', $foo1);
        $foo1['bar'] = 'qux';

        $this->assertTrue($context->hasAttribute('foo'));

        $foo2 = $context->getAttribute('foo');

        $this->assertArrayHasKey('bar', $foo2);
        $this->assertEquals('qux', $foo2['bar']);
    }

    /**
     * @test
     * @since Method available since Release 0.6.0
     */
    public function removeAnAttribute()
    {
        $context = Piece_Unity_Context::singleton();
        $context->setAttribute('foo', 'bar');

        $this->assertTrue($context->hasAttribute('foo'));

        $context->removeAttribute('foo');

        $this->assertFalse($context->hasAttribute('foo'));
    }

    /**
     * @test
     * @since Method available since Release 0.6.0
     */
    public function clearAllAttributes()
    {
        $context = Piece_Unity_Context::singleton();
        $context->setAttribute('foo', 'bar');
        $context->setAttribute('bar', 'baz');

        $this->assertTrue($context->hasAttribute('foo'));
        $this->assertTrue($context->hasAttribute('bar'));

        $context->clearAttributes();

        $this->assertFalse($context->hasAttribute('foo'));
        $this->assertFalse($context->hasAttribute('bar'));
    }

    /**
     * @test
     * @since Method available since Release 0.9.0
     */
    public function supportEventNamesByImageInputType()
    {
        unset($_GET['_event']);

        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['_event_foo_x'] = '19';
        $_POST['_event_foo_y'] = '99';

        $this->assertEquals('foo',
                            Piece_Unity_Context::singleton()->getEventName()
                            );
    }

    /**
     * @test
     * @since Method available since Release 0.9.0
     */
    public function workWithBrokenEventNames()
    {
        unset($_GET['_event']);

        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['_event_foo_x'] = '19';
        $_POST['_event_foo_z'] = '99';

        $this->assertEquals('foo_z',
                            Piece_Unity_Context::singleton()->getEventName()
                            );
    }

    /**
     * @test
     * @since Method available since Release 1.5.0
     */
    public function sendTheStatusLineOfTheResponse()
    {
        $_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.1';
        $context = Piece_Unity_Context::singleton();
        $context->sendHTTPStatus(404);

        $this->assertAttributeEquals('HTTP/1.1 404 Not Found',
                                     '_sentStatusLine',
                                     'Stagehand_HTTP_Status'
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
