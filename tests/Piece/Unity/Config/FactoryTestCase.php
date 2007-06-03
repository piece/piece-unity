<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * PHP versions 4 and 5
 *
 * Copyright (c) 2006-2007 KUBO Atsuhiro <iteman@users.sourceforge.net>,
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
 * @copyright  2006-2007 KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    SVN: $Id$
 * @since      File available since Release 0.1.0
 */

require_once 'PHPUnit.php';
require_once 'Piece/Unity/Config/Factory.php';
require_once 'Piece/Unity/Error.php';
require_once 'Cache/Lite/File.php';

// {{{ Piece_Unity_Config_FactoryTestCase

/**
 * TestCase for Piece_Unity_Config_Factory
 *
 * @package    Piece_Unity
 * @copyright  2006-2007 KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    Release: @package_version@
 * @since      Class available since Release 0.1.0
 */
class Piece_Unity_Config_FactoryTestCase extends PHPUnit_TestCase
{

    // {{{ properties

    /**#@+
     * @access public
     */

    /**#@-*/

    /**#@+
     * @access private
     */

    var $_cacheDirectory;

    /**#@-*/

    /**#@+
     * @access public
     */

    function setUp()
    {
        Piece_Unity_Error::pushCallback(create_function('$error', 'var_dump($error); return ' . PEAR_ERRORSTACK_DIE . ';'));
        $this->_cacheDirectory = dirname(__FILE__) . '/' . basename(__FILE__, '.php');
    }

    function tearDown()
    {
        $cache = &new Cache_Lite_File(array('cacheDir' => "{$this->_cacheDirectory}/",
                                            'masterFile' => '',
                                            'automaticSerialization' => true,
                                            'errorHandlingAPIBreak' => true)
                                      );
        $cache->clean();
        Piece_Unity_Error::clearErrors();
        Piece_Unity_Error::popCallback();
    }

    function testFactoryWithoutConfigurationFile()
    {
        $this->assertEquals(strtolower('Piece_Unity_Config'), strtolower(get_class(Piece_Unity_Config_Factory::factory())));
    }

    function testConfigurationDirectoryNotFound()
    {
        Piece_Unity_Error::pushCallback(create_function('$error', 'return ' . PEAR_ERRORSTACK_PUSHANDLOG . ';'));
        $config = &Piece_Unity_Config_Factory::factory(dirname(__FILE__) . '/foo', $this->_cacheDirectory);

        $this->assertNull($config);
        $this->assertTrue(Piece_Unity_Error::hasErrors('exception'));

        $error = Piece_Unity_Error::pop();

        $this->assertEquals(PIECE_UNITY_ERROR_NOT_FOUND, $error['code']);

        Piece_Unity_Error::popCallback();
    }

    function testConfigurationFileNotFound()
    {
        Piece_Unity_Error::pushCallback(create_function('$error', 'return ' . PEAR_ERRORSTACK_PUSHANDLOG . ';'));
        $config = &Piece_Unity_Config_Factory::factory(dirname(__FILE__), $this->_cacheDirectory);

        $this->assertNull($config);
        $this->assertTrue(Piece_Unity_Error::hasErrors('exception'));

        $error = Piece_Unity_Error::pop();

        $this->assertEquals(PIECE_UNITY_ERROR_NOT_FOUND, $error['code']);

        Piece_Unity_Error::popCallback();
    }

    function testNoCachingIfCacheDirectoryNotFound()
    {
        $config = &Piece_Unity_Config_Factory::factory($this->_cacheDirectory, dirname(__FILE__) . '/foo');

        $this->assertEquals(strtolower('Piece_Unity_Config'), strtolower(get_class($config)));
        $this->assertTrue(Piece_Unity_Error::hasErrors('warning'));

        $error = Piece_Unity_Error::pop();

        $this->assertEquals(PIECE_UNITY_ERROR_NOT_FOUND, $error['code']);
    }

    function testFactoryWithConfigurationFile()
    {
        $config = &Piece_Unity_Config_Factory::factory($this->_cacheDirectory, $this->_cacheDirectory);

        $this->assertEquals(strtolower('Piece_Unity_Config'), strtolower(get_class($config)));
        $this->assertEquals('View', $config->getExtension('Controller', 'view'));
        $this->assertEquals('Dispatcher_Simple', $config->getExtension('Controller', 'dispatcher'));
        $this->assertEquals('../webapp/actions', $config->getConfiguration('Dispatcher_Continuation', 'actionDirectory'));
        $this->assertTrue($config->getConfiguration('Dispatcher_Continuation', 'enableSingleFlowMode'));
        $this->assertEquals('../webapp/cache', $config->getConfiguration('Dispatcher_Continuation', 'cacheDirectory'));

        $flowDefinitions = $config->getConfiguration('Dispatcher_Continuation', 'flowDefinitions');

        $this->assertEquals('Registration', $flowDefinitions[0]['name']);
        $this->assertEquals('../webapp/config/Registration.yaml', $flowDefinitions[0]['file']);
        $this->assertFalse($flowDefinitions[0]['isExclusive']);
        $this->assertEquals('../webapp/actions', $config->getConfiguration('Dispatcher_Simple', 'actionDirectory'));
        $this->assertEquals('../webapp/templates', $config->getConfiguration('Renderer_PHP', 'templateDirectory'));
        $this->assertEquals('Renderer_PHP', $config->getExtension('View', 'renderer'));
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
