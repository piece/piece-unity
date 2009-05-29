<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * PHP version 5
 *
 * Copyright (c) 2006-2009 KUBO Atsuhiro <kubo@iteman.jp>,
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
 * @copyright  2006-2009 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @see        Piece_Right, Piece_Right_Config, Piece_Right_Results
 * @since      File available since Release 0.7.0
 */

// {{{ Piece_Unity_Validation

/**
 * The validation class for Piece_Unity applications.
 *
 * @package    Piece_Unity
 * @copyright  2006-2009 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
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
     * @access protected
     */

    /**#@-*/

    /**#@+
     * @access private
     */

    private $_configDirectory;
    private $_cacheDirectory;
    private $_results;
    private $_config;
    private $_template;

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
    public function setConfigDirectory($configDirectory)
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
    public function setCacheDirectory($cacheDirectory)
    {
        $this->_cacheDirectory = $cacheDirectory;
    }

    // }}}
    // {{{ validate()

    /**
     * Validates the current field values with the given validation set and
     * configuration.
     *
     * @param string  $validationSetName
     * @param mixed   $container
     * @param boolean $keepOriginalFieldValue
     * @return boolean
     */
    public function validate($validationSetName,
                             $container,
                             $keepOriginalFieldValue = true
                             )
    {
        $script = new Piece_Right_Validation_Script($this->_configDirectory,
                                                    $this->_cacheDirectory,
                                                    array(__CLASS__, 'getFieldValueFromContext'),
                                                    array(__CLASS__, 'setResultsAsViewElementAndFlowAttribute')
                                                    );
        $script->setPayload(Piece_Unity_Context::singleton());
        $script->setTemplate($this->_template);
        $this->_results = $script->run($validationSetName,
                                       $container,
                                       $this->_config,
                                       $keepOriginalFieldValue
                                       );

        return !$this->_results->countErrors();
    }

    // }}}
    // {{{ getFieldValueFromContext()

    /**
     * Gets the value of the given field name from the current application
     * context. This method is used as a callback for Piece_Right package.
     *
     * @param string $fieldName
     * @return mixed
     */
    public static function getFieldValueFromContext($fieldName)
    {
        return @Piece_Unity_Context::singleton()->getRequest()
                                                ->getParameter($fieldName);
    }

    // }}}
    // {{{ getConfiguration()

    /**
     * Gets the Piece_Right_Config object for the current validation.
     *
     * @return Piece_Right_Config
     */
    public function getConfiguration()
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
    public function clear()
    {
        $this->_results = null;
        $this->_config = null;
    }

    // }}}
    // {{{ getResults()

    /**
     * Gets the Piece_Right_Results object of the given validation set or
     * the latest validation.
     *
     * @param string $validationSetName
     * @return Piece_Right_Results
     */
    public function getResults($validationSetName = null)
    {
        $name = Piece_Unity_Validation::_createResultsName($validationSetName);

        $context = Piece_Unity_Context::singleton();
        $continuation = $context->getContinuation();
        if (!is_null($continuation)) {
            if ($continuation->hasAttribute($name)) {
                return $continuation->getAttribute($name);
            }
        } else {
            $viewElement = $context->getViewElement();
            if ($viewElement->hasElement($name)) {
                return $viewElement->getElement($name);
            }
        }

        return $this->_results;
    }

    // }}}
    // {{{ setResultsAsViewElementAndFlowAttribute()

    /**
     * Sets a Piece_Right_Result object as a view element and a flow
     * attribute.
     *
     * @param string              $validationSetName
     * @param Piece_Right_Results $results
     */
    public static function setResultsAsViewElementAndFlowAttribute($validationSetName, $results)
    {
        $context = Piece_Unity_Context::singleton();
        $context->getViewElement()->setElement(Piece_Unity_Validation::_createResultsName($validationSetName), $results);

        $continuation = $context->getContinuation();
        if (!is_null($continuation)) {
            $continuation->setAttribute(Piece_Unity_Validation::_createResultsName($validationSetName), $results);
        }
    }

    // }}}
    // {{{ addValidatorDirectory()

    /**
     * Adds a validator directory.
     *
     * @param array $directory
     * @since Method available since Release 0.8.0
     */
    public static function addValidatorDirectory($directory)
    {
        Piece_Right_Validator_Factory::addValidatorDirectory($directory);
    }

    // }}}
    // {{{ addFilterDirectory()

    /**
     * Adds a filter directory.
     *
     * @param array $directory
     * @since Method available since Release 0.8.0
     */
    public static function addFilterDirectory($directory)
    {
        Piece_Right_Filter_Factory::addFilterDirectory($directory);
    }

    // }}}
    // {{{ addValidatorPrefix()

    /**
     * Adds a prefix for a validator.
     *
     * @param string $validatorPrefix
     */
    public static function addValidatorPrefix($validatorPrefix)
    {
        Piece_Right_Validator_Factory::addValidatorPrefix($validatorPrefix);
    }

    // }}}
    // {{{ addFilterPrefix()

    /**
     * Adds a prefix for a filter.
     *
     * @param string $filterPrefix
     */
    public static function addFilterPrefix($filterPrefix)
    {
        Piece_Right_Filter_Factory::addFilterPrefix($filterPrefix);
    }

    // }}}
    // {{{ hasResults()

    /**
     * Returns whether or not the Piece_Right_Results object of the given
     * validation set or the latest validation exists.
     *
     * @param string $validationSetName
     * @return boolean
     */
    public function hasResults($validationSetName = null)
    {
        return (boolean)$this->getResults($validationSetName);
    }

    // }}}
    // {{{ getFieldNames()

    /**
     * Gets all field names corresponding to the given validation set and
     * a Piece_Right_Config object.
     *
     * @param string $validationSetName
     * @return array
     */
    public function getFieldNames($validationSetName)
    {
        $script = new Piece_Right_Validation_Script($this->_configDirectory,
                                                    $this->_cacheDirectory,
                                                    array(__CLASS__, 'getFieldValueFromContext'),
                                                    array(__CLASS__, 'setResultsAsViewElementAndFlowAttribute')
                                                    );
        return $script->getFieldNames($validationSetName, $this->_config);
    }

    // }}}
    // {{{ mergeValidationSet()

    /**
     * Merges the given validation set into the Piece_Right_Config object for
     * the current validation.
     *
     * @param string $validationSetName
     * @param string $configDirectory
     * @param string $cacheDirectory
     * @throws PIECE_UNITY_ERROR_INVOCATION_FAILED
     * @since Method available since Release 1.3.0
     */
    public function mergeValidationSet($validationSetName, $configDirectory = null, $cacheDirectory = null)
    {
        if (is_null($configDirectory)) {
            $configDirectory = $this->_configDirectory;
        }

        if (is_null($cacheDirectory)) {
            $cacheDirectory = $this->_cacheDirectory;
        }

        $config = Piece_Right_Config_Factory::factory($validationSetName,
                                                      $configDirectory,
                                                      $cacheDirectory,
                                                      $this->_template
                                                      );

        if (is_null($this->_config)) {
            $this->_config = new Piece_Right_Config();
        }

        $this->_config->merge($config);
    }

    // }}}
    // {{{ setTemplate()

    /**
     * Sets the given validation set as a template.
     *
     * @param string $template
     * @since Method available since Release 1.3.0
     */
    public function setTemplate($template)
    {
        $this->_template = $template;
    }

    // }}}
    // {{{ setUseUnderscoreAsDirectorySeparator()

    /**
     * Sets whether or not Piece_Right uses underscores in validation set
     * names as directory separators.
     *
     * @param boolean $treatUnderscoreAsDirectorySeparator
     * @since Method available since Release 1.3.0
     */
    public static function setUseUnderscoreAsDirectorySeparator($useUnderscoreAsDirectorySeparator)
    {
        Piece_Right_Config_Factory::setUseUnderscoreAsDirectorySeparator($useUnderscoreAsDirectorySeparator);
    }

    /**#@-*/

    /**#@+
     * @access protected
     */

    /**#@-*/

    /**#@+
     * @access private
     */

    // }}}
    // {{{ _createResultsName()

    /**
     * Creates a field name from the given validation set that
     * Piece_Right_Results will be stored by.
     *
     * @param string $validationSetName
     * @static
     * @since Method available since Release 1.0.0
     */
    private function _createResultsName($validationSetName)
    {
        if (!is_null($validationSetName)) {
            return "__{$validationSetName}Results";
        } else {
            return '__results';
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
