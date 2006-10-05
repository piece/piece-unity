<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * PHP versions 4 and 5
 *
 * Copyright (c) 2006 KUBO Atsuhiro <iteman@users.sourceforge.net>,
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
 * @author     KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @copyright  2006 KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    SVN: $Id$
 * @link       http://piece-framework.com/piece-unity/
 * @see        Piece_Unity_Plugin_KernelConfigurator
 * @since      File available since Release 0.1.0
 */

require_once 'PHPUnit.php';
require_once 'Piece/Unity/Plugin/KernelConfigurator.php';
require_once 'Piece/Unity/Context.php';
require_once 'Piece/Unity/Config.php';
require_once 'Piece/Unity/Plugin/Dispatcher/Simple.php';

// {{{ Piece_Unity_Plugin_KernelConfiguratorTestCase

/**
 * TestCase for Piece_Unity_Plugin_KernelConfigurator
 *
 * @package    Piece_Unity
 * @author     KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @copyright  2006 KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    Release: @package_version@
 * @link       http://piece-framework.com/piece-unity/
 * @see        Piece_Unity_Plugin_KernelConfigurator
 * @since      Class available since Release 0.1.0
 */
class Piece_Unity_Plugin_KernelConfiguratorTestCase extends PHPUnit_TestCase
{

    // {{{ properties

    /**#@+
     * @access public
     */

    /**#@-*/

    /**#@+
     * @access private
     */

    /**#@-*/

    /**#@+
     * @access public
     */

    function setUp()
    {
        Piece_Unity_Error::pushCallback(create_function('$error', 'var_dump($error); return ' . PEAR_ERRORSTACK_DIE . ';'));
    }

    function tearDown()
    {
        $cache = &new Cache_Lite_File(array('cacheDir' => dirname(__FILE__) . '/',
                                            'masterFile' => '',
                                            'automaticSerialization' => true,
                                            'errorHandlingAPIBreak' => true)
                                      );
        $cache->clean();
        Piece_Unity_Context::clear();
        Piece_Unity_Error::clearErrors();
        Piece_Unity_Error::popCallback();
    }

    function testSettingAutoloadClasses()
    {
        $class = 'Piece_Unity_Plugin_AutoloadClass';
        $oldIncludePath = ini_get('include_path');
        ini_set('include_path',
                dirname(__FILE__) . '/../../..' . PATH_SEPARATOR .
                $oldIncludePath
                );
        $config = &new Piece_Unity_Config();
        $config->setConfiguration('KernelConfigurator', 'autoloadClasses', array($class));
        $context = &Piece_Unity_Context::singleton();
        $context->setConfiguration($config);

        $configurator = &new Piece_Unity_Plugin_KernelConfigurator();
        $configurator->invoke();
        $session = &$context->getSession();
        $session->start();

        if (version_compare(phpversion(), '5.0.0', '<')) {
            $found = class_exists($class);
        } else {
            $found = class_exists($class, false);
        }

        $this->assertTrue($found);

        ini_set('include_path', $oldIncludePath);
        unset($_SESSION);
    }

    function testEventNameFixation()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_GET['_event'] = 'foo';

        $config = &new Piece_Unity_Config();
        $config->setConfiguration('KernelConfigurator', 'eventName', 'bar');
        $context = &Piece_Unity_Context::singleton();
        $context->setConfiguration($config);

        $configurator = &new Piece_Unity_Plugin_KernelConfigurator();
        $configurator->invoke();

        $this->assertEquals('bar', $context->getEventName());

