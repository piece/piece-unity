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
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    GIT: $Id$
 * @since      File available since Release 0.2.0
 */

// {{{ Piece_Unity_SessionTest

/**
 * Some tests for Piece_Unity_Session.
 *
 * @package    Piece_Unity
 * @copyright  2006-2007, 2009 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    Release: @package_version@
 * @since      Class available since Release 0.2.0
 */
class Piece_Unity_SessionTest extends Piece_Unity_PHPUnit_TestCase
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

    private $_session;

    /**#@-*/

    /**#@+
     * @access public
     */

    public function setUp()
    {
        parent::setUp();
        $this->_session = new Piece_Unity_Session();
    }

    /**
     * @test
     */
    public function testValidInstance()
    {
        $class = strtolower(get_class($this));

        $this->assertType(substr($class, 0, strpos($class, 'test')), $this->_session);
    }

    /**
     * @test
     */
    public function setAnAttribute()
    {
        @$this->_session->start();
        $this->_session->setAttribute('foo', 'bar');

        $this->assertTrue($this->_session->hasAttribute('foo'));
        $this->assertEquals('bar', $this->_session->getAttribute('foo'));
    }

    /**
     * @test
     */
    public function setAnAttributeByReference()
    {
        $foo1 = array();
        $this->_session->setAttributeByRef('foo', $foo1);
        $foo1['bar'] = 'baz';

        $this->assertTrue($this->_session->hasAttribute('foo'));

        $foo2 = $this->_session->getAttribute('foo');

        $this->assertArrayHasKey('bar', $foo2);
        $this->assertEquals('baz', $foo2['bar']);
    }

    /**
     * @test
     */
    public function autoloadSpecifiedClassesBeforeStartingTheSession()
    {
        $class = 'Piece_Unity_SessionTest_AutoloadClass';
        $oldIncludePath = set_include_path(dirname(__FILE__) . '/../..' . PATH_SEPARATOR . get_include_path());
        Piece_Unity_Session::addAutoloadClass($class);
        @$this->_session->start();
        $found = class_exists($class, false);

        $this->assertTrue($found);

        set_include_path($oldIncludePath);
    }

    /**
     * @test
     */
    public function removeAnAttribute()
    {
        @$this->_session->start();
        $this->_session->setAttribute('foo', 'bar');

        $this->assertTrue($this->_session->hasAttribute('foo'));

        $this->_session->removeAttribute('foo');

        $this->assertFalse($this->_session->hasAttribute('foo'));
    }

    /**
     * @test
     */
    public function removeAllAttributes()
    {
        @$this->_session->start();
        $this->_session->setAttribute('foo', 'bar');
        $this->_session->setAttribute('bar', 'baz');

        $this->assertTrue($this->_session->hasAttribute('foo'));
        $this->assertTrue($this->_session->hasAttribute('bar'));
        $this->assertArrayHasKey('foo', $_SESSION);
        $this->assertArrayHasKey('bar', $_SESSION);

        $this->_session->clearAttributes();

        $this->assertFalse($this->_session->hasAttribute('foo'));
        $this->assertFalse($this->_session->hasAttribute('bar'));
        $this->assertArrayNotHasKey('foo', $_SESSION);
        $this->assertArrayNotHasKey('bar', $_SESSION);
    }

    /**
     * @test
     * @since Method available since Release 0.9.0
     */
    public function preloadClassesForStoredObjectsBeforeStartingTheSession()
    {
        @$this->_session->start();

        $this->assertFalse(class_exists('Piece_Unity_SessionTest_Foo', false));

        $service = 'Piece_Unity_SessionTest_Loader';
        $callback = array($service, 'load');
        $preload = new Piece_Unity_Session_Preload();
        $preload->setCallback($service, $callback);
        $preload->addClass($service, 'Piece_Unity_SessionTest_Foo');
        $preload->addClass($service, 'Piece_Unity_SessionTest_Foo');
        unserialize(serialize($preload));
        $foo = unserialize('O:27:"Piece_Unity_SessionTest_Foo":0:{}');

        $this->assertTrue(class_exists('Piece_Unity_SessionTest_Foo', false));
        $this->assertTrue(is_object($foo));
        $this->assertType('Piece_Unity_SessionTest_Foo', $foo);
        $this->assertEquals(1, $this->readAttribute($service, '_loadCount'));
    }

    /**
     * @test
     * @since Method available since Release 1.6.2
     */
    public function publishTheAttributesByAProperty()
    {
        $this->markTestSkipped();

        @$this->_session->start();
        $this->_session->setAttribute('foo', 'bar');

        $this->assertArrayHasKey('foo', $this->_session->_attributes);
        $this->assertEquals($this->_session->_attributes['foo'], 'bar');
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
