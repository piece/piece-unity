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
 * @author     KUMAKURA Yousuke <kumatch@users.sourceforge.net>
 * @copyright  2006 KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    SVN: $Id$
 * @link       http://piece-framework.com/piece-unity/
 * @see        Piece_Unity_Plugin_Interceptor_Authentication
 * @since      File available since Release 0.9.0
 */

require_once 'PHPUnit.php';
require_once 'Piece/Unity/Plugin/Interceptor/Authentication.php';
require_once 'Piece/Unity/Context.php';
require_once 'Piece/Unity/Config.php';

// {{{ Piece_Unity_Plugin_Interceptor_AuthenticationTestCase

/**
 * TestCase for Piece_Unity_Plugin_Interceptor_Authentication
 *
 * @package    Piece_Unity
 * @author     KUMAKURA Yousuke <kumatch@users.sourceforge.net>
 * @copyright  2006 KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    Release: @package_version@
 * @link       http://piece-framework.com/piece-unity/
 * @see        Piece_Unity_Plugin_Interceptor_Authentication
 * @since      Class available since Release 0.9.0
 */
class Piece_Unity_Plugin_Interceptor_AuthenticationTestCase extends PHPUnit_TestCase
{

    // {{{ properties

    /**#@+
     * @access public
     */

    /**#@-*/

    /**#@+
     * @access private
     */

