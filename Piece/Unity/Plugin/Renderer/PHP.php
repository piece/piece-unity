<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * PHP versions 4 and 5
 *
 * Copyright (c) 2006-2008 KUBO Atsuhiro <iteman@users.sourceforge.net>,
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
 * @copyright  2006-2008 KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    GIT: $Id$
 * @since      File available since Release 0.1.0
 */

require_once 'Piece/Unity/Plugin/Renderer/HTML.php';
require_once 'Piece/Unity/Error.php';
require_once 'Piece/Unity/Service/Rendering/PHP.php';

// {{{ Piece_Unity_Plugin_Renderer_PHP

/**
 * A renderer which uses PHP itself as a template engine.
 *
 * @package    Piece_Unity
 * @copyright  2006-2008 KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    Release: @package_version@
 * @since      Class available since Release 0.1.0
 */
class Piece_Unity_Plugin_Renderer_PHP extends Piece_Unity_Plugin_Renderer_HTML
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

    // }}}
    // {{{ _initialize()

    /**
     * Defines and initializes extension points and configuration points.
     *
     * @since Method available since Release 0.6.0
     */
    function _initialize()
    {
        parent::_initialize();
        $this->_addConfigurationPoint('templateDirectory');
        $this->_addConfigurationPoint('templateExtension', '.php');
    }

    // }}}
    // {{{ _doRender()

    /**
     * Renders a HTML.
     *
     * @param boolean $isLayout
     */
    function _doRender($isLayout)
    {
        $templateDirectory = $this->_getConfiguration('templateDirectory');
        if (!$isLayout) {
            $view = $this->_context->getView();
        } else {
            $layoutDirectory = $this->_getConfiguration('layoutDirectory');
            if (!is_null($layoutDirectory)) {
                $templateDirectory = $layoutDirectory;
            }

            $view = $this->_getConfiguration('layoutView');
        }

        if (is_null($templateDirectory)) {
            return;
        }

        $file = "$templateDirectory/" . str_replace('_', '/', str_replace('.', '', $view)) . $this->_getConfiguration('templateExtension');
        $viewElement = &$this->_context->getViewElement();

        $rendering = &new Piece_Unity_Service_Rendering_PHP();
        $rendering->render($file, $viewElement);
        if (Piece_Unity_Error::hasErrors()) {
            $error = Piece_Unity_Error::pop();
            Piece_Unity_Error::push('PIECE_UNITY_PLUGIN_RENDERER_HTML_ERROR_NOT_FOUND',
                                    $error['message'],
                                    'exception',
                                    array(),
                                    $error
                                    );
        }
    }

    // }}}
    // {{{ _prepareFallback()

    /**
     * Prepares another view as a fallback.
     */
    function _prepareFallback()
    {
        $fallbackDirectory = $this->_getConfiguration('fallbackDirectory');
        if (!is_null($fallbackDirectory)) {
            $config = &$this->_context->getConfiguration();
            $config->setConfiguration('Renderer_PHP', 'templateDirectory', $fallbackDirectory);
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
