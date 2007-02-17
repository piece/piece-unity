<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * PHP versions 4 and 5
 *
 * Copyright (c) 2006-2007 KUBO Atsuhiro <iteman@users.sourceforge.net>,
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
 * @author     KUMAKURA Yousuke <kumatch@users.sourceforge.net>
 * @copyright  2006-2007 KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    SVN: $Id$
 * @link       http://piece-framework.com/piece-unity/
 * @see        Piece_Unity_Plugin_Renderer_Download
 * @since      File available since Release 0.9.0
 */

require_once 'PHPUnit.php';
require_once 'Piece/Unity/Plugin/Renderer/Download.php';
require_once 'Piece/Unity/Context.php';
require_once 'Piece/Unity/Config.php';
require_once 'Piece/Unity/Error.php';
require_once 'Piece/Unity/Plugin/View.php';

// {{{ Piece_Unity_Plugin_Renderer_DownloadTestCase

/**
 * TestCase for Piece_Unity_Plugin_Renderer_Download
 *
 * @package    Piece_Unity
 * @author     KUMAKURA Yousuke <kumatch@users.sourceforge.net>
 * @copyright  2006-2007 KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    Release: @package_version@
 * @link       http://piece-framework.com/piece-unity/
 * @see        Piece_Unity_Plugin_Renderer_Download
 * @since      File available since Release 0.9.0
 */
