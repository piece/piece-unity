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
 * @link       http://piece-framework.com/piece-unity/
 * @since      File available since Release 0.6.0
 */

require_once 'Piece/Unity/Plugin/Common.php';
require_once 'Net/URL.php';

// {{{ Piece_Unity_Plugin_Renderer_Redirection

/**
 * A renderer which is used to redirect requests.
 *
 * @package    Piece_Unity
 * @author     KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @copyright  2006 KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    Release: @package_version@
 * @link       http://piece-framework.com/piece-unity/
 * @since      Class available since Release 0.6.0
 */
class Piece_Unity_Plugin_Renderer_Redirection extends Piece_Unity_Plugin_Common
{

    // {{{ properties

    /**#@+
     * @access public
     */

    /**#@-*/

    /**#@+
     * @access private
     */

    var $_url;

    /**#@-*/

    /**#@+
     * @access public
     */

    // }}}
    // {{{ invoke()

    /**
     * Invokes the plugin specific code.
     */
    function invoke()
    {
        $url = &new Net_URL($this->_context->getView());
        $viewElement = &$this->_context->getViewElement();
        $viewElements = $viewElement->getElements();
        $queryString = $url->querystring;
        foreach (array_keys($queryString) as $elementName) {
            if (array_key_exists($elementName, $viewElements)
                && is_scalar($viewElements[$elementName])
                ) {
                $url->addQueryString($elementName,
                                     $viewElements[$elementName]
                                     );
            }
        }

        if (!$this->getConfiguration('externalURL')) {
            if ($this->_context->usingProxy()) {
                if ($url->host != $_SERVER['HTTP_X_FORWARDED_SERVER']) {
                    $url->host = $_SERVER['HTTP_X_FORWARDED_SERVER'];
                    $url->protocol = 'http';
                    $url->port = 80;
                }
            } else {
                if ($_SERVER['SERVER_PORT'] != 443) {
                    $url->protocol = 'http';
                }
                $url->host = $_SERVER['SERVER_NAME'];
                $url->port = $_SERVER['SERVER_PORT'];
                $proxyPath = $this->_context->getProxyPath();
                $url->path = preg_replace("!^$proxyPath!", '', $url->path);
            }

            if ($this->getConfiguration('addSessionID')) {
                $url->addQueryString($viewElements['__sessionName'],
                                     $viewElements['__sessionID']
                                     );
            }

            if ($this->getConfiguration('addFlowExecutionTicket')) {
                if (array_key_exists('__flowExecutionTicketKey', $viewElements)) {
                    $url->addQueryString($viewElements['__flowExecutionTicketKey'],
                                         $viewElements['__flowExecutionTicket']
                                         );
                }
            }
        }

        $this->_url = $url->getURL();

        if (!headers_sent()) {
            header("Location: {$this->_url}");
        }
    }

    // }}}
    // {{{ getURL()

    /**
     * Gets the url to redirect user's browser to.
     *
     * @return string
     */
    function getURL()
    {
        return $this->_url;
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
     * @since Method available since Release 0.6.0
     */
    function _initialize()
    {
        $this->_addConfigurationPoint('addSessionID', false);
        $this->_addConfigurationPoint('externalURL', false);
        $this->_addConfigurationPoint('addFlowExecutionTicket', false);
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