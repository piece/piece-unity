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
 * @since      File available since Release 0.1.0
 */

require_once 'Piece/Unity/Config.php';
require_once 'Piece/Unity/Error.php';
require_once 'Cache/Lite/File.php';

if (version_compare(phpversion(), '5.0.0', '<')) {
    require_once 'spyc.php';
} else {
    require_once 'spyc.php5';
}

// {{{ Piece_Unity_Config_Factory

/**
 * An factory class for creating an appropriate Piece_Unity_Config object.
 *
 * @package    Piece_Unity
 * @author     KUBO Atsuhiro <iteman2002@yahoo.co.jp>
 * @copyright  2006 KUBO Atsuhiro <iteman2002@yahoo.co.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    Release: @package_version@
 * @link       http://iteman.typepad.jp/piece/
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
     * Creates a new Piece_Unity_Config driver object from the given directory.
     *
     * @param string $configDirectory
     * @param string $cacheDirectory
     * @return mixed
     * @throws PEAR_ErrorStack
     * @static
     */
    function &factory($configDirectory = null, $cacheDirectory = null)
    {
        if (is_null($configDirectory)) {
            $config = &new Piece_Unity_Config();
            return $config;
        }

        $absolutePathOfConfigDirectory = realpath($configDirectory);
        $absolutePathOfConfigFile = "$absolutePathOfConfigDirectory/piece-unity-config.yaml";

        if (!is_readable($absolutePathOfConfigFile)) {
            Piece_Unity_Error::raiseError(PIECE_UNITY_ERROR_NOT_FOUND,
                                          "File [ $absolutePathOfConfigFile ] not found or was not readable."
                                          );
            $config = &new Piece_Unity_Config();
            return $config;
        }

        if (is_null($cacheDirectory)) {
            $cacheDirectory = './cache';
        }

        $absolutePathOfCacheDirectory = realpath($cacheDirectory);
        $cache = &new Cache_Lite_File(array('cacheDir' => "$cacheDirectory/",
                                            'masterFile' => $absolutePathOfConfigFile,
                                            'automaticSerialization' => true)
                                      );
        $config = $cache->get($absolutePathOfConfigDirectory);
        if (!$config && is_readable($absolutePathOfConfigFile)) {
            $config = &new Piece_Unity_Config();
            $yaml = Spyc::YAMLLoad($absolutePathOfConfigFile);
            for ($i = 0; $i < count($yaml); ++$i) {
                if ($yaml[$i]['pointType'] == 'extension') {
                    $config->setExtension($yaml[$i]['plugin'],
                                          $yaml[$i]['point'],
                                          $yaml[$i]['value']
                                          );
                } elseif ($yaml[$i]['pointType'] == 'configuration') {
                    $config->setConfiguration($yaml[$i]['plugin'],
                                              $yaml[$i]['point'],
                                              $yaml[$i]['value']
                                              );
                }
            }
            $cache->save($config);
        }

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
?>
