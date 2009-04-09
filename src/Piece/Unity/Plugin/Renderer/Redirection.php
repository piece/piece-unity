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
 * @since      File available since Release 0.6.0
 */

// {{{ Piece_Unity_Plugin_Renderer_Redirection

/**
 * A renderer which is used to redirect requests.
 *
 * @package    Piece_Unity
 * @copyright  2006-2009 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @since      Class available since Release 0.6.0
 */
class Piece_Unity_Plugin_Renderer_Redirection extends Piece_Unity_Plugin_Common implements Piece_Unity_Plugin_Renderer_Interface
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

    private $_sentURI;

    /**#@-*/

    /**#@+
     * @access public
     */

    // }}}
    // {{{ render()

    /**
     */
    public function render()
    {
        $this->_replaceSelfNotationWithURI();
        $uri = $this->_buildURI();

        if (!headers_sent() && !is_null($uri)) {
            header("Location: $uri");
        }
    }

    /**#@-*/

    /**#@+
     * @access protected
     */

    // }}}
    // {{{ initialize()

    /**
     * Defines and initializes extension points and configuration points.
     *
     * @since Method available since Release 0.6.0
     */
    protected function initialize()
    {
        $this->addConfigurationPoint('addSessionID', false);
        $this->addConfigurationPoint('isExternal', false);
        $this->addConfigurationPoint('addFlowExecutionTicket', false);
    }

    /**#@-*/

    /**#@+
     * @access private
     */

    // }}}
    // {{{ _replaceSelfNotationWithURI()

    /**
     * @since Method available since Release 1.5.0
     */
    private function _replaceSelfNotationWithURI()
    {
        $viewString = $this->context->getView();
        if (!preg_match('!^selfs?://(.*)!', $viewString, $matches)) {
            return;
        }

        $this->context->getConfiguration()
                      ->setConfiguration('Renderer_Redirection', 'addFlowExecutionTicket', true);
        if (substr($viewString, 0, 7) == 'self://') {
            $this->context->setView('http://example.org' . $this->context->getScriptName() . '?' . $matches[1]);
        } elseif (substr($viewString, 0, 8) == 'selfs://') {
            $this->context->setView('https://example.org' . $this->context->getScriptName() . '?' . $matches[1]);
        }
    }

    // }}}
    // {{{ _buildURI()

    /**
     * @since Method available since Release 1.5.0
     */
    private function _buildURI()
    {
        $isExternal = $this->getConfiguration('isExternal');
        $uri = new Piece_Unity_URI($this->context->getView());
        $uri->setIsRedirection(true);
        $uri->setIsExternal($isExternal);

        $viewElements = $this->context->getViewElement()->getElements();
        $queryString = $uri->getQueryString();
        foreach (array_keys($queryString) as $elementName) {
            if (array_key_exists($elementName, $viewElements)
                && is_scalar($viewElements[$elementName])
                ) {
                $uri->addQueryString($elementName,
                                     $viewElements[$elementName]
                                     );
            }
        }

        if (!$isExternal) {
            if ($this->getConfiguration('addSessionID')) {
                $uri->addQueryString($viewElements['__sessionName'],
                                     $viewElements['__sessionID']
                                     );
            }

            if ($this->getConfiguration('addFlowExecutionTicket')) {
                if (array_key_exists('__flowExecutionTicketKey', $viewElements)) {
                    $uri->addQueryString($viewElements['__flowExecutionTicketKey'],
                                         $viewElements['__flowExecutionTicket']
                                         );
                }
            }

            /*
             * Replaces __eventNameKey with the event name key.
             */
            if (array_key_exists('__eventNameKey', $queryString)) {
                $uri->removeQueryString('__eventNameKey');
                $uri->addQueryString($this->context->getEventNameKey(), $queryString['__eventNameKey']);
            }
        }

        $this->_sentURI = $uri->getURI('pass');

        return $this->_sentURI;
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
