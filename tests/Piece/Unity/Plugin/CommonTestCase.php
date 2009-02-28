<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * PHP versions 4 and 5
 *
 * Copyright (c) 2007-2008 KUBO Atsuhiro <iteman@users.sourceforge.net>,
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
 * @copyright  2007-2008 KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    GIT: $Id$
 * @since      File available since Release 0.12.0
 */

require_once realpath(dirname(__FILE__) . '/../../../prepare.php');
require_once 'PHPUnit.php';
require_once 'Piece/Unity/Plugin/Factory.php';
require_once 'Piece/Unity/Error.php';
require_once 'Piece/Unity/Context.php';
require_once 'Piece/Unity/Config.php';

// {{{ Piece_Unity_Plugin_CommonTestCase

/**
 * Some tests for Piece_Unity_Plugin_Common.
 *
 * @package    Piece_Unity
 * @copyright  2007-2008 KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    Release: @package_version@
 * @since      Class available since Release 0.12.0
 */
class Piece_Unity_Plugin_CommonTestCase extends PHPUnit_TestCase
{

    // {{{ properties

    /**#@+
     * @access public
     */

    /**#@-*/

    /**#@+
     * @access private
     */

    var $_oldPluginDirectories;
    var $_oldPluginPrefixes;

    /**#@-*/

    /**#@+
     * @access public
     */

    function setUp()
    {
        $this->_oldPluginDirectories = $GLOBALS['PIECE_UNITY_Plugin_Directories'];
        Piece_Unity_Plugin_Factory::addPluginDirectory(dirname(__FILE__) . '/' . basename(__FILE__, '.php'));
        $this->_oldPluginPrefixes = $GLOBALS['PIECE_UNITY_Plugin_Prefixes'];
    }

    function tearDown()
    {
        Piece_Unity_Context::clear();
        $GLOBALS['PIECE_UNITY_Plugin_Prefixes'] = $this->_oldPluginPrefixes;
        $GLOBALS['PIECE_UNITY_Plugin_Directories'] = $this->_oldPluginDirectories;
        Piece_Unity_Error::clearErrors();
    }

    function testCannotGetConfigurationWithPluginPrefix()
    {
        $config = &new Piece_Unity_Config();
        $config->setConfiguration('Foo', 'bar', 'baz');
        $config->setExtension('Foo', 'baz', 'Qux');
        $config->setConfiguration('CannotGetConfigurationWithPluginPrefixFoo', 'bar', 'baz');
        $config->setExtension('CannotGetConfigurationWithPluginPrefixFoo', 'baz', 'CannotGetConfigurationWithPluginPrefixQux');
        $context = &Piece_Unity_Context::singleton();
        $context->setConfiguration($config);
        Piece_Unity_Plugin_Factory::addPluginPrefix('CommonTestCaseAlias');
        Piece_Unity_Plugin_Factory::addPluginPrefix('');

        $foo = &Piece_Unity_Plugin_Factory::factory('Foo');
        $foo->invoke();

        $this->assertEquals('baz', $foo->_bar);
        $this->assertEquals(strtolower('CommonTestCaseAlias_Qux'), strtolower(get_class($foo->_baz)));

        $empty = &Piece_Unity_Plugin_Factory::factory('CannotGetConfigurationWithPluginPrefixFoo');
        $empty->invoke();

        $this->assertEquals('baz', $empty->_bar);
        $this->assertEquals(strtolower('CannotGetConfigurationWithPluginPrefixQux'), strtolower(get_class($empty->_baz)));
    }

    /**
     * @since Method available since Release 1.0.0
     */
    function testExceptionShouldBeRaisedWhenUndefinedExtensionPointIsUsed()
    {
        $config = &new Piece_Unity_Config();
        $context = &Piece_Unity_Context::singleton();
        $context->setConfiguration($config);
        Piece_Unity_Plugin_Factory::addPluginPrefix('CommonTestCaseAlias');
        $plugin = &Piece_Unity_Plugin_Factory::factory('ExceptionShouldBeRaisedWhenUndefinedExtensionPointIsUsed');
        Piece_Unity_Error::disableCallback();
        $plugin->invoke();
        Piece_Unity_Error::enableCallback();

        $this->assertTrue(Piece_Unity_Error::hasErrors());

        $error = Piece_Unity_Error::pop();

        $this->assertEquals(PIECE_UNITY_ERROR_NOT_FOUND, $error['code']);
    }

    /**
     * @since Method available since Release 1.0.0
     */
    function testExceptionShouldBeRaisedWhenUndefinedConfigurationPointIsUsed()
    {
        $config = &new Piece_Unity_Config();
        $context = &Piece_Unity_Context::singleton();
        $context->setConfiguration($config);
        Piece_Unity_Plugin_Factory::addPluginPrefix('CommonTestCaseAlias');
        $plugin = &Piece_Unity_Plugin_Factory::factory('ExceptionShouldBeRaisedWhenUndefinedConfigurationPointIsUsed');
        Piece_Unity_Error::disableCallback();
        $plugin->invoke();
        Piece_Unity_Error::enableCallback();

        $this->assertTrue(Piece_Unity_Error::hasErrors());

        $error = Piece_Unity_Error::pop();

        $this->assertEquals(PIECE_UNITY_ERROR_NOT_FOUND, $error['code']);
    }

    /**
     * @since Method available since Release 1.1.0
     */
    function testExceptionShouldBeRaisedWhenUndefinedExtensionPointIsUsedInConfiguration()
    {
        $config = &new Piece_Unity_Config();
        $config->setExtension('ExceptionShouldBeRaisedWhenUndefinedExtensionPointIsUsedInConfiguration', 'bar', 'baz');
        $context = &Piece_Unity_Context::singleton();
        $context->setConfiguration($config);
        Piece_Unity_Plugin_Factory::addPluginPrefix('CommonTestCaseAlias');
        Piece_Unity_Error::disableCallback();
        $plugin = &Piece_Unity_Plugin_Factory::factory('ExceptionShouldBeRaisedWhenUndefinedExtensionPointIsUsedInConfiguration');
        Piece_Unity_Error::enableCallback();

        $this->assertNull($plugin);
        $this->assertTrue(Piece_Unity_Error::hasErrors());

        $error = Piece_Unity_Error::pop();

        $this->assertEquals(PIECE_UNITY_ERROR_NOT_FOUND, $error['code']);
    }

    /**
     * @since Method available since Release 1.1.0
     */
    function testExceptionShouldBeRaisedWhenUndefinedConfigurationPointIsUsedInConfiguration()
    {
        $config = &new Piece_Unity_Config();
        $config->setConfiguration('ExceptionShouldBeRaisedWhenUndefinedExtensionPointIsUsedInConfiguration', 'bar', 'baz');
        $context = &Piece_Unity_Context::singleton();
        $context->setConfiguration($config);
        Piece_Unity_Plugin_Factory::addPluginPrefix('CommonTestCaseAlias');
        Piece_Unity_Error::disableCallback();
        $plugin = &Piece_Unity_Plugin_Factory::factory('ExceptionShouldBeRaisedWhenUndefinedExtensionPointIsUsedInConfiguration');
        Piece_Unity_Error::enableCallback();

        $this->assertNull($plugin);
        $this->assertTrue(Piece_Unity_Error::hasErrors());

        $error = Piece_Unity_Error::pop();

        $this->assertEquals(PIECE_UNITY_ERROR_NOT_FOUND, $error['code']);
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
