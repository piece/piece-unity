<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * PHP versions 4 and 5
 *
 * Copyright (c) 2006, KUBO Atsuhiro <iteman@users.sourceforge.net>
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
 * @see        Piece_Unity_Plugin_Interceptor_PieceRight
 * @since      File available since Release 0.6.0
 */

require_once 'PHPUnit.php';
require_once 'Piece/Unity/Plugin/Interceptor/PieceRight.php';
require_once 'Piece/Unity/Context.php';
require_once 'Piece/Unity/Config.php';
require_once 'Piece/Right/Config.php';
require_once 'Cache/Lite/File.php';

// {{{ Piece_Unity_Plugin_Interceptor_PieceRightTestCase

/**
 * TestCase for Piece_Unity_Plugin_Interceptor_PieceRight
 *
 * @package    Piece_Unity
 * @author     KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @copyright  2006 KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    Release: @package_version@
 * @link       http://piece-framework.com/piece-unity/
 * @see        Piece_Unity_Plugin_Interceptor_PieceRight
 * @since      Class available since Release 0.6.0
 */
class Piece_Unity_Plugin_Interceptor_PieceRightTestCase extends PHPUnit_TestCase
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
        $oldValidatorDirectories = $GLOBALS['PIECE_RIGHT_Validator_Directories'];
        $oldFilterDirectories = $GLOBALS['PIECE_RIGHT_Filter_Directories'];
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['first_name'] = 'Foo';
        $_POST['last_name'] = 'Bar';
        $_POST['phone'] = '0123456789';
        $_POST['country'] = 'Japan';
        $_POST['hobbies'] = array('wine', 'manga');
        $_POST['use_php'] = '1';
        $_POST['favorite_framework'] = 'Piece Framework';
        $_POST['birthdayYear'] = '1977';
        $_POST['birthdayMonth'] = '6';
        $_POST['birthdayDay'] = '14';
        $_POST['greeting'] = 'Hello World';
        $dynamicConfig = &new Piece_Right_Config();
        $dynamicConfig->setRequired('phone');
        $dynamicConfig->addValidation('phone', 'Length', array('min' => 10, 'max' => 11));
        $dynamicConfig->setRequired('greeting');
        $dynamicConfig->addValidation('greeting', 'HelloWorld');
        $dynamicConfig->addFilter('greeting', 'LowerCase');

        $config = &new Piece_Unity_Config();
        $config->setConfiguration('Interceptor_PieceRight', 'configDirectory', dirname(__FILE__));
        $config->setConfiguration('Interceptor_PieceRight', 'cacheDirectory', dirname(__FILE__));
        $config->setConfiguration('Interceptor_PieceRight', 'validatorDirectories', array(dirname(__FILE__)));
        $config->setConfiguration('Interceptor_PieceRight', 'filterDirectories', array(dirname(__FILE__)));
        $context = &Piece_Unity_Context::singleton();
        $context->setConfiguration($config);

        $interceptor = &new Piece_Unity_Plugin_Interceptor_PieceRight();
        $interceptor->invoke();

        $right = &$context->getAttribute('_pieceRight');

        $this->assertTrue(is_a($right, 'Piece_Right'));
        $this->assertTrue($right->validate('PieceRightExample', $dynamicConfig));

        unset($_POST['birthdayDay']);
        unset($_POST['birthdayMonth']);
        unset($_POST['birthdayYear']);
        unset($_POST['favorite_framework']);
        unset($_POST['use_php']);
        unset($_POST['hobbies']);
        unset($_POST['country']);
        unset($_POST['phone']);
        unset($_POST['last_name']);
        unset($_POST['first_name']);
        unset($_SERVER['REQUEST_METHOD']);
        $GLOBALS['PIECE_RIGHT_Filter_Instances'] = array();
        $GLOBALS['PIECE_RIGHT_Filter_Directories'] = $oldValidatorDirectories;
        $GLOBALS['PIECE_RIGHT_Validator_Instances'] = array();
        $GLOBALS['PIECE_RIGHT_Validator_Directories'] = $oldValidatorDirectories;
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
