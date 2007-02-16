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
 * @since      File available since Release 0.11.0
 */

require_once 'Piece/Unity/Plugin/Common.php';
require_once 'HTTP/Download.php';

// {{{ Piece_Unity_Plugin_Renderer_Download

/**
 * A renderer which download file.
 *
 * @package    Piece_Unity
 * @author     KUMAKURA Yousuke <kumatch@users.sourceforge.net>
 * @copyright  2006-2007 KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    Release: @package_version@
 * @link       http://piece-framework.com/piece-unity/
 * @since      Class available since Release 0.11.0
 */
class Piece_Unity_Plugin_Renderer_Download extends Piece_Unity_Plugin_Common
{

    // {{{ properties

    /**#@+
     * @access public
     */

    var $httpDownload;

    /**#@-*/

    /**#@+
     * @access private
     */

    var $_httpDownloadClassVariables = array('gzip'               => null,
                                             'cache'              => null,
                                             'lastmodified'       => null,
                                             'contenttype'        => null,
                                             'contentdisposition' => null,
                                             'buffersize'         => null,
                                             'throttledelay'      => null,
                                             'cachecontrol'       => null,
                                             );

    /**#@-*/

    /**#@+
     * @access public
     */

    // }}}
    // {{{ invoke()

    /**
     * Invokes the plugin specific code.
     *
     * @throws PIECE_UNITY_ERROR_INVOCATION_FAILED
     */
    function invoke()
    {
        $this->initializeHTTPDownload();
        if (Piece_Unity_Error::hasErrors('exception')) {
            return;
        }

        $this->setHTTPDownloadParamsByConfigurationPoint();
        if (Piece_Unity_Error::hasErrors('exception')) {
            return;
        }

        $this->setHTTPDownloadParamsByViewElements();
        if (Piece_Unity_Error::hasErrors('exception')) {
            return;
        }

        $this->setFilePath();
        $this->setResource();
        $this->setDataSource();

        $this->setDownloadFilename();

        $sendResult = $this->httpDownload->send();
        if (PEAR::isError($sendResult)) {
            Piece_Unity_Error::pushPEARError($this->httpDownload,
                                             PIECE_UNITY_ERROR_INVOCATION_FAILED,
                                             'Failed to invoke the plugin [ ' . __CLASS__ . ' ].',
                                             'exception',
                                             array('plugin' => __CLASS__)
                                             );
        }
    }

    // }}}
    // {{{ initializeHTTPDownload()

    /**
     * Initializes HTTP_Download class
     */
    function initializeHTTPDownload()
    {
        if (strtolower(get_class($this->httpDownload) ==='http_download')) {
            return;
        }

        $httpDownload = new HTTP_Download();
        if (!$httpDownload) {
            Piece_Unity_Error::push(PIECE_UNITY_ERROR_NOT_FOUND,
                                    'The HTTP_Download class not found or was not readable.'
                                    );
            return;
        }

        if (PEAR::isError($httpDownload)) {
            Piece_Unity_Error::pushPEARError($httpDownload,
                                             PIECE_UNITY_ERROR_INVOCATION_FAILED,
                                             'Failed to invoke the plugin [ ' . __CLASS__ . ' ].',
                                             'exception',
                                             array('plugin' => __CLASS__)
                                             );
            return;
        }

        $this->httpDownload = &$httpDownload;
    }

    // }}}
    // {{{ setHTTPDownloadParamsByConfigurationPoint()

    /**
     * Set the HTTP_Download parameter by this plugin's configuration points.
     */
    function setHTTPDownloadParamsByConfigurationPoint()
    {
        $paramsConfigurations = array();
        foreach ($this->_httpDownloadClassVariables as $point => $default) {
            $configuration = $this->getConfiguration($point);
            if (!is_null($configuration)) {
                $paramsConfigurations[$point] = $configuration;
            }
        }
        if (count($paramsConfigurations)) {
            $this->_setHTTPDownloadParams($paramsConfigurations);
        }
    }

    // }}}
    // {{{ setHTTPDownloadParamsByViewElements()

    /**
     * Set the HTTP_Download parameter by view element values.
     */
    function setHTTPDownloadParamsByViewElements()
    {
        $params = $this->_getElementsValueByKeyword('optionParamsKey');
        if (!$params) {
            $params = array();
        }
        $this->_setHTTPDownloadParams($params);
    }

