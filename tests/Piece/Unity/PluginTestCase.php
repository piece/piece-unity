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
 * @see        Piece_Unity
 * @since      File available since Release 0.1.0
 */

require_once 'Piece/Unity/Plugin.php';

require_once 'PHPUnit.php';
require_once 'Piece/Unity/Plugin/Common.php';
require_once 'Piece/Unity/Context.php';
require_once 'Piece/Unity/Config.php';

// {{{ Piece_Unity_PluginTestCase

/**
 * TestCase for Piece_Unity_Plugin
 *
 * @package    Piece_Unity
 * @author     KUBO Atsuhiro <iteman2002@yahoo.co.jp>
 * @copyright  2006 KUBO Atsuhiro <iteman2002@yahoo.co.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    Release: @package_version@
 * @link       http://iteman.typepad.jp/piece/
 * @see        Piece_Unity
 * @since      Class available since Release 0.1.0
 */
class Piece_Unity_PluginTestCase extends PHPUnit_TestCase
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

    function testInvocation()
    {
        $GLOBALS['fooinvokeCalled'] = 0;
        $GLOBALS['barinvokeCalled'] = 0;
        $GLOBALS['bazinvokeCalled'] = 0;
        $GLOBALS['quxinvokeCalled'] = 0;
        $config = &new Piece_Unity_Config();
        $config->setExtension('Foo', 'bar', 'Bar');
        $config->setExtension('Bar', 'baz', 'Baz');
        $config->setExtension('Bar', 'bar', 'Qux');
        $context = &Piece_Unity_Context::singleton();
        $context->setConfiguration($config);
        Piece_Unity_Plugin::invoke('Foo');

        $this->assertEquals(1, $GLOBALS['fooinvokeCalled']);
        $this->assertEquals(1, $GLOBALS['barinvokeCalled']);
        $this->assertEquals(1, $GLOBALS['bazinvokeCalled']);
        $this->assertEquals(1, $GLOBALS['quxinvokeCalled']);
    }

    /**#@-*/

    /**#@+
     * @access private
     */

    /**#@-*/

    // }}}
}

// }}}

class Foo extends Piece_Unity_Plugin_Common
{
    function Foo()
    {
        parent::Piece_Unity_Plugin_Common();
        $this->_addExtensionPoint('bar');
    }

    function invoke()
    {
        ++$GLOBALS[strtolower(__CLASS__) . strtolower(__FUNCTION__) . 'Called'];
        $bar = &$this->getExtension('bar');
        $bar->invoke();
    }
}

class Bar extends Piece_Unity_Plugin_Common
{
    function Bar()
    {
        parent::Piece_Unity_Plugin_Common();
        $this->_addExtensionPoint('bar');
        $this->_addExtensionPoint('baz');
    }

    function invoke()
    {
        ++$GLOBALS[strtolower(__CLASS__) . strtolower(__FUNCTION__) . 'Called'];
        $bar = &$this->getExtension('bar');
        $bar->invoke();
        $baz = &$this->getExtension('baz');
        $baz->invoke();
    }
}

class Baz extends Piece_Unity_Plugin_Common
{
    function invoke()
    {
        ++$GLOBALS[strtolower(__CLASS__) . strtolower(__FUNCTION__) . 'Called'];
    }
}

class Qux extends Piece_Unity_Plugin_Common
{
    function invoke()
    {
        ++$GLOBALS[strtolower(__CLASS__) . strtolower(__FUNCTION__) . 'Called'];
    }
}

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
