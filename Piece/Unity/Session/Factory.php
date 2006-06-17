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
 * @link       http://iteman.typepad.jp/piece/
 */

require_once 'Piece/Unity/Error.php';

// {{{ GLOBALS

$GLOBALS['PIECE_UNITY_Session_Mode'] = 'PHP';

// }}}
// {{{ Piece_Unity_Session_Factory

/**
 * A factory class for creating Piece_Unity_Session objects.
 *
 * @package    Piece_Unity
 * @author     KUBO Atsuhiro <iteman2002@yahoo.co.jp>
 * @copyright  2006 KUBO Atsuhiro <iteman2002@yahoo.co.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    Release: @package_version@
 * @link       http://iteman.typepad.jp/piece/
 */
class Piece_Unity_Session_Factory
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
     * @static
     */

    // }}}
    // {{{ factory()

    /**
     * Creates a Piece_Unity_Session object with an appropriate strategy.
     *
     * @return mixed
     * @throws PEAR_ErrorStack
     */
    function &factory()
    {
        $class = "Piece_Unity_Session_{$GLOBALS['PIECE_UNITY_Session_Mode']}";
        if (!class_exists($class)) {
            $file = str_replace('_', DIRECTORY_SEPARATOR, $class) . '.php';
            if (!@include_once $file) {
                return Piece_Unity_Error::raiseError(PIECE_UNITY_ERROR_NOT_FOUND,
                                                     "The mode file [ $file ] not found or was not readable."
                                                     );
            }

            if (!class_exists($class)) {
                $error = &Piece_Unity_Error::raiseError(PIECE_UNITY_ERROR_INVALID_DRIVER,
                                                        "The mode [ $class ] not defined in the file [ $file ]."
                                                       );
                return $error;
            }
        }

        $session = &new $class();
        return $session;
    }

    // }}}
    // {{{ setMode()

    /**
     * Sets a mode for handling session state.
     *
     * @param string $mode
     */
    function setMode($mode)
    {
        $GLOBALS['PIECE_UNITY_Session_Mode'] = $mode;
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
