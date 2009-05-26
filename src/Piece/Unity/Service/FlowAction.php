<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * PHP version 5
 *
 * Copyright (c) 2007, 2009 KUBO Atsuhiro <kubo@iteman.jp>,
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
 * @copyright  2007, 2009 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    GIT: $Id$
 * @see        Piece_Flow_Action
 * @since      File available since Release 1.0.0
 */

// {{{ Piece_Unity_Service_FlowAction

/**
 * The base class for Piece_Flow actions.
 *
 * @package    Piece_Unity
 * @copyright  2007, 2009 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    Release: @package_version@
 * @see        Piece_Flow_Action
 * @since      Class available since Release 1.0.0
 */
abstract class Piece_Unity_Service_FlowAction
{

    // {{{ properties

    /**#@+
     * @access public
     */

    /**#@-*/

    /**#@+
     * @access protected
     */

    protected $flow;
    protected $context;
    protected $event;

    /**#@-*/

    /**#@+
     * @access private
     */

    /**#@-*/

    /**#@+
     * @access public
     */

    // }}}
    // {{{ setFlow()

    /**
     * Sets the Piece_Flow object which is used by the flow execution
     * in progress.
     *
     * @param Piece_Flow $flow
     */
    public function setFlow(Piece_Flow $flow)
    {
        $this->flow = $flow;
    }

    // }}}
    // {{{ setPayload()

    /**
     * Sets a single instance of Piece_Unity_Context class.
     *
     * @param Piece_Unity_Context $context
     */
    public function setPayload(Piece_Unity_Context $context)
    {
        $this->context = $context;
    }

    // }}}
    // {{{ setEvent()

    /**
     * Sets the current event name.
     *
     * @param string $event
     */
    public function setEvent($event)
    {
        $this->event = $event;
    }

    // }}}
    // {{{ prepare()

    /**
     * Prepares something for an event handler which will be invoked just
     * after this method call.
     */
    public function prepare() {}

    // }}}
    // {{{ clear()

    /**
     * Clears all properties for the next use.
     *
     * @since Method available since Release 1.1.0
     */
    public function clear()
    {
        unset($this->flow);
        unset($this->context);
        $this->event = null;
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
