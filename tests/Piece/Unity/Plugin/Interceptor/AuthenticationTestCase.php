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
require_once 'Piece/Unity/Error.php';

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
                                 'useCallback' => true),
                           array('name'      => 'Authentication Service Three',
                                 'guard'     => array('class' => 'AuthenticationCheckerThree', 'method' => 'isAuthenticated'),
                                 'url'       => 'http://example.org/three/authenticate.php',
                                 'resources' => array('/three/foo.php', '/three/bar.php'),
                                 'useCallback' => true,
                                 'callbackKey' => 'three'),
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

    function testAccessToProtectedResourcesByOneService()
    {
        $this->assertFalse($this->_assertAccess('/one/foo.php', 'http://example.org/one/authenticate.php', false));
        $this->assertTrue($this->_assertAccess('/one/foo.php', 'http://example.org/one/authenticate.php', true));
        $this->assertFalse($this->_assertAccess('/one/bar.php', 'http://example.org/one/authenticate.php', false));
        $this->assertTrue($this->_assertAccess('/one/bar.php', 'http://example.org/one/authenticate.php', true));
        $this->assertTrue($this->_assertAccess('/one/baz.php', 'http://example.org/one/authenticate.php', false));
        $this->assertTrue($this->_assertAccess('/one/baz.php', 'http://example.org/one/authenticate.php', true));
    }

    function testAccessToProtectedResourcesByTwoService()
    {
        $this->assertFalse($this->_assertAccess('/two/foo.php', 'http://example.org/two/authenticate.php?callback=%2Ftwo%2Ffoo.php', false));
        $this->assertTrue($this->_assertAccess('/two/foo.php', 'http://example.org/two/authenticate.php?callback=%2Ftwo%2Ffoo.php', true));
        $this->assertFalse($this->_assertAccess('/two/bar.php', 'http://example.org/two/authenticate.php?callback=%2Ftwo%2Fbar.php', false));
        $this->assertTrue($this->_assertAccess('/two/bar.php', 'http://example.org/two/authenticate.php?callback=%2Ftwo%2Fbar.php', true));
        $this->assertTrue($this->_assertAccess('/two/baz.php', 'http://example.org/two/authenticate.php?callback=%2Ftwo%2Fbaz.php', false));
        $this->assertTrue($this->_assertAccess('/two/baz.php', 'http://example.org/two/authenticate.php?callback=%2Ftwo%2Fbaz.php', true));
    }

    function testAccessToProtectedResourcesByTwoServiceWithPathInfo()
    {
        $this->assertFalse($this->_assertAccess('/two/foo.php', 'http://example.org/two/authenticate.php?callback=%2Ftwo%2Ffoo.php%2Ffoo%2Fbar%2F', false, '/foo/bar/'));
        $this->assertTrue($this->_assertAccess('/two/foo.php', 'http://example.org/two/authenticate.php?callback=%2Ftwo%2Ffoo.php%2Ffoo%2Fbar%2F', true, '/foo/bar/'));
        $this->assertFalse($this->_assertAccess('/two/bar.php', 'http://example.org/two/authenticate.php?callback=%2Ftwo%2Fbar.php%2Ffoo%2Fbar%2F', false, '/foo/bar/'));
        $this->assertTrue($this->_assertAccess('/two/bar.php', 'http://example.org/two/authenticate.php?callback=%2Ftwo%2Fbar.php%2Ffoo%2Fbar%2F', true, '/foo/bar/'));
        $this->assertTrue($this->_assertAccess('/two/baz.php', 'http://example.org/two/authenticate.php?callback=%2Ftwo%2Fbaz.php%2Ffoo%2Fbar%2F', false, '/foo/bar/'));
        $this->assertTrue($this->_assertAccess('/two/baz.php', 'http://example.org/two/authenticate.php?callback=%2Ftwo%2Fbaz.php%2Ffoo%2Fbar%2F', true, '/foo/bar/'));
    }

    function testAccessToProtectedResourcesByTwoServiceWithQueryString()
    {
        $this->assertFalse($this->_assertAccess('/two/foo.php', 'http://example.org/two/authenticate.php?callback=%2Ftwo%2Ffoo.php%3Ffoo%3Dbar', false, null, 'foo=bar'));
        $this->assertTrue($this->_assertAccess('/two/foo.php', 'http://example.org/two/authenticate.php?callback=%2Ftwo%2Ffoo.php%3Ffoo%3Dbar', true, null, 'foo=bar'));
        $this->assertFalse($this->_assertAccess('/two/bar.php', 'http://example.org/two/authenticate.php?callback=%2Ftwo%2Fbar.php%3Ffoo%3Dbar', false, null, 'foo=bar'));
        $this->assertTrue($this->_assertAccess('/two/bar.php', 'http://example.org/two/authenticate.php?callback=%2Ftwo%2Fbar.php%3Ffoo%3Dbar', true, null, 'foo=bar'));
        $this->assertTrue($this->_assertAccess('/two/baz.php', 'http://example.org/two/authenticate.php?callback=%2Ftwo%2Fbaz.php%3Ffoo%3Dbar', false, null, 'foo=bar'));
        $this->assertTrue($this->_assertAccess('/two/baz.php', 'http://example.org/two/authenticate.php?callback=%2Ftwo%2Fbaz.php%3Ffoo%3Dbar', true, null, 'foo=bar'));
    }

    function testAccessToProtectedResourcesByTwoServiceWithPathInfoAndQueryString()
    {
        $this->assertFalse($this->_assertAccess('/two/foo.php', 'http://example.org/two/authenticate.php?callback=%2Ftwo%2Ffoo.php%2Ffoo%2Fbar%2F%3Ffoo%3Dbar', false, '/foo/bar/', 'foo=bar'));
        $this->assertTrue($this->_assertAccess('/two/foo.php', 'http://example.org/two/authenticate.php?callback=%2Ftwo%2Ffoo.php%2Ffoo%2Fbar%2F%3Ffoo%3Dbar', true, '/foo/bar/', 'foo=bar'));
        $this->assertFalse($this->_assertAccess('/two/bar.php', 'http://example.org/two/authenticate.php?callback=%2Ftwo%2Fbar.php%2Ffoo%2Fbar%2F%3Ffoo%3Dbar', false, '/foo/bar/', 'foo=bar'));
        $this->assertTrue($this->_assertAccess('/two/bar.php', 'http://example.org/two/authenticate.php?callback=%2Ftwo%2Fbar.php%2Ffoo%2Fbar%2F%3Ffoo%3Dbar', true, '/foo/bar/', 'foo=bar'));
        $this->assertTrue($this->_assertAccess('/two/baz.php', 'http://example.org/two/authenticate.php?callback=%2Ftwo%2Fbaz.php%2Ffoo%2Fbar%2F%3Ffoo%3Dbar', false, '/foo/bar/', 'foo=bar'));
        $this->assertTrue($this->_assertAccess('/two/baz.php', 'http://example.org/two/authenticate.php?callback=%2Ftwo%2Fbaz.php%2Ffoo%2Fbar%2F%3Ffoo%3Dbar', true, '/foo/bar/', 'foo=bar'));
    }

    function testAccessToProtectedResourcesByThreeService()
    {
        $this->assertFalse($this->_assertAccess('/three/foo.php', 'http://example.org/three/authenticate.php?three=%2Fthree%2Ffoo.php', false));
        $this->assertTrue($this->_assertAccess('/three/foo.php', 'http://example.org/three/authenticate.php?three=%2Fthree%2Ffoo.php', true));
        $this->assertFalse($this->_assertAccess('/three/bar.php', 'http://example.org/three/authenticate.php?three=%2Fthree%2Fbar.php', false));
        $this->assertTrue($this->_assertAccess('/three/bar.php', 'http://example.org/three/authenticate.php?three=%2Fthree%2Fbar.php', true));
        $this->assertTrue($this->_assertAccess('/three/baz.php', 'http://example.org/three/authenticate.php?three=%2Fthree%2Fbaz.php', false));
        $this->assertTrue($this->_assertAccess('/three/baz.php', 'http://example.org/three/authenticate.php?three=%2Fthree%2Fbaz.php', true));
    }

    function testAccessToProtectedResourcesByThreeServiceWithProxyPath()
    {
        $this->assertFalse($this->_assertAccess('/proxy/three/foo.php', 'http://example.org/three/authenticate.php?three=%2Fproxy%2Fthree%2Ffoo.php', false, null, null, '/proxy'));
        $this->assertTrue($this->_assertAccess('/proxy/three/foo.php', 'http://example.org/three/authenticate.php?three=%2Fproxy%2Fthree%2Ffoo.php', true, null, null, '/proxy'));
        $this->assertFalse($this->_assertAccess('/proxy/three/bar.php', 'http://example.org/three/authenticate.php?three=%2Fproxy%2Fthree%2Fbar.php', false, null, null, '/proxy'));
        $this->assertTrue($this->_assertAccess('/proxy/three/bar.php', 'http://example.org/three/authenticate.php?three=%2Fproxy%2Fthree%2Fbar.php', true, null, null, '/proxy'));
        $this->assertTrue($this->_assertAccess('/proxy/three/baz.php', 'http://example.org/three/authenticate.php?three=%2Fproxy%2Fthree%2Fbaz.php', false, null, null, '/proxy'));
        $this->assertTrue($this->_assertAccess('/proxy/three/baz.php', 'http://example.org/three/authenticate.php?three=%2Fproxy%2Fthree%2Fbaz.php', true, null, null, '/proxy'));

        $this->assertFalse($this->_assertAccess('/proxy/three/foo.php', 'http://example.org/three/authenticate.php?three=%2Fproxy%2Fthree%2Ffoo.php', false, null, null, '/proxy'));
        $this->assertTrue($this->_assertAccess('/proxy/three/foo.php', 'http://example.org/three/authenticate.php?three=%2Fproxy%2Fthree%2Ffoo.php', true, null, null, '/proxy'));
        $this->assertFalse($this->_assertAccess('/proxy/three/bar.php', 'http://example.org/three/authenticate.php?three=%2Fproxy%2Fthree%2Fbar.php', false, null, null, '/proxy'));
        $this->assertTrue($this->_assertAccess('/proxy/three/bar.php', 'http://example.org/three/authenticate.php?three=%2Fproxy%2Fthree%2Fbar.php', true, null, null, '/proxy'));
        $this->assertTrue($this->_assertAccess('/proxy/three/baz.php', 'http://example.org/three/authenticate.php?three=%2Fproxy%2Fthree%2Fbaz.php', false, null, null, '/proxy'));
        $this->assertTrue($this->_assertAccess('/proxy/three/baz.php', 'http://example.org/three/authenticate.php?three=%2Fproxy%2Fthree%2Fbaz.php', true, null, null, '/proxy'));

        $this->assertFalse($this->_assertAccess('/proxy/three/foo.php', 'http://example.org/three/authenticate.php?three=%2Fproxy%2Fthree%2Ffoo.php%2Ffoo%2Fbar%2F', false, '/foo/bar/', null, '/proxy'));
        $this->assertTrue($this->_assertAccess('/proxy/three/foo.php', 'http://example.org/three/authenticate.php?three=%2Fproxy%2Fthree%2Ffoo.php%2Ffoo%2Fbar%2F', true, '/foo/bar/', null, '/proxy'));
        $this->assertFalse($this->_assertAccess('/proxy/three/bar.php', 'http://example.org/three/authenticate.php?three=%2Fproxy%2Fthree%2Fbar.php%2Ffoo%2Fbar%2F', false, '/foo/bar/', null, '/proxy'));
        $this->assertTrue($this->_assertAccess('/proxy/three/bar.php', 'http://example.org/three/authenticate.php?three=%2Fproxy%2Fthree%2Fbar.php%2Ffoo%2Fbar%2F', true, '/foo/bar/', null, '/proxy'));
        $this->assertTrue($this->_assertAccess('/proxy/three/baz.php', 'http://example.org/three/authenticate.php?three=%2Fproxy%2Fthree%2Fbaz.php%2Ffoo%2Fbar%2F', false, '/foo/bar/', null, '/proxy'));
        $this->assertTrue($this->_assertAccess('/proxy/three/baz.php', 'http://example.org/three/authenticate.php?three=%2Fproxy%2Fthree%2Fbaz.php%2Ffoo%2Fbar%2F', true, '/foo/bar/', null, '/proxy'));

        $this->assertFalse($this->_assertAccess('/proxy/three/foo.php', 'http://example.org/three/authenticate.php?three=%2Fproxy%2Fthree%2Ffoo.php%3Ffoo%3Dbar', false, null, 'foo=bar', '/proxy'));
        $this->assertTrue($this->_assertAccess('/proxy/three/foo.php', 'http://example.org/three/authenticate.php?three=%2Fproxy%2Fthree%2Ffoo.php%3Ffoo%3Dbar', true, null, 'foo=bar', '/proxy'));
        $this->assertFalse($this->_assertAccess('/proxy/three/bar.php', 'http://example.org/three/authenticate.php?three=%2Fproxy%2Fthree%2Fbar.php%3Ffoo%3Dbar', false, null, 'foo=bar', '/proxy'));
        $this->assertTrue($this->_assertAccess('/proxy/three/bar.php', 'http://example.org/three/authenticate.php?three=%2Fproxy%2Fthree%2Fbar.php%3Ffoo%3Dbar', true, null, 'foo=bar', '/proxy'));
        $this->assertTrue($this->_assertAccess('/proxy/three/baz.php', 'http://example.org/three/authenticate.php?three=%2Fproxy%2Fthree%2Fbaz.php%3Ffoo%3Dbar', false, null, 'foo=bar', '/proxy'));
        $this->assertTrue($this->_assertAccess('/proxy/three/baz.php', 'http://example.org/three/authenticate.php?three=%2Fproxy%2Fthree%2Fbaz.php%3Ffoo%3Dbar', true, null, 'foo=bar', '/proxy'));

        $this->assertFalse($this->_assertAccess('/proxy/three/foo.php', 'http://example.org/three/authenticate.php?three=%2Fproxy%2Fthree%2Ffoo.php%2Ffoo%2Fbar%2F%3Ffoo%3Dbar', false, '/foo/bar/', 'foo=bar', '/proxy'));
        $this->assertTrue($this->_assertAccess('/proxy/three/foo.php', 'http://example.org/three/authenticate.php?three=%2Fproxy%2Fthree%2Ffoo.php%2Ffoo%2Fbar%2F%3Ffoo%3Dbar', true, '/foo/bar/', 'foo=bar', '/proxy'));
        $this->assertFalse($this->_assertAccess('/proxy/three/bar.php', 'http://example.org/three/authenticate.php?three=%2Fproxy%2Fthree%2Fbar.php%2Ffoo%2Fbar%2F%3Ffoo%3Dbar', false, '/foo/bar/', 'foo=bar', '/proxy'));
        $this->assertTrue($this->_assertAccess('/proxy/three/bar.php', 'http://example.org/three/authenticate.php?three=%2Fproxy%2Fthree%2Fbar.php%2Ffoo%2Fbar%2F%3Ffoo%3Dbar', true, '/foo/bar/', 'foo=bar', '/proxy'));
        $this->assertTrue($this->_assertAccess('/proxy/three/baz.php', 'http://example.org/three/authenticate.php?three=%2Fproxy%2Fthree%2Fbaz.php%2Ffoo%2Fbar%2F%3Ffoo%3Dbar', false, '/foo/bar/', 'foo=bar', '/proxy'));
        $this->assertTrue($this->_assertAccess('/proxy/three/baz.php', 'http://example.org/three/authenticate.php?three=%2Fproxy%2Fthree%2Fbaz.php%2Ffoo%2Fbar%2F%3Ffoo%3Dbar', true, '/foo/bar/', 'foo=bar', '/proxy'));
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

    function _assertAccess($scriptName,
                           $url,
                           $isAuthenticated,
                           $pathInfo = null,
                           $queryString = null,
                           $proxy = null
                           )
    {
        $previousScriptName = $_SERVER['SCRIPT_NAME'];
        $_SERVER['SCRIPT_NAME'] = $scriptName;
        if ($pathInfo) {
            $_SERVER['PATH_INFO'] = $pathInfo;
        }
        if ($queryString) {
            $_SERVER['QUERY_STRING'] = $queryString;
        }
        $GLOBALS['isAuthenticated'] = $isAuthenticated;
        $GLOBALS['isAuthenticatedCalled'] = false;

        $config = &new Piece_Unity_Config();
        $config->setConfiguration('Interceptor_Authentication', 'services', $this->_services);
        $config->setConfiguration('Interceptor_Authentication', 'guardDirectory', dirname(__FILE__));
        $context = &Piece_Unity_Context::singleton();
        $context->setConfiguration($config);
        if ($proxy) {
            $_SERVER['HTTP_X_FORWARDED_FOR'] = '1.2.3.4';
            $context->setProxyPath($proxy);
            $context->setBasePath($proxy . $context->getBasePath());
        }

        $interceptor = &new Piece_Unity_Plugin_Interceptor_Authentication();
        $interceptor->invoke();

        if (Piece_Unity_Error::hasErrors('exception')) {
            Piece_Unity_Context::clear();
            unset($GLOBALS['isAuthenticatedCalled']);
            unset($GLOBALS['isAuthenticated']);
            unset($_SERVER['PATH_INFO']);
            unset($_SERVER['QUERY_STRING']);
            unset($_SERVER['HTTP_X_FORWARDED_FOR']);
            $_SERVER['SCRIPT_NAME'] = $previousScriptName;
            return;
        }

        $view = $context->getView();
        if ($this->_isProtectedResource($scriptName, $proxy)) {
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
        unset($_SERVER['PATH_INFO']);
        unset($_SERVER['QUERY_STRING']);
        unset($_SERVER['HTTP_X_FORWARDED_FOR']);
        $_SERVER['SCRIPT_NAME'] = $previousScriptName;

        return is_null($view) ? true : false;
    }

    function _isProtectedResource($scriptName, $proxy)
    {
        foreach ($this->_services as $service) {
            if ($proxy) {
                $resources = array();
                foreach ($service['resources'] as $resource) {
                    $resources[] = $proxy . $resource;
                }
            } else {
                $resources = $service['resources'];
            }

            if (in_array($scriptName, $resources)) {
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
