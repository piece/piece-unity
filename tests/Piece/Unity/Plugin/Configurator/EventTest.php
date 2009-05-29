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
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @since      File available since Release 0.11.0
 */

// {{{ Piece_Unity_Plugin_Configurator_EventTest

/**
 * Some tests for Piece_Unity_Plugin_Configurator_Event.
 *
 * @package    Piece_Unity
 * @copyright  2007, 2009 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @since      Class available since Release 0.11.0
 */
class Piece_Unity_Plugin_Configurator_EventTest extends Piece_Unity_PHPUnit_TestCase
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

    /**
     * @test
     */
    public function setTheEventNameKey()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_GET['_foo'] = 'bar';

        $config = new Piece_Unity_Config();
        $config->setConfiguration('Configurator_Event', 'eventNameKey', '_foo');
        $context = Piece_Unity_Context::singleton();
        $context->setConfiguration($config);

        $configurator = new Piece_Unity_Plugin_Configurator_Event();
        $configurator->configure();

        $this->assertEquals('bar', $context->getEventName());
    }

    /**
     * @test
     */
    public function setTheEventName()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_GET['_event'] = 'foo';

        $config = new Piece_Unity_Config();
        $config->setConfiguration('Configurator_Event', 'eventName', 'bar');
        $context = Piece_Unity_Context::singleton();
        $context->setConfiguration($config);

        $configurator = new Piece_Unity_Plugin_Configurator_Event();
        $configurator->configure();

        $this->assertEquals('bar', $context->getEventName());
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
