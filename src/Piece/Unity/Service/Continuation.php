<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * PHP version 5
 *
 * Copyright (c) 2008-2009 KUBO Atsuhiro <kubo@iteman.jp>,
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
 * @copyright  2008-2009 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @since      File available since Release 1.5.0
 */

// {{{ Piece_Unity_Service_Continuation

/**
 * A helper class which make it easy to do continuation stuff.
 *
 * @package    Piece_Unity
 * @copyright  2008-2009 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @since      Class available since Release 1.5.0
 */
class Piece_Unity_Service_Continuation
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

    private static $_flowExecutionTicketKey;

    /**#@-*/

    /**#@+
     * @access public
     */

    // }}}
    // {{{ createURI()

    /**
     * Creates a Piece_Unity_URI object based on the active flow execution or a given
     * flow ID.
     *
     * @param string $eventName
     * @param string $flowID
     * @return Piece_Unity_URI
     */
    public static function createURI($eventName, $flowID = null)
    {
        $context = Piece_Unity_Context::singleton();
        $continuation = $context->getContinuation();
        $uri = new Piece_Unity_URI(is_null($flowID) ? $context->getScriptName()
                                                    : $flowID
                                   );
        $uri->setQueryVariable(self::$_flowExecutionTicketKey,
                               is_null($flowID) ? $continuation->getActiveFlowExecutionTicket()
                                                : $continuation->getFlowExecutionTicketByFlowID($flowID)
                               );
        $uri->setQueryVariable($context->getEventNameKey(), $eventName);

        return $uri;
    }

    // }}}
    // {{{ getFlowExecutionTicketKey()

    /**
     * Gets the key which represents the flow execution ticket parameter.
     *
     * @return string
     */
    public static function getFlowExecutionTicketKey()
    {
        return self::$_flowExecutionTicketKey;
    }

    // }}}
    // {{{ setFlowExecutionTicketKey()

    /**
     * Sets the key which represents the flow execution ticket parameter.
     *
     * @param string $flowExecutionTicketKey
     */
    public static function setFlowExecutionTicketKey($flowExecutionTicketKey)
    {
        self::$_flowExecutionTicketKey = $flowExecutionTicketKey;
    }

    /**#@-*/

    /**#@+
     * @access protected
     */

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
