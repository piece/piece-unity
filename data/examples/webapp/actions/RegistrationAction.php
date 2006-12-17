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
 * @since      File available since Release 0.9.0
 */

require_once 'Piece/Flow/Action.php';

// {{{ RegistrationAction

/**
 * Action class for the flow Registration.
 *
 * @package    Piece_Unity
 * @author     KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @copyright  2006 KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    Release: @package_version@
 * @link       http://piece-framework.com/piece-unity/
 * @since      Class available since Release 0.9.0
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

    var $_user;
    var $_flowName;
    var $_useAHAH = false;
    var $_renderedLayout = false;

    /**#@-*/

    /**#@+
     * @access public
     */

    function RegistrationAction()
    {
        $this->_user = &new stdClass();
    }

    function validate()
    {
        $validation = &$this->_payload->getValidation();
        if ($validation->validate('Registration', $this->_user)) {
            return 'goDisplayConfirmationFromProcessConfirmForm';
        } else {
            return 'goDisplayFormFromProcessConfirmForm';
        }
    }

    function register()
    {
        return 'goDisplayFinishFromProcessRegister';
    }

    function setupForm()
    {
        $this->_setupFormAttributes();

        $fields = $this->_getFormFields();
        $elements = $this->_getFormElements();
        foreach ($fields as $field) {
            $elements[$field]['_value'] = @$this->_user->$field;
        }

        $viewElement = &$this->_payload->getViewElement();
        $viewElement->setElement('_elements', $elements);
        $viewElement->setElement('useAHAH', $this->_useAHAH);

        $this->_configureLayout();
        $this->_setTitle();
    }

    function setupConfirmation()
    {
        $this->_setupFormAttributes();

        $viewElement = &$this->_payload->getViewElement();
        $viewElement->setElementByRef('user', $this->_user);
        $viewElement->setElement('useAHAH', $this->_useAHAH);

        $this->_configureLayout();
        $this->_setTitle();
    }

    function setupFinish()
    {
        $viewElement = &$this->_payload->getViewElement();
        $viewElement->setElement('useAHAH', $this->_useAHAH);

        $this->_configureLayout();
        $this->_setTitle();
    }

    function prepare()
    {
        $continuation = &$this->_payload->getContinuation();
        $this->_flowName = $continuation->getCurrentFlowName();
        if ($this->_flowName == 'RegistrationWithExclusiveModeAndAHAH') {
            $this->_useAHAH = true;
        }
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

    function _setTitle()
    {
        if ($this->_flowName == 'RegistrationWithNonExclusiveMode') {
            $title = 'A.1. Registration Application with Non-Exclusive Mode.';
        } elseif ($this->_flowName == 'RegistrationWithExclusiveMode') {
            $title = 'A.2. Registration Application with Exclusive Mode.';
        } elseif ($this->_flowName == 'RegistrationWithExclusiveModeAndAHAH') {
            $title = 'A.3. Registration Application with Exclusive Mode and AHAH.';
        }

        $viewElement = &$this->_payload->getViewElement();
        $viewElement->setElement('title', $title);
    }

    function _configureLayout()
    {
        $request = &$this->_payload->getRequest();
        if ($request->hasParameter('useLayout')) {
            if (!$request->getParameter('useLayout')) {
                $config = &$this->_payload->getConfiguration();
                $config->setConfiguration('Renderer_Flexy', 'useLayout', false);
            }

            $this->_renderedLayout = true;
            return;
        }

        if ($this->_useAHAH) {
            if ($this->_renderedLayout) {
                $config = &$this->_payload->getConfiguration();
                $config->setConfiguration('Renderer_Flexy', 'useLayout', false);
            } else {
                $this->_renderedLayout = true;
            }
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
?>
