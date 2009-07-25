<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * PHP version 5
 *
 * Copyright (c) 2006-2009 KUBO Atsuhiro <kubo@iteman.jp>,
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
 * @copyright  2006-2009 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @since      File available since Release 0.9.0
 */

// {{{ Piece_Unity_Plugin_Renderer_HTML

/**
 * An abstract renderer which is used to render HTML.
 *
 * @package    Piece_Unity
 * @copyright  2006-2009 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @since      Class available since Release 0.9.0
 */
abstract class Piece_Unity_Plugin_Renderer_HTML extends Piece_Unity_Plugin_Common implements Piece_Unity_Plugin_Renderer_Interface
{

    // {{{ properties

    /**#@+
     * @access public
     */

    /**#@-*/

    /**#@+
     * @access protected
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
    // {{{ render()

    /**
     * @throws Piece_Unity_Exception
     */
    public function render()
    {
        $useLayout = $this->useLayout;
        if ($this->turnOffLayoutByHTTPAccept) {
            if (array_key_exists('HTTP_ACCEPT', $_SERVER)) {
                if ($_SERVER['HTTP_ACCEPT'] == 'application/x-piece-html-fragment') {
                    $useLayout = false;
                }
            }
        }

        if (!is_array($this->components)) {
            throw new Piece_Unity_Exception('The value of the extension point [ components ] on the plug-in [ ' .
                                            $this->getName() .
                                            ' ] should be an array'
                                            );
        }

        foreach ($this->components as $extension) {
            $extension->invoke();
        }

        if (!$useLayout) {
            $this->_render(false);
        } else {
            $viewElement = $this->context->getViewElement();

            ob_start();
            try {
                $this->_render(false);
                $viewElement->setElement('__content', ob_get_contents());
            } catch (Exception $e) {
                ob_end_clean();
                throw $e;
            }
            ob_end_clean();

            $this->_render(true);
        }
    }

    /**#@-*/

    /**#@+
     * @access protected
     */

    // }}}
    // {{{ doRender()

    /**
     * Renders a HTML.
     *
     * @param boolean $isLayout
     */
    abstract protected function doRender($isLayout);

    // }}}
    // {{{ _prepareFallback()

    /**
     * Prepares another view as a fallback.
     */
    abstract protected function prepareFallback();

    /**#@-*/

    /**#@+
     * @access private
     */

    // }}}
    // {{{ _render()

    /**
     * Renders a HTML.
     * If an error occured while rendering with a specified view and
     * useFallback is true, a fallback view will be rendered.
     *
     * @param boolean $isLayout
     * @throws Piece_Unity_Service_Rendering_NotFoundException
     */
    private function _render($isLayout)
    {
        try {
            $this->doRender($isLayout);
        } catch (Piece_Unity_Service_Rendering_NotFoundException $e) {
            trigger_error('Failed to render a HTML template', E_USER_WARNING);
            if ($this->useFallback) {
                $this->context->setView($this->fallbackView);
                $this->prepareFallback();
                $this->doRender($isLayout);
                return;
            }

            throw $e;
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
