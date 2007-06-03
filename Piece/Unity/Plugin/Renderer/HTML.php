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
 * @subpackage Piece_Unity_Plugin_Renderer_HTML
 * @copyright  2006-2007 KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    SVN: $Id$
 * @since      File available since Release 0.9.0
 */

require_once 'Piece/Unity/Plugin/Common.php';
require_once 'Piece/Unity/Error.php';

// {{{ Piece_Unity_Plugin_Renderer_HTML

/**
 * An abstract renderer which is used to render HTML.
 *
 * @package    Piece_Unity
 * @subpackage Piece_Unity_Plugin_Renderer_HTML
 * @copyright  2006-2007 KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    Release: @package_version@
 * @since      Class available since Release 0.9.0
 * @abstract
 */
class Piece_Unity_Plugin_Renderer_HTML extends Piece_Unity_Plugin_Common
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
    // {{{ invoke()

    /**
     * Invokes the plugin specific code.
     *
     * @throws PIECE_UNITY_ERROR_INVOCATION_FAILED
     */
    function invoke()
    {
        $useLayout = $this->_getConfiguration('useLayout');
        if ($this->_getConfiguration('turnOffLayoutByHTTPAccept')) {
            if (array_key_exists('HTTP_ACCEPT', $_SERVER)) {
                if ($_SERVER['HTTP_ACCEPT'] == 'application/x-piece-html-fragment') {
                    $useLayout = false;
                }
            }
        }

        if (!$useLayout) {
            $this->_render(false);
        } else {
            $viewElement = &$this->_context->getViewElement();

            ob_start();
            $this->_render(false);
            if (Piece_Unity_Error::hasErrors('exception')) {
                ob_end_clean();
                return;
            }
            $content = ob_get_contents();
            ob_end_clean();

            $viewElement->setElement('__content', $content);

            $this->_render(true);
        }
    }

    /**#@-*/

    /**#@+
     * @access private
     */

    // }}}
    // {{{ _initialize()

    /**
     * Defines and initializes extension points and configuration points.
     */
    function _initialize()
    {
        $this->_addConfigurationPoint('useLayout', false);
        $this->_addConfigurationPoint('layoutView');
        $this->_addConfigurationPoint('layoutDirectory');
        $this->_addConfigurationPoint('layoutCompileDirectory');
        $this->_addConfigurationPoint('turnOffLayoutByHTTPAccept', false);
        $this->_addConfigurationPoint('useFallback', false);
        $this->_addConfigurationPoint('fallbackView');
        $this->_addConfigurationPoint('fallbackDirectory');
        $this->_addConfigurationPoint('fallbackCompileDirectory');
    }

    // }}}
    // {{{ _render()

    /**
     * Renders a HTML.
     * If an error occured while rendering with a specified view and
     * useFallback is true, a fallback view will be rendered.
     *
     * @param boolean $isLayout
     * @throws PIECE_UNITY_ERROR_INVOCATION_FAILED
     */
    function _render($isLayout)
    {
        Piece_Unity_Error::pushCallback(create_function('$error', 'return ' . PEAR_ERRORSTACK_PUSHANDLOG . ';'));
        $this->_doRender($isLayout);
        Piece_Unity_Error::popCallback();

        if (Piece_Unity_Error::hasErrors('exception')) {
            $error = Piece_Unity_Error::pop();
            if ($error['code'] == 'PIECE_UNITY_PLUGIN_RENDERER_HTML_ERROR_NOT_FOUND') {
                if ($this->_getConfiguration('useFallback')) {
                    $this->_context->setView($this->_getConfiguration('fallbackView'));
                    $this->_prepareFallback();
                    $this->_doRender($isLayout);
                    return;
                } else {
                    $level = 'warning';
                }
            } else {
                $level = 'exception';
            }

            if ($level == 'warning') {
                Piece_Unity_Error::pushCallback(create_function('$error', 'return ' . PEAR_ERRORSTACK_PUSHANDLOG . ';'));
            }

            Piece_Unity_Error::push(PIECE_UNITY_ERROR_INVOCATION_FAILED,
                                    'Failed to render a HTML template with the plugin [ ' . get_class($this) . ' ].',
                                    $level,
                                    array('plugin' => __CLASS__),
                                    $error
                                    );

            if ($level == 'warning') {
                Piece_Unity_Error::popCallback();
            }
        }
    }

    // }}}
    // {{{ _doRender()

    /**
     * Renders a HTML.
     *
     * @param boolean $isLayout
     * @throws PIECE_UNITY_ERROR_INVOCATION_FAILED
     * @abstract
     */
    function _doRender($isLayout) {}

    // }}}
    // {{{ _prepareFallback()

    /**
     * Prepares another view as a fallback.
     *
     * @abstract
     */
    function _prepareFallback() {}

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
