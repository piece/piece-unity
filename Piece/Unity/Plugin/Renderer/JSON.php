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
 * @subpackage Piece_Unity_Plugin_Renderer_JSON
 * @copyright  2006-2007 KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    SVN: $Id$
 * @link       http://pecl.php.net/package/json
 * @link       http://pear.php.net/package/HTML_AJAX
 * @see        HTML_AJAX_JSON
 * @since      File available since Release 0.9.0
 */

require_once 'Piece/Unity/Plugin/Common.php';
require_once 'Piece/Unity/Error.php';
require_once 'PEAR.php';

// {{{ Piece_Unity_Plugin_Renderer_JSON

/**
 * A renderer to output view elements as JSON.
 *
 * @package    Piece_Unity
 * @subpackage Piece_Unity_Plugin_Renderer_JSON
 * @copyright  2006-2007 KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    Release: @package_version@
 * @link       http://pecl.php.net/package/json
 * @link       http://pear.php.net/package/HTML_AJAX
 * @see        HTML_AJAX_JSON
 * @since      Class available since Release 0.9.0
 */
class Piece_Unity_Plugin_Renderer_JSON extends Piece_Unity_Plugin_Common
{

    // {{{ properties

    /**#@+
     * @access public
     */

    /**#@-*/

    /**#@+
     * @access private
     */

    /**
     * @var boolean the flag to use HTML_AJAX instead of json extension.
     *              this property is only for testing.
     */
    var $_useHTMLAJAX = false;
    var $_encoderCallback;
    var $_contentType;

    /**#@-*/

    /**#@+
     * @access public
     */

    // }}}
    // {{{ invoke()

    /**
     * Invokes the plugin specific code.
     *
     * @throws PIECE_UNITY_ERROR_INVOCATION_FAILED
     * @throws PIECE_UNITY_ERROR_UNEXPECTED_VALUE
     */
    function invoke()
    {
        $data = $this->_createData();

        /*
         * walk the data recusively to determine whether
         * it contains any circular references.
         */
        $visited = array();
        $this->_visit($data, $visited);
        if (Piece_Unity_Error::hasErrors('exception')) {
            return;
        }

        // finally encode the data as JSON.
        $json = call_user_func($this->_encoderCallback, $data);
        if (Piece_Unity_Error::hasErrors('exception')) {
            return;
        }

        if ($this->_getConfiguration('useJSONP')) {
            $callbackKey = $this->_getConfiguration('callbackKey');
            if (!is_null($callbackKey)) {
                $request = &$this->_context->getRequest();
                if ($request->hasParameter($callbackKey)) {
                    $json = $request->getParameter($callbackKey) . "($json);";
                }
            }
        }

        if (!is_null($this->_contentType) && strlen($this->_contentType)) {
            @header("Content-Type: {$this->_contentType}");
        }

        echo $json;
    }

    // }}}
    // {{{ encodeWithHTMLAJAX()

    /**
     * Encode a given value with PEAR::HTML_AJAX.
     *
     * @param mixed $value
     * @return string JSON string representation of a given value
     *                or false if error ocurrs.
     * @throws PIECE_UNITY_ERROR_INVOCATION_FAILED
     * @static
     */
    function encodeWithHTMLAJAX($value)
    {
        $encoder = &new HTML_AJAX_JSON();
        PEAR::staticPushErrorHandling(PEAR_ERROR_RETURN);
        $json = $encoder->encode($value);
        PEAR::staticPopErrorHandling();
        if (HTML_AJAX_JSON::isError($json)) {
            Piece_Unity_Error::pushPEARError($json,
                                             PIECE_UNITY_ERROR_INVOCATION_FAILED,
                                             'Failed to invoke the plugin [ ' . __CLASS__ . ' ].',
                                             'exception',
                                             array('plugin' => __CLASS__)
                                             );
            return;
        }

        return $json;
    }

    /**#@-*/

    /**#@+
     * @access private
     */

    // }}}
    // {{{ _visit()

    /**
     * Traverse the given value recursively.
     *
     * @param mixed &$value   the value to be visited.
     * @param array &$visited the array of objects which have been visited.
     * @return boolean
     * @throws PIECE_UNITY_ERROR_UNEXPECTED_VALUE
     */
    function _visit(&$value, &$visited)
    {
        if (is_array($value)) {
            $this->_visitArray($value, $visited);
        } elseif (is_object($value)) {
            $this->_visitObject($value, $visited);
        }
    }

    // }}}
    // {{{ _visit()

    /**
     * Visit an array.
     *
     * @param mixed &$value   the value to be visited.
     * @param array &$visited the array of objects which have been visited.
     * @return boolean
     * @throws PIECE_UNITY_ERROR_UNEXPECTED_VALUE
     */
    function _visitArray(&$value, &$visited)
    {
        foreach (array_keys($value) as $key) {
            $next = &$value[$key];

            if ($this->_wasVisited($next, $visited)) {
                Piece_Unity_Error::push(PIECE_UNITY_ERROR_UNEXPECTED_VALUE,
                                        "A circular refrence detected in an array at the key {$key}.",
                                        'exception',
                                        array('plugin' => __CLASS__)
                                        );
                return;
            }

            $this->_visit($next, $visited);
            if (Piece_Unity_Error::hasErrors('exception')) {
                return;
            }
        }
    }