    var $_services = array(array('name'      => 'Authentication Service One',
                                 'guard'     => array('class' => 'AuthenticationCheckerOne', 'method' => 'isAuthenticated'),
                                 'url'       => 'http://example.org/one/authenticate.php',
                                 'resources' => array('/one/foo.php', '/one/bar.php')),
                           array('name'      => 'Authentication Service Two',
                                 'guard'     => array('class' => 'AuthenticationCheckerTwo', 'method' => 'isAuthenticated'),
                                 'url'       => 'http://example.org/two/authenticate.php',
                                 'resources' => array('/two/foo.php', '/two/bar.php'),
                                 'useCallback' => true,
                                 ),
                           array('name'      => 'Authentication Service Three',
                                 'guard'     => array('class' => 'AuthenticationCheckerThree', 'method' => 'isAuthenticated'),
                                 'url'       => 'http://example.org/three/authenticate.php',
                                 'resources' => array('/three/foo.php', '/three/bar.php'),
                                 'useCallback' => true,
                                 'callbackKey' => 'three',
                                 ),
                           array('name'      => 'Authentication Service Four',
                                 'guard'     => array('class' => 'AuthenticationCheckerFour', 'method' => 'isAuthenticated'),
                                 'url'       => 'http://example.org/four/authenticate.php',
                                 'resources' => array('/four/foo.php', '/four/bar.php')
                                 )
                           );

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
        Piece_Unity_Error::clearErrors();
        Piece_Unity_Error::popCallback();
    }

    function testAccessToProtectedResourcesByFirstService()
    {
        $this->assertFalse($this->_assertAccess('/one/foo.php', 'http://example.org/one/authenticate.php', false));
        $this->assertTrue($this->_assertAccess('/one/foo.php', 'http://example.org/one/authenticate.php', true));
        $this->assertFalse($this->_assertAccess('/one/bar.php', 'http://example.org/one/authenticate.php', false));
        $this->assertTrue($this->_assertAccess('/one/bar.php', 'http://example.org/one/authenticate.php', true));
        $this->assertTrue($this->_assertAccess('/one/baz.php', 'http://example.org/one/authenticate.php', false));
        $this->assertTrue($this->_assertAccess('/one/baz.php', 'http://example.org/one/authenticate.php', true));

        $this->assertFalse($this->_assertAccess('/two/foo.php', 'http://example.org/two/authenticate.php?callback=/two/foo.php', false));
        $this->assertTrue($this->_assertAccess('/two/foo.php', 'http://example.org/two/authenticate.php?callback=/two/foo.php', true));
        $this->assertFalse($this->_assertAccess('/two/bar.php', 'http://example.org/two/authenticate.php?callback=/two/bar.php', false));
        $this->assertTrue($this->_assertAccess('/two/bar.php', 'http://example.org/two/authenticate.php?callback=/two/bar.php', true));
        $this->assertTrue($this->_assertAccess('/two/baz.php', 'http://example.org/two/authenticate.php?callback=/two/baz.php', false));
        $this->assertTrue($this->_assertAccess('/two/baz.php', 'http://example.org/two/authenticate.php?callback=/two/baz.php', true));

        $this->assertFalse($this->_assertAccess('/three/foo.php', 'http://example.org/three/authenticate.php?three=/three/foo.php', false));
        $this->assertTrue($this->_assertAccess('/three/foo.php', 'http://example.org/three/authenticate.php?three=/three/foo.php', true));
        $this->assertFalse($this->_assertAccess('/three/bar.php', 'http://example.org/three/authenticate.php?three=/three/bar.php', false));
        $this->assertTrue($this->_assertAccess('/three/bar.php', 'http://example.org/three/authenticate.php?three=/three/bar.php', true));
        $this->assertTrue($this->_assertAccess('/three/baz.php', 'http://example.org/three/authenticate.php?three=/three/baz.php', false));
        $this->assertTrue($this->_assertAccess('/three/baz.php', 'http://example.org/three/authenticate.php?three=/three/baz.php', true));
    }

    function testFailureToCheckSinceGuardNotFound()
    {
        Piece_Unity_Error::pushCallback(create_function('$error', 'return ' . PEAR_ERRORSTACK_PUSHANDLOG . ';'));

        $this->_assertAccess('/four/foo.php', 'http://example.org/four/authenticate.php', false);
        $this->assertTrue(Piece_Unity_Error::hasErrors('exception'));

        $error = Piece_Unity_Error::pop();

        $this->assertEquals(PIECE_UNITY_ERROR_NOT_FOUND, $error['code']);

        Piece_Unity_Error::popCallback();
    }

    /**#@-*/

    /**#@+
     * @access private
     */

    function _assertAccess($scriptName, $url, $isAuthenticated)
    {
        $previousScriptName = $_SERVER['SCRIPT_NAME'];
        $_SERVER['SCRIPT_NAME'] = $scriptName;
        $GLOBALS['isAuthenticated'] = $isAuthenticated;
        $GLOBALS['isAuthenticatedCalled'] = false;

        $config = &new Piece_Unity_Config();
        $config->setConfiguration('Interceptor_Authentication', 'services', $this->_services);
        $config->setConfiguration('Interceptor_Authentication', 'guardDirectory', dirname(__FILE__));
        $context = &Piece_Unity_Context::singleton();
        $context->setConfiguration($config);

        $interceptor = &new Piece_Unity_Plugin_Interceptor_Authentication();
        $interceptor->invoke();

        if (Piece_Unity_Error::hasErrors('exception')) {
            Piece_Unity_Context::clear();
            unset($GLOBALS['isAuthenticatedCalled']);
            unset($GLOBALS['isAuthenticated']);
            $_SERVER['SCRIPT_NAME'] = $previousScriptName;
            return;
        }

        $view = $context->getView();
        if ($this->_isProtectedResource($scriptName)) {
            if (!$isAuthenticated) {
                $this->assertEquals($url, $view);
                $this->assertFalse($GLOBALS['isAuthenticated']);
            } else {
                $this->assertNull($view);
                $this->assertTrue($GLOBALS['isAuthenticated']);
            }

            $this->assertTrue($GLOBALS['isAuthenticatedCalled']);
        } else {
            $this->assertFalse($GLOBALS['isAuthenticatedCalled']);
        }

        Piece_Unity_Context::clear();
        unset($GLOBALS['isAuthenticatedCalled']);
        unset($GLOBALS['isAuthenticated']);
        $_SERVER['SCRIPT_NAME'] = $previousScriptName;

        return is_null($view) ? true : false;
    }

    function _isProtectedResource($scriptName)
    {
        foreach ($this->_services as $service) {
            if (in_array($scriptName, $service['resources'])) {
                return true;
            }
        }

        return false;
    }

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
