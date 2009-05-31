<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * PHP version 5
 *
 * Copyright (c) 2006-2009 KUBO Atsuhiro <kubo@iteman.jp>,
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
 * @copyright  2006-2009 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @since      File available since Release 0.1.0
 */

// {{{ Piece_Unity_Plugin_Renderer_PHPTest

/**
 * Some tests for Piece_Unity_Plugin_Renderer_PHP.
 *
 * @package    Piece_Unity
 * @copyright  2006-2009 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @since      Class available since Release 0.1.0
 */
class Piece_Unity_Plugin_Renderer_PHPTest extends Piece_Unity_Plugin_Renderer_HTML_CompatibilityTests
{

    // {{{ properties

    /**#@+
     * @access public
     */

    /**#@-*/

    /**#@+
     * @access protected
     */

    protected $target = 'PHP';

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
     * @since Method available since Release 1.5.0
     */
    public function supportHtmlComponents()
    {
        $context = Piece_Unity_Context::singleton();
        $context->setView($this->target . 'HTMLComponent');
        $config = $this->getConfig();
        $config->setExtension('Renderer_' . $this->target,
                              'components',
                              array('Renderer_PHPTest_HTMLComponentExample')
                              );
        $context->setConfiguration($config);

        $this->assertEquals('This is a html fragment from a HTML Component.',
                            $this->render()
                            );
    }

    /**#@-*/

    /**#@+
     * @access protected
     */

    protected function getConfig()
    {
        $config = new Piece_Unity_Config();
        $config->setConfiguration('Renderer_PHP', 'templateDirectory', $this->exclusiveDirectory . '/templates/Content');

        return $config;
    }

    /**
     * @since Method available since Release 1.0.0
     */
    protected function doSetUp()
    {
        $this->exclusiveDirectory = dirname(__FILE__) . '/' . basename(__FILE__, '.php');
    }

    /**
     * @since Method available since Release 1.3.0
     */
    protected function getConfigForLayeredStructure()
    {
        $config = new Piece_Unity_Config();
        $config->setConfiguration('Renderer_PHP', 'templateDirectory', $this->exclusiveDirectory . '/templates');

        return $config;
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
