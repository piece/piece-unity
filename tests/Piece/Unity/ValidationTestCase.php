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
 * @see        Piece_Unity_Validation
 * @since      File available since Release 0.7.0
 */

require_once 'PHPUnit.php';
require_once 'Piece/Unity/Validation.php';

// {{{ Piece_Unity_ValidationTestCase

/**
 * TestCase for Piece_Unity_Validation
 *
 * @package    Piece_Unity
 * @author     KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @copyright  2006 KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    Release: @package_version@
 * @link       http://piece-framework.com/piece-unity/
 * @see        Piece_Unity_Validation
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

    function testValidation()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['login_name'] = 'iteman';
        $_POST['password'] = 'iteman30';
        $_POST['email'] = 'iteman@users.sourceforge.net';

        $validation = &new Piece_Unity_Validation();
        $validation->setConfigDirectory(dirname(__FILE__));
        $validation->setCacheDirectory(dirname(__FILE__));
        $config = &$validation->getConfiguration();
        $config->setRequired('email');
        $config->addValidation('email', 'Email');

        $container = &new stdClass();

        $this->assertTrue($validation->validate('Authentication', $container));
        $this->assertEquals($_POST['login_name'], $container->login_name);
        $this->assertEquals($_POST['password'], $container->password);
        $this->assertEquals($_POST['email'], $container->email);
        $this->assertTrue(is_a($validation->getResults(), 'Piece_Right_Results'));

        unset($_POST['email']);
        unset($_POST['password']);
        unset($_POST['login_name']);
        unset($_SERVER['REQUEST_METHOD']);
    }

    function testFailureToValidation()
    {
        Piece_Unity_Error::pushCallback(create_function('$error', 'return ' . PEAR_ERRORSTACK_PUSHANDLOG . ';'));

        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['foo'] = 'bar';

        $validation = &new Piece_Unity_Validation();
        $config = &$validation->getConfiguration();
        $config->setRequired('foo');
        $config->addValidation('foo', 'NonExistingValidator');

        $container = &new stdClass();
        $validation->validate(null, $container);

        $this->assertTrue(Piece_Unity_Error::hasErrors('exception'));

        $error = Piece_Unity_Error::pop();

        $this->assertEquals(PIECE_UNITY_ERROR_INVOCATION_FAILED, $error['code']);
        $this->assertEquals(PIECE_RIGHT_ERROR_NOT_FOUND, $error['repackage']['code']);

        unset($_POST['foo']);
        unset($_SERVER['REQUEST_METHOD']);

        Piece_Unity_Error::popCallback();
    }

    function testNotKeepingOriginalFieldValue()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['login_name'] = ' iteman ';
        $_POST['password'] = 'itema';
        $_POST['email'] = 'iteman@users.sourceforge.net';

        $validation = &new Piece_Unity_Validation();
        $validation->setConfigDirectory(dirname(__FILE__));
        $validation->setCacheDirectory(dirname(__FILE__));
        $config = &$validation->getConfiguration();
        $config->setRequired('email');
        $config->addValidation('email', 'Email');
        $config->addFilter('login_name', 'trim');

        $container = &new stdClass();

        $this->assertFalse($validation->validate('Authentication', $container, false));
        $this->assertEquals(trim($_POST['login_name']), $container->login_name);
        $this->assertEquals($_POST['password'], $container->password);
        $this->assertEquals($_POST['email'], $container->email);

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
