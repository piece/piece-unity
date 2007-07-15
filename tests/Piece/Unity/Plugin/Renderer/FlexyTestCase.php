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
 * @subpackage Piece_Unity_Plugin_Renderer_Flexy
 * @copyright  2006-2007 KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    SVN: $Id$
 * @since      File available since Release 0.2.0
 */

require dirname(__FILE__) . '/../../../../prepare.php';
require_once 'Piece/Unity/Plugin/Renderer/HTMLTest.php';
require_once 'Piece/Unity/Plugin/Renderer/Flexy.php';
require_once 'Piece/Unity/Config.php';
require_once 'Piece/Unity/Context.php';

// {{{ Piece_Unity_Plugin_Renderer_FlexyTestCase

/**
 * TestCase for Piece_Unity_Plugin_Renderer_Flexy
 *
 * @package    Piece_Unity
 * @subpackage Piece_Unity_Plugin_Renderer_Flexy
 * @copyright  2006-2007 KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    Release: @package_version@
 * @since      Class available since Release 0.2.0
 */
class Piece_Unity_Plugin_Renderer_FlexyTestCase extends Piece_Unity_Plugin_Renderer_HTMLTest
{

    // {{{ properties

    /**#@+
     * @access public
     */

    /**#@-*/

    /**#@+
     * @access private
     */

    var $_target = 'Flexy';
    var $_expectedOutput = '<body>
  <form name="theform" action="http://pear.php.net">    <textarea name="test_textarea">Blogs</textarea>
    <select name="test_select"><option value="123">a select option</option><option value="1234" selected>another select option</option></select>
    <input name="test_checkbox" type="checkbox" value="1" checked>
    <input name="test_checkbox_array[]" type="checkbox" value="1" id="tmpId1" checked>1<br>
    <input name="test_checkbox_array[]" type="checkbox" value="2" id="tmpId2" checked>2<br>
    <input name="test_checkbox_array[]" type="checkbox" value="3" id="tmpId3">3<br>

    <input name="test_radio" type="radio" id="test_radio_yes" value="yes" checked>yes<br>
    <input name="test_radio" type="radio" id="test_radio_no" value="no">no<br>
  </form>
</body>
';

    /**#@-*/

    /**#@+
     * @access public
     */

    function testAutomaticFormElements()
    {
        $viewString = "{$this->_target}AutomaticFormElements";
        $context = &Piece_Unity_Context::singleton();

        $config = &$this->_getConfig();
        $context->setConfiguration($config);

        $elements['theform']['_attributes']['action'] = 'http://pear.php.net';
        $elements['test_textarea']['_value'] = 'Blogs';
        $elements['test_select']['_options'] = array('123' => 'a select option',
                                                     '1234' => 'another select option'
                                                     );
        $elements['test_select']['_value'] = '1234';
        $elements['test_checkbox']['_value'] = '1';
        $elements['test_checkbox_array[]']['_value'] = array(1, 2);
        $elements['test_radio']['_value'] = 'yes';

        $viewElement = &$context->getViewElement();
        $viewElement->setElement('_elements', $elements);
        $context->setView($viewString);
        $buffer = $this->_render();

        $this->assertEquals($this->_expectedOutput, $buffer);

        $this->_clear($viewString);
    }

    function testDebug()
    {
        $viewString = "{$this->_target}NonExistingTemplate";
        $context = &Piece_Unity_Context::singleton();

        $config = &$this->_getConfig();
        $config->setConfiguration('Renderer_Flexy', 'debug', 1);
        $context->setConfiguration($config);
        $context->setView($viewString);
        $buffer = $this->_render();

        $this->assertTrue(strstr($buffer, 'FLEXY DEBUG:'));

        $this->_clear($viewString);
    }

    /**
     * @since Method available since Release 0.13.0
     */
    function testControllerShouldBeUsedIfUseControllerIsTrue()
    {
        $viewString = "{$this->_target}ControllerShouldBeUsedIfUseControllerIsTrue";
        $context = &Piece_Unity_Context::singleton();

        $config = &$this->_getConfig();
        $config->setConfiguration('Renderer_Flexy', 'useController', true);
        $config->setConfiguration('Renderer_Flexy', 'controllerClass', "Piece_Unity_Plugin_Renderer_FlexyTestCase_{$this->_target}ControllerShouldBeUsedIfUseControllerIsTrue");
        $config->setConfiguration('Renderer_Flexy', 'controllerDirectory', "{$this->_cacheDirectory}/lib");
        $context->setConfiguration($config);
        $context->setView($viewString);

        $viewElement = &$context->getViewElement();
        $viewElement->setElement('foo', 'BAR');
        $buffer = $this->_render();

        $this->assertEquals('<p>bar</p>', rtrim($buffer));

        $this->_clear($viewString);
    }

