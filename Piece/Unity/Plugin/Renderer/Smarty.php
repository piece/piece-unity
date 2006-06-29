<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * PHP versions 4 and 5
 *
 * Copyright (c) 2006, KUBO Atsuhiro <iteman2002@yahoo.co.jp>
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
 * @author     KUBO Atsuhiro <iteman2002@yahoo.co.jp>
 * @copyright  2006 KUBO Atsuhiro <iteman2002@yahoo.co.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    SVN: $Id$
 * @link       http://iteman.typepad.jp/piece/
 * @since      File available since Release 0.2.0
 */

require_once 'Piece/Unity/Plugin/Common.php';

// {{{ Piece_Unity_Plugin_Renderer_Smarty

/**
 * @package    Piece_Unity
 * @author     KUBO Atsuhiro <iteman2002@yahoo.co.jp>
 * @copyright  2006 KUBO Atsuhiro <iteman2002@yahoo.co.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    Release: @package_version@
 * @link       http://iteman.typepad.jp/piece/
 * @since      Class available since Release 0.2.0
 */
class Piece_Unity_Plugin_Renderer_Smarty extends Piece_Unity_Plugin_Common
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

    // }}}
    // {{{ constructor

    /**
     * Defines extension points and configuration points for the plugin.
     */
    function Piece_Unity_Plugin_Renderer_Smarty()
    {
        parent::Piece_Unity_Plugin_Common();
        $this->_addConfigurationPoint('templateDirectory', null);
        $this->_addConfigurationPoint('templateSuffix', '.tpl');
        $this->_addConfigurationPoint('smartyDirectory', null);
        $this->_addConfigurationPoint('compileDirectory', null);
        $this->_addConfigurationPoint('configDirectory', null);
        $this->_addConfigurationPoint('cacheDirectory', null);
    }

    // }}}
    // {{{ invoke()

    /**
     * Invokes the plugin specific code.
     *
     * @throws PIECE_UNITY_ERROR_INVOCATION_FAILED
     */
    function invoke()
    {
        if (!defined('SMARTY_DIR')) {
            $smartyDirectory = $this->getConfiguration('smartyDirectory');
            if (!is_null($smartyDirectory)) {
                define('SMARTY_DIR', $smartyDirectory);
            }
        }

        if (defined('SMARTY_DIR')) {
            @include_once SMARTY_DIR . 'Smarty.class.php';
        } else {
            @include_once 'Smarty.class.php';
        }

        if (!class_exists('Smarty')) {
            Piece_Unity_Error::push(PIECE_UNITY_ERROR_INVOCATION_FAILED,
                                    'Failed to invoke the plugin [ ' . __CLASS__ . ' ].',
                                    'exception',
                                    array('plugin' => __CLASS__)
                                    );
            return;
        }

        $smarty = &new Smarty();

        $templateDirectory = $this->getConfiguration('templateDirectory');
        if (!is_null($templateDirectory)) {
            $smarty->template_dir = $templateDirectory;
        }

        $compileDirectory = $this->getConfiguration('compileDirectory');
        if (!is_null($compileDirectory)) {
            $smarty->compile_dir = $compileDirectory;
        }

        $configDirectory = $this->getConfiguration('configDirectory');
        if (!is_null($configDirectory)) {
            $smarty->config_dir = $configDirectory;
        }

        $cacheDirectory = $this->getConfiguration('cacheDirectory');
        if (!is_null($cacheDirectory)) {
            $smarty->cache_dir = $cacheDirectory;
        }

        $viewElement = &$this->_context->getViewElement();
        $viewElements = $viewElement->getElements();
        foreach ($viewElements as $name => &$value) {
            $smarty->assign_by_ref($name, $value);
        }

        @$smarty->display(str_replace('_', '/', str_replace('.', '', $this->_context->getView())) . $this->getConfiguration('templateSuffix'));
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
