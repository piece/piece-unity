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
 * @author     KUMAKURA Yousuke <kumatch@users.sourceforge.net>
 * @copyright  2006-2007 KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    SVN: $Id$
 * @since      File available since Release 0.9.0
 */

require_once 'Piece/Unity/Service/FlowAction.php';
require_once 'Piece/Unity/Service/FlexyForm.php';

// {{{ AuthenticationAction

/**
 * Action class for the flow AuthenticationAction.
 *
 * @package    Piece_Unity
 * @author     KUMAKURA Yousuke <kumatch@users.sourceforge.net>
 * @copyright  2006-2007 KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    Release: @package_version@
 * @since      Class available since Release 0.9.0
 */
class AuthenticationAction extends Piece_Unity_Service_FlowAction
{

    // {{{ properties

    /**#@+
     * @access public
     */

    /**#@-*/

    /**#@+
     * @access private
     */

    var $_user;

    /**#@-*/

    /**#@+
     * @access public
     */

    function AuthenticationAction()
    {
        $this->_user = &new stdClass();
    }

    function login()
    {
        $validation = &$this->_context->getValidation();
        if ($validation->validate('Authentication', $this->_user)) {
            if ($this->_user->loginName === 'guest' && $this->_user->password === 'guest') {
                $session = &$this->_context->getSession();
                $session->setAttribute('isAuthenticated', true);

                $request = &$this->_context->getRequest();
                if ($request->hasParameter('callback')) {
                    $callback = $request->getParameter('callback');
                    if (!is_null($callback) && $callback !== '') {
                        $config = &$this->_context->getConfiguration();
                        $config->setConfiguration('View',
                                                  'forcedView',
                                                  Piece_Unity_URL::create('http://example.org' . rawurldecode(html_entity_decode($callback))));
                    }
                }

                return 'goDisplayHomeFromProcessLogin';
            } else {
                $viewElement = &$this->_context->getViewElement();
                $viewElement->setElement('message', 'Login Failed!');
                return 'goDisplayFormFromProcessLogin';
            }
        } else {
            return 'goDisplayFormFromProcessLogin';
        }
    }

    function logout()
    {
        $session = &$this->_context->getSession();
        $session->setAttribute('isAuthenticated', false);
        return 'goDisplayFinishFromProcessLogout';
    }

    function setupForm()
    {
        $flexyForm = &new Piece_Unity_Service_FlexyForm();
        $flexyForm->addForm($this->_flow->getView(), $this->_context->getScriptName());

        $results = &$this->_flow->getAttribute("__AuthenticationResults");
        if ($results) {
            foreach ($results->getFieldNames() as $field) {
                $flexyForm->setValue($field, @$this->_user->$field);
            }
        }

        $request = &$this->_context->getRequest();
        $viewElement = &$this->_context->getViewElement();
        $viewElement->setElement('callback', @$request->getParameter('callback'));

        $this->_setTitle();
    }

    function setupHome()
    {
        $viewElement = &$this->_context->getViewElement();
        $viewElement->setElement('user', $this->_user);

        $this->_setTitle();
    }

    function isAuthenticated()
    {
        $context = &Piece_Unity_Context::singleton();
        $session = &$context->getSession();
        if ($session->hasAttribute('isAuthenticated')) {
            return $session->getAttribute('isAuthenticated');
        } else {
            return false;
        }
    }

    function setupFinish()
    {
        $this->_setTitle();
    }

    /**#@-*/

    /**#@+
     * @access private
     */

    function _setTitle()
    {
        $viewElement = &$this->_context->getViewElement();
        $viewElement->setElement('title', 'B.1. An authentication service. *exclusive*');
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
