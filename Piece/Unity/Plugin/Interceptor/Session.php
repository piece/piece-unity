<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * PHP versions 4 and 5
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
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    GIT: $Id$
 * @since      File available since Release 0.5.0
 */

require_once 'Piece/Unity/Plugin/Common.php';
require_once 'Piece/Unity/Error.php';

// {{{ Piece_Unity_Plugin_Interceptor_Session

/**
 * An interceptor for session handling.
 *
 * @package    Piece_Unity
 * @copyright  2006-2009 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    Release: @package_version@
 * @since      Class available since Release 0.5.0
 */
class Piece_Unity_Plugin_Interceptor_Session extends Piece_Unity_Plugin_Common
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
     * @return boolean
     */
    function invoke()
    {
        $session = &$this->_context->getSession();
        $session->start();
        if (Piece_Unity_Error::hasErrors()) {
            return;
        }

        if (!$this->_getConfiguration('enableExpiration')) {
            return true;
        }

        if ($session->hasAttribute('_sessionUpdatedAt')
            && !$this->_handleExpiration()
            ) {
            return false;
        }

        $session->setAttribute('_sessionUpdatedAt', time());
        return true;
    }

    /**#@-*/

    /**#@+
     * @access private
     */

    // }}}
    // {{{ _initialize()

    /**
     * Defines and initializes extension points and configuration points.
     *
     * @since Method available since Release 1.6.0
     */
    function _initialize()
    {
        $this->_addConfigurationPoint('enableExpiration', false);
        $this->_addConfigurationPoint('expirationTime', 1440);
        $this->_addConfigurationPoint('expirationFallbackURI');
    }

    // }}}
    // {{{ _handleExpiration()

    /**
     * Checks whether the current session has been expired or not. If it has been
     * expired, this method makrs the current session as expired, and redirects
     * the current request to a given fallback URI. And this method will starts a new
     * session in the next request.
     *
     * @return boolean
     * @since Method available since Release 1.6.0
     */
    function _handleExpiration()
    {
        $session = &$this->_context->getSession();
        if ($session->getAttribute('_sessionExpired')) {
            $session->restart();
            $this->_context->setAttribute('_sessionExpired', true);
            return true;
        }

        if (time() - $session->getAttribute('_sessionUpdatedAt') > $this->_getConfiguration('expirationTime')) {
            $session->setAttribute('_sessionExpired', true);
            $this->_context->sendHTTPStatus(302);
            $this->_context->setView($this->_getConfiguration('expirationFallbackURI'));
            return false;
        }

        return true;
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
