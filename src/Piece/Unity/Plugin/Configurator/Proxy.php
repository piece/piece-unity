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
 * @since      File available since Release 0.12.0
 */

// {{{ Piece_Unity_Plugin_Configurator_Proxy

/**
 * An configurator to adjust the base path and the script name of the current request
 * which are held in the Piece_Unity_Context object.
 * This configurator is used and only works if the web servers where your application
 * is running on are used as back-end servers for reverse proxy servers.
 *
 * The base path and the script name are both relative paths since they are based on
 * the SCRIPT_NAME variable. The following is a example of a context change when
 * 'proxyPath' configuration point is set to '/foo'.
 *
 * <pre>
 * Configuration Point 'proxyPath' in Configurator_Proxy plug-in: /foo
 *
 * Requested URI (front-end): http://example.org/foo/bar/baz.php
 * Requested URI (back-end):  http://back-end.example.org/bar/baz.php
 * Base Path (original):     /bar
 * Base Path (adjusted):     /foo/bar
 * Script Name (original):   /bar/baz.php
 * Script Name (adjusted):   /foo/bar/baz.php
 * </pre>
 *
 * @package    Piece_Unity
 * @copyright  2007-2009 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @since      Class available since Release 0.12.0
 */
class Piece_Unity_Plugin_Configurator_Proxy extends Piece_Unity_Plugin_Common implements Piece_Unity_Plugin_Configurator_Interface
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

    // }}}
    // {{{ configure()

    /**
     * Configures the runtime.
     */
    public function configure()
    {
        $proxyPath = $this->getConfiguration('proxyPath');
        $this->context->setProxyPath($proxyPath);

        if (!Stagehand_HTTP_ServerEnv::usingProxy()) {
            return;
        }

        if (!is_null($proxyPath)) {
            $this->context->setBasePath($proxyPath . $this->context->getBasePath());
            $this->context->setScriptName($proxyPath . $this->context->getScriptName());
            $this->context->setAppRootPath($proxyPath . $this->context->getAppRootPath());

            $adjustSessionCookiePath = $this->getConfiguration('adjustSessionCookiePath');
            if ($adjustSessionCookiePath) {
                ini_set('session.cookie_path',
                        $proxyPath . str_replace('//', '/', ini_get('session.cookie_path'))
                        );
            }
        }
    }

    /**#@-*/

    /**#@+
     * @access protected
     */

    // }}}
    // {{{ initialize()

    /**
     * Defines and initializes extension points and configuration points.
     */
    protected function initialize()
    {
        $this->addConfigurationPoint('proxyPath');
        $this->addConfigurationPoint('adjustSessionCookiePath', true);
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
