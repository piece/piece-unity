<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * PHP version 5
 *
 * Copyright (c) 2008-2009 KUBO Atsuhiro <kubo@iteman.jp>,
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
 * @copyright  2008-2009 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    GIT: $Id$
 * @since      File available since Release 1.5.0
 */

// {{{ Piece_Unity_Service_Renderring_PHP

/**
 * A rendering service which uses PHP itself as a template engine.
 *
 * @package    Piece_Unity
 * @copyright  2008-2009 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    Release: @package_version@
 * @since      Class available since Release 1.5.0
 */
class Piece_Unity_Service_Rendering_PHP
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

    private $_fileForRender;
    private $_viewElementForRender;

    /**#@-*/

    /**#@+
     * @access public
     */

    // }}}
    // {{{ render()

    /**
     * Renders a HTML or HTML fragment.
     *
     * @param string                  $file
     * @param Piece_Unity_ViewElement $viewElement
     * @throws Piece_Unity_Service_Rendering_NotFoundException
     */
    public function render($file, Piece_Unity_ViewElement $viewElement)
    {
        if (!file_exists($file)) {
            throw new Piece_Unity_Service_Rendering_NotFoundException(
                'The HTML template file [ ' . $file . ' ] is not found'
                                                                      );
        }

        if (!is_readable($file)) {
            throw new Piece_Unity_Service_Rendering_NotFoundException(
                'The HTML template file [ ' . $file . ' ] is not readable'
                                                                      );
        }

        $this->_fileForRender = $file;
        $this->_viewElementForRender = $viewElement;
        extract($this->_viewElementForRender->getElements(), EXTR_OVERWRITE | EXTR_REFS);

        if (!include $this->_fileForRender) {
            throw new Piece_Unity_Service_Rendering_NotFoundException(
                'The HTML template file [ ' .
                $this->_fileForRender .
                ' ] is not found or is not readable'
                                                                      );
        }
    }

    /**#@-*/

    /**#@+
     * @access protected
     */

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
