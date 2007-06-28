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
 * @subpackage Piece_Unity_Plugin_Renderer_Flexy
 * @copyright  2006-2007 KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    SVN: $Id$
 * @link       http://pear.php.net/package/HTML_Template_Flexy/
 * @since      File available since Release 0.13.0
 */

require_once 'Piece/Unity/Context.php';

// {{{ Piece_Unity_Service_FlexyElement

/**
 * A helper class which make it easy to build HTML_Template_Flexy elements
 * such as HTML forms and dynamic elements.
 *
 * @package    Piece_Unity
 * @subpackage Piece_Unity_Plugin_Renderer_Flexy
 * @copyright  2006-2007 KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    Release: @package_version@
 * @link       http://pear.php.net/package/HTML_Template_Flexy/
 * @since      Class available since Release 0.13.0
 */
class Piece_Unity_Service_FlexyElement
{

    // {{{ properties

    /**#@+
     * @access public
     */

    /**#@-*/

    /**#@+
     * @access private
     */

    var $_viewElement;

    /**#@-*/

    /**#@+
     * @access public
     */

    // }}}
    // {{{ constructor

    /**
     * Sets the Piece_Unity_ViewElement object.
     */
    function Piece_Unity_Service_FlexyElement()
    {
        $context = &Piece_Unity_Context::singleton();
        $this->_viewElement = &$context->getViewElement();
    }

    // }}}
    // {{{ addForm()

    /**
     * Adds a form element.
     *
     * @param string  $name
     * @param string  $action
     * @param boolean $usePost
     * @param boolean $upload
     */
    function addForm($name, $action, $usePost = true, $upload = false)
    {
        if ($upload) {
            $usePost = true;
        }

        $elements = $this->_getElements();
        $elements[$name]['_attributes']['action'] = $action;
        $elements[$name]['_attributes']['method'] = $usePost ? 'post' : 'get';
        $elements[$name]['_attributes']['enctype'] = !$upload ? 'application/x-www-form-urlencoded' : 'multipart/form-data';

        $this->_viewElement->setElement('_elements', $elements);
    }

    // }}}
    // {{{ setValue()

    /**
     * Sets a value to a given field.
     *
     * @param string $field
     * @param mixed  $value
     */
    function setValue($field, $value)
    {
        $elements = $this->_getElements();
        if (!is_array($value)) {
            $elements[$field]['_value'] = $value;
        } else {
            $elements["{$field}[]"]['_value'] = $value;
        }

        $this->_viewElement->setElement('_elements', $elements);
    }

    // }}}
    // {{{ setOptions()

    /**
     * Sets an options array for a HTML select field.
     *
     * @param string $field
     * @param array  $options
     */
    function setOptions($field, $options)
    {
        $elements = $this->_getElements();
        $elements[$field]['_options'] = $options;

        $this->_viewElement->setElement('_elements', $elements);
    }

    // }}}
    // {{{ setAttirubutes()

    /**
     * Sets an attributes array to a given field.
     *
     * @param string $field
     * @param array  $attributes
     */
    function setAttributes($field, $attributes)
    {
        $elements = $this->_getElements();
        $elements[$field]['_attributes'] = $attributes;

        $this->_viewElement->setElement('_elements', $elements);
    }

    /**#@-*/

    /**#@+
     * @access private
     */

    // }}}
    // {{{ _getElements()

    /**
     * Gets all form elements.
     *
     * @return array
     */
    function _getElements()
    {
        if (!$this->_viewElement->hasElement('_elements')) {
            $elements = array();
        } else {
            $elements = $this->_viewElement->getElement('_elements');
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
