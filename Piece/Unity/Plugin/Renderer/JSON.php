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
 * @author     Chihiro Sakatoku <csakatoku@users.sourceforge.net>
 * @copyright  2006 KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    SVN: $Id$
 * @link       http://piece-framework.com/piece-unity/
 * @link       http://pear.php.net/package/HTML_AJAX
 * @see        HTML_AJAX_JSON
 * @since      File available since Release 0.9.0
 */

require_once 'Piece/Unity/Plugin/Common.php';

// {{{ Piece_Unity_Plugin_Renderer_JSON

/**
 * A renderer .
 *
 * @package    Piece_Unity
 * @author     Chihiro Sakatoku <csakatoku@users.sourceforge.net>
 * @copyright  2006 KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    Release: @package_version@
 * @link       http://piece-framework.com/piece-unity/
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
     * @var boolean the flag to use iconv instead of mbstring.
     *              this property is only for testing.
     */
    var $_useIconv = false;

    /**
     * @var boolean the flag to use HTML_AJAX instead of php-json.
     *              this property is only for testing.
     */
    var $_useHTMLAJAX = false;

    /**
     * @var string the HTTP response header sent in
     *             the rendering process.
     *             this property is only for testing.
     */
    var $_header;

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
        if (!$this->_useHTMLAJAX
            && function_exists('json_encode')) {
            $jsonEncoder = 'json_encode';
        } else {
            if (!include_once 'HTML/AJAX/JSON.php') {
                // no json encoder is available. sorry.
                $this->_sendInternalServerError();
                return;
            }
            $jsonEncoder = array(__CLASS__, '_encodeWithHTMLAJAX');
        }

        $viewElement = &$this->_context->getViewElement();
        $viewElements = $viewElement->getElements();

        $include = $this->getConfiguration('include');
        if (!is_array($include)) {
            $include = array();
        }

        $exclude = $this->getConfiguration('exclude');
        if (!is_array($exclude)) {
            $exclude = array();
        }

        // data to be encoded as json.
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

        /*
         * This callback function is called
         * when a walker find a string while walking the data recursively.
         * Note that if mbstring functions is available,
         * we call mb_convert_variable function later in the process.
         */
        $encodingCallback = null;
        if ($this->_useIconv
            && function_exists('iconv')) {
            $encoding = $this->getConfiguration('internalEncoding');
            if (!is_string($encoding)) {
                $encoding = iconv_get_encoding('internal_encoding');
            }
            $encodingCallback = create_function('$v', "return iconv('{$encoding}', 'UTF-8//IGNORE', \$v);");
        }

        /*
         * walk the data recusively to determine whether
         * it contains any circular references.
         */
        $visited = array();
        if (!$this->_visit($data, $visited, $encodingCallback)) {
            // circular refereces found.
            $this->_sendInternalServerError();
            return;
        }

        // convert the charset to UTF-8 if we have not done the task yet.
        if (is_null($encodingCallback)
            && function_exists('mb_convert_variables')) {
            $encoding = $this->getConfiguration('internalEncoding');
            if (!is_string($encoding)) {
                $encoding = mb_internal_encoding();
            }
            mb_convert_variables('UTF-8', $encoding, $data);
        }

        // finally encode the data as json.
        $json = call_user_func($jsonEncoder, $data);
        if ($json === false) {
            $this->_sendInternalServerError();
            return;
        }

        if ($this->getConfiguration('useJSONP')) {
            $callback = $this->getConfiguration('callbackField');
            if (isset($_GET[$callback])) {
                $json = "{$_GET[$callback]}({$json});";
            }
        }

        $contentType = $this->getConfiguration('contentType');
        if (!is_null($contentType)) {
            /*
             * this looks redundant, but for the testing sake,
             * do not modify it.
             */
            $this->_header = $contentType;
            @header($this->_header);
        }

        echo $json;
    }

    /**#@-*/

    /**#@+
     * @access private
     */

    // }}}
    // {{{ _sendInternalServerError()

    /**
     * Send the response code '500 Internal Server Error'.
     */
    function _sendInternalServerError()
    {
        $this->_header = 'HTTP/1.0 500 Internal Server Error';
        @header($this->_header);
    }

    // }}}
    // {{{ _encodeWithHTMLAJAX()

    /**
     * Encode a given value with PEAR::HTML_AJAX.
     *
     * @param mixed $value
     * @return string json string representation of a given value
     *                or false if error ocurrs.
     * @static
     */
    function _encodeWithHTMLAJAX($value)
    {
        $encoder = &new HTML_AJAX_JSON();
        $json = $encoder->encode($value);
        if (HTML_AJAX_JSON::isError($json)) {
            return false;
        }
        return $json;
    }

    // }}}
    // {{{ _visit()

    /**
     * Traverse the given value recursively.
     *
     * @param mixed $value            the value to be visited.
     * @param array $visited          the array of objects which have been visited.
     * @param mixed $encodingCallback the callback fundtion to convert the
     *                                charset.
     * @return boolean
     */
    function _visit(&$value, &$visited, $encodingCallback = null)
    {
        if (is_array($value)) {
            if (!$this->_visitArray($value, $visited, $encodingCallback)) {
                return false;
            }
        } elseif (is_object($value)) {
            if (!$this->_visitObject($value, $visited, $encodingCallback)) {
                return false;
            }
        }

        if (is_string($value) && !is_null($encodingCallback)) {
            $value = call_user_func($encodingCallback, $value);
        }

        return true;
    }

    // }}}
    // {{{ _visit()

    /**
     * Visit an array.
     *
     * @param mixed $value            the value to be visited.
     * @param array $visited          the array of objects which have been visited.
     * @param mixed $encodingCallback the callback fundtion to convert the
     *                                charset.
     * @return boolean
     */
    function _visitArray(&$value, &$visited, $encodingCallback = null)
    {
        foreach (array_keys($value) as $key) {
            $next = &$value[$key];

            if ($this->_wasVisited($next, $visited)) {
                Piece_Unity_Error::pushCallback(create_function('$error', 'return ' . PEAR_ERRORSTACK_PUSHANDLOG . ';'));
                Piece_Unity_Error::push(PIECE_UNITY_ERROR_UNEXPECTED_VALUE,
                                        "a circular refrence detected in an array at the key {$key}",
                                        'warning'
                                        );
                Piece_Unity_Error::popCallback();
                return false;
            }

            if (!$this->_visit($next, $visited, $encodingCallback)) {
                return false;
            }
        }

        return true;
    }

    // }}}
    // {{{ _visitObject()

    /**
     * Visit an object.
     *
     * @param mixed $value            the value to be visited.
     * @param array $visited          the array of objects which have been visited.
     * @param mixed $encodingCallback the callback fundtion to convert the
     *                                charset.
     * @return boolean
     */
    function _visitObject(&$value, &$visited, $encodingCallback = null)
    {
        $keys = array_keys(get_object_vars($value));
        foreach ($keys as $key) {
            $next = &$value->$key;

            if ($this->_wasVisited($next, $visited)) {
                $class = get_class($value);
                Piece_Unity_Error::pushCallback(create_function('$error', 'return ' . PEAR_ERRORSTACK_PUSHANDLOG . ';'));
                Piece_Unity_Error::push(PIECE_UNITY_ERROR_UNEXPECTED_VALUE,
                                        "a circular refrence detected at the property {$key}, class {$class}.",
                                        'warning'
                                        );
                Piece_Unity_Error::popCallback();
                return false;
            }

            if (!$this->_visit($next, $visited, $encodingCallback)) {
                return false;
            }
        }

        return true;
    }

    // }}}
    // {{{ _wasVisited()

    /**
     * Check whether a given object was visited.
     *
     * @param mixed $value
     * @param array $visited
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
            for ($i = 0; $i < count($visited); ++$i) {
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
     * @param mixed $x the some reference
     * @param mixed $y the antoher reference to be compared
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
     */
    function _initialize()
    {
        $this->_addConfigurationPoint('contentType', 'text/javascript');
        $this->_addConfigurationPoint('internalEncoding');
        $this->_addConfigurationPoint('useJSONP', false);
        $this->_addConfigurationPoint('callbackField');
        $this->_addConfigurationPoint('include',
                                      array('__eventNameKey',
                                            '__scriptName',
                                            '__basePath',
                                            '__flowExecutionTicketKey',
                                            '__flowNameKey',
                                            '__flowExecutionTicket')
                                     );
        $this->_addConfigurationPoint('exclude', array());
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
