<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * PHP versions 4 and 5
 *
 * Copyright (c) 2006-2008 KUBO Atsuhiro <iteman@users.sourceforge.net>,
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
 * @copyright  2006-2008 KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    SVN: $Id$
 * @since      File available since Release 0.1.0
 */

require_once 'Piece/Unity/Config.php';
require_once 'Piece/Unity/Error.php';
require_once 'Cache/Lite/File.php';
require_once 'PEAR.php';

if (version_compare(phpversion(), '5.0.0', '<')) {
    require_once 'spyc.php';
} else {
    require_once 'spyc.php5';
}

require_once 'Piece/Unity/Env.php';

// {{{ Piece_Unity_Config_Factory

/**
 * A factory class for creating Piece_Unity_Config objects.
 *
 * @package    Piece_Unity
 * @copyright  2006-2008 KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    Release: @package_version@
 * @since      Class available since Release 0.1.0
 */
class Piece_Unity_Config_Factory
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

    // }}}
    // {{{ factory()

    /**
     * Creates a Piece_Unity_Config object from a configuration file or
     * a cache.
     *
     * @param string $configDirectory
     * @param string $cacheDirectory
     * @return Piece_Unity_Config
     * @static
     */
    function &factory($configDirectory = null, $cacheDirectory = null)
    {
        if (is_null($configDirectory)) {
            $config = &new Piece_Unity_Config();
            return $config;
        }

        if (!file_exists($configDirectory)) {
            Piece_Unity_Error::push(PIECE_UNITY_ERROR_NOT_FOUND,
                                    "The configuration directory [ $configDirectory ] is not found."
                                    );
            $return = null;
            return $return;
        }

        $configFile = "$configDirectory/piece-unity-config.yaml";
        if (!file_exists($configFile)) {
            Piece_Unity_Error::push(PIECE_UNITY_ERROR_NOT_FOUND,
                                    "The configuration file [ $configFile ] is not found."
                                    );
            $return = null;
            return $return;
        }

        if (!is_readable($configFile)) {
            Piece_Unity_Error::push(PIECE_UNITY_ERROR_NOT_READABLE,
                                    "The configuration file [ $configFile ] is not readable."
                                    );
            $return = null;
            return $return;
        }

        if (is_null($cacheDirectory)) {
            return Piece_Unity_Config_Factory::_getConfigurationFromFile($configFile);
        }

        if (!file_exists($cacheDirectory)) {
            trigger_error("The cache directory [ $cacheDirectory ] is not found.",
                          E_USER_WARNING
                          );
            return Piece_Unity_Config_Factory::_getConfigurationFromFile($configFile);
        }

        if (!is_readable($cacheDirectory) || !is_writable($cacheDirectory)) {
            trigger_error("The cache directory [ $cacheDirectory ] is not readable or writable.",
                          E_USER_WARNING
                          );
            return Piece_Unity_Config_Factory::_getConfigurationFromFile($configFile);
        }

        return Piece_Unity_Config_Factory::_getConfiguration($configFile, $cacheDirectory);
    }

    /**#@-*/

    /**#@+
     * @access private
     * @static
     */

    // }}}
    // {{{ _getConfiguration()

    /**
     * Gets a Piece_Unity_Config object from a configuration file or a cache.
     *
     * @param string $masterFile
     * @param string $cacheDirectory
     * @return Piece_Unity_Config
     */
    function &_getConfiguration($masterFile, $cacheDirectory)
    {
        $masterFile = realpath($masterFile);
        $cache = &new Cache_Lite_File(array('cacheDir' => "$cacheDirectory/",
                                            'masterFile' => $masterFile,
                                            'automaticSerialization' => true,
                                            'errorHandlingAPIBreak' => true)
                                      );

        if (!Piece_Unity_Env::isProduction()) {
            $cache->remove($masterFile);
        }

        /*
         * The Cache_Lite class always specifies PEAR_ERROR_RETURN when
         * calling PEAR::raiseError in default.
         */
        $config = $cache->get($masterFile);
        if (PEAR::isError($config)) {
            trigger_error("Cannot read the cache file in the directory [ $cacheDirectory ].",
                          E_USER_WARNING
                          );
            return Piece_Unity_Config_Factory::_getConfigurationFromFile($masterFile);
        }

        if (!$config) {
            $config = &Piece_Unity_Config_Factory::_getConfigurationFromFile($masterFile);
            $result = $cache->save($config);
            if (PEAR::isError($result)) {
                trigger_error("Cannot write the Piece_Unity_Config object to the cache file in the directory [ $cacheDirectory ].",
                              E_USER_WARNING
                              );
            }
        }

        return $config;
    }

    // }}}
    // {{{ _getConfigurationFromFile()

    /**
     * Parses the given file and returns a Piece_Unity_Config object.
     *
     * @param string $file
     * @return Piece_Unity_Config
     */
    function &_getConfigurationFromFile($file)
    {
        $config = &new Piece_Unity_Config();
        $yaml = Spyc::YAMLLoad($file);
        foreach ($yaml as $plugin) {
            foreach ($plugin['point'] as $point) {
                if ($point['type'] == 'extension') {
                    $config->setExtension($plugin['name'],
                                          $point['name'],
                                          $point['value']
                                          );
                } elseif ($point['type'] == 'configuration') {
                    $config->setConfiguration($plugin['name'],
                                              $point['name'],
                                              $point['value']
                                              );
                }
            }
        }

        return $config;
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
