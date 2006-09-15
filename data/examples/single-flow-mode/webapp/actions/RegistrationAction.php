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
 * @since      File available since Release 0.2.0
 */

require_once 'Piece/Flow/Action.php';

// {{{ RegistrationAction

/**
 * Action class for the flow 'RegistrationAction'.
 *
 * @package    Piece_Unity
 * @author     KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @copyright  2006 KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    Release: @package_version@
 * @link       http://piece-framework.com/piece-unity/
 * @since      Class available since Release 0.2.0
 */
class RegistrationAction extends Piece_Flow_Action
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

    function validate()
    {
        $right = &$this->_payload->getAttribute('_pieceRight');
        $right->validate('Registration');
        $results = &$right->getResults();

        if (!$results->countErrors()) {
            $user = &new stdClass();
            foreach ($results->getFieldNames() as $field) {
                $user->$field = $results->getFieldValue($field);
            }
            $this->_flow->setAttributeByRef('user', $user);

            return 'goDisplayConfirmation';
        } else {
            $request = &$this->_payload->getRequest();
            $user = &new stdClass();
            foreach ($results->getFieldNames() as $field) {
                $user->$field = @$request->getParameter($field);
            }
            $this->_flow->setAttributeByRef('user', $user);

            $viewElement = &$this->_payload->getViewElement();
            $viewElement->setElement('_results', $results);

            return 'goDisplayForm';
        }
    }

    function register()
    {
        $this->_flow->clearAttributes();
        return 'goDisplayFinish';
    }

    function setupForm()
    {
        $this->_setupFormAttributes();

        $viewElement = &$this->_payload->getViewElement();

        if ($this->_flow->hasAttribute('user')) {
            $user = &$this->_flow->getAttribute('user');
            $fields = $this->_getFormFields();
            $elements = $this->_getFormElements();
            foreach ($fields as $field) {
                $elements[$field]['_value'] = $user->$field;
            }

            $viewElement->setElement('_elements', $elements);
        }
    }

    function setupConfirmation()
    {
        $this->_setupFormAttributes();

        $user = &$this->_flow->getAttribute('user');
        $viewElement = &$this->_payload->getViewElement();
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

    function _setupFormAttributes()
    {
        $view = $this->_flow->getView();
        $elements = $this->_getFormElements();
        $elements[$view]['_attributes']['action'] = $this->_payload->getScriptName();
        $elements[$view]['_attributes']['method'] = 'post';
        $viewElement = &$this->_payload->getViewElement();
        $viewElement->setElement('_elements', $elements);
    }

    function _getFormElements()
    {
        $viewElement = &$this->_payload->getViewElement();
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
