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
 * @subpackage Piece_Unity_Plugin_Renderer_Smarty
 * @copyright  2006-2007 KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    SVN: $Id$
 * @since      File available since Release 0.2.0
 */

require_once 'Piece/Unity/Plugin/Renderer/Smarty.php';
require_once dirname(__FILE__) . '/HTMLCompatibilityTest.php';
require_once 'Piece/Unity/Config.php';
require_once 'Piece/Unity/Error.php';

// {{{ Piece_Unity_Plugin_Renderer_SmartyTestCase

/**
 * TestCase for Piece_Unity_Plugin_Renderer_Smarty
 *
 * @package    Piece_Unity
 * @subpackage Piece_Unity_Plugin_Renderer_Smarty
 * @copyright  2006-2007 KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    Release: @package_version@
 * @since      Class available since Release 0.2.0
 */
class Piece_Unity_Plugin_Renderer_SmartyTestCase extends Piece_Unity_Plugin_Renderer_HTMLCompatibilityTest
{

    // {{{ properties

    /**#@+
     * @access public
     */

    /**#@-*/

    /**#@+
     * @access private
     */

    var $_target = 'Smarty';

    /**#@-*/

    /**#@+
     * @access public
     */

    function testLoadingPlugins()
    {
        $viewString = "{$this->_target}LoadingPlugins";
        $context = &Piece_Unity_Context::singleton();
        
        $config = &$this->_getConfig();
        $context->setConfiguration($config);
        $context->setView($viewString);
        $buffer = $this->_render();

        $this->assertEquals('Hello World', trim($buffer));

        $this->_clear($viewString);
    }

    /**#@-*/

    /**#@+
     * @access private
     */

    function _clear($view)
    {
        foreach (array('Content', 'Layout', 'Fallback') as $directory) {
            $smarty = &new Smarty();
            $smarty->compile_dir = "{$this->_cacheDirectory}/compiled-templates/$directory";
            $smarty->clear_compiled_tpl("$view.tpl");
        }
    }

    function &_getConfig()
    {
        $config = &new Piece_Unity_Config();
        $config->setConfiguration('Dispatcher_Simple', 'actionDirectory', "{$this->_cacheDirectory}/actions");
        $config->setConfiguration('Renderer_Smarty', 'template_dir', "{$this->_cacheDirectory}/templates/Content");
        $config->setConfiguration('Renderer_Smarty', 'compile_dir', "{$this->_cacheDirectory}/compiled-templates/Content");
        $config->setConfiguration('Renderer_Smarty', 'plugins_dir', array("{$this->_cacheDirectory}/plugins"));
        $config->setExtension('View', 'renderer', 'Renderer_Smarty');

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
