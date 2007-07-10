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
 * @see        HTML_Template_Flexy
 * @since      File available since Release 0.2.0
 */

require_once 'HTML/Template/Flexy.php';
require_once 'HTML/Template/Flexy/Element.php';
require_once 'PEAR.php';
require_once 'Piece/Unity/Plugin/Renderer/HTML.php';
require_once 'Piece/Unity/Error.php';

// {{{ Piece_Unity_Plugin_Renderer_Flexy

/**
 * A renderer which is based on HTML_Template_Flexy template engine.
 *
 * @package    Piece_Unity
 * @subpackage Piece_Unity_Plugin_Renderer_Flexy
 * @copyright  2006-2007 KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    Release: @package_version@
 * @link       http://pear.php.net/package/HTML_Template_Flexy/
 * @see        HTML_Template_Flexy
 * @since      Class available since Release 0.2.0
 */
class Piece_Unity_Plugin_Renderer_Flexy extends Piece_Unity_Plugin_Renderer_HTML
{

    // {{{ properties

    /**#@+
     * @access public
     */

    /**#@-*/

    /**#@+
     * @access private
     */

    var $_configurationOptions = array('templateDir' => null,
                                       'compileDir'  => null,
                                       'debug'       => 0,
                                       'plugins'     => array()
                                       );

    /**#@-*/

    /**#@+
     * @access public
     */

    /**#@-*/

    /**#@+
     * @access private
     */

    // }}}
    // {{{ _createFormElements()

    /**
     * Creates form elements which are passed to
     * HTML_Template_Flexy::outputObject() method from the view elements.
     *
     * @param array $elements
     * @return array
     */
    function _createFormElements($elements)
    {
        $formElements = array();
        foreach ($elements as $name => $type) {
            $formElements[$name] = &new HTML_Template_Flexy_Element();
            if (!is_array($type)) {
                continue;
            }

            if (array_key_exists('_value', $type)) {
                $formElements[$name]->setValue($type['_value']);
            }

            if (array_key_exists('_options', $type)
                && is_array($type['_options'])
                ) {
                $formElements[$name]->setOptions($type['_options']);
            }

            if (array_key_exists('_attributes', $type)
                && is_array($type['_attributes'])
                ) {
                $formElements[$name]->setAttributes($type['_attributes']);
            }
        }

        return $formElements;
    }

    // }}}
    // {{{ _getOptions()

    /**
     * Gets an array which contains configuration options for
     * a HTML_Template_Flexy object.
     *
     * @return array
     */
    function _getOptions()
    {
        $options = array('fatalError'      => HTML_TEMPLATE_FLEXY_ERROR_RETURN,
                         'privates'        => true,
                         'globals'         => true,
                         'globalfunctions' => true
                         );

        foreach (array_keys($this->_configurationOptions) as $point) {
            $$point = $this->_getConfiguration($point);
            if (!is_null($$point)) {
                $options[$point] = $$point;
            }
        }

        $externalPlugins = $this->_getConfiguration('externalPlugins');
        if (is_array($externalPlugins) && count(array_keys($externalPlugins))) {
            $options['plugins'] = array_merge($options['plugins'], $externalPlugins);
        }

        return $options;
    }

    // }}}
    // {{{ _initialize()

    /**
     * Defines and initializes extension points and configuration points.
     *
     * @since Method available since Release 0.6.0
     */
    function _initialize()
    {
        parent::_initialize();
        $this->_addConfigurationPoint('templateExtension', '.html');
        $this->_addConfigurationPoint('useController', false);
        $this->_addConfigurationPoint('controllerClass');
        $this->_addConfigurationPoint('controllerDirectory');
        $this->_addConfigurationPoint('externalPlugins', array());
        foreach ($this->_configurationOptions as $point => $default) {
            $this->_addConfigurationPoint($point, $default);
        }
    }

    // }}}
    // {{{ _doRender()

