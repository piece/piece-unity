<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * PHP versions 4 and 5
 *
 * Copyright (c) 2006-2008 KUBO Atsuhiro <iteman@users.sourceforge.net>,
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
 * @copyright  2006-2008 KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    SVN: $Id$
 * @since      File available since Release 0.7.0
 */

require_once realpath(dirname(__FILE__) . '/../../prepare.php');
require_once 'PHPUnit.php';
require_once 'Piece/Unity/Validation.php';
require_once 'Piece/Unity/Error.php';
require_once 'Cache/Lite/File.php';

// {{{ Piece_Unity_ValidationTestCase

/**
 * Some tests for Piece_Unity_Validation.
 *
 * @package    Piece_Unity
 * @copyright  2006-2008 KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    Release: @package_version@
 * @since      Class available since Release 0.7.0
 */
class Piece_Unity_ValidationTestCase extends PHPUnit_TestCase
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
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $this->_cacheDirectory = dirname(__FILE__) . '/' . basename(__FILE__, '.php');
    }

    function tearDown()
    {
        foreach (array_keys($_POST) as $field) {
            unset($_POST[$field]);
        }
        unset($_SERVER['REQUEST_METHOD']);
        $cache = &new Cache_Lite_File(array('cacheDir' => "{$this->_cacheDirectory}/",
                                            'masterFile' => '',
                                            'automaticSerialization' => true,
                                            'errorHandlingAPIBreak' => true)
                                      );
        $cache->clean();
        Piece_Unity_Context::clear();
        Piece_Unity_Error::clearErrors();
    }

    function testValidationSuccess()
    {
        $_POST['login_name'] = 'iteman';
        $_POST['password'] = 'iteman30';
        $_POST['email'] = 'iteman@users.sourceforge.net';

        $validation = &new Piece_Unity_Validation();
        $validation->setConfigDirectory($this->_cacheDirectory);
        $validation->setCacheDirectory($this->_cacheDirectory);
        $config = &$validation->getConfiguration();
        $config->setRequired('email');
        $config->addValidation('email', 'Email');

        $container = &new stdClass();

        $this->assertTrue($validation->validate('Authentication', $container));
        $this->assertEquals($_POST['login_name'], $container->login_name);
        $this->assertEquals($_POST['password'], $container->password);
        $this->assertEquals($_POST['email'], $container->email);
        $this->assertTrue(is_a($validation->getResults(), 'Piece_Right_Results'));
    }

    function testValidationFailure()
    {
        $_POST['foo'] = 'bar';

        $validation = &new Piece_Unity_Validation();
        $config = &$validation->getConfiguration();
        $config->setRequired('foo');
        $config->addValidation('foo', 'NonExistingValidator');

        $container = &new stdClass();
        Piece_Unity_Error::disableCallback();
        $validation->validate(null, $container);
        Piece_Unity_Error::enableCallback();

        $this->assertTrue(Piece_Unity_Error::hasErrors());

        $error = Piece_Unity_Error::pop();

        $this->assertEquals(PIECE_UNITY_ERROR_INVOCATION_FAILED, $error['code']);
        $this->assertEquals(PIECE_RIGHT_ERROR_NOT_FOUND, $error['repackage']['code']);
    }

    function testNotKeepOriginalFieldValue()
    {
        $_POST['login_name'] = ' iteman ';
        $_POST['password'] = 'itema';
        $_POST['email'] = 'iteman@users.sourceforge.net';

        $validation = &new Piece_Unity_Validation();
        $validation->setConfigDirectory($this->_cacheDirectory);
        $validation->setCacheDirectory($this->_cacheDirectory);
        $config = &$validation->getConfiguration();
        $config->setRequired('email');
        $config->addValidation('email', 'Email');
        $config->addFilter('login_name', 'trim');

        $container = &new stdClass();

        $this->assertFalse($validation->validate('Authentication', $container, false));
        $this->assertEquals(trim($_POST['login_name']), $container->login_name);
        $this->assertEquals($_POST['password'], $container->password);
        $this->assertEquals($_POST['email'], $container->email);
    }

    /**
     * @since Method available since Release 0.9.0
     */
    function testResultsByReference()
    {
        $_POST['foo'] = 'bar';

        $validation = &new Piece_Unity_Validation();
        $validation->setConfigDirectory($this->_cacheDirectory);
        $validation->setCacheDirectory($this->_cacheDirectory);
        $config = &$validation->getConfiguration();
        $config->setRequired('foo');
        $container = &new stdClass();

        $this->assertTrue($validation->validate(null, $container));
        $this->assertEquals($_POST['foo'], $container->foo);

        $results = &$validation->getResults();
        $results->bar = 'baz';

        $this->assertTrue(is_a($results, 'Piece_Right_Results'));

        $context = &Piece_Unity_Context::singleton();
        $viewElement = &$context->getViewElement();
        $resultsViaViewElement = &$viewElement->getElement('__results');

        $this->assertTrue(array_key_exists('bar', $resultsViaViewElement));
        $this->assertEquals($results->bar, $resultsViaViewElement->bar);
    }

    /**
     * @since Method available since Release 0.9.0
     */
    function testPayload()
    {
        $_POST['foo'] = 'bar';

        $validation = &new Piece_Unity_Validation();
        $validation->setConfigDirectory($this->_cacheDirectory);
        $validation->setCacheDirectory($this->_cacheDirectory);
        $validation->addValidatorDirectory($this->_cacheDirectory);
        $config = &$validation->getConfiguration();
        $config->setRequired('foo');
        $config->addValidation('foo', 'PayloadTest');
        $container = &new stdClass();

        $this->assertTrue($validation->validate(null, $container));
        $this->assertEquals($_POST['foo'], $container->foo);

        $context = &Piece_Unity_Context::singleton();

        $this->assertTrue($context->hasAttribute('bar'));
        $this->assertEquals('baz', $context->getAttribute('bar'));
    }

    /**
     * @since Method available since Release 0.9.0
     */
    function testSingleFileUpload()
    {
        $size = filesize(__FILE__);
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_FILES['userfile'] = array('tmp_name' => __FILE__,
                                    'name'     => __FILE__,
                                    'size'     => $size,
                                    'type'     => 'text/plain',
                                    'error'    => 0
                                    );

        $validation = &new Piece_Unity_Validation();
        $validation->setConfigDirectory($this->_cacheDirectory);
        $validation->setCacheDirectory($this->_cacheDirectory);
        $config = &$validation->getConfiguration();
        $config->setRequired('userfile');
        $config->addValidation('userfile',
                               'File',
                               array('maxSize'  => $size,
                                     'minSize'  => $size,
                                     'mimetype' => 'text')
                               );
        $container = &new stdClass();

        $this->assertTrue($validation->validate(null, $container));

        unset($_FILES['userfile']);
    }

    /**
     * @since Method available since Release 0.9.0
     */
    function testMultipleFileUpload()
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

        $validation = &new Piece_Unity_Validation();
        $validation->setConfigDirectory($this->_cacheDirectory);
        $validation->setCacheDirectory($this->_cacheDirectory);
        $config = &$validation->getConfiguration();
        $config->setRequired('userfile');
        $config->addValidation('userfile',
                               'File',
                               array('maxSize'  => $size,
                                     'minSize'  => $size,
                                     'mimetype' => 'text')
                               );
        $container = &new stdClass();

        $this->assertTrue($validation->validate(null, $container));

        unset($_FILES['userfile']);
    }

    /**
     * @since Method available since Release 1.3.0
     */
    function testMergeValidationSetShouldMergeConfigurationFileIntoConfiguration()
    {
        $_POST['foo'] = '1';
        $_POST['bar'] = '2';
        $_POST['baz'] = '3';
        $_POST['qux'] = '4';
        $validation = &new Piece_Unity_Validation();
        $validation->setConfigDirectory($this->_cacheDirectory);
        $validation->setCacheDirectory($this->_cacheDirectory);
        $validation->mergeValidationSet('MergeValidationSetShouldMergeConfigurationFileIntoConfiguration2');
        $validation->mergeValidationSet('MergeValidationSetShouldMergeConfigurationFileIntoConfiguration3');
        $container = &new stdClass();
        $result = $validation->validate('MergeValidationSetShouldMergeConfigurationFileIntoConfiguration1', $container);
        $properties = get_object_vars($container);

        $this->assertTrue($result);
        $this->assertEquals(4, count(array_keys($properties)));
        $this->assertTrue(array_key_exists('foo', $container));
        $this->assertTrue(array_key_exists('bar', $container));
        $this->assertTrue(array_key_exists('baz', $container));
        $this->assertTrue(array_key_exists('qux', $container));
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
     * @since Method available since Release 1.3.0
     */
    function testTemplateShouldBeUsedIfFileIsSetAndBasedOnElementIsSpecified()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['firstName'] = ' Foo ';
        $_POST['lastName'] = 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa';

        $validation = &new Piece_Unity_Validation();
        $validation->setConfigDirectory($this->_cacheDirectory);
        $validation->setCacheDirectory($this->_cacheDirectory);
        $validation->setTemplate('Common');
        $container = &new stdClass();
        $result = $validation->validate('TemplateShouldBeUsedIfFileIsSetAndBasedOnElementIsSpecified', $container);
        $results = &$validation->getResults();

        $this->assertFalse($result);
        $this->assertEquals(1, $results->countErrors());
        $this->assertTrue(in_array('firstName', $results->getValidFields()));
        $this->assertTrue(in_array('lastName', $results->getErrorFields()));
        $this->assertEquals('Foo', $results->getFieldValue('firstName'));
        $this->assertEquals('The length of Last Name must be less than 255 characters', $results->getErrorMessage('lastName'));
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
