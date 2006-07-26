<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * PHP versions 4 and 5
 *
 * Copyright (c) 2006, KUBO Atsuhiro <iteman@users.sourceforge.net>
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
 * @link       http://iteman.typepad.jp/piece/
 * @since      File available since Release 0.6.0
 */

require_once 'Piece/Unity/Plugin/Common.php';
require_once 'Piece/Right.php';
require_once 'Piece/Right/Validator/Factory.php';
require_once 'Piece/Unity/Error.php';
require_once 'Piece/Right/Filter/Factory.php';

// {{{ Piece_Unity_Plugin_Interceptor_PieceRight

/**
 * An interceptor to set a Piece_Right object to the current application
 * context.
 *
 * @package    Piece_Unity
 * @author     KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @copyright  2006 KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    Release: @package_version@
 * @link       http://iteman.typepad.jp/piece/
 * @since      Class available since Release 0.6.0
 */
class Piece_Unity_Plugin_Interceptor_PieceRight extends Piece_Unity_Plugin_Common
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
    // {{{ constructor

    /**
     * Defines extension points and configuration points for the plugin.
     */
    function Piece_Unity_Plugin_Interceptor_PieceRight()
    {
        parent::Piece_Unity_Plugin_Common();
        $this->_addConfigurationPoint('configDirectory');
        $this->_addConfigurationPoint('cacheDirectory');
        $this->_addConfigurationPoint('validatorDirectories', array());
        $this->_addConfigurationPoint('filterDirectories', array());
    }

    // }}}
    // {{{ invoke()

    /**
     * Invokes the plugin specific code.
     *
     * @return boolean
     */
    function invoke()
    {
        $validatorDirectories = $this->getConfiguration('validatorDirectories');
        if (is_array($validatorDirectories)) {
            foreach (array_reverse($validatorDirectories) as $validatorDirectory) {
                Piece_Right_Validator_Factory::addValidatorDirectory($validatorDirectory);
            }
        } else {
            Piece_Unity_Error::pushCallback(create_function('$error', 'return ' . PEAR_ERRORSTACK_PUSHANDLOG . ';'));
            Piece_Unity_Error::push(PIECE_UNITY_ERROR_INVALID_CONFIGURATION,
                                    'Failed to configure the configuration point [ validatorDirectories ] at the plugin [ ' . __CLASS__ . ' ].',
                                    'warning',
                                    array('plugin' => __CLASS__)
                                    );
            Piece_Unity_Error::popCallback();
        }

        $filterDirectories = $this->getConfiguration('filterDirectories');
        if (is_array($filterDirectories)) {
            foreach (array_reverse($filterDirectories) as $filterDirectory) {
                Piece_Right_Filter_Factory::addFilterDirectory($filterDirectory);
            }
        } else {
            Piece_Unity_Error::pushCallback(create_function('$error', 'return ' . PEAR_ERRORSTACK_PUSHANDLOG . ';'));
            Piece_Unity_Error::push(PIECE_UNITY_ERROR_INVALID_CONFIGURATION,
                                    'Failed to configure the configuration point [ filterDirectories ] at the plugin [ ' . __CLASS__ . ' ].',
                                    'warning',
                                    array('plugin' => __CLASS__)
                                    );
            Piece_Unity_Error::popCallback();
        }

        $right = &new Piece_Right($this->getConfiguration('configDirectory'),
                                  $this->getConfiguration('cacheDirectory'),
                                  array(&$this, 'getFieldValueFromContext')
                                  );
        $this->_context->setAttributeByRef('_pieceRight', $right);

        return true;
    }

    // }}}
    // {{{ getFieldValueFromContext()

    /**
     * Gets the value of the given field name from the current application
     * context.This method is used as a callback for Piece_Right package.
     *
     * @param string $fieldName
     * @return mixed
     */
    function getFieldValueFromContext($fieldName)
    {
        $request = &$this->_context->getRequest();

        return @$request->getParameter($fieldName);
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
