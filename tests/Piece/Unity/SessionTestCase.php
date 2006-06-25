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
 * @see        Piece_Unity_Session
 * @since      File available since Release 0.2.0
 */

require_once 'PHPUnit.php';
require_once 'Piece/Unity/Session.php';

// {{{ Piece_Unity_SessionTestCase

/**
 * TestCase for Piece_Unity_Session
 *
 * @package    Piece_Unity
 * @author     KUBO Atsuhiro <iteman2002@yahoo.co.jp>
 * @copyright  2006 KUBO Atsuhiro <iteman2002@yahoo.co.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    Release: @package_version@
 * @link       http://iteman.typepad.jp/piece/
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
        unset($_SESSION);
        $this->_session = &new Piece_Unity_Session();
    }

    function tearDown()
    {
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
        $class = 'Piece_Unity_AutoloadClass';
        $includePath = ini_get('include_path');
        ini_set('include_path',
                dirname(__FILE__) . '/../..' . PATH_SEPARATOR .
                $includePath
                );
        Piece_Unity_Session::addAutoloadClass($class);
        $session = &new Piece_Unity_Session();

        if (version_compare(phpversion(), '5.0.0', '<')) {
            $found = class_exists($class);
        } else {
            $found = class_exists($class, false);
        }

        $this->assertTrue($found);

        ini_set('include_path', $includePath);
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