    /**
     * @since Method available since Release 0.13.0
     */
    function testControllerShouldNotBeUsedIfUseControllerIsFalse()
    {
        $viewString = "{$this->_target}ControllerShouldBeUsedIfUseControllerIsTrue";
        $context = &Piece_Unity_Context::singleton();

        $config = &$this->_getConfig();
        $config->setConfiguration('Renderer_Flexy', 'useController', false);
        $config->setConfiguration('Renderer_Flexy', 'controllerClass', "Piece_Unity_Plugin_Renderer_FlexyTestCase_{$this->_target}ControllerShouldBeUsedIfUseControllerIsTrue");
        $config->setConfiguration('Renderer_Flexy', 'controllerDirectory', "{$this->_cacheDirectory}/lib");
        $context->setConfiguration($config);
        $context->setView($viewString);

        $viewElement = &$context->getViewElement();
        $viewElement->setElement('foo', 'BAR');
        $buffer = $this->_render();

        $this->assertEquals('<p></p>', rtrim($buffer));

        $this->_clear($viewString);
    }

    /**
     * @since Method available since Release 0.13.0
     */
    function testExceptionShouldBeRaisedIfControllerDirectoryIsNotSpecified()
    {
        Piece_Unity_Error::pushCallback(create_function('$error', 'return ' . PEAR_ERRORSTACK_PUSHANDLOG . ';'));
        $viewString = "{$this->_target}ControllerShouldBeUsedIfUseControllerIsTrue";
        $context = &Piece_Unity_Context::singleton();

        $config = &$this->_getConfig();
        $config->setConfiguration('Renderer_Flexy', 'useController', true);
        $config->setConfiguration('Renderer_Flexy', 'controllerDirectory', "{$this->_cacheDirectory}/lib");
        $context->setConfiguration($config);
        $context->setView($viewString);

        $viewElement = &$context->getViewElement();
        $viewElement->setElement('foo', 'BAR');
        $this->_render();

        $this->assertTrue(Piece_Unity_Error::hasErrors('exception'));

        $error = Piece_Unity_Error::pop();

        $this->assertEquals(PIECE_UNITY_ERROR_INVOCATION_FAILED, $error['code']);

        Piece_Unity_Error::popCallback();
    }

    /**
     * @since Method available since Release 0.13.0
     */
    function testExceptionShouldBeRaisedIfControllerClassIsNotSpecified()
    {
        Piece_Unity_Error::pushCallback(create_function('$error', 'return ' . PEAR_ERRORSTACK_PUSHANDLOG . ';'));
        $viewString = "{$this->_target}ControllerShouldBeUsedIfUseControllerIsTrue";
        $context = &Piece_Unity_Context::singleton();

        $config = &$this->_getConfig();
        $config->setConfiguration('Renderer_Flexy', 'useController', true);
        $config->setConfiguration('Renderer_Flexy', 'controllerClass', "Piece_Unity_Plugin_Renderer_FlexyTestCase_{$this->_target}ControllerShouldBeUsedIfUseControllerIsTrue");
        $context->setConfiguration($config);
        $context->setView($viewString);

        $viewElement = &$context->getViewElement();
        $viewElement->setElement('foo', 'BAR');
        $this->_render();

        $this->assertTrue(Piece_Unity_Error::hasErrors('exception'));

        $error = Piece_Unity_Error::pop();

        $this->assertEquals(PIECE_UNITY_ERROR_INVOCATION_FAILED, $error['code']);

        Piece_Unity_Error::popCallback();
    }

    /**
     * @since Method available since Release 0.13.0
     */
    function testExternalPluginShouldBeAbleToUseByExternalPlugins()
    {
        $oldIncludePath = set_include_path("$this->_cacheDirectory/lib" . PATH_SEPARATOR . get_include_path());

        $viewString = "{$this->_target}ExternalPluginShouldBeAbleToUseByExternalPlugins";
        $context = &Piece_Unity_Context::singleton();

        $config = &$this->_getConfig();
        $config->setConfiguration('Renderer_Flexy',
                                  'externalPlugins',
                                  array("Piece_Unity_Plugin_Renderer_FlexyTestCase_{$this->_target}ExternalPluginShouldBeAbleToUseByExternalPlugins" => "Piece/Unity/Plugin/Renderer/FlexyTestCase/{$this->_target}ExternalPluginShouldBeAbleToUseByExternalPlugins.php")
                                  );
        $context->setConfiguration($config);
        $context->setView($viewString);

        $viewElement = &$context->getViewElement();
        $viewElement->setElement('foo', 'BAR');
        $viewElement->setElement('bar', 1000);
        $buffer = $this->_render();

        $this->assertEquals('bar:bar:[pear_error: message=&quot;could not find plugin with method: \'numberformat\'&quot; code=0 mode=return level=notice prefix=&quot;&quot; info=&quot;&quot;]:[pear_error: message="could not find plugin with method: \'numberformat\'" code=0 mode=return level=notice prefix="" info=""]', rtrim($buffer));

        set_include_path($oldIncludePath);
        $this->_clear($viewString);
    }

