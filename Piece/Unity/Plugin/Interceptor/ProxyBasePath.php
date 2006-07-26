<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * PHP versions 4 and 5
 *
 * Copyright (c) 2006, KUBO Atsuhiro <iteman@users.sourceforge.net>
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
 * @link       http://iteman.typepad.jp/piece/
 * @since      File available since Release 0.5.0
 */

require_once 'Piece/Unity/Plugin/Common.php';

// {{{ Piece_Unity_Plugin_Interceptor_ProxyBasePath

/**
 * An interceptor to adjust the base path and the script name of the current
 * request which are held in the Piece_Unity_Context object.
 * This interceptor is used and only works when your web servers are used as
 * reverse proxies.
 *
 * The base path and the script name are both relative paths since they are
 * based on SCRIPT_NAME environment variable. The following is a example of a
 * context change when 'path' configuration point is set to '/foo'.
 *
 * <pre>
 * 'path' Configuration Point: /foo
 *
 * Requested URL (frontend): http://example.org/foo/bar/baz.php
 * Requested URL (backend):  http://backend.example.org/bar/baz.php
 * Base Path (original):     /bar
 * Base Path (adjusted):     /foo/bar
 * Script Name (original):   /bar/baz.php
 * Script Name (adjusted):   /foo/bar/baz.php
 * </pre>
 *
 * @package    Piece_Unity
 * @author     KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @copyright  2006 KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    Release: @package_version@
 * @link       http://iteman.typepad.jp/piece/
 * @since      Class available since Release 0.5.0
 */
class Piece_Unity_Plugin_Interceptor_ProxyBasePath extends Piece_Unity_Plugin_Common
{

    // {{{ properties

    /**#@+
     * @access public
     */

    /**#@-*/

    /**#@+
     * @access private
     */

    var $_measures = array('HTTP_X_FORWARDED_FOR',
                           'HTTP_X_FORWARDED',
                           'HTTP_FORWARDED_FOR',
                           'HTTP_FORWARDED',
                           'HTTP_VIA',
                           'HTTP_X_COMING_FROM',
                           'HTTP_COMING_FROM'
                           );

    /**#@-*/

    /**#@+
     * @access public
     */

    // }}}
    // {{{ constructor

    /**
     * Defines extension points and configuration points for the plugin.
     */
    function Piece_Unity_Plugin_Interceptor_ProxyBasePath()
    {
        parent::Piece_Unity_Plugin_Common();
        $this->_addConfigurationPoint('path');
        $this->_addConfigurationPoint('adjustSessionCookiePath', true);
    }

    // }}}
    // {{{ invoke()

    /**
     * Invokes the plugin specific code.
     *
     * @return boolean
     */
    function invoke()
    {
        if (!$this->_useProxy()) {
            return true;
        }

        $path = $this->getConfiguration('path');
        if (!is_null($path)) {
            $this->_context->setBasePath($path . $this->_context->getBasePath());
            $this->_context->setScriptName($path . $this->_context->getScriptName());

            $adjustSessionCookiePath = $this->getConfiguration('adjustSessionCookiePath');
            if ($adjustSessionCookiePath) {
                ini_set('session.cookie_path',
                        $path . str_replace('//', '/', ini_get('session.cookie_path'))
                        );
            }
        }

        return true;
    }

    /**#@-*/

    /**#@+
     * @access private
     */

    // }}}
    // {{{ _useProxy()

    /**
     * Returns whether the application is accessed via reverse proxies.
     *
     * @return boolean
     */
    function _useProxy()
    {
        foreach ($this->_measures as $measure) {
            if (array_key_exists($measure, $_SERVER)) {
                return true;
            }
        }

        return false;
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
