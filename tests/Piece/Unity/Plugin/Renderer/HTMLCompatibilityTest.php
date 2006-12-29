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
 * @see        Piece_Unity_Plugin_View
 * @since      File available since Release 0.2.0
 */

require_once 'PHPUnit.php';
require_once 'Piece/Unity/Context.php';
require_once 'Piece/Unity/Plugin/Dispatcher/Simple.php';
require_once 'Piece/Unity/Plugin/View.php';
require_once 'Piece/Unity/Plugin/Factory.php';
require_once 'Piece/Unity/Error.php';

// {{{ Piece_Unity_Plugin_Renderer_HTMLCompatibilityTest

/**
 * The base class for compatibility test of Piece_Unity_Plugin_View's
 * renderers.
 *
 * @package    Piece_Unity
 * @author     KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @copyright  2006 KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    Release: @package_version@
 * @link       http://piece-framework.com/piece-unity/
 * @see        Piece_Unity_Plugin_View
 * @since      Class available since Release 0.2.0
 */
class Piece_Unity_Plugin_Renderer_HTMLCompatibilityTest extends PHPUnit_TestCase
{

    // {{{ properties

    /**#@+
     * @access public
     */

    /**#@-*/

    /**#@+
     * @access private
     */

    var $_target;
    var $_errorCodeWhenTemplateNotExists;

    /**#@-*/

    /**#@+
     * @access public
     */

    function setUp()
    {
        Piece_Unity_Error::pushCallback(create_function('$error', 'var_dump($error); return ' . PEAR_ERRORSTACK_DIE . ';'));
        $_SERVER['REQUEST_METHOD'] = 'GET';
    }

    function tearDown()
    {
        Piece_Unity_Plugin_Factory::clearInstances();
        Piece_Unity_Context::clear();
        Piece_Unity_Error::clearErrors();
        Piece_Unity_Error::popCallback();
        unset($_GET['_event']);
        unset($_SERVER['REQUEST_METHOD']);
    }

    function testRendering()
    {
        $viewString = "{$this->_target}Example";
        $_GET['_event'] = $viewString;

        $this->assertEquals("This is a test for rendering dynamic pages.\nThis is a dynamic content.", $this->_render());

        $this->_clear($viewString);
    }

    function testRelativePathVulnerability()
    {
        $_GET['_event'] = '../RelativePathVulnerability';

        $this->assertEquals('', $this->_render());
    }

    function testKeepingReference()
    {
        $viewString = "{$this->_target}KeepingReference";
        $context = &Piece_Unity_Context::singleton();

        $config = &$this->_getConfig();
        $context->setConfiguration($config);

        $foo = &new stdClass();
        $viewElement = &$context->getViewElement();
        $viewElement->setElementByRef('foo', $foo);
        $context->setView($viewString);

        $class = "Piece_Unity_Plugin_Renderer_{$this->_target}";
        $renderer = &new $class();
        ob_start();
        $renderer->invoke();
        ob_end_clean();

        $this->assertTrue(array_key_exists('bar', $foo));
        $this->assertEquals('baz', $foo->bar);

        $this->_clear($viewString);
    }

    function testBuiltinElements()
    {
        $viewString = "{$this->_target}BuiltinElements";
        $context = &Piece_Unity_Context::singleton();

        $config = &$this->_getConfig();
        $context->setConfiguration($config);

        $foo = &new stdClass();
        $viewElement = &$context->getViewElement();
        $viewElement->setElementByRef('foo', $foo);
        $context->setView($viewString);

        $view = &new Piece_Unity_Plugin_View();
        ob_start();
        $view->invoke();
        $buffer = ob_get_contents();
        ob_end_clean();

        $this->assertEquals('OK', $buffer);
        $this->_clear($viewString);
    }

    function testNonExistingTemplate()
    {
        Piece_Unity_Error::pushCallback(create_function('$error', 'return ' . PEAR_ERRORSTACK_DIE . ';'));

        $viewString = "{$this->_target}NonExistingTemplate";
        $context = &Piece_Unity_Context::singleton();

        $config = &$this->_getConfig();
        $context->setConfiguration($config);
        $context->setView($viewString);

        $class = "Piece_Unity_Plugin_Renderer_{$this->_target}";
        $renderer = &new $class();
        $renderer->invoke();

        $this->assertTrue(Piece_Unity_Error::hasErrors('warning'));

        $error = Piece_Unity_Error::pop();

        $this->assertEquals($this->_errorCodeWhenTemplateNotExists, $error['code']);

        $this->_clear($viewString);

        Piece_Unity_Error::popCallback();
    }

