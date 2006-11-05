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
 * @since      File available since Release 0.1.0
 */

require_once 'PEAR/ErrorStack.php';

// {{{ constants

/*
 * Error codes
 */
define('PIECE_UNITY_ERROR_NOT_FOUND',             -1);
define('PIECE_UNITY_ERROR_NOT_READABLE',          -2);
define('PIECE_UNITY_ERROR_CANNOT_READ',           -3);
define('PIECE_UNITY_ERROR_CANNOT_WRITE',          -4);
define('PIECE_UNITY_ERROR_INVALID_PLUGIN',        -5);
define('PIECE_UNITY_ERROR_INVALID_CONFIGURATION', -6);
define('PIECE_UNITY_ERROR_INVOCATION_FAILED',     -7);
define('PIECE_UNITY_ERROR_PHP_ERROR',             -8);
define('PIECE_UNITY_ERROR_UNEXPECTED_VALUE',      -9);

// }}}
// {{{ Piece_Unity_Error

/**
 * An error class for Piece_Unity package.
 *
 * @package    Piece_Unity
 * @author     KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @copyright  2006 KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    Release: @package_version@
 * @link       http://piece-framework.com/piece-unity/
 * @since      Class available since Release 0.1.0
 */
class Piece_Unity_Error
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
    // {{{ push()

    /**
     * Adds an error to the stack for the package. This method is a wrapper
     * for PEAR_ErrorStack::staticPush() method.
     *
     * @param integer $code
     * @param string  $message
     * @param string  $level
     * @param array   $params
     * @param array   $repackage
     * @param array   $backtrace
     * @see PEAR_ErrorStack::staticPush()
     */
    function push($code, $message = false, $level = 'exception',
                  $params = array(), $repackage = false, $backtrace = false
                  )
    {
        if (!$backtrace) {
            $backtrace = debug_backtrace();
        }

        PEAR_ErrorStack::staticPush('Piece_Unity', $code, $level, $params, $message, $repackage, $backtrace);
    }

    // }}}
    // {{{ pushCallback()

    /**
     * Pushes a callback. This method is a wrapper for
     * PEAR_ErrorStack::staticPushCallback() method.
     *
     * @param callback $callback
     * @see PEAR_ErrorStack::staticPushCallback()
     */
    function pushCallback($callback)
    {
        PEAR_ErrorStack::staticPushCallback($callback);
    }

    // }}}
    // {{{ popCallback()

    /**
     * Pops a callback. This method is a wrapper for
     * PEAR_ErrorStack::staticPopCallback() method.
     *
     * @return callback
     * @see PEAR_ErrorStack::staticPopCallback()
     */
    function popCallback()
    {
        return PEAR_ErrorStack::staticPopCallback();
    }

    // }}}
    // {{{ hasErrors()

    /**
     * Returns whether the stack has errors or not. This method is a wrapper
     * for PEAR_ErrorStack::staticHasErrors() method.
     *
     * @param string $level
     * @return boolean
     * @see PEAR_ErrorStack::staticHasErrors()
     */
    function hasErrors($level = false)
    {
        return PEAR_ErrorStack::staticHasErrors('Piece_Unity', $level);
    }

    // }}}
    // {{{ pop()

    /**
     * Pops an error off of the error stack for the package. This method is a
     * wrapper for PEAR_ErrorStack::pop() method.
     *
     * @return array
     * @see PEAR_ErrorStack::pop()
     */
    function pop()
    {
        $stack = &PEAR_ErrorStack::singleton('Piece_Unity');
        return $stack->pop();
    }

    // }}}
    // {{{ clearErrors()

    /**
     * Clears the error stack for the package.
     *
     * @see PEAR_ErrorStack::getErrors()
     */
    function clearErrors()
    {
        $stack = &PEAR_ErrorStack::singleton('Piece_Unity');
        $stack->getErrors(true);
    }

    // }}}
    // {{{ pushPEARError()

    /**
     * Adds a PEAR error to the stack for the package.
     *
     * @param PEAR_Error $error
     * @param integer    $code
     * @param string     $message
     * @param string     $level
     * @param array      $params
     * @param array      $backtrace
     * @see Piece_Unity_Error::push()
     */
    function pushPEARError($error, $code, $message = false,
                           $level = 'exception', $params = array(),
                           $backtrace = false
                           )
    {
        $time = explode(' ', microtime());
        $time = $time[1] + $time[0];

        if (!$backtrace) {
            $backtrace = debug_backtrace();
        }

        if ($level == 'exception') {
            $context = $error->getBacktrace();
        } else {
            $context = array();
        }

        Piece_Unity_Error::push($code, $message, $level, $params,
                                array('code' => $error->getCode(),
                                      'message' => $error->getMessage(),
                                      'params' => array(),
                                      'package' => 'PEAR',
                                      'level' => $level,
                                      'time' => $time,
                                      'context' => $context),
                                $backtrace
                                );
    }

    // }}}
    // {{{ pushPHPError()

    /**
     *
     * @param integer $code
     * @param string  $message
     * @param string  $file
     * @param integer $line
     * @throws PIECE_UNITY_ERROR_PHP_ERROR
     * @see Piece_Unity_Error::push()
     */
    function pushPHPError($code, $message, $file, $line)
    {
        $errorReporting = error_reporting();
        if (!($errorReporting & $code)) {
            return;
        }

        switch ($code) {
        case E_STRICT:
            return;
        case E_WARNING:
        case E_USER_WARNING:
            $level = 'warning';
            break;
        case E_NOTICE:
        case E_USER_NOTICE:
            $level = 'info';
            break;
        case E_USER_ERROR:
        default:
            $level = 'exception';
            break;
        }

        $time = explode(' ', microtime());
        $time = $time[1] + $time[0];
        Piece_Unity_Error::push(PIECE_UNITY_ERROR_PHP_ERROR,
                                'A PHP error raised.',
                                $level,
                                array(),
                                array('code' => $code,
                                      'message' => $message,
                                      'params' => array(),
                                      'package' => 'PHP',
                                      'level' => $level,
                                      'time' => $time,
                                      'context' => array('file' => $file,
                                                         'line' => $line)),
                                debug_backtrace()
                                );
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
