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
 * @see        Piece_Unity_Config_Factory
 * @since      File available since Release 0.1.0
 */

require_once 'PHPUnit.php';
require_once 'Piece/Unity/Config/Factory.php';
require_once 'Piece/Unity/Error.php';
require_once 'Piece/Unity/Plugin/Factory.php';
require_once 'Cache/Lite/File.php';
require_once 'Piece/Unity.php';

// {{{ Piece_Unity_Config_FactoryTestCase

/**
 * TestCase for Piece_Unity_Config_Factory
 *
 * @package    Piece_Unity
 * @author     KUBO Atsuhiro <iteman2002@yahoo.co.jp>
 * @copyright  2006 KUBO Atsuhiro <iteman2002@yahoo.co.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    Release: @package_version@
 * @link       http://iteman.typepad.jp/piece/
 * @see        Piece_Unity_Config_Factory
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

    var $_pluginDirectories;

    /**#@-*/

    /**#@+
     * @access public
     */

    function setUp()
    {
        Piece_Unity_Error::pushCallback(create_function('$error', 'var_dump($error); return ' . PEAR_ERRORSTACK_DIE . ';'));
        $this->_pluginDirectories = $GLOBALS['PIECE_UNITY_Plugin_Directories'];
        Piece_Unity_Plugin_Factory::addPluginDirectory(dirname(__FILE__) . '/../../..');
    }

    function tearDown()
    {
        $GLOBALS['PIECE_UNITY_Plugin_Instances'] = array();
        $GLOBALS['PIECE_UNITY_Plugin_Directories'] = $this->_pluginDirectories;
        $cache = &new Cache_Lite_File(array('cacheDir' => dirname(__FILE__) . '/',
                                            'masterFile' => '',
                                            'automaticSerialization' => true,
                                            'errorHandlingAPIBreak' => true)
                                      );
        $cache->clean();
        Piece_Unity_Error::clearErrors();
        Piece_Unity_Error::popCallback();
    }

    function testCreating()
    {
        $this->assertTrue(is_a(Piece_Unity_Config_Factory::factory(),
                               'Piece_Unity_Config')
                          );
    }

    function testCreatingUsingConfigurationFile()
    {
        $config = &Piece_Unity_Config_Factory::factory(dirname(__FILE__) . '/../../../../data',
                                                       dirname(__FILE__)
                                                       );
        $this->assertTrue(is_a($config, 'Piece_Unity_Config'));
        $this->assertEquals('SmartyRenderer',
                            $config->getExtension(PIECE_UNITY_ROOT_PLUGIN, 'renderer')
                            );
    }

    function testCreatingIfConfigurationDirectoryNotFound()
    {
        Piece_Unity_Error::pushCallback(create_function('$error', 'return ' . PEAR_ERRORSTACK_PUSHANDLOG . ';'));

        $config = &Piece_Unity_Config_Factory::factory(dirname(__FILE__) . '/foo',
                                                       dirname(__FILE__)
                                                       );
        $this->assertTrue(is_a($config, 'Piece_Unity_Config'));

        $config->getExtension(PIECE_UNITY_ROOT_PLUGIN, 'renderer');

        $this->assertTrue(Piece_Unity_Error::hasErrors('warning'));

        $error = Piece_Unity_Error::pop();

        $this->assertEquals(PIECE_UNITY_ERROR_NOT_FOUND, $error['code']);

        Piece_Unity_Error::popCallback();
    }

    function testCreatingIfConfigurationFileWasNotReadable()
    {
        Piece_Unity_Error::pushCallback(create_function('$error', 'return ' . PEAR_ERRORSTACK_PUSHANDLOG . ';'));

        $config = &Piece_Unity_Config_Factory::factory(dirname(__FILE__) . '/../../../../tests',
                                                       dirname(__FILE__)
                                                       );
        $this->assertTrue(is_a($config, 'Piece_Unity_Config'));

        $config->getExtension(PIECE_UNITY_ROOT_PLUGIN, 'renderer');

        $this->assertTrue(Piece_Unity_Error::hasErrors('warning'));

        $error = Piece_Unity_Error::pop();

        $this->assertEquals(PIECE_UNITY_ERROR_NOT_READABLE, $error['code']);

        Piece_Unity_Error::popCallback();
    }

    function testNoCachingIfCacheDirectoryNotFound()
    {
        Piece_Unity_Error::pushCallback(create_function('$error', 'return ' . PEAR_ERRORSTACK_PUSHANDLOG . ';'));

        $config = &Piece_Unity_Config_Factory::factory(dirname(__FILE__) . '/../../../../data');

        $this->assertTrue(is_a($config, 'Piece_Unity_Config'));

        $config->getExtension(PIECE_UNITY_ROOT_PLUGIN, 'renderer');

        $this->assertTrue(Piece_Unity_Error::hasErrors('warning'));

        $error = Piece_Unity_Error::pop();

        $this->assertEquals(PIECE_UNITY_ERROR_NOT_FOUND, $error['code']);

        Piece_Unity_Error::popCallback();
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
