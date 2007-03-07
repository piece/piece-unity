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
 * @author     KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @copyright  2006-2007 KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    SVN: $Id$
 * @link       http://piece-framework.com/piece-unity/
 * @see        Piece_Unity_Plugin_Interceptor_SessionStart
 * @since      File available since Release 0.10.0
 */

require_once 'PHPUnit.php';
require_once 'Piece/Unity/Plugin/Interceptor/SessionStart.php';
require_once 'Piece/Unity/Context.php';
require_once 'Piece/Unity/Config.php';

// {{{ Piece_Unity_Plugin_Interceptor_SessionStartTestCase

/**
 * TestCase for Piece_Unity_Plugin_Interceptor_SessionStart
 *
 * @package    Piece_Unity
 * @author     KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @copyright  2006-2007 KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    Release: @package_version@
 * @link       http://piece-framework.com/piece-unity/
 * @see        Piece_Unity_Plugin_Interceptor_SessionStart
 * @since      Class available since Release 0.10.0
 */
class Piece_Unity_Plugin_Interceptor_SessionStartTestCase extends PHPUnit_TestCase
{

    // {{{ properties

    /**#@+
     * @access public
     */

    /**#@-*/

    /**#@+
     * @access private
     */

    var $_oldSessionName;

    /**#@-*/

    /**#@+
     * @access public
     */

    function setUp()
    {
        Piece_Unity_Error::pushCallback(create_function('$error', 'var_dump($error); return ' . PEAR_ERRORSTACK_DIE . ';'));
        if (is_null($this->_oldSessionName)) {
            $this->_oldSessionName = session_name();
        }

        $_SESSION = array();
    }

    function tearDown()
    {
        unset($_SESSION);
        session_name($this->_oldSessionName);
        Piece_Unity_Error::clearErrors();
        Piece_Unity_Error::popCallback();
        Piece_Unity_Context::clear();
    }

    function testImportSessionIDFromRequest()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_GET['PHPSESSID'] = 'foo';
        $config = &new Piece_Unity_Config();
        $config->setConfiguration('Interceptor_SessionStart', 'importSessionIDFromRequest', true);
        $context = &Piece_Unity_Context::singleton();
        $context->setConfiguration($config);

        $interceptor = &new Piece_Unity_Plugin_Interceptor_SessionStart();
        @$interceptor->invoke();

        $this->assertEquals('foo', session_id());

        unset($_GET['PHPSESSID']);
        unset($_SERVER['REQUEST_METHOD']);
    }

    function testImportSessionIDFromRequestWithArbitrarySessionName()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_GET['MYSESSID'] = 'bar';
        session_name('MYSESSID');
        $config = &new Piece_Unity_Config();
        $config->setConfiguration('Interceptor_SessionStart', 'importSessionIDFromRequest', true);
        $context = &Piece_Unity_Context::singleton();
        $context->setConfiguration($config);

        $interceptor = &new Piece_Unity_Plugin_Interceptor_SessionStart();
        @$interceptor->invoke();

        $this->assertEquals('bar', session_id());

        unset($_GET['MYSESSID']);
        unset($_SERVER['REQUEST_METHOD']);
    }

    function testFailureToImportSessionIDFromRequest()
    {
        Piece_Unity_Error::pushCallback(create_function('$error', 'return ' . PEAR_ERRORSTACK_PUSHANDLOG . ';'));

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_GET['PHPSESSID'] = 'foo';
        session_name('MYSESSID');
        $config = &new Piece_Unity_Config();
        $config->setConfiguration('Interceptor_SessionStart', 'importSessionIDFromRequest', true);
        $context = &Piece_Unity_Context::singleton();
        $context->setConfiguration($config);

        $interceptor = &new Piece_Unity_Plugin_Interceptor_SessionStart();
        $interceptor->invoke();

        $this->assertTrue(Piece_Unity_Error::hasErrors('exception'));

        $error = Piece_Unity_Error::pop();

        $this->assertEquals(PIECE_UNITY_ERROR_NOT_FOUND, $error['code']);

        unset($_GET['PHPSESSID']);
        unset($_SERVER['REQUEST_METHOD']);

        Piece_Unity_Error::popCallback();
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
