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
 * @see        Piece_Right, Piece_Right_Config, Piece_Right_Results
 * @since      File available since Release 0.7.0
 */

require_once 'Piece/Right/Config.php';
require_once 'Piece/Unity/Error.php';
require_once 'Piece/Right/Error.php';
require_once 'Piece/Unity/Context.php';
require_once 'Piece/Right/Validation/Script.php';
require_once 'Piece/Right/Validator/Factory.php';
require_once 'Piece/Right/Filter/Factory.php';

// {{{ Piece_Unity_Validation

/**
 * The validation class for Piece_Unity applications.
 *
 * @package    Piece_Unity
 * @author     KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @copyright  2006 KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    Release: @package_version@
 * @link       http://piece-framework.com/piece-unity/
 * @see        Piece_Right, Piece_Right_Config, Piece_Right_Results
 * @since      Class available since Release 0.7.0
 */
class Piece_Unity_Validation
{

    // {{{ properties

    /**#@+
     * @access public
     */

    /**#@-*/

    /**#@+
     * @access private
     */

    var $_configDirectory;
    var $_cacheDirectory;
    var $_results;
    var $_config;

    /**#@-*/

    /**#@+
     * @access public
     */

    // }}}
    // {{{ setConfigDirectory()

    /**
     * Sets the directory where configuration files have been placed in.
     *
     * @param string $configDirectory
     */
    function setConfigDirectory($configDirectory)
    {
        $this->_configDirectory = $configDirectory;
    }

    // }}}
    // {{{ setCacheDirectory()

    /**
     * Sets the directory where configuration files will be cached in.
     *
     * @param string $cacheDirectory
     */
    function setCacheDirectory($cacheDirectory)
    {
        $this->_cacheDirectory = $cacheDirectory;
    }

    // }}}
    // {{{ validate()

    /**
     * Validates the current field values with the given validation set and
     * configuration.
     *
     * @param string  $validationSet
     * @param mixed   &$container
     * @param boolean $keepOriginalFieldValue
     * @return boolean
     * @throws PIECE_UNITY_ERROR_INVOCATION_FAILED
     */
    function validate($validationSet,
                      &$container,
                      $keepOriginalFieldValue = true
                      )
    {
        $script = &new Piece_Right_Validation_Script($this->_configDirectory,
                                                     $this->_cacheDirectory,
                                                     array(__CLASS__, 'getFieldValueFromContext'),
                                                     array(__CLASS__, 'setResultsAsViewElementAndFlowAttribute')
                                                     );

        Piece_Unity_Error::pushCallback(create_function('$error', 'return ' . PEAR_ERRORSTACK_PUSHANDLOG . ';'));
        $this->_results = $script->run($validationSet,
                                       $container,
                                       $this->_config,
                                       $keepOriginalFieldValue
                                       );
        Piece_Unity_Error::popCallback();
        if (Piece_Right_Error::hasErrors('exception')) {
            Piece_Unity_Error::push(PIECE_UNITY_ERROR_INVOCATION_FAILED,
                                    'Failed to invoke Piece_Right_Validation_Processor::process() method for any reasons.',
                                    'exception',
                                    array(),
                                    Piece_Right_Error::pop()
                                    );
            return;
        }

        return !$this->_results->countErrors();
    }

    // }}}
    // {{{ getFieldValueFromContext()

    /**
     * Gets the value of the given field name from the current application
     * context.This method is used as a callback for Piece_Right package.
     *
     * @param string $fieldName
     * @return mixed
     * @static
     */
    function getFieldValueFromContext($fieldName)
    {
        $context = &Piece_Unity_Context::singleton();
        $request = &$context->getRequest();

        return @$request->getParameter($fieldName);
    }

    // }}}
    // {{{ getConfiguration()

    /**
     * Gets the Piece_Right_Config object for the current validation.
     *
     * @return Piece_Right_Config
     */
    function &getConfiguration()
    {
        if (is_null($this->_config)) {
            $this->_config = new Piece_Right_Config();
        }

        return $this->_config;
    }

    // }}}
    // {{{ clear()

    /**
     * Clears some properties for the next use.
     */
    function clear()
    {
        $this->_results = null;
        $this->_config = null;
    }

    // }}}
    // {{{ getResults()

    /**
     * Gets the Piece_Right_Results object of the latest validation.
     *
     * @return Piece_Right_Results
     */
    function &getResults()
    {
        return $this->_results;
    }

    // }}}
    // {{{ setResultsAsViewElementAndFlowAttribute()

    /**
     * Sets a Piece_Right_Result object as a view element and a flow
     * attribute.
     *
     * @param string              $validationSet
     * @param Piece_Right_Results $results
     * @static
     */
    function setResultsAsViewElementAndFlowAttribute($validationSet, $results)
    {
        $context = &Piece_Unity_Context::singleton();
        $viewElement = &$context->getViewElement();
        $viewElement->setElement(!is_null($validationSet) ? "__{$validationSet}Results" : '__results',
                                 $results
                                 );

        $continuation = &$context->getContinuation();
        if (!is_null($continuation)) {
            $continuation->setAttribute(!is_null($validationSet) ? "__{$validationSet}Results" : '__results',
                                        $results
                                        );
        }
    }

    // }}}
    // {{{ addValidatorDirectory()

    /**
     * Adds a validator directory.
     *
     * @param array $directory
     * @static
     * @since Method available since Release 0.8.0
     */
    function addValidatorDirectory($directory)
    {
        Piece_Right_Validator_Factory::addValidatorDirectory($directory);
    }

    // }}}
    // {{{ addFilterDirectory()

    /**
     * Adds a filter directory.
     *
     * @param array $directory
     * @static
     * @since Method available since Release 0.8.0
     */
    function addFilterDirectory($directory)
    {
        Piece_Right_Filter_Factory::addFilterDirectory($directory);
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
