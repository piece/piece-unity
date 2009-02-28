<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * PHP versions 4 and 5
 *
 * Copyright (c) 2008 KUBO Atsuhiro <iteman@users.sourceforge.net>,
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
 * @copyright  2008 KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    GIT: $Id$
 * @since      File available since Release 1.5.0
 */

require_once 'Piece/Unity/Plugin/Common.php';
require_once 'Piece/Unity/Error.php';

// {{{ Piece_Unity_Plugin_ViewSchemeHandler

/**
 * The view scheme handler which determines appropriate renderer extension by
 * the view scheme in the current view string such like "http:", "json:", "html:",
 * etc.
 *
 * @package    Piece_Unity
 * @copyright  2008 KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    Release: @package_version@
 * @since      Class available since Release 1.5.0
 */
class Piece_Unity_Plugin_ViewSchemeHandler extends Piece_Unity_Plugin_Common
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
     * Determines appropriate renderer extension by the view scheme in the current
     * view string such like "http:", "json:", "html:", etc.
     *
     * @return string
     * @throws PIECE_UNITY_ERROR_UNEXPECTED_VALUE
     */
    function invoke()
    {
        $viewString = $this->_context->getView();
        $positionOfColon = strpos($viewString, ':');
        if ($positionOfColon === 0) {
            Piece_Unity_Error::push(PIECE_UNITY_ERROR_UNEXPECTED_VALUE,
                                    "The view string [ $viewString ] should not start with colon."
                                    );
            return;
        }

        $viewScheme = !$positionOfColon ? 'html'
                                        : substr($viewString, 0, $positionOfColon);
        $rendererExtension = $this->_getConfiguration($viewScheme);
        if (Piece_Unity_Error::hasErrors()) {
            return;
        }

        $this->_context->setView(preg_replace('/^html:/', '', $viewString));

        return $rendererExtension;
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
        $this->_addConfigurationPoint('html', 'Renderer_PHP');
        $this->_addConfigurationPoint('http', 'Renderer_Redirection');
        $this->_addConfigurationPoint('https', 'Renderer_Redirection');
        $this->_addConfigurationPoint('self', 'Renderer_Redirection');
        $this->_addConfigurationPoint('selfs', 'Renderer_Redirection');
        $this->_addConfigurationPoint('json', 'Renderer_JSON');
        $this->_addConfigurationPoint('raw', 'Renderer_Raw');
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
