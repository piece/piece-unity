<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * PHP version 5
 *
 * Copyright (c) 2007-2009 KUBO Atsuhiro <kubo@iteman.jp>,
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
 * @copyright  2007-2009 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @since      File available since Release 0.12.0
 */

// {{{ Piece_Unity_Plugin_CommonTest

/**
 * Some tests for Piece_Unity_Plugin_Common.
 *
 * @package    Piece_Unity
 * @copyright  2007-2009 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @since      Class available since Release 0.12.0
 */
class Piece_Unity_Plugin_CommonTest extends PHPUnit_Framework_TestCase
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
        Piece_Unity_Plugin_Factory::addPluginDirectory(dirname(__FILE__) . '/' . basename(__FILE__, '.php'));
    }

    public function tearDown()
    {
        Piece_Unity_Plugin_Factory::initializePluginDirectories();
        Piece_Unity_Plugin_Factory::initializePluginPrefixes();
        Piece_Unity_Context::clear();
        Piece_Unity_Error::clearErrors();
    }

    /**
     * @test
     */
    public function getConfigurationByTheGivenPluginPrefix()
    {
        $config = new Piece_Unity_Config();
        $config->setConfiguration('Foo', 'bar', 'baz');
        $config->setExtension('Foo', 'baz', 'Qux');
        $config->setConfiguration('CannotGetConfigurationWithPluginPrefixFoo', 'bar', 'baz');
        $config->setExtension('CannotGetConfigurationWithPluginPrefixFoo', 'baz', 'CannotGetConfigurationWithPluginPrefixQux');
        Piece_Unity_Context::singleton()->setConfiguration($config);
        Piece_Unity_Plugin_Factory::addPluginPrefix('CommonTestCaseAlias');
        Piece_Unity_Plugin_Factory::addPluginPrefix('');

        $foo = Piece_Unity_Plugin_Factory::factory('Foo');
        $foo->invoke();

        $this->assertEquals('baz', $this->readAttribute($foo, '_bar'));
        $this->assertType('CommonTestCaseAlias_Qux',
                          $this->readAttribute($foo, '_baz')
                          );

        $empty = Piece_Unity_Plugin_Factory::factory('CannotGetConfigurationWithPluginPrefixFoo');
        $empty->invoke();

        $this->assertEquals('baz', $this->readAttribute($empty, '_bar'));
        $this->assertType('CannotGetConfigurationWithPluginPrefixQux',
                          $this->readAttribute($empty, '_baz')
                          );
    }

    /**
     * @test
     * @since Method available since Release 1.0.0
     */
    public function raiseAnExceptionWhenAnUndefinedExtensionPointIsUsed()
    {
        Piece_Unity_Context::singleton()->setConfiguration(new Piece_Unity_Config());
        Piece_Unity_Plugin_Factory::addPluginPrefix('CommonTestCaseAlias');
        $plugin = Piece_Unity_Plugin_Factory::factory('ExceptionShouldBeRaisedWhenUndefinedExtensionPointIsUsed');
        Piece_Unity_Error::disableCallback();
        $plugin->invoke();
        Piece_Unity_Error::enableCallback();

        $this->assertTrue(Piece_Unity_Error::hasErrors());

        $error = Piece_Unity_Error::pop();

        $this->assertEquals(PIECE_UNITY_ERROR_NOT_FOUND, $error['code']);
    }

    /**
     * @test
     * @since Method available since Release 1.0.0
     */
    public function raiseAnExceptionWhenAnUndefinedConfigurationPointIsUsed()
    {
        Piece_Unity_Context::singleton()->setConfiguration(new Piece_Unity_Config());
        Piece_Unity_Plugin_Factory::addPluginPrefix('CommonTestCaseAlias');
        $plugin = Piece_Unity_Plugin_Factory::factory('ExceptionShouldBeRaisedWhenUndefinedConfigurationPointIsUsed');
        Piece_Unity_Error::disableCallback();
        $plugin->invoke();
        Piece_Unity_Error::enableCallback();

        $this->assertTrue(Piece_Unity_Error::hasErrors());

        $error = Piece_Unity_Error::pop();

        $this->assertEquals(PIECE_UNITY_ERROR_NOT_FOUND, $error['code']);
    }

    /**
     * @test
     * @since Method available since Release 1.1.0
     */
    public function raiseAnExceptionWhenAnUndefinedExtensionPointIsUsedInConfiguration()
    {
        $config = new Piece_Unity_Config();
        $config->setExtension('ExceptionShouldBeRaisedWhenUndefinedExtensionPointIsUsedInConfiguration', 'bar', 'baz');
        Piece_Unity_Context::singleton()->setConfiguration($config);
        Piece_Unity_Plugin_Factory::addPluginPrefix('CommonTestCaseAlias');
        Piece_Unity_Error::disableCallback();
        $plugin = Piece_Unity_Plugin_Factory::factory('ExceptionShouldBeRaisedWhenUndefinedExtensionPointIsUsedInConfiguration');
        Piece_Unity_Error::enableCallback();

        $this->assertNull($plugin);
        $this->assertTrue(Piece_Unity_Error::hasErrors());

        $error = Piece_Unity_Error::pop();

        $this->assertEquals(PIECE_UNITY_ERROR_NOT_FOUND, $error['code']);
    }

    /**
     * @test
     * @since Method available since Release 1.1.0
     */
    public function raiseAnExceptionWhenAnUndefinedConfigurationPointIsUsedInConfiguration()
    {
        $config = new Piece_Unity_Config();
        $config->setConfiguration('ExceptionShouldBeRaisedWhenUndefinedConfigurationPointIsUsedInConfiguration', 'bar', 'baz');
        Piece_Unity_Context::singleton()->setConfiguration($config);
        Piece_Unity_Plugin_Factory::addPluginPrefix('CommonTestCaseAlias');
        Piece_Unity_Error::disableCallback();
        $plugin = Piece_Unity_Plugin_Factory::factory('ExceptionShouldBeRaisedWhenUndefinedConfigurationPointIsUsedInConfiguration');
        Piece_Unity_Error::enableCallback();

        $this->assertNull($plugin);
        $this->assertTrue(Piece_Unity_Error::hasErrors());

        $error = Piece_Unity_Error::pop();

        $this->assertEquals(PIECE_UNITY_ERROR_NOT_FOUND, $error['code']);
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
