<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * PHP version 5
 *
 * Copyright (c) 2007-2009 KUBO Atsuhiro <kubo@iteman.jp>,
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
 * @copyright  2007-2009 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @since      File available since Release 0.11.0
 */

// {{{ Piece_Unity_Plugin_Configurator_Validation

/**
 * A configurator for validation stuff.
 *
 * @package    Piece_Unity
 * @copyright  2007-2009 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @since      Class available since Release 0.11.0
 */
class Piece_Unity_Plugin_Configurator_Validation implements Piece_Unity_Plugin_Configurator_Interface
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

    /**#@-*/

    /**#@+
     * @access public
     */

    // }}}
    // {{{ configure()

    /**
     * Configures the runtime.
     */
    public function configure()
    {
        $validation = $this->context->getValidation();
        $validation->setConfigDirectory($this->configDirectory);
        $validation->setCacheDirectory($this->cacheDirectory);
        $validation->setTemplate($this->template);
        $validation->setUseUnderscoreAsDirectorySeparator($this->useUnderscoreAsDirectorySeparator);

        $this->_setValidatorDirectories();
        $this->_setFilterDirectories();
        $this->_setValidatorPrefixes();
        $this->_setFilterPrefixes();
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
    // {{{ _setValidatorDirectories()

    /**
     * Sets validator directories.
     *
     * @throws Piece_Unity_Exception
     */
    private function _setValidatorDirectories()
    {
        foreach (array_reverse($this->validatorDirectories) as $validatorDirectory) {
            Piece_Unity_Validation::addValidatorDirectory($validatorDirectory);
        }
    }

    // }}}
    // {{{ _setFilterDirectories()

    /**
     * Sets filter directories.
     *
     * @throws Piece_Unity_Exception
     */
    private function _setFilterDirectories()
    {
        foreach (array_reverse($this->filterDirectories) as $filterDirectory) {
            Piece_Unity_Validation::addFilterDirectory($filterDirectory);
        }
    }

    // }}}
    // {{{ _setValidatorPrefixes()

    /**
     * Sets validator prefixes.
     *
     * @throws Piece_Unity_Exception
     */
    private function _setValidatorPrefixes()
    {
        foreach (array_reverse($this->validatorPrefixes) as $validatorPrefix) {
            Piece_Unity_Validation::addValidatorPrefix($validatorPrefix);
        }
    }

    // }}}
    // {{{ _setFilterPrefixes()

    /**
     * Sets filter prefixes.
     *
     * @throws Piece_Unity_Exception
     */
    private function _setFilterPrefixes()
    {
        foreach (array_reverse($this->filterPrefixes) as $filterPrefix) {
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
