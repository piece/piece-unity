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
 * @author     Chihiro Sakatoku <csakatoku@users.sourceforge.net>
 * @copyright  2006 KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    SVN: $Id$
 * @link       http://piece-framework.com/piece-unity/
 * @see        Piece_Unity_Plugin_Renderer_JSON
 * @since      File available since Release 0.9.0
 */

require_once 'PHPUnit.php';
require_once 'HTML/AJAX/JSON.php';
require_once 'Piece/Unity/Plugin/Renderer/JSON.php';

// {{{ Piece_Unity_Plugin_Renderer_JSONTestCase

/**
 * TestCase for Piece_Unity_Plugin_Renderer_JSON
 *
 * @package    Piece_Unity
 * @author     Chihiro Sakatoku <csakatoku@users.sourceforge.net>
 * @copyright  2006 KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    Release: @package_version@
 * @link       http://piece-framework.com/piece-unity/
 * @see        Piece_Unity_Plugin_Renderer_Json
 * @since      File available since Release 0.9.0
 */
class Piece_Unity_Plugin_Renderer_JSONTestCase extends PHPUnit_TestCase
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

    /**#@-*/

    /**#@+
     * @access private
     */

    function setUp()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
    }

    function tearDown()
    {
        Piece_Unity_Context::clear();
        Piece_Unity_Error::clearErrors();
        unset($GLOBALS['PIECE_UNITY_Plugin_Instances']['Renderer_JSON']);
        unset($_SERVER['REQUEST_METHOD']);
    }

    function &getView($viewElements, $settings = array(), $class = 'Piece_Unity_Plugin_Renderer_JSON')
    {
        $context = &Piece_Unity_Context::singleton();
        $context->setView(null);
        $config = &new Piece_Unity_Config();
        $config->setExtension('View', 'renderer', 'Renderer_JSON');
        foreach($settings as $key => $value) {
            $config->setConfiguration('Renderer_JSON', $key, $value);
        }

        $viewElement = &$context->getViewElement();
        foreach(array_keys($viewElements) as $key) {
            $value =& $viewElements[$key];
            $viewElement->setElementByRef($key, $value);
        }

        $context->setConfiguration($config);
        $view = &new $class();
        
        return $view;
    }

    function jsonEncode($value)
    {
        if (function_exists('json_decode')) {
            return json_encode($value);
        } else {
            $encoder = &new HTML_AJAX_JSON();
            return $encoder->encode($value);
        }
    }

    function jsonDecode($json)
    {
        if (function_exists('json_decode')) {
            return json_decode($json);
        } else {
            $encoder = &new HTML_AJAX_JSON();
            return $encoder->decode($json);
        }
    }

    function testEncodeWithPHPJSON()
    {
        $value = array('content'=>'hello world');
        $view = &$this->getView($value,
                                array(),
                                'Piece_Unity_Plugin_View'
                               );

        ob_start();
        $view->invoke();
        $json = ob_get_contents();
        ob_end_clean();

        $result = $this->jsonDecode($json);
        $this->assertEquals('hello world', $result->content);
        $this->assertNotNull($result->__eventNameKey);
        $this->assertNotNull($result->__scriptName);
        $this->assertNotNull($result->__basePath);
    }
    
    function testEncodeWithHTTPAJAX()
    {
        $value = array('content'=>'hello world');
        $view = &$this->getView($value);
        $view->_useHTTPAJAX = true;

        ob_start();
        $view->invoke();
        $json = ob_get_contents();
        ob_end_clean();

        $result = $this->jsonDecode($json);
        $this->assertEquals('hello world', $result->content);
    }

    function testEncodeFailure()
    {
        /*
         * test a view element which contains circular references.
         */
        $obj = &new stdClass();
        $obj->favorite = 'Sake';
        $value = array(&$obj);
        $obj->self = &$value;

        $view = &$this->getView($value,
                                array('include' => array(),
                                      'exclude' => array())
                                );

        ob_start();
        $view->invoke();
        $json = ob_get_contents();
        ob_end_clean();

        $this->assertEquals('', $json);
        $this->assertEquals('HTTP/1.0 500 Internal Server Error', $view->_header);
    }

    function testExclude()
    {
        $value = array('content' => 'hello world',
                       'spam'    => 'spamspamspam'
                      );
        $view = &$this->getView($value,
                                array('include' => array(),
                                      'exclude' => array('spam')),
                                'Piece_Unity_Plugin_View'
                               );

        ob_start();
        $view->invoke();
        $json = ob_get_contents();
        ob_end_clean();

        $result = $this->jsonDecode($json);
        $this->assertEquals('hello world', $result->content);
        $vars = get_object_vars($result);
        $this->assertTrue(array_key_exists('content', $vars));
        $this->assertFalse(array_key_exists('spam', $vars));
    }

    function testInclude()
    {
        $value = array(
                    '_content' => 'hello world'
                );
        $view = &$this->getView($value,
                                array('include' => array('_content')),
                                'Piece_Unity_Plugin_View'
                               );

        ob_start();
        $view->invoke();
        $json = ob_get_contents();
        ob_end_clean();

        $result = $this->jsonDecode($json);
        $this->assertEquals('hello world', $result->_content);
    }

    function testContentType()
    {
        $value = array('content'=>'hello world');
        $view = &$this->getView($value,
                                array('contentType' => 'text/json',
                                      'include'     => array(),
                                      'exclude'     => array())
                               );

        ob_start();
        $view->invoke();
        $json = ob_get_contents();
        ob_end_clean();

        $this->assertEquals('text/json', $view->_header);
    }

    function testJSONP()
    {
        $value = array('content'=>'hello world');
        $view = &$this->getView($value,
                                array('contentType'   => 'text/javascript',
                                      'include'       => array(),
                                      'exclude'       => array(),
                                      'useJSONP'      => true,
                                      'callbackField' => 'callback')
                               );
        $_GET['callback'] = 'callback';

        ob_start();
        $view->invoke();
        $json = ob_get_contents();
        ob_end_clean();

        $this->assertEquals('callback({"content":"hello world"});', $json);
        $this->assertEquals('text/javascript', $view->_header);
        
        unset($_GET['callback']);
    }

    function testInternalServerError()
    {
        $a = array();
        $b = array(&$a);
        $a[] =& $b;
        $value = array($a, $b);
        $view = &$this->getView($value, array());

        ob_start();
        $view->invoke();
        $json = ob_get_contents();
        ob_end_clean();

        $this->assertEquals('', $json);
        $this->assertEquals('HTTP/1.0 500 Internal Server Error', $view->_header);
    }

    function testUnicodeWithMulitByteString()
    {
        $tmp = mb_internal_encoding();

        $obj = &new stdClass();
        $obj->favorite = '¼ò';
        $value = array($obj);
        $view = &$this->getView($value,
                                array('include'          => array(),
                                      'exclude'          => array(),
                                      'internalEncoding' => 'EUC-JP')
                                );

        ob_start();
        $view->invoke();
        $json = ob_get_contents();
        ob_end_clean();

        $this->assertEquals('[{"favorite":"\u9152"}]', $json);
        
        mb_internal_encoding($tmp);
    }

    function testUnicodeWithIconv()
    {
        $tmp = iconv_get_encoding('internal_encoding');

        $obj = &new stdClass();
        $obj->favorite = '¼ò';
        $value = array($obj);
        $view = &$this->getView($value,
                                array('include'          => array(),
                                      'exclude'          => array(),
                                      'internalEncoding' => 'EUC-JP')
                                );
        $view->_useIconv = true;

        ob_start();
        $view->invoke();
        $json = ob_get_contents();
        ob_end_clean();
        
        $this->assertEquals('[{"favorite":"\u9152"}]', $json);

        iconv_set_encoding('internal_encoding', $tmp);
    }

    function testDetectCicularReferenceInArray()
    {
        $r = &new Piece_Unity_Plugin_Renderer_Json();
        
        $b = array(false, 2, 3.0, '4');
        $a = array(1, 2, 'spam', &$b);
        $b[] =& $a;

        $visited = array();
        $r->_visit($a, $visited);

        $this->assertTrue(Piece_Unity_Error::hasErrors('exception'));
    }
    
    function testDetectCicularReferenceInObject()
    {
        $r = &new Piece_Unity_Plugin_Renderer_Json();

        $arr = array(1, 2, 3, 4);
        $obj = &new stdClass();
        $obj->prop1 =& $arr;
        $arr[] =& $obj;

        $visited = array();
        $r->_visit($obj, $visited);

        $this->assertTrue(Piece_Unity_Error::hasErrors('exception'));
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
