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
 * @since      File available since Release 0.7.0
 */

require_once 'Piece/Right/Validator/Factory.php';
require_once 'Piece/Right/Config/Factory.php';
require_once 'Piece/Right/Env.php';

// {{{ Piece_Unity_ValidationTest

/**
 * Some tests for Piece_Unity_Validation.
 *
 * @package    Piece_Unity
 * @copyright  2006-2009 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @since      Class available since Release 0.7.0
 */
class Piece_Unity_ValidationTest extends Piece_Unity_PHPUnit_TestCase
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

    /**#@-*/

    /**#@+
     * @access public
     */

    public function setUp()
    {
        parent::setUp();
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $this->cacheDirectory = dirname(__FILE__) . '/' . basename(__FILE__, '.php');
    }

    /**
     * @test
     */
    public function validateTheInputData()
    {
        $_POST['login_name'] = 'iteman';
        $_POST['password'] = 'iteman30';
        $_POST['email'] = 'kubo@iteman.jp';
        $this->initializeContext();

        $validation = new Piece_Unity_Validation();
        $validation->setConfigDirectory($this->cacheDirectory);
        $validation->setCacheDirectory($this->cacheDirectory);
        $config = $validation->getConfiguration();
        $config->setRequired('email');
        $config->addValidation('email', 'Email');

        $container = new stdClass();

        $this->assertTrue($validation->validate('Authentication', $container));
        $this->assertEquals($_POST['login_name'], $container->login_name);
        $this->assertEquals($_POST['password'], $container->password);
        $this->assertEquals($_POST['email'], $container->email);
        $this->assertType('Piece_Right_Results', $validation->getResults());
    }

    /**
     * @test
     * @expectedException Stagehand_LegacyError_PEARErrorStack_Exception
     */
    public function passThroughAnExceptionRaisedByPieceRight()
    {
        $_POST['foo'] = 'bar';
        $this->initializeContext();

        $validation = new Piece_Unity_Validation();
        $config = $validation->getConfiguration();
        $config->setRequired('foo');
        $config->addValidation('foo', 'NonExistingValidator');

        $container = new stdClass();
        $validation->validate(null, $container);
    }

    /**
     * @test
     */
    public function notKeepOriginalFieldValue()
    {
        $_POST['login_name'] = ' iteman ';
        $_POST['password'] = 'itema';
        $_POST['email'] = 'kubo@iteman.jp';
        $this->initializeContext();

        $validation = new Piece_Unity_Validation();
        $validation->setConfigDirectory($this->cacheDirectory);
        $validation->setCacheDirectory($this->cacheDirectory);
        $config = $validation->getConfiguration();
        $config->setRequired('email');
        $config->addValidation('email', 'Email');
        $config->addFilter('login_name', 'trim');

        $container = new stdClass();

        $this->assertFalse($validation->validate('Authentication', $container, false));
        $this->assertEquals(trim($_POST['login_name']), $container->login_name);
        $this->assertEquals($_POST['password'], $container->password);
        $this->assertEquals($_POST['email'], $container->email);
    }

    /**
     * @test
     * @since Method available since Release 0.9.0
     */
    public function passTheContextObjectToValidators()
    {
        $_POST['foo'] = 'bar';
        $this->initializeContext();

        $validation = new Piece_Unity_Validation();
        $validation->setConfigDirectory($this->cacheDirectory);
        $validation->setCacheDirectory($this->cacheDirectory);
        $validation->addValidatorDirectory($this->cacheDirectory);
        $config = $validation->getConfiguration();
        $config->setRequired('foo');
        $config->addValidation('foo', 'PayloadTest');
        $container = new stdClass();

        $this->assertTrue($validation->validate(null, $container));
        $this->assertEquals($_POST['foo'], $container->foo);

        $this->assertTrue($this->context->hasAttribute('bar'));
        $this->assertEquals('baz', $this->context->getAttribute('bar'));
    }

    /**
     * @test
     * @since Method available since Release 0.9.0
     */
    public function canUploadAFile()
    {
        $size = filesize(__FILE__);
        $_FILES['userfile'] = array('tmp_name' => __FILE__,
                                    'name'     => __FILE__,
                                    'size'     => $size,
                                    'type'     => 'text/plain',
                                    'error'    => 0
                                    );
        $this->initializeContext();

        $validation = new Piece_Unity_Validation();
        $validation->setConfigDirectory($this->cacheDirectory);
        $validation->setCacheDirectory($this->cacheDirectory);
        $config = $validation->getConfiguration();
        $config->setRequired('userfile');
        $config->addValidation('userfile',
                               'File',
                               array('maxSize'  => $size,
                                     'minSize'  => $size,
                                     'mimetype' => 'text')
                               );
        $container = new stdClass();

        $this->assertTrue($validation->validate(null, $container));
    }

    /**
     * @test
     * @since Method available since Release 0.9.0
     */
    public function canUploadMultipleFiles()
    {
        $size = filesize(__FILE__);
        $_SERVER['REQUEST_METHOD'] = 'POST';
        for ($i = 0; $i < 5; ++$i) {
            $_FILES['userfile']['tmp_name'][$i] = __FILE__;
            $_FILES['userfile']['name'][$i] = __FILE__;
            $_FILES['userfile']['type'][$i] = 'text/plain';
            $_FILES['userfile']['size'][$i] = $size;
            $_FILES['userfile']['error'][$i] = 0;
        }
        $this->initializeContext();

        $validation = new Piece_Unity_Validation();
        $validation->setConfigDirectory($this->cacheDirectory);
        $validation->setCacheDirectory($this->cacheDirectory);
        $config = $validation->getConfiguration();
        $config->setRequired('userfile');
        $config->addValidation('userfile',
                               'File',
                               array('maxSize'  => $size,
                                     'minSize'  => $size,
                                     'mimetype' => 'text')
                               );
        $container = new stdClass();

        $this->assertTrue($validation->validate(null, $container));
    }

    /**
     * @test
     * @since Method available since Release 1.3.0
     */
    public function mergeAConfigurationByAConfigurationFileIntoTheExistingConfiguration()
    {
        $_POST['foo'] = '1';
        $_POST['bar'] = '2';
        $_POST['baz'] = '3';
        $_POST['qux'] = '4';
        $this->initializeContext();

        $validation = new Piece_Unity_Validation();
        $validation->setConfigDirectory($this->cacheDirectory);
        $validation->setCacheDirectory($this->cacheDirectory);
        $validation->mergeValidationSet('MergeValidationSetShouldMergeConfigurationFileIntoConfiguration2');
        $validation->mergeValidationSet('MergeValidationSetShouldMergeConfigurationFileIntoConfiguration3');
        $container = new stdClass();
        $result = $validation->validate('MergeValidationSetShouldMergeConfigurationFileIntoConfiguration1', $container);
        $properties = get_object_vars($container);

        $this->assertTrue($result);
        $this->assertEquals(4, count(array_keys($properties)));
        $this->assertObjectHasAttribute('foo', $container);
        $this->assertObjectHasAttribute('bar', $container);
        $this->assertObjectHasAttribute('baz', $container);
        $this->assertObjectHasAttribute('qux', $container);
        $this->assertEquals('1', $container->foo);
        $this->assertEquals('2', $container->bar);
        $this->assertEquals('3', $container->baz);
        $this->assertEquals('4', $container->qux);

        $fieldNames = $validation->getFieldNames('MergeValidationSetShouldMergeConfigurationFileIntoConfiguration1');

        $this->assertEquals(4, count($fieldNames));
        $this->assertContains('foo', $fieldNames);
        $this->assertContains('bar', $fieldNames);
        $this->assertContains('baz', $fieldNames);
        $this->assertContains('qux', $fieldNames);
    }

    /**
     * @test
     * @since Method available since Release 1.3.0
     */
    public function useATemplate()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['firstName'] = ' Foo ';
        $_POST['lastName'] = 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa';
        $this->initializeContext();

        $validation = new Piece_Unity_Validation();
        $validation->setConfigDirectory($this->cacheDirectory);
        $validation->setCacheDirectory($this->cacheDirectory);
        $validation->setTemplate('Common');
        $container = new stdClass();
        $result = $validation->validate('TemplateShouldBeUsedIfFileIsSetAndBasedOnElementIsSpecified', $container);
        $results = $validation->getResults();

        $this->assertFalse($result);
        $this->assertEquals(1, $results->countErrors());
        $this->assertContains('firstName', $results->getValidFields());
        $this->assertContains('lastName', $results->getErrorFields());
        $this->assertEquals('Foo', $results->getFieldValue('firstName'));
        $this->assertEquals('The length of Last Name must be less than 255 characters', $results->getErrorMessage('lastName'));
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
