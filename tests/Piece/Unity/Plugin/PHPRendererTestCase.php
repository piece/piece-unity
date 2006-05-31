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
 * @see        Piece_Unity_Plugin_PHPRenderer
 * @since      File available since Release 0.1.0
 */

require_once 'Piece/Unity/Plugin/PHPRenderer.php';

require_once 'Piece/Unity/Request.php';
require_once 'Piece/Unity/Config.php';

// {{{ Piece_Unity_Plugin_PHPRendererTestCase

/**
 * TestCase for Piece_Unity_Plugin_PHPRenderer
 *
 * @package    Piece_Unity
 * @author     KUBO Atsuhiro <iteman2002@yahoo.co.jp>
 * @copyright  2006 KUBO Atsuhiro <iteman2002@yahoo.co.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    Release: @package_version@
 * @link       http://iteman.typepad.jp/piece/
 * @see        Piece_Unity_Plugin_PHPRenderer
 * @since      Class available since Release 0.1.0
 */
class Piece_Unity_Plugin_PHPRendererTestCase extends PHPUnit_TestCase
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

    function tearDown()
    {
        $context = &Piece_Unity_Context::singleton();
        $context->clear();
    }

    function testRendering()
    {
        $_GET['event'] = 'Example';

        $this->assertEquals("This is a test.\n", $this->_render());
    }

    function testRelativePathVulnerability()
    {
        $_GET['event'] = '../RelativePathVulnerability';

        $this->assertEquals('', $this->_render());
    }

    /**#@-*/

    /**#@+
     * @access private
     */

    function _render()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $request = &new Piece_Unity_Request();
        $context = &Piece_Unity_Context::singleton();
        $context->setRequest($request);
        $config = &new Piece_Unity_Config();
        $config->setConfiguration('Piece_Unity_Plugin_Dispatcher_Simple', 'actionPath', dirname(__FILE__));
        $config->setConfiguration('Piece_Unity_Plugin_PHPRenderer', 'templatePath', dirname(__FILE__));
        $context->setConfiguration($config);
        $dispatcher = &new Piece_Unity_Plugin_Dispatcher_Simple();
        $context->setView($dispatcher->invoke());
        $renderer = &new Piece_Unity_Plugin_PHPRenderer();

        ob_start();
        $renderer->invoke();
        $buffer = ob_get_contents();
        ob_end_clean();

        return $buffer;
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
?>
