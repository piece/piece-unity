<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * PHP versions 4 and 5
 *
 * Copyright (c) 2006-2008 KUBO Atsuhiro <iteman@users.sourceforge.net>,
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
 * @copyright  2006-2008 KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    SVN: $Id$
 * @since      File available since Release 0.1.0
 */

require_once realpath(dirname(__FILE__) . '/../../../prepare.php');
require_once 'PHPUnit.php';
require_once 'Piece/Unity/Plugin/Factory.php';
require_once 'Piece/Unity/Error.php';
require_once 'Piece/Unity/Config.php';
require_once 'Piece/Unity/Context.php';

// {{{ Piece_Unity_Plugin_FactoryTestCase

/**
 * Some tests for Piece_Unity_Plugin_Factory.
 *
 * @package    Piece_Unity
 * @copyright  2006-2008 KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    Release: @package_version@
 * @since      Class available since Release 0.1.0
 */
class Piece_Unity_Plugin_FactoryTestCase extends PHPUnit_TestCase
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
        Piece_Unity_Plugin_Factory::addPluginDirectory(dirname(__FILE__) . '/FactoryTestCase');
        $this->_oldPluginPrefixes = $GLOBALS['PIECE_UNITY_Plugin_Prefixes'];
        $config = &new Piece_Unity_Config();
        $context = &Piece_Unity_Context::singleton();
        $context->setConfiguration($config);
    }

    function tearDown()
    {
        Piece_Unity_Context::clear();
        $GLOBALS['PIECE_UNITY_Plugin_Prefixes'] = $this->_oldPluginPrefixes;
        Piece_Unity_Plugin_Factory::clearInstances();
        $GLOBALS['PIECE_UNITY_Plugin_Directories'] = $this->_oldPluginDirectories;
        Piece_Unity_Error::clearErrors();
    }

    function testFailureToCreateByNonExistingFile()
    {
        Piece_Unity_Error::disableCallback();
        Piece_Unity_Plugin_Factory::factory('NonExisting');
        Piece_Unity_Error::enableCallback();

        $this->assertTrue(Piece_Unity_Error::hasErrors());

        $error = Piece_Unity_Error::pop();

        $this->assertEquals(PIECE_UNITY_ERROR_NOT_FOUND, $error['code']);
    }

    function testFailureToCreateByInvalidPlugin()
    {
        Piece_Unity_Error::disableCallback();
        Piece_Unity_Plugin_Factory::factory('FactoryTestCase_Invalid');
        Piece_Unity_Error::enableCallback();

        $this->assertTrue(Piece_Unity_Error::hasErrors());

        $error = Piece_Unity_Error::pop();

        $this->assertEquals(PIECE_UNITY_ERROR_INVALID_PLUGIN, $error['code']);
    }

    function testFactory()
    {
        $fooPlugin = &Piece_Unity_Plugin_Factory::factory('FactoryTestCase_Foo');

        $this->assertTrue(is_a($fooPlugin, 'Piece_Unity_Plugin_FactoryTestCase_Foo'));

        $barPlugin = &Piece_Unity_Plugin_Factory::factory('FactoryTestCase_Bar');

        $this->assertTrue(is_a($barPlugin, 'Piece_Unity_Plugin_FactoryTestCase_Bar'));

        $fooPlugin->baz = 'qux';

        $plugin = &Piece_Unity_Plugin_Factory::factory('FactoryTestCase_Foo');

        $this->assertTrue(array_key_exists('baz', $fooPlugin));
    }

    /**
     * @since Method available since Release 0.11.0
     */
    function testAlias()
    {
        Piece_Unity_Plugin_Factory::addPluginPrefix('FactoryTestCaseAlias');
        $foo = &Piece_Unity_Plugin_Factory::factory('Foo');

        $this->assertTrue(is_object($foo));
        $this->assertTrue(is_a($foo, 'FactoryTestCaseAlias_Foo'));
    }

    /**
     * @since Method available since Release 0.11.0
     */
    function testAliasWithEmptyPrefix()
    {
        Piece_Unity_Plugin_Factory::addPluginPrefix('');
        $bar = &Piece_Unity_Plugin_Factory::factory('Bar');

        $this->assertTrue(is_object($bar));
        $this->assertTrue(is_a($bar, 'Bar'));
    }

    /**
     * @since Method available since Release 0.11.0
     */
    function testCreateExistingClass()
    {
        Piece_Unity_Plugin_Factory::addPluginPrefix('FactoryTestCaseAlias');
        $foo = &Piece_Unity_Plugin_Factory::factory('FactoryTestCase_Foo');

        $this->assertTrue(is_object($foo));
        $this->assertFalse(is_a($foo, 'FactoryTestCaseAlias_FactoryTestCase_Foo'));
        $this->assertTrue(is_a($foo, 'Piece_Unity_Plugin_FactoryTestCase_Foo'));
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
