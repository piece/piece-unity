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
 * @see        Piece_Unity_Config
 * @since      File available since Release 0.1.0
 */

require_once 'PHPUnit.php';
require_once 'Piece/Unity/Config.php';
require_once 'Piece/Unity/Error.php';

// {{{ Piece_Unity_ConfigTestCase

/**
 * TestCase for Piece_Unity_Config
 *
 * @package    Piece_Unity
 * @author     KUBO Atsuhiro <iteman2002@yahoo.co.jp>
 * @copyright  2006 KUBO Atsuhiro <iteman2002@yahoo.co.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    Release: @package_version@
 * @link       http://iteman.typepad.jp/piece/
 * @see        Piece_Unity_Config
 * @since      Class available since Release 0.1.0
 */
class Piece_Unity_ConfigTestCase extends PHPUnit_TestCase
{

    // {{{ properties

    /**#@+
     * @access public
     */

    /**#@-*/

    /**#@+
     * @access private
     */

    var $_config;

    /**#@-*/

    /**#@+
     * @access public
     */

    function setUp()
    {
        Piece_Unity_Error::pushCallback(create_function('$error', 'var_dump($error); return ' . PEAR_ERRORSTACK_DIE . ';'));
        $this->_config = &new Piece_Unity_Config();
    }

    function tearDown()
    {
        $this->_config = null;
        Piece_Unity_Error::clearErrors();
        Piece_Unity_Error::popCallback();
    }

    function testSettingExtension()
    {
        $this->_config->setExtension('Foo', 'bar', 'Bar');

        $this->assertEquals('Bar', $this->_config->getExtension('Foo', 'bar'));
    }

    function testSettingConfiguration()
    {
        $this->_config->setConfiguration('Foo', 'bar', 'Bar');

        $this->assertEquals('Bar', $this->_config->getConfiguration('Foo', 'bar'));
    }

    function testGettingExtensionWithInvalidPlugin()
    {
        $this->_config->setExtension('Foo', 'bar', 'Bar');

        $this->assertNull($this->_config->getExtension('Bar', 'bar'));
    }

    function testGettingExtensionWithInvalidExtensionPoint()
    {
        $this->_config->setExtension('Foo', 'bar', 'Bar');

        $this->assertNull($this->_config->getExtension('Foo', 'baz'));
    }

    function testGettingConfigurationWithInvalidConfigurationPoint()
    {
        $this->_config->setConfiguration('Foo', 'bar', 'Bar');

        $this->assertNull($this->_config->getConfiguration('Foo', 'baz'));
    }

    function testSettingConfigurationDirectory()
    {
        $this->_config->setConfigurationDirectory('/path/to/config');

        $this->assertEquals('/path/to/config',
                            $this->_config->getConfigurationDirectory()
                            );
    }

    function testSettingCacheDirectory()
    {
        $this->_config->setCacheDirectory('/path/to/cache');

        $this->assertEquals('/path/to/cache',
                            $this->_config->getCacheDirectory()
                            );
    }

    function testMergingConfiguration()
    {
        $this->_config->setExtension('Foo', 'bar', 'Bar');
        $this->_config->setConfiguration('Bar', 'baz', 'Baz');
        $config = &new Piece_Unity_Config();
        $config->setExtension('Foo', 'bar', 'Baz');
        $config->setConfiguration('Bar', 'baz', 'Qux');
        $this->_config->merge($config);

        $this->assertEquals('Baz', $this->_config->getExtension('Foo', 'bar'));
        $this->assertEquals('Qux', $this->_config->getConfiguration('Bar', 'baz'));
        $this->assertTrue($this->_config->isMergedExtensionPoint('Foo', 'bar'));
        $this->assertTrue($this->_config->isMergedConfigurationPoint('Bar', 'baz'));
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
