<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * PHP versions 4 and 5
 *
 * Copyright (c) 2006, KUBO Atsuhiro <iteman@users.sourceforge.net>
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
 * @link       http://iteman.typepad.jp/piece/
 * @since      File available since Release 0.2.0
 */

// {{{ RegistrationAction

/**
 * Action class for the flow 'RegistrationAction'.
 *
 * @package    Piece_Unity
 * @author     KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @copyright  2006 KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    Release: @package_version@
 * @link       http://iteman.typepad.jp/piece/
 * @since      Class available since Release 0.2.0
 */
class RegistrationAction
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

    function validate(&$flow, $event, &$context)
    {
        $fields = $this->_getFormFields();

        $request = &$context->getRequest();
        $user = &new stdClass();
        foreach ($fields as $field) {
            $user->$field = @$request->getParameter($field);
        }

        $flow->setAttributeByRef('user', $user);

        return 'goDisplayConfirmation';
    }

    function register(&$flow, $event, &$context)
    {
        $flow->clearAttributes();
        return 'goDisplayFinish';
    }

    function setupForm(&$flow, $event, &$context)
    {
        $this->_setupFormAttributes($flow, $context);

        if ($flow->hasAttribute('user')) {
            $user = &$flow->getAttribute('user');
            $fields = $this->_getFormFields();
            $elements = $this->_getFormElements($context);
            foreach ($fields as $field) {
                $elements[$field]['_value'] = $user->$field;
            }

            $viewElement = &$context->getViewElement();
            $viewElement->setElement('_elements', $elements);
        }
    }

    function setupConfirmation(&$flow, $event, &$context)
    {
        $this->_setupFormAttributes($flow, $context);

        $user = &$flow->getAttribute('user');
        $viewElement = &$context->getViewElement();
        $viewElement->setElementByRef('user', $user);
    }

    /**#@-*/

    /**#@+
     * @access private
     */

    function _getFormFields()
    {
        $fields = array('first_name', 'last_name');
        return $fields;
    }

    function _setupFormAttributes(&$flow, &$context)
    {
        $view = $flow->getView();
        $elements = $this->_getFormElements($context);
        $elements[$view]['_attributes']['action'] = $context->getBaseURL();
        $elements[$view]['_attributes']['method'] = 'post';
        $viewElement = &$context->getViewElement();
        $viewElement->setElement('_elements', $elements);
    }

    function _getFormElements(&$context)
    {
        $viewElement = &$context->getViewElement();
        if (!$viewElement->hasElement('_elements')) {
            $elements = array();
        } else {
            $elements = $viewElement->getElement('_elements');
        }

        return $elements;
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