        unset($_GET['_event']);
        unset($_SERVER['REQUEST_METHOD']);
    }

    function testSettingEventNameKey()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_GET['_foo'] = 'bar';

        $config = &new Piece_Unity_Config();
        $config->setConfiguration('KernelConfigurator', 'eventNameKey', '_foo');
        $context = &Piece_Unity_Context::singleton();
        $context->setConfiguration($config);

        $configurator = &new Piece_Unity_Plugin_KernelConfigurator();
        $configurator->invoke();

        $this->assertEquals('bar', $context->getEventName());

        unset($_GET['_foo']);
        unset($_SERVER['REQUEST_METHOD']);
    }

    function testImportingPathInfo()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['PATH_INFO'] = '/foo/bar/bar/baz/qux';

        $config = &new Piece_Unity_Config();
        $config->setConfiguration('KernelConfigurator', 'importPathInfo', true);
        $context = &Piece_Unity_Context::singleton();
        $context->setConfiguration($config);

        $configurator = &new Piece_Unity_Plugin_KernelConfigurator();
        $configurator->invoke();
        $request = &$context->getRequest();

        $this->assertEquals('bar', $request->getParameter('foo'));
        $this->assertEquals('baz', $request->getParameter('bar'));
        $this->assertNull($request->getParameter('qux'));

        unset($_SERVER['PATH_INFO']);
        unset($_SERVER['REQUEST_METHOD']);
    }

    function testSettingPluginDirectories()
    {
        $oldPluginDirectories = $GLOBALS['PIECE_UNITY_Plugin_Directories'];
        $config = &new Piece_Unity_Config();
        $config->setConfiguration('KernelConfigurator', 'pluginDirectories', array(dirname(__FILE__) . '/../../..'));
        $context = &Piece_Unity_Context::singleton();
        $context->setConfiguration($config);

        $configurator = &new Piece_Unity_Plugin_KernelConfigurator();
        $configurator->invoke();

        $fooPlugin = &Piece_Unity_Plugin_Factory::factory('Foo');

        $this->assertTrue(is_a($fooPlugin, 'Piece_Unity_Plugin_Foo'));

        $barPlugin = &Piece_Unity_Plugin_Factory::factory('Bar');

        $this->assertTrue(is_a($barPlugin, 'Piece_Unity_Plugin_Bar'));

        $fooPlugin->baz = 'qux';

        $plugin = &Piece_Unity_Plugin_Factory::factory('Foo');

        $this->assertTrue(array_key_exists('baz', $fooPlugin));

        $GLOBALS['PIECE_UNITY_Plugin_Instances'] = array();
        $GLOBALS['PIECE_UNITY_Plugin_Directories'] = $oldPluginDirectories;
    }

    /**
     * @since Method available since Release 0.7.0
     */
    function testSettingTwoDirectoriesForValidation()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['login_name'] = 'iteman';
        $_POST['password'] = 'iteman30';
        $_POST['email'] = 'iteman@users.sourceforge.net';
        $_POST['greeting'] = 'Hello World';
        $oldValidatorDirectories = $GLOBALS['PIECE_RIGHT_Validator_Directories'];
        $oldFilterDirectories = $GLOBALS['PIECE_RIGHT_Filter_Directories'];

        $config = &new Piece_Unity_Config();
        $config->setConfiguration('KernelConfigurator', 'validationConfigDirectory', dirname(__FILE__) . '/..');
        $config->setConfiguration('KernelConfigurator', 'validationCacheDirectory', dirname(__FILE__));
        $config->setConfiguration('KernelConfigurator', 'validationValidatorDirectories', array(dirname(__FILE__) . '/KernelConfigurator'));
        $config->setConfiguration('KernelConfigurator', 'validationFilterDirectories', array(dirname(__FILE__) . '/KernelConfigurator'));
        $context = &Piece_Unity_Context::singleton();
        $context->setConfiguration($config);

        $configurator = &new Piece_Unity_Plugin_KernelConfigurator();
        $configurator->invoke();

        $validation = &$context->getValidation();
        $validationConfig = &$validation->getConfiguration();
        $validationConfig->setRequired('email');
        $validationConfig->addValidation('email', 'Email');
        $validationConfig->setRequired('greeting');
        $validationConfig->addValidation('greeting', 'HelloWorld');
        $validationConfig->addFilter('greeting', 'LowerCase');

        $container = &new stdClass();

        $this->assertTrue($validation->validate('Authentication', $container));
        $this->assertEquals($_POST['login_name'], $container->login_name);
        $this->assertEquals($_POST['password'], $container->password);
        $this->assertEquals($_POST['email'], $container->email);

        $GLOBALS['PIECE_RIGHT_Filter_Instances'] = array();
        $GLOBALS['PIECE_RIGHT_Filter_Directories'] = $oldValidatorDirectories;
        $GLOBALS['PIECE_RIGHT_Validator_Instances'] = array();
        $GLOBALS['PIECE_RIGHT_Validator_Directories'] = $oldValidatorDirectories;
        unset($_POST['greeting']);
        unset($_POST['email']);
        unset($_POST['password']);
        unset($_POST['login_name']);
        unset($_SERVER['REQUEST_METHOD']);
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
