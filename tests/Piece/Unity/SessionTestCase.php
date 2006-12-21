<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * PHP versions 4 and 5
 *
 * Copyright (c) 2006 KUBO Atsuhiro <iteman@users.sourceforge.net>,
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
 * @link       http://piece-framework.com/piece-unity/
 * @see        Piece_Unity_Session
 * @since      File available since Release 0.2.0
 */

require_once 'PHPUnit.php';
require_once 'Piece/Unity/Session.php';
require_once 'Piece/Unity/Session/Preload.php';
require_once dirname(__FILE__) . '/SessionTestCase/Loader.php';

// {{{ Piece_Unity_SessionTestCase

/**
 * TestCase for Piece_Unity_Session
 *
 * @package    Piece_Unity
 * @author     KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @copyright  2006 KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    Release: @package_version@
 * @link       http://piece-framework.com/piece-unity/
 * @see        Piece_Unity_Session
 * @since      Class available since Release 0.2.0
 */
class Piece_Unity_SessionTestCase extends PHPUnit_TestCase
{

    // {{{ properties

    /**#@+
     * @access public
     */

    /**#@-*/

    /**#@+
     * @access private
     */

    var $_session;

    /**#@-*/

    /**#@+
     * @access public
     */

    function setUp()
    {
        $this->_session = &new Piece_Unity_Session();
        $_SESSION = array();
    }

    function tearDown()
    {
        unset($_SESSION);
        $this->_session = null;
    }

    function testValidInstance()
    {
        $class = strtolower(get_class($this));

        $this->assertTrue(is_a($this->_session, substr($class, 0, strpos($class, 'testcase'))));
    }

    function testSettingAttribute()
    {
        $this->_session->setAttribute('foo', 'bar');

        $this->assertTrue($this->_session->hasAttribute('foo'));
        $this->assertEquals('bar', $this->_session->getAttribute('foo'));
    }

    function testSettingAttributeByReference()
    {
        $foo1 = &new stdClass();
        $this->_session->setAttributeByRef('foo', $foo1);
        $foo1->bar = 'baz';

        $this->assertTrue($this->_session->hasAttribute('foo'));

        $foo2 = &$this->_session->getAttribute('foo');

        $this->assertTrue(array_key_exists('bar', $foo2));
        $this->assertEquals('baz', $foo2->bar);
    }

    function testAutoloaingClass()
    {
        $class = 'Piece_Unity_SessionTestCase_AutoloadClass';
        $oldIncludePath = set_include_path(dirname(__FILE__) . '/../..' . PATH_SEPARATOR . get_include_path());
        Piece_Unity_Session::addAutoloadClass($class);
        $this->_session->start();

        if (version_compare(phpversion(), '5.0.0', '<')) {
            $found = class_exists($class);
        } else {
            $found = class_exists($class, false);
        }

        $this->assertTrue($found);

        set_include_path($oldIncludePath);
    }

    function testRemovingAttribute()
    {
        $this->_session->setAttribute('foo', 'bar');

        $this->assertTrue($this->_session->hasAttribute('foo'));

        $this->_session->removeAttribute('foo');

        $this->assertFalse($this->_session->hasAttribute('foo'));
    }

    function testClearingAttributes()
    {
        $this->_session->setAttribute('foo', 'bar');
        $this->_session->setAttribute('bar', 'baz');

        $this->assertTrue($this->_session->hasAttribute('foo'));
        $this->assertTrue($this->_session->hasAttribute('bar'));

        $this->_session->clearAttributes();

        $this->assertFalse($this->_session->hasAttribute('foo'));
        $this->assertFalse($this->_session->hasAttribute('bar'));
    }

    /**
     * @since Method available since Release 0.9.0
     */
    function testPreload()
    {
        $GLOBALS['loadCount'] = 0;
        $this->_session->start();

        if (version_compare(phpversion(), '5.0.0', '<')) {
            $this->assertFalse(class_exists('Piece_Unity_SessionTestCase_Foo'));
        } else {
            $this->assertFalse(class_exists('Piece_Unity_SessionTestCase_Foo', false));
        }

        $service = 'Piece_Unity_SessionTestCase_Loader';
        $callback = array($service, 'load');
        $preload = &new Piece_Unity_Session_Preload();
        $preload->setCallback($service, $callback);
        $preload->addClass($service, 'Piece_Unity_SessionTestCase_Foo');
        $preload->addClass($service, 'Piece_Unity_SessionTestCase_Foo');
        unserialize(serialize($preload));
        $foo = unserialize('O:31:"Piece_Unity_SessionTestCase_Foo":0:{}');

        if (version_compare(phpversion(), '5.0.0', '<')) {
            $this->assertTrue(class_exists('Piece_Unity_SessionTestCase_Foo'));
        } else {
            $this->assertTrue(class_exists('Piece_Unity_SessionTestCase_Foo', false));
        }

        $this->assertTrue(is_object($foo));
        $this->assertEquals(strtolower('Piece_Unity_SessionTestCase_Foo'), strtolower(get_class($foo)));
        $this->assertEquals(1, $GLOBALS['loadCount']);

        unset($GLOBALS['loadCount']);
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