    function testLayout()
    {
        $viewString = "{$this->_target}LayoutContent";
        $layoutViewString = "{$this->_target}Layout";
        $context = &Piece_Unity_Context::singleton();

        $config = &$this->_getConfig();
        $config->setConfiguration("Renderer_{$this->_target}", 'useLayout', true);
        $config->setConfiguration("Renderer_{$this->_target}", 'layoutView', $layoutViewString);
        $config->setConfiguration("Renderer_{$this->_target}", 'layoutDirectory', dirname(__FILE__) . "/{$this->_target}TestCase/templates/Layout");
        $config->setConfiguration("Renderer_{$this->_target}", 'layoutCompileDirectory', dirname(__FILE__) . "/{$this->_target}TestCase/compiled-templates/Layout");
        $context->setConfiguration($config);

        $viewElement = &$context->getViewElement();
        $viewElement->setElement('foo', 'This is an element for the content.');
        $viewElement->setElement('bar', 'This is an element for the layout.');
        $context->setView($viewString);

        $view = &new Piece_Unity_Plugin_View();
        ob_start();
        $view->invoke();
        $buffer = ob_get_contents();
        ob_end_clean();

        $this->assertEquals('<html>
  <body>
    <h1>This is an element for the layout.</h1>
    <div>
  <h2>This is an element for the content.</h2>
</div>
  </body>
</html>', rtrim($buffer));

        $this->_clear($viewString);
        $this->_clear($layoutViewString);
    }

    function testTurnOffLayoutByHTTPAcceptSuccess()
    {
        $this->_assertTurnOffLayoutByHTTPAccept(true, '<div>
  <h2>This is an element for the content.</h2>
</div>');
    }

    function testTurnOffLayoutByHTTPAcceptFailure()
    {
        $this->_assertTurnOffLayoutByHTTPAccept(false, '<html>
  <body>
    <h1>This is an element for the layout.</h1>
    <div>
  <h2>This is an element for the content.</h2>
</div>
  </body>
</html>');
    }

    /**#@-*/

    /**#@+
     * @access private
     */

    function _render()
    {
        $context = &Piece_Unity_Context::singleton();

        $config = &$this->_getConfig();
        $context->setConfiguration($config);

        $dispatcher = &new Piece_Unity_Plugin_Dispatcher_Simple();
        $context->setView($dispatcher->invoke());
        $class = "Piece_Unity_Plugin_Renderer_{$this->_target}";
        $renderer = &new $class();

        ob_start();
        $renderer->invoke();
        $buffer = ob_get_contents();
        ob_end_clean();

        return $buffer;
    }

    function _clear($view) {}

    function _getConfig() {}

    function _assertTurnOffLayoutByHTTPAccept($turnOffLayoutByHTTPAccept, $result)
    {
        if ($this->_target == 'PHP') {
            return;
        }
    
        $viewString = "{$this->_target}LayoutContent";
        $layoutViewString = "{$this->_target}Layout";
        $context = &Piece_Unity_Context::singleton();

        $_SERVER['HTTP_ACCEPT'] = 'application/x-piece-html-fragment';

        $config = &$this->_getConfig();
        $config->setConfiguration("Renderer_{$this->_target}", 'turnOffLayoutByHTTPAccept', $turnOffLayoutByHTTPAccept);
        $config->setConfiguration("Renderer_{$this->_target}", 'useLayout', true);
        $config->setConfiguration("Renderer_{$this->_target}", 'layoutView', $layoutViewString);
        $config->setConfiguration("Renderer_{$this->_target}", 'layoutDirectory', dirname(__FILE__) . "/{$this->_target}TestCase/templates/Layout");
        $config->setConfiguration("Renderer_{$this->_target}", 'layoutCompileDirectory', dirname(__FILE__) . "/{$this->_target}TestCase/compiled-templates/Layout");
        $context->setConfiguration($config);

        $viewElement = &$context->getViewElement();
        $viewElement->setElement('foo', 'This is an element for the content.');
        $viewElement->setElement('bar', 'This is an element for the layout.');
        $context->setView($viewString);

        $view = &new Piece_Unity_Plugin_View();
        ob_start();
        $view->invoke();
        $buffer = ob_get_contents();
        ob_end_clean();

        $this->assertEquals($result, rtrim($buffer));

        $this->_clear($viewString);
        $this->_clear($layoutViewString);
        
        unset($_SERVER['HTTP_ACCEPT']);
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
