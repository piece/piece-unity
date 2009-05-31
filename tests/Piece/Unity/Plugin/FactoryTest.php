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

// {{{ Piece_Unity_Plugin_FactoryTest

/**
 * Some tests for Piece_Unity_Plugin_Factory.
 *
 * @package    Piece_Unity
 * @copyright  2006-2009 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @since      Class available since Release 0.1.0
 */
class Piece_Unity_Plugin_FactoryTest extends Piece_Unity_PHPUnit_TestCase
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

    /**#@-*/

    /**#@+
     * @access public
     */

    public function setUp()
    {
        parent::setUp();
        Piece_Unity_Plugin_Factory::addPluginPrefix(__CLASS__);
        Piece_Unity_Context::singleton()->setConfiguration(new Piece_Unity_Config());
    }

    /**
     * @test
     * @expectedException Piece_Unity_Exception
     */
    public function raiseAnExceptionWhenCreatingAPluginObjectByANonExistingPluginName()
    {
        Piece_Unity_Plugin_Factory::factory('NonExisting');
    }

    /**
     * @test
     * @expectedException Piece_Unity_Exception
     */
    public function raiseAnExceptionWhenTheGivenPluginIsInvalid()
    {
        Piece_Unity_Plugin_Factory::factory('Invalid');
    }

    /**
     * @test
     */
    public function createAPluginObject()
    {
        $fooPlugin1 = Piece_Unity_Plugin_Factory::factory('Foo');

        $this->assertType(__CLASS__ . '_Foo', $fooPlugin1);
        $this->assertType(__CLASS__ . '_Bar',
                          Piece_Unity_Plugin_Factory::factory('Bar')
                          );

        $fooPlugin1->baz = 'qux';
        $fooPlugin2 = Piece_Unity_Plugin_Factory::factory('Foo');

        $this->assertObjectHasAttribute('baz', $fooPlugin2);
    }

    /**
     * @test
     * @since Method available since Release 0.11.0
     */
    public function addAnEmptyStringAsAPrefixOfPluginClasses()
    {
        Piece_Unity_Plugin_Factory::addPluginPrefix('');
        $pluginName = str_replace('_', '', __CLASS__);
        $bar = Piece_Unity_Plugin_Factory::factory($pluginName);

        $this->assertType($pluginName, $bar);
    }

    /**
     * @test
     * @since Method available since Release 0.11.0
     */
    public function returnTheExistingPluginObjectIfItIsAlreadyInstantiated()
    {
        $foo1 = Piece_Unity_Plugin_Factory::factory('Foo');
        $foo1->bar = 'baz';
        $foo2 = Piece_Unity_Plugin_Factory::factory('Foo');

        $this->assertObjectHasAttribute('bar', $foo2);
        $this->assertEquals('baz', $foo2->bar);
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
