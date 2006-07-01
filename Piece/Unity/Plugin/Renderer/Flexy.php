<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * PHP versions 4 and 5
 *
 * Copyright (c) 2006, KUBO Atsuhiro <iteman2002@yahoo.co.jp>
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
 * @author     KUBO Atsuhiro <iteman2002@yahoo.co.jp>
 * @copyright  2006 KUBO Atsuhiro <iteman2002@yahoo.co.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    SVN: $Id$
 * @link       http://pear.php.net/package/HTML_Template_Flexy/
 * @link       http://iteman.typepad.jp/piece/
 * @see        HTML_Template_Flexy
 * @since      File available since Release 0.2.0
 */

require_once 'Piece/Unity/Plugin/Common.php';
require_once 'HTML/Template/Flexy.php';
require_once 'HTML/Template/Flexy/Element.php';
require_once 'PEAR.php';

// {{{ Piece_Unity_Plugin_Renderer_Flexy

/**
 * @package    Piece_Unity
 * @author     KUBO Atsuhiro <iteman2002@yahoo.co.jp>
 * @copyright  2006 KUBO Atsuhiro <iteman2002@yahoo.co.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    Release: @package_version@
 * @link       http://pear.php.net/package/HTML_Template_Flexy/
 * @link       http://iteman.typepad.jp/piece/
 * @see        HTML_Template_Flexy
 * @since      Class available since Release 0.2.0
 */
class Piece_Unity_Plugin_Renderer_Flexy extends Piece_Unity_Plugin_Common
{

    // {{{ properties

    /**#@+
     * @access public
     */

    /**#@-*/

    /**#@+
     * @access private
     */

    var $_configurationOptions = array('templateDir',
                                       'compileDir'
                                       );

    /**#@-*/

    /**#@+
     * @access public
     */

    // }}}
    // {{{ constructor

    /**
     * Defines extension points and configuration points for the plugin.
     */
    function Piece_Unity_Plugin_Renderer_Flexy()
    {
        parent::Piece_Unity_Plugin_Common();
        $this->_addConfigurationPoint('templateExtension', '.html');
        foreach ($this->_configurationOptions as $point) {
            $this->_addConfigurationPoint($point, null);
        }
    }

    // }}}
    // {{{ invoke()

    /**
     * Invokes the plugin specific code.
     */
    function invoke()
    {
        $options = array('fatalError'      => HTML_TEMPLATE_FLEXY_ERROR_RETURN,
                         'privates'        => true,
                         'globals'         => true,
                         'globalfunctions' => true
                         );

        foreach ($this->_configurationOptions as $point) {
            $$point = $this->getConfiguration($point);
            if (!is_null($$point)) {
                $options[$point] = $$point;
            }
        }

        $flexy = &new HTML_Template_Flexy($options);
        $resultOfCompile = $flexy->compile(str_replace('_', '/', str_replace('.', '', $this->_context->getView())) . $this->getConfiguration('templateExtension'));
        if (PEAR::isError($resultOfCompile)) {
            return;
        }

        $viewElement = &$this->_context->getViewElement();
        $viewElements = $viewElement->getElements();
        $controller = (object)$viewElements;

        $automaticFormElements = array();
        if (array_key_exists('_elements', $viewElements)) {
            foreach ($viewElements['_elements'] as $name => $type) {
                $automaticFormElements[$name] = &new HTML_Template_Flexy_Element();
                if (is_array($type)) {
                    if (array_key_exists('_attributes', $type) && is_array($type['_attributes'])) {
                        $automaticFormElements[$name]->setAttributes($type['_attributes']);
                    }

                    if (array_key_exists('_value', $type)) {
                        $automaticFormElements[$name]->setValue($type['_value']);
                    }

                    if (array_key_exists('_options', $type) && is_array($type['_options'])) {
                        $automaticFormElements[$name]->setOptions($type['_options']);
                    }
                }
            }
        }

        $resultOfOutputObject = $flexy->outputObject($controller, $automaticFormElements);
        if (PEAR::isError($resultOfOutputObject)) {
            return;
        }
    }

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
?>