class Piece_Unity_Plugin_Renderer_DownloadTestCase extends PHPUnit_TestCase
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

    function setUp()
    {
        Piece_Unity_Error::pushCallback(create_function('$error', 'var_dump($error); return ' . PEAR_ERRORSTACK_DIE . ';'));
    }

    function tearDown()
    {
        Piece_Unity_Context::clear();
        Piece_Unity_Plugin_Factory::clearInstances();
        Piece_Unity_Error::clearErrors();
        Piece_Unity_Error::popCallback();
    }

    function testInitializeHTTPDownload()
    {
        $context = &Piece_Unity_Context::singleton();
        $config = &$this->_getConfig();
        $context->setConfiguration($config);

        $view = &new Piece_Unity_Plugin_View();
        $renderer = &$view->getExtension('renderer');

        $renderer->initializeHTTPDownload();

        $this->assertEquals('http_download',
                            strtolower(get_class($renderer->httpDownload))
                            );
    }
    
    function testSettingHTTPDownloadParamsByConfigurationPoint()
    {
        $context = &Piece_Unity_Context::singleton();
        $config = &$this->_getConfig();

        $configList = $this->_getConfigurationPoints();
        foreach ($configList as $key => $value) {
            $config->setConfiguration('Renderer_Download', $key, $value);
        }
        $context->setConfiguration($config);

        $view = &new Piece_Unity_Plugin_View();
        $renderer = &$view->getExtension('renderer');

        $renderer->initializeHTTPDownload();
        $renderer->setHTTPDownloadParamsByConfigurationPoint();

        $this->assertEquals($renderer->httpDownload->gzip, $configList['gzip']);
        $this->assertEquals($renderer->httpDownload->cache, $configList['cache']);
        $this->assertEquals($renderer->httpDownload->lastModified,
                            $configList['lastmodified']);
        $this->assertEquals($renderer->httpDownload->headers['Content-Type'],
                            $configList['contenttype']);
        $this->assertEquals($renderer->httpDownload->headers['Content-Disposition'],
                            "attachment; filename=\"{$configList['contentdisposition'][1]}\"");
        $this->assertEquals($renderer->httpDownload->bufferSize,
                            $configList['buffersize']);
        $this->assertEquals($renderer->httpDownload->throttleDelay,
                            abs($configList['throttledelay']) * 1000);
        $this->assertTrue(preg_match("/{$configList['cachecontrol']}/",
                                     $renderer->httpDownload->headers['Cache-Control'])
                          );
    }

    function testSettingHTTPDownloadParamsByViewElements()
    {
        $context = &Piece_Unity_Context::singleton();
        $config = &$this->_getConfig();
        $context->setConfiguration($config);

        $configList = $this->_getConfigurationPoints();
        $viewElement = &$context->getViewElement();
        $viewElement->setElement('_params', $configList);

        $view = &new Piece_Unity_Plugin_View();
        $renderer = &$view->getExtension('renderer');

        $renderer->initializeHTTPDownload();
        $renderer->setHTTPDownloadParamsByViewElements();

        $this->assertEquals($renderer->httpDownload->gzip, $configList['gzip']);
        $this->assertEquals($renderer->httpDownload->cache, $configList['cache']);
        $this->assertEquals($renderer->httpDownload->lastModified,
                            $configList['lastmodified']);
        $this->assertEquals($renderer->httpDownload->headers['Content-Type'],
                            $configList['contenttype']);
        $this->assertEquals($renderer->httpDownload->headers['Content-Disposition'],
                            "attachment; filename=\"{$configList['contentdisposition'][1]}\"");
        $this->assertEquals($renderer->httpDownload->bufferSize,
                            $configList['buffersize']);
        $this->assertEquals($renderer->httpDownload->throttleDelay,
                            abs($configList['throttledelay']) * 1000);
        $this->assertTrue(preg_match("/{$configList['cachecontrol']}/",
                                     $renderer->httpDownload->headers['Cache-Control'])
                          );
    }

    function testSettingFilePath()
    {
        $filePath = __FILE__;

        $context = &Piece_Unity_Context::singleton();
        $config = &$this->_getConfig();
        $context->setConfiguration($config);

        $viewElement = &$context->getViewElement();
        $viewElement->setElement('_file', $filePath);

        $view = &new Piece_Unity_Plugin_View();
        $renderer = &$view->getExtension('renderer');

        $renderer->initializeHTTPDownload();
        $renderer->setFilePath();

        $this->assertEquals($renderer->httpDownload->file, $filePath);

        $renderer->httpDownload->file = null;
        $keyword = 'filepath';
        $config->setConfiguration('Renderer_Download', 'filePathKey', $keyword);
        $viewElement->setElement($keyword, $filePath);

        $renderer->setFilePath();

        $this->assertEquals($renderer->httpDownload->file, $filePath);
    }

    function testSettingResource()
    {
        $resource = fopen(__FILE__, 'r');

        $context = &Piece_Unity_Context::singleton();
        $config = &$this->_getConfig();
        $context->setConfiguration($config);

        $viewElement = &$context->getViewElement();
        $viewElement->setElement('_resource', $resource);

        $view = &new Piece_Unity_Plugin_View();
        $renderer = &$view->getExtension('renderer');

        $renderer->initializeHTTPDownload();
        $renderer->setResource();

        $this->assertTrue(is_resource($renderer->httpDownload->handle));
        $this->assertEquals('stream',
                            get_resource_type($renderer->httpDownload->handle));

        $renderer->httpDownload->handle = null;
        $keyword = 'fileresource';
        $resource2 = fopen(__FILE__, 'r');
        $config->setConfiguration('Renderer_Download', 'resourceKey', $keyword);
        $viewElement->setElement($keyword, $resource2);

        $renderer->setResource();

        $this->assertTrue(is_resource($renderer->httpDownload->handle));
        $this->assertEquals('stream',
                            get_resource_type($renderer->httpDownload->handle));
    }

    function testSettingDataSource()
    {
        $data = "== Data sample ==
foo
bar
baz
";

        $context = &Piece_Unity_Context::singleton();
        $config = &$this->_getConfig();
        $context->setConfiguration($config);

        $viewElement = &$context->getViewElement();
        $viewElement->setElement('_data', $data);

        $view = &new Piece_Unity_Plugin_View();
        $renderer = &$view->getExtension('renderer');

        $renderer->initializeHTTPDownload();
        $renderer->setDataSource();

        $this->assertEquals($renderer->httpDownload->data, $data);

        $renderer->httpDownload->data = null;
        $keyword = 'datasource';
        $data2 = "foobarbazquuxqux";
        $config->setConfiguration('Renderer_Download', 'datasourceKey', $keyword);
        $viewElement->setElement($keyword, $data2);

        $renderer->setDataSource();

        $this->assertEquals($renderer->httpDownload->data, $data2);
    }

    function testSettingSeparatedValues()
    {
        $list = array(
                      array('foo', 'red', 'sun'),
                      array('bar', 'green', 'forest'),
                      array('baz', 'blue', 'sky')
                      );
        $csv = "\"foo\",\"red\",\"sun\"\r\n\"bar\",\"green\",\"forest\"\r\n\"baz\",\"blue\",\"sky\"\r\n";

        $context = &Piece_Unity_Context::singleton();
        $config = &$this->_getConfig();
        $context->setConfiguration($config);

        $viewElement = &$context->getViewElement();
        $viewElement->setElement('_data', $list);

        $view = &new Piece_Unity_Plugin_View();
        $renderer = &$view->getExtension('renderer');

        $renderer->initializeHTTPDownload();
        $renderer->setDataSource();

        $this->assertEquals($renderer->httpDownload->data, $csv);

        $renderer->httpDownload->data = null;
        $config->setConfiguration('Renderer_Download', 'separator', "\t");
        $tsv = "\"foo\"\t\"red\"\t\"sun\"\r\n\"bar\"\t\"green\"\t\"forest\"\r\n\"baz\"\t\"blue\"\t\"sky\"\r\n";

        $renderer->setDataSource();

        $this->assertEquals($renderer->httpDownload->data, $tsv);
    }

    function testSettingDownloadFilename()
    {
        $filename = 'example.txt';

        $context = &Piece_Unity_Context::singleton();
        $config = &$this->_getConfig();
        $context->setConfiguration($config);

        $viewElement = &$context->getViewElement();
        $viewElement->setElement('_filename', $filename);

        $view = &new Piece_Unity_Plugin_View();
        $renderer = &$view->getExtension('renderer');

        $renderer->initializeHTTPDownload();
        $renderer->setDownloadFilename();

        $this->assertEquals($renderer->httpDownload->headers['Content-Disposition'],
                            "attachment; filename=\"{$filename}\"");

        $renderer->httpDownload->headers['Content-Disposition'] = null;
        $keyword = 'downloadFilename';
        $config->setConfiguration('Renderer_Download', 'filenameKey', $keyword);
        $viewElement->setElement($keyword, $filename);

        $renderer->setDownloadFilename();

        $this->assertEquals($renderer->httpDownload->headers['Content-Disposition'],
                            "attachment; filename=\"{$filename}\"");
    }

    /**#@-*/

    /**#@+
     * @access private
     */

    function &_getConfig()
    {
        $config = &new Piece_Unity_Config();
        $config->setExtension('View', 'renderer', 'Renderer_Download');
        return $config;
    }

    function _getConfigurationPoints()
    {
        return array('gzip'               => true,
                     'cache'              => false,
                     'lastmodified'       => time(),
                     'contenttype'        => 'application/x-gzip',
                     'contentdisposition' => array('attachment', 'test.tgz'),
                     'buffersize'         => 10 * 1024,
                     'throttledelay'      => 1,
                     'cachecontrol'       => 'private',
                     );
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
