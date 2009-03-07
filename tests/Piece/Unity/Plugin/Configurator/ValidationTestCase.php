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
 * @since      File available since Release 0.11.0
 */

require_once realpath(dirname(__FILE__) . '/../../../../prepare.php');
require_once 'PHPUnit.php';
require_once 'Piece/Unity/Plugin/Configurator/Validation.php';
require_once 'Piece/Unity/Context.php';
require_once 'Piece/Unity/Config.php';
require_once 'Piece/Right/Filter/Factory.php';
require_once 'Piece/Right/Validator/Factory.php';
require_once 'Piece/Unity/Error.php';
require_once 'Cache/Lite/File.php';

// {{{ Piece_Unity_Plugin_Configurator_ValidationTestCase

/**
 * Some tests for Piece_Unity_Plugin_Configurator_Validation.
 *
 * @package    Piece_Unity
 * @copyright  2007-2008 KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    Release: @package_version@
 * @since      Class available since Release 0.11.0
 */
class Piece_Unity_Plugin_Configurator_ValidationTestCase extends PHPUnit_TestCase
{

    // {{{ properties

    /**#@+
     * @access public
     */

    /**#@-*/

    /**#@+
     * @access private
     */

    var $_oldValidatorPrefixes;
    var $_oldValidatorDirectories;
    var $_oldFilterDirectories;

    /**#@-*/

    /**#@+
     * @access public
     */

    function setUp()
    {
        $this->_oldValidatorPrefixes = $GLOBALS['PIECE_RIGHT_Validator_Prefixes'];
        $this->_oldValidatorDirectories = $GLOBALS['PIECE_RIGHT_Validator_Directories'];
        $this->_oldFilterDirectories = $GLOBALS['PIECE_RIGHT_Filter_Directories'];
    }

    function tearDown()
    {
        Piece_Right_Filter_Factory::clearInstances();
        $GLOBALS['PIECE_RIGHT_Filter_Directories'] = $this->_oldFilterDirectories;
        Piece_Right_Validator_Factory::clearInstances();
        $GLOBALS['PIECE_RIGHT_Validator_Directories'] = $this->_oldValidatorDirectories;
        $GLOBALS['PIECE_RIGHT_Validator_Prefixes'] = $this->_oldValidatorPrefixes;
        $cache = &new Cache_Lite_File(array('cacheDir' => dirname(__FILE__) . '/ValidationTestCase/',
                                            'masterFile' => '',
                                            'automaticSerialization' => true,
                                            'errorHandlingAPIBreak' => true)
                                      );
        $cache->clean();
        Piece_Unity_Context::clear();
        Piece_Unity_Error::clearErrors();
    }

    function testConfigure()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['login_name'] = 'iteman';
        $_POST['password'] = 'iteman30';
        $_POST['email'] = 'iteman@users.sourceforge.net';
        $_POST['greeting'] = 'Hello World';

        $config = &new Piece_Unity_Config();
        $config->setConfiguration('Configurator_Validation', 'configDirectory', dirname(__FILE__) . '/ValidationTestCase');
        $config->setConfiguration('Configurator_Validation', 'cacheDirectory', dirname(__FILE__) . '/ValidationTestCase');
        $config->setConfiguration('Configurator_Validation', 'validatorDirectories', array(dirname(__FILE__) . '/ValidationTestCase'));
        $config->setConfiguration('Configurator_Validation', 'filterDirectories', array(dirname(__FILE__) . '/ValidationTestCase'));
        $config->setConfiguration('Configurator_Validation', 'validatorPrefixes', array('ValidationTestCaseAlias'));
        $config->setConfiguration('Configurator_Validation', 'filterPrefixes', array('ValidationTestCaseAlias'));
        $context = &Piece_Unity_Context::singleton();
        $context->setConfiguration($config);

        $configurator = &new Piece_Unity_Plugin_Configurator_Validation();
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
