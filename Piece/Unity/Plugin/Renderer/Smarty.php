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
 * @link       http://smarty.php.net/
 * @link       http://piece-framework.com/piece-unity/
 * @see        Smarty
 * @since      File available since Release 0.2.0
 */

require_once 'Piece/Unity/Plugin/Common.php';
require_once 'Piece/Unity/Plugin/Renderer/HTML.php';
require_once 'Piece/Unity/Error.php';

// {{{ Piece_Unity_Plugin_Renderer_Smarty

/**
 * A renderer which is based on Smarty template engine.
 *
 * @package    Piece_Unity
 * @author     KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @copyright  2006 KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    Release: @package_version@
 * @link       http://smarty.php.net/
 * @link       http://piece-framework.com/piece-unity/
 * @see        Smarty
 * @since      Class available since Release 0.2.0
 */
class Piece_Unity_Plugin_Renderer_Smarty extends Piece_Unity_Plugin_Renderer_HTML
{

    // {{{ properties

    /**#@+
     * @access public
     */

    /**#@-*/

    /**#@+
     * @access private
     */

    var $_smartyClassVariables = array('template_dir' => null,
                                       'compile_dir'  => null,
                                       'config_dir'   => null,
                                       'cache_dir'    => null
                                       );

    /**#@-*/

    /**#@+
     * @access public
     */

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
     */
    function _adjustEndingSlash($directory)
    {
        if (substr($directory, -1, 1) != '/' && substr($directory, -1, 1) != '\\') {
            $directory .= '/';
        }

        return $directory;
    }

    // }}}
    // {{{ _load()

    /**
     * Loads a Smarty class.
     *
     * @throws PIECE_UNITY_ERROR_NOT_FOUND
     */
    function _load()
    {
        if (!defined('SMARTY_DIR')) {
            $SMARTY_DIR = $this->getConfiguration('SMARTY_DIR');
            if (!is_null($SMARTY_DIR)) {
                define('SMARTY_DIR', Piece_Unity_Plugin_Renderer_Smarty::_adjustEndingSlash($SMARTY_DIR));
            }
        }

        if (defined('SMARTY_DIR')) {
            $included = @include_once SMARTY_DIR . 'Smarty.class.php';
        } else {
            $included = @include_once 'Smarty.class.php';
            if (!$included) {
                $included = @include_once 'Smarty/Smarty.class.php';
            }
        }

        if (!$included) {
            Piece_Unity_Error::push(PIECE_UNITY_ERROR_NOT_FOUND,
                                    'The Smarty class file [ Smarty.class.php ] not found or was not readable.'
                                    );
            return;
        }

        if (version_compare(phpversion(), '5.0.0', '<')) {
            $loaded = class_exists('Smarty');
        } else {
            $loaded = class_exists('Smarty', false);
        }

        if (!$loaded) {
            Piece_Unity_Error::push(PIECE_UNITY_ERROR_NOT_FOUND,
                                    'The class [ Smarty ] not defined in the class file [ Smarty.class.php ].'
                                    );
        }
    }

    // }}}
    // {{{ _initialize()

    /**
     * Defines and initializes extension points and configuration points.
     *
     * @throws PIECE_UNITY_ERROR_NOT_FOUND
     * @since Method available since Release 0.6.0
     */
    function _initialize()
    {
        parent::_initialize();
        $this->_addConfigurationPoint('templateExtension', '.tpl');
        $this->_addConfigurationPoint('SMARTY_DIR');
        foreach ($this->_smartyClassVariables as $point => $default) {
            $this->_addConfigurationPoint($point, $default);
        }

        $this->_load();
    }

    // }}}
    // {{{ _render()

    /**
     * Renders a HTML.
     *
     * @param boolean $isLayout
     * @throws PIECE_UNITY_ERROR_INVOCATION_FAILED
     */
    function _render($isLayout)
    {
        $smarty = &new Smarty();

        foreach (array_keys($this->_smartyClassVariables) as $point) {
            $$point = $this->getConfiguration($point);
            if (!is_null($$point)) {
                $smarty->$point = $this->_adjustEndingSlash($$point);
            }
        }

        if (!$isLayout) {
            $view = $this->_context->getView();
        } else {
            $smarty->template_dir = $this->getConfiguration('layoutDirectory');
            $smarty->compile_dir = $this->getConfiguration('layoutCompileDirectory');
            $view = $this->getConfiguration('layoutView');
        }

        $viewElement = &$this->_context->getViewElement();
        $viewElements = $viewElement->getElements();
        foreach (array_keys($viewElements) as $elementName) {
            $smarty->assign_by_ref($elementName, $viewElements[$elementName]);
        }

        set_error_handler(array('Piece_Unity_Error', 'pushPHPError'));
        Piece_Unity_Error::pushCallback(create_function('$error', 'return ' . PEAR_ERRORSTACK_PUSHANDLOG . ';'));
        $smarty->display(str_replace('_', '/', str_replace('.', '', $view)) . $this->getConfiguration('templateExtension'));
        Piece_Unity_Error::popCallback();
        restore_error_handler();
        if (Piece_Unity_Error::hasErrors('exception')) {
            Piece_Unity_Error::push(PIECE_UNITY_ERROR_INVOCATION_FAILED,
                                    'Failed to invoke the plugin [ ' . __CLASS__ . ' ].',
                                    'exception',
                                    array('plugin' => __CLASS__),
                                    Piece_Unity_Error::pop()
                                    );
        }
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