    // }}}
    // {{{ setFilePath()

    /**
     * Set a file path for download data.
     */
    function setFilePath()
    {
        if ($filePath = $this->_getElementsValueByKeyword('filePathKey')) {
            $this->httpDownload->setFile($filePath);
        }
    }

    // }}}
    // {{{ setResource()

    /**
     * Set a resource for download data.
     */
    function setResource()
    {
        if ($resource = $this->_getElementsValueByKeyword('resourceKey')) {
            $this->httpDownload->setResource($resource);
        }
    }

    // }}}
    // {{{ setDataSource()

    /**
     * Set a data source for download data.
     */
    function setDataSource()
    {
        if ($dataSource = $this->_getElementsValueByKeyword('dataSourceKey')) {
            if (is_array($dataSource)) {
                $separator = $this->getConfiguration('separator');
                $endOfLine = $this->getConfiguration('endOfLine');
                $data = $this->_makeSeparatedValues($dataSource,
                                                    $separator,
                                                    $endOfLine
                                                    );
            } else {
                $data = $dataSource;
            }

            if ($encoding = $this->getConfiguration('encoding')) {
                $data = mb_convert_encoding($data, $encoding);
            }
            $this->httpDownload->setData($data);
        }
    }

    // }}}
    // {{{ setDownloadFilename()

    /**
     * Set a download file name.
     */
    function setDownloadFilename()
    {
        if ($filename = $this->_getElementsValueByKeyword('filenameKey')) {
            $this->httpDownload->setContentDisposition(HTTP_DOWNLOAD_ATTACHMENT,
                                                       $filename
                                                       );
        }
    }

    /**#@-*/

    /**#@+
     * @access private
     */

    // }}}
    // {{{ _initialize()

    /**
     * Defines and initializes extension points and configuration points.
     *
     * @since Method available since Release 0.6.0
     */
    function _initialize()
    {
        $this->_addConfigurationPoint('optionParamsKey', '_params');
        $this->_addConfigurationPoint('filePathKey', '_file');
        $this->_addConfigurationPoint('dataSourceKey', '_data');
        $this->_addConfigurationPoint('resourceKey', '_resource');
        $this->_addConfigurationPoint('filenameKey', '_filename');
        $this->_addConfigurationPoint('separator', ',');
        $this->_addConfigurationPoint('endOfLine', "\r\n");
        $this->_addConfigurationPoint('encoding');

        foreach ($this->_httpDownloadClassVariables as $point => $default) {
            $this->_addConfigurationPoint($point, $default);
        }
    }
 
    // }}}
    // {{{ _makeSeparatedValues()

    /**
     * Makes the specific separated values from array.
     *
     * @param array $lists
     * @param string $separator
     * @param string $endOfLine
     * @return string
     */
    function _makeSeparatedValues($lists, $separator, $endOfLine)
    {
        if (!$endOfLine) {
            $endOfLine = PHP_EOL;
        }

        $result = '';
        foreach ($lists as $list) {
            if (!is_array($list)) {
                $list = (array)$list;
            }
            $result .= '"' . implode("\"{$separator}\"", $list) . '"' . $endOfLine;
        }

        return $result;
    }

    // }}}
    // {{{ _setHTTPDownloadParams()

    /**
     * Set HTTP_Download various parameters.
     *
     * @param array $params
     */
    function _setHTTPDownloadParams($params)
    {
        $setParamsResult = $this->httpDownload->setParams($params);
        if (PEAR::isError($setParamsResult)) {
            Piece_Unity_Error::pushPEARError($this->httpDownload,
                                             PIECE_UNITY_ERROR_UNEXPECTED_VALUE,
                                             "Unexpected configuration parameter in HTTP_Download::setParams",
                                             'exception',
                                             array('plugin' => __CLASS__)
                                             );
        }
    }

    // }}}
    // {{{ _getElementsValueByKeyword()

    /**
     * Set HTTP_Download various parameters.
     *
     * @param string $keyword
     * @return mixed
     */
    function _getElementsValueByKeyword($keyword)
    {
        $value = null;

        $viewElement = &$this->_context->getViewElement();
        $viewElements = $viewElement->getElements();

        $paramKey = $this->getConfiguration($keyword);
        if (array_key_exists($paramKey, $viewElements)) {
            $value = $viewElements[$paramKey];
            unset($viewElements[$paramKey]);
        }

        return $value;
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
