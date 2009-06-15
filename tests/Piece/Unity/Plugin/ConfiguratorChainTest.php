<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * PHP version 5
 *
 * Copyright (c) 2007-2009 KUBO Atsuhiro <kubo@iteman.jp>,
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
 * @copyright  2007-2009 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @since      File available since Release 0.11.0
 */

// {{{ Piece_Unity_Plugin_ConfiguratorChainTest

/**
 * Some tests for Piece_Unity_Plugin_ConfiguratorChain.
 *
 * @package    Piece_Unity
 * @copyright  2007-2009 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @since      Class available since Release 0.11.0
 */
class Piece_Unity_Plugin_ConfiguratorChainTest extends Piece_Unity_PHPUnit_TestCase
{

    // {{{ properties

    /**#@+
     * @access public
     */

    /**#@-*/

    /**#@+
     * @access protected
     */

    protected $serviceName = 'Piece_Unity_Plugin_ConfiguratorChain';

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
    public function invokeAConfigurator()
    {
        $this->config->queueExtension($this->serviceName, 'configurators', __CLASS__ . '_FirstConfigurator');
        $this->config->instantiateFeature($this->serviceName)->invoke();

        $this->assertTrue($this->context->hasAttribute('FirstConfiguratorCalled'));
        $this->assertTrue($this->context->hasAttribute('FirstConfiguratorCalled'));
    }

    /**
     * @test
     */
    public function invokeMultipleConfigurators()
    {
        $this->config->queueExtension($this->serviceName, 'configurators', __CLASS__ . '_FirstConfigurator');
        $this->config->queueExtension($this->serviceName, 'configurators', __CLASS__ . '_SecondConfigurator');
        $this->config->instantiateFeature($this->serviceName)->invoke();

        $this->assertTrue($this->context->hasAttribute('FirstConfiguratorCalled'));
        $this->assertTrue($this->context->getAttribute('FirstConfiguratorCalled'));
        $this->assertTrue($this->context->hasAttribute('SecondConfiguratorCalled'));
        $this->assertTrue($this->context->getAttribute('SecondConfiguratorCalled'));

        $logs = $this->context->getAttribute('logs');

        $this->assertEquals(__CLASS__ . '_FirstConfigurator', array_shift($logs));
        $this->assertEquals(__CLASS__ . '_SecondConfigurator', array_shift($logs));
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
