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

// {{{ Piece_UnityTest

/**
 * Some tests for Piece_Unity.
 *
 * @package    Piece_Unity
 * @copyright  2006-2009 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @since      Class available since Release 0.1.0
 */
class Piece_UnityTest extends Piece_Unity_PHPUnit_TestCase
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

    private $_exclusiveDirectory;

    /**#@-*/

    /**#@+
     * @access public
     */

    public function setUp()
    {
        parent::setUp();
        $this->_exclusiveDirectory = dirname(__FILE__) . '/' . basename(__FILE__, '.php');
    }

    public function tearDown()
    {
        $cache = new Cache_Lite_File(array('cacheDir' => $this->_exclusiveDirectory . '/',
                                           'masterFile' => '',
                                           'automaticSerialization' => true,
                                           'errorHandlingAPIBreak' => true)
                                     );
        $cache->clean();
    }

    /**
     * @test
     */
    public function configureTheRuntimeByTheConstructorWithAConfigurationObject()
    {
        $dynamicConfig = new Piece_Unity_Config();
        $dynamicConfig->setExtension(Piece_Unity::ROOT_PLUGIN, 'renderer', 'Foo');
        $dynamicConfig->setConfiguration(Piece_Unity::ROOT_PLUGIN, 'Bar', 'Baz');
        $unity = new Piece_Unity(dirname(__FILE__) . '/../../data',
                                 $this->_exclusiveDirectory,
                                 $dynamicConfig
                                 );
        $config = Piece_Unity_Context::singleton()->getConfiguration();

        $this->assertEquals('Foo',
                            $config->getExtensionDefinition(Piece_Unity::ROOT_PLUGIN, 'renderer')
                            );
        $this->assertEquals('Baz',
                            $config->getConfigurationDefinition(Piece_Unity::ROOT_PLUGIN, 'Bar')
                            );

        $context = Piece_Unity_Context::singleton();
        $masterFile = realpath(dirname(__FILE__) . '/../../data/piece-unity-config.yaml');
        $cache = new Cache_Lite_File(array('cacheDir' => $this->_exclusiveDirectory . '/',
                                           'masterFile' => $masterFile,
                                           'automaticSerialization' => true)
                                     );
        $config = $cache->get($masterFile);

        $this->assertType('Piece_Unity_Config', $config);
    }

    /**
     * @test
     */
    public function configureTheRuntimeByTheConstructor()
    {
        $unity = new Piece_Unity(dirname(__FILE__) . '/../../data',
                                 $this->_exclusiveDirectory
                                 );

        $this->assertType('Piece_Unity_Config',
                          Piece_Unity_Context::singleton()->getConfiguration()
                          );
    }

    /**
     * @test
     */
    public function dispatchTheRequestToTheController()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_GET['_event'] = 'foo';

        $unity = new Piece_Unity(dirname(__FILE__) . '/../../data',
                                 $this->_exclusiveDirectory
                                 );
        $unity->setExtension('controller', 'dispatcher', 'Dispatcher_Simple');
        $unity->setConfiguration('Renderer_PHP',
                                 'templateDirectory',
                                 $this->_exclusiveDirectory
                                 );
        ob_start();
        @$unity->dispatch();
        ob_end_clean();

        $this->assertEquals('foo', Piece_Unity_Context::singleton()->getView());
    }

    /**
     * @test
     * @since Method available since Release 1.1.0
     */
    public function configureTheRuntimeByMethod()
    {
        $unity = new Piece_Unity();
        $unity->configure();
        $unity->setConfiguration(Piece_Unity::ROOT_PLUGIN, 'Bar', 'Baz');
        $unity->setExtension(Piece_Unity::ROOT_PLUGIN, 'renderer', 'Foo');
        $config = Piece_Unity_Context::singleton()->getConfiguration();

        $this->assertEquals('Baz', $config->getConfigurationDefinition(Piece_Unity::ROOT_PLUGIN, 'Bar'));
        $this->assertEquals('Foo', $config->getExtensionDefinition(Piece_Unity::ROOT_PLUGIN, 'renderer'));
    }

    /**
     * @test
     * @since Method available since Release 1.5.0
     */
    public function createTheRuntime()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_GET['_event'] = 'foo';
        ob_start();
        @Piece_Unity::createRuntime(array($this, 'configureRuntime'))->dispatch();
        ob_end_clean();

        $this->assertEquals('foo', Piece_Unity_Context::singleton()->getView());
    }

    /**
     * @since Method available since Release 1.5.0
     */
    public function configureRuntime($runtime)
    {
        $runtime->configure(dirname(__FILE__) . '/../../data',
                            $this->_exclusiveDirectory
                            );
        $runtime->setExtension('controller', 'dispatcher', 'Dispatcher_Simple');
        $runtime->setConfiguration('Renderer_PHP',
                                   'templateDirectory',
                                   $this->_exclusiveDirectory
                                   );
    }

    /**
     * @test
     * @expectedException Piece_Unity_Exception
     * @since Method available since Release 1.5.0
     */
    public function raiseAnExceptionIfTheDispatchMethodIsCalledBeforeConfiguration()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_GET['_event'] = 'foo';
        $unity = new Piece_Unity();
        $unity->dispatch();
    }

    /**
     * @test
     * @expectedException Piece_Unity_Exception
     * @since Method available since Release 1.5.0
     */
    public function raiseAnExceptionIfTheSetconfigurationMethodIsCalledBeforeConfiguration()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_GET['_event'] = 'foo';
        $unity = new Piece_Unity();
        $unity->setConfiguration(Piece_Unity::ROOT_PLUGIN, 'Bar', 'Baz');
    }

    /**
     * @test
     * @expectedException Piece_Unity_Exception
     * @since Method available since Release 1.5.0
     */
    public function raiseAnExceptionIfTheSetextensionMethodIsCalledBeforeConfiguration()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_GET['_event'] = 'foo';
        $unity = new Piece_Unity();
        $unity->setExtension(Piece_Unity::ROOT_PLUGIN, 'renderer', 'Foo');
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
