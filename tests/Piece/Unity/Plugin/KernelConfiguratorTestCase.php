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
 * @subpackage Piece_Unity_Plugin_KernelConfigurator
 * @copyright  2006-2007 KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    SVN: $Id$
 * @since      File available since Release 0.1.0
 */

require_once 'PHPUnit.php';
require_once 'Piece/Unity/Plugin/KernelConfigurator.php';
require_once 'Piece/Unity/Context.php';
require_once 'Piece/Unity/Config.php';
require_once 'Piece/Right/Filter/Factory.php';
require_once 'Piece/Right/Validator/Factory.php';
require_once 'Piece/Unity/Error.php';
require_once 'Piece/Unity/Plugin/Factory.php';
require_once 'Piece/Unity/URL.php';
require_once 'Cache/Lite/File.php';

// {{{ Piece_Unity_Plugin_KernelConfiguratorTestCase

/**
 * TestCase for Piece_Unity_Plugin_KernelConfigurator
 *
 * @package    Piece_Unity
 * @subpackage Piece_Unity_Plugin_KernelConfigurator
 * @copyright  2006-2007 KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    Release: @package_version@
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
        $cache = &new Cache_Lite_File(array('cacheDir' => dirname(__FILE__) . '/KernelConfiguratorTestCase/',
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
        $_SESSION = array();
        $class = 'Piece_Unity_Plugin_KernelConfiguratorTestCase_AutoloadClass';
        $oldIncludePath = set_include_path(dirname(__FILE__) . '/../../..' . PATH_SEPARATOR . get_include_path());
        $config = &new Piece_Unity_Config();
        $config->setConfiguration('KernelConfigurator', 'autoloadClasses', array($class));
        $context = &Piece_Unity_Context::singleton();
        $context->setConfiguration($config);

        $configurator = &new Piece_Unity_Plugin_KernelConfigurator();
        $configurator->invoke();
        $session = &$context->getSession();
        @$session->start();

        if (version_compare(phpversion(), '5.0.0', '<')) {
            $found = class_exists($class);
        } else {
            $found = class_exists($class, false);
        }

        $this->assertTrue($found);

        set_include_path($oldIncludePath);
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
        $config->setConfiguration('KernelConfigurator', 'pluginDirectories', array(dirname(__FILE__) . '/KernelConfiguratorTestCase'));
        $context = &Piece_Unity_Context::singleton();
        $context->setConfiguration($config);

        $configurator = &new Piece_Unity_Plugin_KernelConfigurator();
        $configurator->invoke();

        $fooPlugin = &Piece_Unity_Plugin_Factory::factory('KernelConfiguratorTestCase_Foo');

        $this->assertTrue(is_a($fooPlugin, 'Piece_Unity_Plugin_KernelConfiguratorTestCase_Foo'));

        $barPlugin = &Piece_Unity_Plugin_Factory::factory('KernelConfiguratorTestCase_Bar');

        $this->assertTrue(is_a($barPlugin, 'Piece_Unity_Plugin_KernelConfiguratorTestCase_Bar'));

        $fooPlugin->baz = 'qux';

        $plugin = &Piece_Unity_Plugin_Factory::factory('KernelConfiguratorTestCase_Foo');

        $this->assertTrue(array_key_exists('baz', $fooPlugin));

        Piece_Unity_Plugin_Factory::clearInstances();
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
        $config->setConfiguration('KernelConfigurator', 'validationConfigDirectory', dirname(__FILE__) . '/KernelConfiguratorTestCase');
        $config->setConfiguration('KernelConfigurator', 'validationCacheDirectory', dirname(__FILE__) . '/KernelConfiguratorTestCase');
        $config->setConfiguration('KernelConfigurator', 'validationValidatorDirectories', array(dirname(__FILE__) . '/KernelConfiguratorTestCase'));
        $config->setConfiguration('KernelConfigurator', 'validationFilterDirectories', array(dirname(__FILE__) . '/KernelConfiguratorTestCase'));
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

        Piece_Right_Filter_Factory::clearInstances();
        $GLOBALS['PIECE_RIGHT_Filter_Directories'] = $oldFilterDirectories;
        Piece_Right_Validator_Factory::clearInstances();
        $GLOBALS['PIECE_RIGHT_Validator_Directories'] = $oldValidatorDirectories;
        unset($_POST['greeting']);
        unset($_POST['email']);
        unset($_POST['password']);
        unset($_POST['login_name']);
        unset($_SERVER['REQUEST_METHOD']);
    }

    /**
     * @since Method available since Release 0.9.0
     */
    function testNonSSLableServers()
    {
        $_SERVER['SERVER_NAME'] = 'example.org';
        $_SERVER['SERVER_PORT'] = '80';
        $config = &new Piece_Unity_Config();
        $config->setConfiguration('KernelConfigurator', 'nonSSLableServers', array('example.org'));
        $context = &Piece_Unity_Context::singleton();
        $context->setConfiguration($config);

        $configurator = &new Piece_Unity_Plugin_KernelConfigurator();
        $configurator->invoke();

        $this->assertEquals('http://example.org/foo/bar/baz.php',
                            Piece_Unity_URL::createSSL('http://example.com/foo/bar/baz.php')
                            );
        $this->assertEquals('http://example.org/foo/bar/baz.php',
                            Piece_Unity_URL::createSSL('/foo/bar/baz.php')
                            );

        $_SERVER['SERVER_PORT'] = '443';

        $this->assertEquals('http://example.org/foo/bar/baz.php',
                            Piece_Unity_URL::create('https://example.com/foo/bar/baz.php')
                            );
        $this->assertEquals('http://example.org/foo/bar/baz.php',
                            Piece_Unity_URL::create('/foo/bar/baz.php')
                            );

        Piece_Unity_URL::clearNonSSLableServers();
        unset($_SERVER['SERVER_PORT']);
        unset($_SERVER['SERVER_NAME']);
    }

    /**
     * @since Method available since Release 0.11.0
     */
    function testPluginPrefixes()
    {
        $oldPluginPrefixes = $GLOBALS['PIECE_UNITY_Plugin_Prefixes'];
        $oldPluginDirectories = $GLOBALS['PIECE_UNITY_Plugin_Directories'];
        $config = &new Piece_Unity_Config();
        $config->setConfiguration('KernelConfigurator', 'pluginPrefixes', array('KernelConfiguratorTestCaseAlias'));
        $config->setConfiguration('KernelConfigurator', 'pluginDirectories', array(dirname(__FILE__) . '/KernelConfiguratorTestCase'));
        $context = &Piece_Unity_Context::singleton();
        $context->setConfiguration($config);

        $configurator = &new Piece_Unity_Plugin_KernelConfigurator();
        $configurator->invoke();

        $foo = &Piece_Unity_Plugin_Factory::factory('FooPlugin');

        $this->assertTrue(is_object($foo));
        $this->assertTrue(is_a($foo, 'KernelConfiguratorTestCaseAlias_FooPlugin'));

        Piece_Unity_Plugin_Factory::clearInstances();
        $GLOBALS['PIECE_UNITY_Plugin_Directories'] = $oldPluginDirectories;
        $GLOBALS['PIECE_UNITY_Plugin_Prefixes'] = $oldPluginPrefixes;
    }

    /**
     * @since Method available since Release 0.11.0
     */
    function testValidatorPrefixes()
    {
        $oldValidatorPrefixes = $GLOBALS['PIECE_RIGHT_Validator_Prefixes'];
        $oldValidatorDirectories = $GLOBALS['PIECE_RIGHT_Validator_Directories'];
        $config = &new Piece_Unity_Config();
        $config->setConfiguration('KernelConfigurator', 'validationValidatorPrefixes', array('KernelConfiguratorTestCaseAlias'));
        $config->setConfiguration('KernelConfigurator', 'validationValidatorDirectories', array(dirname(__FILE__) . '/KernelConfiguratorTestCase'));
        $context = &Piece_Unity_Context::singleton();
        $context->setConfiguration($config);

        $configurator = &new Piece_Unity_Plugin_KernelConfigurator();
        $configurator->invoke();

        $foo = &Piece_Right_Validator_Factory::factory('FooValidator');

        $this->assertTrue(is_object($foo));
        $this->assertTrue(is_a($foo, 'KernelConfiguratorTestCaseAlias_FooValidator'));

        Piece_Right_Validator_Factory::clearInstances();
        $GLOBALS['PIECE_RIGHT_Validator_Directories'] = $oldValidatorDirectories;
        $GLOBALS['PIECE_RIGHT_Validator_Prefixes'] = $oldValidatorPrefixes;
    }

    /**
     * @since Method available since Release 0.11.0
     */
    function testFilterPrefixes()
    {
        $oldFilterPrefixes = $GLOBALS['PIECE_RIGHT_Filter_Prefixes'];
        $oldFilterDirectories = $GLOBALS['PIECE_RIGHT_Filter_Directories'];
        $config = &new Piece_Unity_Config();
        $config->setConfiguration('KernelConfigurator', 'validationFilterPrefixes', array('KernelConfiguratorTestCaseAlias'));
        $config->setConfiguration('KernelConfigurator', 'validationFilterDirectories', array(dirname(__FILE__) . '/KernelConfiguratorTestCase'));
        $context = &Piece_Unity_Context::singleton();
        $context->setConfiguration($config);

        $configurator = &new Piece_Unity_Plugin_KernelConfigurator();
        $configurator->invoke();

        $foo = &Piece_Right_Filter_Factory::factory('FooFilter');

        $this->assertTrue(is_object($foo));
        $this->assertTrue(is_a($foo, 'KernelConfiguratorTestCaseAlias_FooFilter'));

        Piece_Right_Filter_Factory::clearInstances();
        $GLOBALS['PIECE_RIGHT_Filter_Directories'] = $oldFilterDirectories;
        $GLOBALS['PIECE_RIGHT_Filter_Prefixes'] = $oldFilterPrefixes;
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
