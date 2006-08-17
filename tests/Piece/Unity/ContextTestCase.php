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
 * @see        Piece_Unity_Context
 * @since      File available since Release 0.1.0
 */

require_once 'PHPUnit.php';
require_once 'Piece/Unity/Context.php';

// {{{ Piece_Unity_ContextTestCase

/**
 * TestCase for Piece_Unity_Context
 *
 * @package    Piece_Unity
 * @author     KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @copyright  2006 KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    Release: @package_version@
 * @link       http://iteman.typepad.jp/piece/
 * @see        Piece_Unity_Context
 * @since      Class available since Release 0.1.0
 */
class Piece_Unity_ContextTestCase extends PHPUnit_TestCase
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
        $_GET['_event'] = 'foo';
    }

    function tearDown()
    {
        unset($_GET['_event']);
        unset($_SERVER['REQUEST_METHOD']);
        Piece_Unity_Context::clear();
    }

    function testSettingView()
    {
        $context = &Piece_Unity_Context::singleton();
        $context->setView('foo');

        $this->assertEquals('foo', $context->getView());
    }

    function testInitializingProperties()
    {
        $context = &Piece_Unity_Context::singleton();

        $this->assertTrue(is_a($context->getRequest(), 'Piece_Unity_Request'));
        $this->assertEquals('foo', $context->getEventName());
        $this->assertTrue(is_a($context->getViewElement(), 'Piece_Unity_ViewElement'));
        $this->assertTrue(is_a($context->getSession(), 'Piece_Unity_Session'));
    }

    function testImportingEventNameFromRequestParameters()
    {
        $_GET['_event_bar'] = null;

        $context = &Piece_Unity_Context::singleton();

        $this->assertEquals('bar', $context->getEventName());

        unset($_GET['_event_bar']);
    }

    function testSettingEventNameKey()
    {
        $_GET['_foo'] = 'bar';

        $context = &Piece_Unity_Context::singleton();
        $context->setEventNameKey('_foo');

        $this->assertEquals('bar', $context->getEventName());

        unset($_GET['_foo']);
    }

    function testImportingEventNameFromRequestParametersWithSpecifiedEventNameKey()
    {
        $_GET['_foo'] = 'bar';
        $_GET['_foo_baz'] = null;

        $context = &Piece_Unity_Context::singleton();
        $context->setEventNameKey('_foo');

        $this->assertEquals('baz', $context->getEventName());

        unset($_GET['_foo_baz']);
        unset($_GET['_foo']);
    }

    function testGettingEventNameKey()
    {
        $context = &Piece_Unity_Context::singleton();
        $context->setEventNameKey('_foo');

        $this->assertEquals('_foo', $context->getEventNameKey());
    }

    function testEventNameFixation()
    {
        $context = &Piece_Unity_Context::singleton();
        $context->setEventName('bar');

        $this->assertEquals('bar', $context->getEventName());
    }

    function testGettingScriptName()
    {
        $previousScriptName = $_SERVER['SCRIPT_NAME'];
        $_SERVER['SCRIPT_NAME'] = '/path/to/foo.php';

        $context = &Piece_Unity_Context::singleton();

        $this->assertEquals('/path/to/foo.php', $context->getScriptName());

        $_SERVER['SCRIPT_NAME'] = $previousScriptName;
    }

    function testGettingBasePath()
    {
        $previousScriptName = $_SERVER['SCRIPT_NAME'];
        $_SERVER['SCRIPT_NAME'] = '/path/to/foo.php';

        $context = &Piece_Unity_Context::singleton();

        $this->assertEquals('/path/to', $context->getBasePath());

        $_SERVER['SCRIPT_NAME'] = $previousScriptName;
    }

    /**
     * @since Method available since Release 0.5.0
     */
    function testSettingScriptName()
    {
        $previousScriptName = $_SERVER['SCRIPT_NAME'];
        $_SERVER['SCRIPT_NAME'] = '/path/to/foo.php';

        $context = &Piece_Unity_Context::singleton();

        $this->assertEquals('/path/to/foo.php', $context->getScriptName());

        $context->setScriptName('/path/to/foo/bar.php');

        $this->assertEquals('/path/to/foo/bar.php', $context->getScriptName());

        $_SERVER['SCRIPT_NAME'] = $previousScriptName;
    }

    /**
     * @since Method available since Release 0.5.0
     */
    function testSettingBasePath()
    {
        $previousScriptName = $_SERVER['SCRIPT_NAME'];
        $_SERVER['SCRIPT_NAME'] = '/path/to/foo.php';

        $context = &Piece_Unity_Context::singleton();

        $this->assertEquals('/path/to', $context->getBasePath());

        $context->setBasePath('/path/to/foo/bar');

        $this->assertEquals('/path/to/foo/bar', $context->getBasePath());

        $_SERVER['SCRIPT_NAME'] = $previousScriptName;
    }

    /**
     * @since Method available since Release 0.5.0
     */
    function testGettingBasePathWithWindows()
    {
        $previousScriptName = $_SERVER['SCRIPT_NAME'];
        $_SERVER['SCRIPT_NAME'] = '//path/to/foo.php';

        $context = &Piece_Unity_Context::singleton();

        $this->assertEquals('/path/to', $context->getBasePath());

        $_SERVER['SCRIPT_NAME'] = $previousScriptName;
    }

    /**
     * @since Method available since Release 0.6.0
     */
    function testSettingAttribute()
    {
        $context = &Piece_Unity_Context::singleton();
        $context->setAttribute('foo', 'bar');

        $this->assertTrue($context->hasAttribute('foo'));
        $this->assertEquals('bar', $context->getAttribute('foo'));
    }

    /**
     * @since Method available since Release 0.6.0
     */
    function testSettingAttributeByReference()
    {
        $foo1 = &new stdClass();
        $context = &Piece_Unity_Context::singleton();
        $context->setAttributeByRef('foo', $foo1);
        $foo1->bar = 'baz';

        $this->assertTrue($context->hasAttribute('foo'));

        $foo2 = &$context->getAttribute('foo');

        $this->assertTrue(array_key_exists('bar', $foo2));
        $this->assertEquals('baz', $foo2->bar);
    }

    /**
     * @since Method available since Release 0.6.0
     */
    function testRemovingAttribute()
    {
        $context = &Piece_Unity_Context::singleton();
        $context->setAttribute('foo', 'bar');

        $this->assertTrue($context->hasAttribute('foo'));

        $context->removeAttribute('foo');

        $this->assertFalse($context->hasAttribute('foo'));
    }

    /**
     * @since Method available since Release 0.6.0
     */
    function testClearingAttributes()
    {
        $context = &Piece_Unity_Context::singleton();
        $context->setAttribute('foo', 'bar');
        $context->setAttribute('bar', 'baz');

        $this->assertTrue($context->hasAttribute('foo'));
        $this->assertTrue($context->hasAttribute('bar'));

        $context->clearAttributes();

        $this->assertFalse($context->hasAttribute('foo'));
        $this->assertFalse($context->hasAttribute('bar'));
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
