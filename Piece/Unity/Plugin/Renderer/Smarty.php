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
 * @link       http://smarty.php.net/
 * @link       http://iteman.typepad.jp/piece/
 * @see        Smarty
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
 * @link       http://smarty.php.net/
 * @link       http://iteman.typepad.jp/piece/
 * @see        Smarty
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

    var $_smartyClassVariables = array('template_dir',
                                       'compile_dir',
                                       'config_dir',
                                       'cache_dir'
                                       );

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
        $this->_addConfigurationPoint('templateExtension', '.tpl');
        $this->_addConfigurationPoint('SMARTY_DIR', null);
        foreach ($this->_smartyClassVariables as $point) {
            $this->_addConfigurationPoint($point, null);
        }
    }

    // }}}
    // {{{ invoke()

    /**
     * Invokes the plugin specific code.
     */
    function invoke()
    {
        if (!defined('SMARTY_DIR')) {
            $SMARTY_DIR = $this->getConfiguration('SMARTY_DIR');
            if (!is_null($SMARTY_DIR)) {
                define('SMARTY_DIR', Piece_Unity_Plugin_Renderer_Smarty::_adjustEndingSlash($SMARTY_DIR));
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

        foreach ($this->_smartyClassVariables as $point) {
            $$point = $this->getConfiguration($point);
            if (!is_null($$point)) {
                $smarty->$point = Piece_Unity_Plugin_Renderer_Smarty::_adjustEndingSlash($$point);
            }
        }

        $viewElement = &$this->_context->getViewElement();
        $viewElements = $viewElement->getElements();
        foreach ($viewElements as $name => &$value) {
            $smarty->assign_by_ref($name, $value);
        }

        @$smarty->display(str_replace('_', '/', str_replace('.', '', $this->_context->getView())) . $this->getConfiguration('templateExtension'));
    }

    /**#@-*/

    /**#@+
     * @access private
     */

    // }}}
    // {{{ _adjustEndingSlash()

    /**
     * Added an ending slash to the directory if it is required.
     *
     * @param string $directory
     * @return string
     * @static
     */
    function _adjustEndingSlash($directory)
    {
        if (substr($directory, -1, 1) != '/' && substr($directory, -1, 1) != '\\') {
            $directory .= '/';
        }

        return $directory;
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
