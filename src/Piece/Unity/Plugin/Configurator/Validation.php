<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * PHP versions 4 and 5
 *
 * Copyright (c) 2007-2008 KUBO Atsuhiro <iteman@users.sourceforge.net>,
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
 * @copyright  2007-2008 KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    SVN: $Id$
 * @since      File available since Release 0.11.0
 */

require_once 'Piece/Unity/Plugin/Common.php';
require_once 'Piece/Unity/Validation.php';
require_once 'Piece/Unity/Error.php';

// {{{ Piece_Unity_Plugin_Configurator_Validation

/**
 * A configurator for validation stuff.
 *
 * @package    Piece_Unity
 * @copyright  2007-2008 KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    Release: @package_version@
 * @since      Class available since Release 0.11.0
 */
class Piece_Unity_Plugin_Configurator_Validation extends Piece_Unity_Plugin_Common
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

    // }}}
    // {{{ invoke()

    /**
     * Invokes the plugin specific code.
     */
    function invoke()
    {
        $validation = &$this->_context->getValidation();
        $validation->setConfigDirectory($this->_getConfiguration('configDirectory'));
        $validation->setCacheDirectory($this->_getConfiguration('cacheDirectory'));
        $validation->setTemplate($this->_getConfiguration('template'));
        $validation->setUseUnderscoreAsDirectorySeparator($this->_getConfiguration('useUnderscoreAsDirectorySeparator'));

        $this->_setValidatorDirectories();
        if (Piece_Unity_Error::hasErrors()) {
            return;
        }

        $this->_setFilterDirectories();
        if (Piece_Unity_Error::hasErrors()) {
            return;
        }

        $this->_setValidatorPrefixes();
        if (Piece_Unity_Error::hasErrors()) {
            return;
        }

        $this->_setFilterPrefixes();
    }

    /**#@-*/

    /**#@+
     * @access private
     */

    // }}}
    // {{{ _initialize()

    /**
     * Defines and initializes extension points and configuration points.
     */
    function _initialize()
    {
        $this->_addConfigurationPoint('configDirectory');
        $this->_addConfigurationPoint('cacheDirectory');
        $this->_addConfigurationPoint('validatorDirectories', array());
        $this->_addConfigurationPoint('filterDirectories', array());
        $this->_addConfigurationPoint('validatorPrefixes', array());
        $this->_addConfigurationPoint('filterPrefixes', array());
        $this->_addConfigurationPoint('template');
        $this->_addConfigurationPoint('useUnderscoreAsDirectorySeparator', false);
    }

    // }}}
    // {{{ _setValidatorDirectories()

    /**
     * Sets validator directories.
     *
     * @throws PIECE_UNITY_ERROR_INVALID_CONFIGURATION
     */
    function _setValidatorDirectories()
    {
        $validatorDirectories = $this->_getConfiguration('validatorDirectories');
        if (!is_array($validatorDirectories)) {
            Piece_Unity_Error::push(PIECE_UNITY_ERROR_INVALID_CONFIGURATION,
                                    "The value of the configuration point [ validatorDirectories ] on the plug-in [ {$this->_name} ] should be an array."
                                    );
            return;
        }

        foreach (array_reverse($validatorDirectories) as $validatorDirectory) {
            Piece_Unity_Validation::addValidatorDirectory($validatorDirectory);
        }
    }

    // }}}
    // {{{ _setFilterDirectories()

    /**
     * Sets filter directories.
     *
     * @throws PIECE_UNITY_ERROR_INVALID_CONFIGURATION
     */
    function _setFilterDirectories()
    {
        $filterDirectories = $this->_getConfiguration('filterDirectories');
        if (!is_array($filterDirectories)) {
            Piece_Unity_Error::push(PIECE_UNITY_ERROR_INVALID_CONFIGURATION,
                                    "The value of the configuration point [ filterDirectories ] on the plug-in [ {$this->_name} ] should be an array."
                                    );
            return;
        }

        foreach (array_reverse($filterDirectories) as $filterDirectory) {
            Piece_Unity_Validation::addFilterDirectory($filterDirectory);
        }
    }

    // }}}
    // {{{ _setValidatorPrefixes()

    /**
     * Sets validator prefixes.
     *
     * @throws PIECE_UNITY_ERROR_INVALID_CONFIGURATION
     */
    function _setValidatorPrefixes()
    {
        $validatorPrefixes = $this->_getConfiguration('validatorPrefixes');
        if (!is_array($validatorPrefixes)) {
            Piece_Unity_Error::push(PIECE_UNITY_ERROR_INVALID_CONFIGURATION,
                                    "The value of the configuration point [ validatorPrefixes ] on the plug-in [ {$this->_name} ] should be an array."
                                    );
            return;
        }

        foreach (array_reverse($validatorPrefixes) as $validatorPrefix) {
            Piece_Unity_Validation::addValidatorPrefix($validatorPrefix);
        }
    }

    // }}}
    // {{{ _setFilterPrefixes()

    /**
     * Sets filter prefixes.
     *
     * @throws PIECE_UNITY_ERROR_INVALID_CONFIGURATION
     */
    function _setFilterPrefixes()
    {
        $filterPrefixes = $this->_getConfiguration('filterPrefixes');
        if (!is_array($filterPrefixes)) {
            Piece_Unity_Error::push(PIECE_UNITY_ERROR_INVALID_CONFIGURATION,
                                    "The value of the configuration point [ filterPrefixes ] on the plug-in [ {$this->_name} ] should be an array."
                                    );
            return;
        }

        foreach (array_reverse($filterPrefixes) as $filterPrefix) {
            Piece_Unity_Validation::addFilterPrefix($filterPrefix);
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