    /**
     * Renders a HTML.
     *
     * @param boolean $isLayout
     * @throws PIECE_UNITY_ERROR_INVOCATION_FAILED
     * @throws PIECE_UNITY_ERROR_INVALID_CONFIGURATION
     * @throws PIECE_UNITY_ERROR_NOT_READABLE
     * @throws PIECE_UNITY_ERROR_NOT_FOUND
     * @throws PIECE_UNITY_ERROR_CANNOT_READ
     */
    function _doRender($isLayout)
    {
        $options = $this->_getOptions();
        if (!$isLayout) {
            $view = $this->_context->getView();
        } else {
            $options['templateDir'] = $this->_getConfiguration('layoutDirectory');
            $options['compileDir'] = $this->_getConfiguration('layoutCompileDirectory');
            $view = $this->_getConfiguration('layoutView');
        }

        $flexy = &new HTML_Template_Flexy($options);
        $file = str_replace('_', '/', str_replace('.', '', $view)) . $this->_getConfiguration('templateExtension');
        $resultOfCompile = $flexy->compile($file);
        if (PEAR::isError($resultOfCompile)) {
            if ($flexy->currentTemplate === false) {
                Piece_Unity_Error::pushPEARError($resultOfCompile,
                                                 'PIECE_UNITY_PLUGIN_RENDERER_HTML_ERROR_NOT_FOUND',
                                                 "The HTML template file [ $file ] not found."
                                                 );
                return;
            }

            Piece_Unity_Error::pushPEARError($resultOfCompile,
                                             PIECE_UNITY_ERROR_INVOCATION_FAILED,
                                             "Failed to invoke the plugin [ {$this->_name} ]."
                                             );
            return;
        }

        $viewElement = &$this->_context->getViewElement();
        $viewElements = $viewElement->getElements();

        $formElements = array();
        if (array_key_exists('_elements', $viewElements)) {
            $formElements = $this->_createFormElements($viewElements['_elements']);
            unset($viewElements['_elements']);
        }
        
        if (!$this->_getConfiguration('useController')) {
            $controller = (object)$viewElements;
        } else {
            $controller = &$this->_createController($viewElements);
            if (Piece_Unity_Error::hasErrors('exception')) {
                return;
            }
        }

        $resultOfOutputObject = $flexy->outputObject($controller, $formElements);
        if (PEAR::isError($resultOfOutputObject)) {
            Piece_Unity_Error::pushPEARError($resultOfOutputObject,
                                             PIECE_UNITY_ERROR_INVOCATION_FAILED,
                                             "Failed to invoke the plugin [ {$this->_name} ]."
                                             );
        }
    }

    // }}}
    // {{{ _prepareFallback()

    /**
     * Prepares another view as a fallback.
     */
    function _prepareFallback()
    {
        $config = &$this->_context->getConfiguration();
        $config->setConfiguration('Renderer_Flexy', 'templateDir', $this->_getConfiguration('fallbackDirectory'));
        $config->setConfiguration('Renderer_Flexy', 'compileDir', $this->_getConfiguration('fallbackCompileDirectory'));
    }

    // }}}
    // {{{ _createController()

    /**
     * Creates a user-defined object with the given view elements. The object
     * is used as a page controller by HTML_Template_Flexy::outputObject().
     *
     * @param array $viewElements
     * @return mixed
     * @throws PIECE_UNITY_ERROR_INVALID_CONFIGURATION
     * @throws PIECE_UNITY_ERROR_NOT_READABLE
     * @throws PIECE_UNITY_ERROR_NOT_FOUND
     * @throws PIECE_UNITY_ERROR_CANNOT_READ
     */
    function &_createController($viewElements)
    {
        $controllerDirectory = $this->_getConfiguration('controllerDirectory');
        if (is_null($controllerDirectory)) {
            Piece_Unity_Error::push(PIECE_UNITY_ERROR_INVALID_CONFIGURATION,
                                    "The value of the configuration point [ controllerDirectory ] on the plug-in [ {$this->_name} ] is required."
                                    );
            $return = null;
            return $return;
        }

        $controllerClass = $this->_getConfiguration('controllerClass');
        if (is_null($controllerClass)) {
            Piece_Unity_Error::push(PIECE_UNITY_ERROR_INVALID_CONFIGURATION,
                                    "The value of the configuration point [ controllerClass ] on the plug-in [ {$this->_name} ] is required."
                                    );
            $return = null;
            return $return;
        }

        if (!Piece_Unity_ClassLoader::loaded($controllerClass)) {
            Piece_Unity_ClassLoader::load($controllerClass, $controllerDirectory);
            if (Piece_Unity_Error::hasErrors('exception')) {
                $return = null;
                return $return;
            }

            if (!Piece_Unity_ClassLoader::loaded($controllerClass)) {
                Piece_Unity_Error::push(PIECE_UNITY_ERROR_NOT_FOUND,
                                        "The class [ $controllerClass ] not found in the loaded file."
                                        );
                $return = null;
                return $return;
            }
        }

        $controller = &new $controllerClass();
        foreach (array_keys($viewElements) as $element) {
            if (!is_object($viewElements[$element])) {
                $controller->$element = $viewElements[$element];
            } else {
                $controller->$element = &$viewElements[$element];
            }
        }

        return $controller;
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
