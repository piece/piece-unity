<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * PHP version 5
 *
 * Copyright (c) 2007-2009 KUBO Atsuhiro <kubo@iteman.jp>,
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
 * @copyright  2007-2009 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @since      File available since Release 0.11.0
 */

require_once 'Piece/Right/Filter/Factory.php';
require_once 'Piece/Right/Validator/Factory.php';

// {{{ Piece_Unity_Plugin_Configurator_ValidationTest

/**
 * Some tests for Piece_Unity_Plugin_Configurator_Validation.
 *
 * @package    Piece_Unity
 * @copyright  2007-2009 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @since      Class available since Release 0.11.0
 */
class Piece_Unity_Plugin_Configurator_ValidationTest extends Piece_Unity_PHPUnit_TestCase
{

    // {{{ properties

    /**#@+
     * @access public
     */

    /**#@-*/

    /**#@+
     * @access protected
     */

    protected $serviceName = 'Piece_Unity_Plugin_Configurator_Validation';

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
        Piece_Right_Filter_Factory::clearInstances();
        Piece_Right_Validator_Factory::clearInstances();
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
    public function configure()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['login_name'] = 'iteman';
        $_POST['password'] = 'iteman30';
        $_POST['email'] = 'kubo@iteman.jp';
        $_POST['greeting'] = 'Hello World';

        $this->initializeContext();
        $validatorDirectory = dirname(__FILE__) . '/../../../..';
        $this->addExtension('configDirectory', $this->_exclusiveDirectory);
        $this->addExtension('cacheDirectory', $this->_exclusiveDirectory);
        $this->addExtension('validatorDirectories', $validatorDirectory);
        $this->addExtension('filterDirectories', $validatorDirectory);
        $this->addExtension('validatorPrefixes', __CLASS__);
        $this->addExtension('filterPrefixes', __CLASS__);
        $this->materializeFeature()->configure();

        $validation = $this->context->getValidation();
        $validationConfig = $validation->getConfiguration();
        $validationConfig->setRequired('email');
        $validationConfig->addValidation('email', 'Email');
        $validationConfig->setRequired('greeting');
        $validationConfig->addValidation('greeting', 'HelloWorld');
        $validationConfig->addFilter('greeting', 'LowerCase');

        $container = new stdClass();

        $this->assertTrue($validation->validate('Authentication', $container));
        $this->assertEquals($_POST['login_name'], $container->login_name);
        $this->assertEquals($_POST['password'], $container->password);
        $this->assertEquals($_POST['email'], $container->email);
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
