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
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    GIT: $Id$
 * @since      File available since Release 0.1.0
 */

// {{{ Piece_Unity_ConfigTest

/**
 * Some tests for Piece_Unity_Config.
 *
 * @package    Piece_Unity
 * @copyright  2006-2009 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    Release: @package_version@
 * @since      Class available since Release 0.1.0
 */
class Piece_Unity_ConfigTest extends Piece_Unity_PHPUnit_TestCase
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

    private $_config;

    /**#@-*/

    /**#@+
     * @access public
     */

    public function setUp()
    {
        parent::setUp();
        $this->_config = new Piece_Unity_Config();
    }

    /**
     * @test
     */
    public function setAnExtension()
    {
        $this->_config->setExtension('Foo', 'bar', 'Bar');

        $this->assertEquals('Bar', $this->_config->getExtension('Foo', 'bar'));
    }

    /**
     * @test
     */
    public function setAConfiguration()
    {
        $this->_config->setConfiguration('Foo', 'bar', 'Bar');

        $this->assertEquals('Bar', $this->_config->getConfiguration('Foo', 'bar'));
    }

    /**
     * @test
     */
    public function returnNullFromGetextensionIfTheGivenPluginIsNotDefined()
    {
        $this->_config->setExtension('Foo', 'bar', 'Bar');

        $this->assertNull($this->_config->getExtension('Bar', 'bar'));
    }

    /**
     * @test
     */
    public function returnNullFromGetextensionIfTheGivenExtensionPointIsNotDefined()
    {
        $this->_config->setExtension('Foo', 'bar', 'Bar');

        $this->assertNull($this->_config->getExtension('Foo', 'baz'));
    }

    /**
     * @test
     */
    public function returnNullFromGetconfigurationIfTheGivenConfigurationPointIsNotDefined()
    {
        $this->_config->setConfiguration('Foo', 'bar', 'Bar');

        $this->assertNull($this->_config->getConfiguration('Foo', 'baz'));
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