    /**
     * @since Method available since Release 0.13.0
     */
    function testFlexyBuiltinPluginShouldBeAbleToUseByPlugins()
    {
        $oldIncludePath = set_include_path("$this->_cacheDirectory/lib" . PATH_SEPARATOR . get_include_path());

        $viewString = "{$this->_target}ExternalPluginShouldBeAbleToUseByExternalPlugins";
        $context = &Piece_Unity_Context::singleton();

        $config = &$this->_getConfig();
        $config->setConfiguration('Renderer_Flexy', 'plugins', array('Savant'));
        $context->setConfiguration($config);
        $context->setView($viewString);

        $viewElement = &$context->getViewElement();
        $viewElement->setElement('foo', 'BAR');
        $viewElement->setElement('bar', 1000);
        $buffer = $this->_render();

        $this->assertEquals('[pear_error: message=&quot;could not find plugin with method: \'lowerCase\'&quot; code=0 mode=return level=notice prefix=&quot;&quot; info=&quot;&quot;]:[pear_error: message="could not find plugin with method: \'lowerCase\'" code=0 mode=return level=notice prefix="" info=""]:1,000.00:1,000.00', rtrim($buffer));

        set_include_path($oldIncludePath);
        $this->_clear($viewString);
    }

    /**
     * @since Method available since Release 0.13.0
     */
    function testFlexyBuiltinPluginAndExternalPluginShouldBeAbleToUseTogether()
    {
        $oldIncludePath = set_include_path("$this->_cacheDirectory/lib" . PATH_SEPARATOR . get_include_path());

        $viewString = "{$this->_target}ExternalPluginShouldBeAbleToUseByExternalPlugins";
        $context = &Piece_Unity_Context::singleton();

        $config = &$this->_getConfig();
        $config->setConfiguration('Renderer_Flexy', 'plugins', array('Savant'));
        $config->setConfiguration('Renderer_Flexy',
                                  'externalPlugins',
                                  array("Piece_Unity_Plugin_Renderer_FlexyTestCase_{$this->_target}ExternalPluginShouldBeAbleToUseByExternalPlugins" => "Piece/Unity/Plugin/Renderer/FlexyTestCase/{$this->_target}ExternalPluginShouldBeAbleToUseByExternalPlugins.php")
                                  );
        $context->setConfiguration($config);
        $context->setView($viewString);

        $viewElement = &$context->getViewElement();
        $viewElement->setElement('foo', 'BAR');
        $viewElement->setElement('bar', 1000);
        $buffer = $this->_render();

        $this->assertEquals('bar:bar:1,000.00:1,000.00', rtrim($buffer));

        set_include_path($oldIncludePath);
        $this->_clear($viewString);
    }

    /**#@-*/

    /**#@+
     * @access private
     */

    function _clear($view)
    {
        $files = array("{$this->_cacheDirectory}/compiled-templates/Content/$view.html.en.php",
                       "{$this->_cacheDirectory}/compiled-templates/Content/$view.html.gettext.serial",
                       "{$this->_cacheDirectory}/compiled-templates/Content/$view.html.elements.serial",
                       "{$this->_cacheDirectory}/compiled-templates/Layout/$view.html.en.php",
                       "{$this->_cacheDirectory}/compiled-templates/Layout/$view.html.gettext.serial",
                       "{$this->_cacheDirectory}/compiled-templates/Layout/$view.html.elements.serial",
                       "{$this->_cacheDirectory}/compiled-templates/Fallback/$view.html.en.php",
                       "{$this->_cacheDirectory}/compiled-templates/Fallback/$view.html.gettext.serial",
                       "{$this->_cacheDirectory}/compiled-templates/Fallback/$view.html.elements.serial"
                       );
        foreach ($files as $file) {
            if (file_exists($file)) {
                unlink($file);
            }
        }
    }

    function &_getConfig()
    {
        $config = &new Piece_Unity_Config();
        $config->setConfiguration('Dispatcher_Simple', 'actionDirectory', "{$this->_cacheDirectory}/actions");
        $config->setConfiguration('Renderer_Flexy', 'templateDir', "{$this->_cacheDirectory}/templates/Content");
        $config->setConfiguration('Renderer_Flexy', 'compileDir', "{$this->_cacheDirectory}/compiled-templates/Content");
        $config->setExtension('View', 'renderer', 'Renderer_Flexy');

        return $config;
    }

    /**
     * @since Method available since Release 0.13.0
     */
    function _doSetUp()
    {
        $this->_cacheDirectory = dirname(__FILE__) . '/' . basename(__FILE__, '.php');
    }

    /**#@-*/

    // }}}
}

// }}}

function setBaz(&$foo)
{
    $foo->bar = 'baz';
}

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
