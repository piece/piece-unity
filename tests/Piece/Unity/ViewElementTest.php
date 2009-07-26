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

// {{{ Piece_Unity_ViewElementTest

/**
 * TestCase for Piece_Unity_ViewElement
 *
 * @package    Piece_Unity
 * @copyright  2006-2007, 2009 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @since      Class available since Release 0.1.0
 */
class Piece_Unity_ViewElementTest extends Piece_Unity_PHPUnit_TestCase
{

    // {{{ properties

    /**#@+
     * @access public
     */

    /**#@-*/

    /**#@+
     * @access protected
     */

    protected $serviceName = 'Piece_Unity_ViewElement';

    /**#@-*/

    /**#@+
     * @access private
     */

    /**#@-*/

    /**#@+
     * @access public
     */

    /**
     * @test
     */
    public function setAnElement()
    {
        $viewElement = $this->materializeFeature();
        $viewElement->setElement('foo', 'bar');
        $viewElement->setElement('bar', 'baz');

        $this->assertTrue($viewElement->hasElement('foo'));
        $this->assertTrue($viewElement->hasElement('bar'));

        $elements = $viewElement->getElements();

        $this->assertEquals('bar', $elements['foo']);
        $this->assertEquals('baz', $elements['bar']);
    }

    /**
     * @test
     */
    public function setAnElementByReference()
    {
        $foo = array();
        $viewElement = $this->materializeFeature();
        $viewElement->setElementByRef('foo', $foo);
        $foo['bar'] = 'baz';

        $this->assertTrue($viewElement->hasElement('foo'));

        $elements = $viewElement->getElements();

        $this->assertArrayHasKey('foo', $elements);
        $this->assertArrayHasKey('bar', $elements['foo']);
        $this->assertEquals('baz', $elements['foo']['bar']);
    }

    /**
     * @test
     */
    public function getAnElement()
    {
        $element1 = array('foo' => 1, 'bar' => 2, 'baz' => 3);
        $viewElement = $this->materializeFeature();
        $viewElement->setElement('foo', $element1);

        $this->assertTrue($viewElement->hasElement('foo'));

        $element2 = $viewElement->getElement('foo');

        $this->assertEquals($element1, $element2);

        $element2['qux'] = 4;
        $viewElement->setElement('foo', $element2);

        $element3 = $viewElement->getElement('foo');

        $this->assertArrayHasKey('qux', $element3);
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