    // }}}
    // {{{ _visitObject()

    /**
     * Visit an object.
     *
     * @param mixed &$value   the value to be visited.
     * @param array &$visited the array of objects which have been visited.
     * @return boolean
     * @throws PIECE_UNITY_ERROR_UNEXPECTED_VALUE
     */
    function _visitObject(&$value, &$visited)
    {
        $keys = array_keys(get_object_vars($value));
        foreach ($keys as $key) {
            $next = &$value->$key;

            if ($this->_wasVisited($next, $visited)) {
                $class = get_class($value);
                Piece_Unity_Error::push(PIECE_UNITY_ERROR_UNEXPECTED_VALUE,
                                        "A circular refrence detected at the property {$key}, class {$class}.",
                                        'exception',
                                        array('plugin' => __CLASS__)
                                        );
                return;
            }

            $this->_visit($next, $visited);
            if (Piece_Unity_Error::hasErrors('exception')) {
                return;
            }
        }
    }

    // }}}
    // {{{ _wasVisited()

    /**
     * Check whether a given object was visited.
     *
     * @param mixed &$value
     * @param array &$visited
     * @return boolean
     */
    function _wasVisited(&$value, &$visited)
    {
        static $php5;

        if (is_null($php5)) {
            $php5 = version_compare(phpversion(), '5.0.0', '>=');
        }

        if (!($isObj = is_object($value)) && !is_array($value)) {
            return false;
        }

        $result = false;
        if ($php5 && $isObj) {
            $result = in_array($value, $visited, true);
        } else {
            for ($i = 0, $count = count($visited); $i < $count; ++$i) {
                $sentinel = &$visited[$i];
                if ($this->_isReference($value, $sentinel)) {
                    $result = true;
                    break;
                }
            }
        }

        $visited[] = &$value;

        return $result;
    }

    // }}}
    // {{{ _isReference()

    /**
     * Check if the given variables references the same object.
     * Note that this function is not necessary for PHP5 objects
     * since the operator === does the same job much better.
     *
     * @param mixed &$x the some reference
     * @param mixed &$y the antoher reference to be compared
     * @return boolean
     * @static
     */
    function _isReference(&$x, &$y)
    {
        $tmp = $x;
        $x = uniqid(__CLASS__);
        $isref = ($x === $y);
        $x = $tmp;
        return $isref;
    }

    // }}}
    // {{{ _initialize()

    /**
     * Defines and initializes extension points and configuration points.
     *
     * @throws PIECE_UNITY_ERROR_NOT_FOUND
     */
    function _initialize()
    {
        $this->_addConfigurationPoint('contentType', 'text/javascript');
        $this->_addConfigurationPoint('useJSONP', false);
        $this->_addConfigurationPoint('callbackKey');
        $this->_addConfigurationPoint('include',
                                      array('__eventNameKey',
                                            '__scriptName',
                                            '__basePath',
                                            '__flowExecutionTicketKey',
                                            '__flowNameKey',
                                            '__flowExecutionTicket')
                                     );
        $this->_addConfigurationPoint('exclude', array());
        $this->_addConfigurationPoint('useHTMLAJAX', false);

        $this->_setEncoderCallback();
        $this->_contentType = $this->_getConfiguration('contentType');
    }

    // }}}
    // {{{ _setEncoderCallback()

    /**
     * Sets a callback as a JSON encoder.
     *
     * @throws PIECE_UNITY_ERROR_NOT_FOUND
     */
    function _setEncoderCallback()
    {
        if (!$this->_getConfiguration('useHTMLAJAX')) {
            if (!extension_loaded('json')) {
                Piece_Unity_Error::push(PIECE_UNITY_ERROR_NOT_FOUND,
                                        'json extension not loaded. Please check PHP configuration.',
                                        'exception',
                                        array('plugin' => __CLASS__)
                                        );
                return;
            }

            $this->_encoderCallback = 'json_encode';
        } else {
            if (!include_once 'HTML/AJAX/JSON.php') {
                Piece_Unity_Error::push(PIECE_UNITY_ERROR_NOT_FOUND,
                                        'The file [ HTML/AJAX/JSON.php ] not found or is not readable.',
                                        'exception',
                                        array('plugin' => __CLASS__)
                                        );
                return;
            }

            $this->_encoderCallback = array(__CLASS__, 'encodeWithHTMLAJAX');
        }
    }

    // }}}
    // {{{ _createData()

    /**
     * Creates data to be encoded as JSON.
     *
     * @return string
     */
    function _createData()
    {
        $include = $this->_getConfiguration('include');
        if (!is_array($include)) {
            $include = array();
        }

        $exclude = $this->_getConfiguration('exclude');
        if (!is_array($exclude)) {
            $exclude = array();
        }

        $viewElement = &$this->_context->getViewElement();
        $viewElements = $viewElement->getElements();

        $data = array();
        foreach (array_keys($viewElements) as $key) {
            if (in_array($key, $exclude)) {
                // discard an element which is listed explicitly.
                continue;
            }

            if (in_array($key, $include) || substr($key, 0, 1) != '_') {
                /*
                 * accept an element which is listed explicitly
                 * or whose first letter is not underscore.
                 */
                $data[$key] = $viewElements[$key];
            }
        }

        return $data;
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
