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

// {{{ Piece_Unity_Config_FactoryTest

/**
 * Some tests for Piece_Unity_Config_Factory.
 *
 * @package    Piece_Unity
 * @copyright  2006-2009 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @since      Class available since Release 0.1.0
 */
class Piece_Unity_Config_FactoryTest extends Piece_Unity_PHPUnit_TestCase
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

    private $_cacheDirectory;
    private $_hasWarnings = false;

    /**#@-*/

    /**#@+
     * @access public
     */

    public function setUp()
    {
        $this->_cacheDirectory = dirname(__FILE__) . '/' . basename(__FILE__, '.php');
    }

    public function tearDown()
    {
        $cache = new Cache_Lite_File(array('cacheDir' => $this->_cacheDirectory . '/',
                                           'masterFile' => '',
                                           'automaticSerialization' => true,
                                           'errorHandlingAPIBreak' => true)
                                     );
        $cache->clean();
    }

    /**
     * @test
     */
    public function createAnObjectWithoutConfigurationFile()
    {
        $this->assertType('Piece_Unity_Config', Piece_Unity_Config_Factory::factory());
    }

    /**
     * @test
     * @expectedException Piece_Unity_Exception
     */
    public function raiseAnExceptionWhenTheGivenConfigurationDirectoryIsNotFound()
    {
        Piece_Unity_Config_Factory::factory(dirname(__FILE__) . '/foo', $this->_cacheDirectory);
    }

    /**
     * @test
     * @expectedException Piece_Unity_Exception
     */
    public function raiseAnExceptionWhenTheGivenConfigurationFileIsNotFound()
    {
        Piece_Unity_Config_Factory::factory(dirname(__FILE__), $this->_cacheDirectory);
    }

    /**
     * @test
     */
    public function createAnObjectEvenThoughTheGivenCacheDirectoryIsNotFound()
    {
        set_error_handler(array($this, 'handleWarning'));
        $config = Piece_Unity_Config_Factory::factory($this->_cacheDirectory, dirname(__FILE__) . '/foo');
        restore_error_handler();

        $this->assertTrue($this->_hasWarnings);
        $this->assertType('Piece_Unity_Config', $config);
    }

    public function handleWarning($code, $message, $file, $line)
    {
       if ($code == E_USER_WARNING) {
           $this->_hasWarnings = true;
       }
    }

    /**
     * @test
     */
    public function createAnObjectByTheGivenConfigurationFile()
    {
        $config = Piece_Unity_Config_Factory::factory($this->_cacheDirectory, $this->_cacheDirectory);

        $this->assertType('Piece_Unity_Config', $config);
        $this->assertEquals('View', $config->getExtension('Controller', 'view'));
        $this->assertEquals('Dispatcher_Simple', $config->getExtension('Controller', 'dispatcher'));
        $this->assertEquals('../webapp/actions', $config->getConfiguration('Dispatcher_Continuation', 'actionDirectory'));
        $this->assertEquals('../webapp/cache', $config->getConfiguration('Dispatcher_Continuation', 'cacheDirectory'));

        $flowDefinitions = $config->getConfiguration('Dispatcher_Continuation', 'flowDefinitions');

        $this->assertEquals('Registration', $flowDefinitions[0]['name']);
        $this->assertEquals('../webapp/config/Registration.yaml', $flowDefinitions[0]['file']);
        $this->assertFalse($flowDefinitions[0]['isExclusive']);
        $this->assertEquals('../webapp/actions', $config->getConfiguration('Dispatcher_Simple', 'actionDirectory'));
        $this->assertEquals('../webapp/templates', $config->getConfiguration('Renderer_PHP', 'templateDirectory'));
        $this->assertEquals('Renderer_PHP', $config->getExtension('View', 'renderer'));
    }

    /**
     * @test
     * @since Method available since Release 1.2.0
     */
    public function createUniqueCacheIdsInOneCacheDirectory()
    {
        $oldDirectory = getcwd();
        chdir($this->_cacheDirectory . '/CacheIDsShouldBeUniqueInOneCacheDirectory1');
        Piece_Unity_Config_Factory::factory('.', $this->_cacheDirectory);

        $this->assertEquals(1, $this->_getCacheFileCount($this->_cacheDirectory));

        chdir($this->_cacheDirectory . '/CacheIDsShouldBeUniqueInOneCacheDirectory2');
        Piece_Unity_Config_Factory::factory('.', $this->_cacheDirectory);

        $this->assertEquals(2, $this->_getCacheFileCount($this->_cacheDirectory));

        chdir($oldDirectory);
    }

    /**#@-*/

    /**#@+
     * @access protected
     */

    /**#@-*/

    /**#@+
     * @access private
     */

    /**
     * @since Method available since Release 1.2.0
     */
    private function _getCacheFileCount($directory)
    {
        $cacheFileCount = 0;
        if ($dh = opendir($directory)) {
            while (true) {
                $file = readdir($dh);
                if ($file === false) {
                    break;
                }

                if (filetype($directory . '/' . $file) == 'file') {
                    if (preg_match('/^cache_.+/', $file)) {
                        ++$cacheFileCount;
                    }
                }
            }

            closedir($dh);
        }

        return $cacheFileCount;
    }

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
