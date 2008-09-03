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

require_once 'PEAR/PackageFileManager2.php';
require_once 'PEAR.php';

PEAR::staticPushErrorHandling(PEAR_ERROR_CALLBACK, create_function('$error', 'var_dump($error); exit();'));

$releaseVersion = '1.5.0';
$releaseStability = 'stable';
$apiVersion = '1.5.0';
$apiStability = 'stable';
$notes = 'A new release of Piece_Unity is now available.

What\'s New in Piece_Unity 1.5.0

 * HTML component system: The Renderer_HTML plug-in allows you to deploy any ready-to-use HTML components as view elements using "components" extension point.
 * View Scheme: "View Scheme" is a feature that the appropriate renderer is automatically determined by the scheme part in the current view string such like "http:", "json:", "html:", etc.
 * Raw Rendering support: This allows you to print any contents directly in an action such like file downloading without a dummy HTML view. If you use this, set "raw:" to the view.
 * Improved error handling: The behavior of internal error handling has been changed so as to handle only own and "exception" level errors.

Backward Compatibility

 * Piece_Unity_Component_Smarty: Piece_Unity_Component_Smarty 1.1.0 or less does not work properly with Piece_Unity 1.5.0. Upgrade to the upcoming version 1.2.0.
 * PEAR_ErrorStack: Piece_Unity 1.5.0 does not work properly with PEAR_ErrorStack::staticPushCallback(). Use PEAR_ErrorStack::setDefaultCallback() instead.

See the following release notes for details.

Enhancements
============

- Added support for Raw Rendering which does nothing. (View plug-in)
- Added Piece_Unity_Service_Rendering_PHP as a rendering service.
- Added an extension point "components" to deploy HTML components before it is required. (Renderer_HTML plug-in)
- Added a feature named "View Scheme" that the appropriate renderer is automatically determined by the view scheme in the current view string such like "http:", "json:", "html:", etc. (Ticket #105) (View plug-in, ViewSchemeHandler plug-in)
- Added createRuntime() to create a Piece_Unity object and invokes a given callback for any configuration. (Ticket #99) (Piece_Unity)
- Changed code so that a plug-in directory is always converted to absolute path. (Piece_Unity_Plugin_Factory)
- Changed the behavior of the Piece_Unity_URL class to always return the backend URI if redirection is enabled. (Ticket #102) (Piece_Unity_URL, Renderer_Redirection plug-in)
- Changed the behavior of internal error handling so as to handle only own and "exception" level errors.
- Added removeProxyPath() to remove the proxy path from a given URI Path. (Piece_Unity_Context)
- Added Piece_Unity_HTTPStatus. (Ticket #113)
- Added sendHTTPStatus() to send a HTTP status line like "HTTP/1.1 404 Not Found". (Piece_Unity_Context)
- Added Piece_Unity_Service_Continuation::createURI() to create a Piece_Unity_URI object based on the active flow execution or a given flow ID. (Ticket #110)
- Changed code so as to use "URI" instead of "URL". (Ticket #119)
- Marked pushPHPError() as deprecated. (Piece_Unity_Error)
- Added configure() to configure the runtime after object instantiation. (Piece_Unity)
- Changed the behavior of initialize() so that $context->getAppRootPath() to be added to the beginning of a given URI if the URI is internal and not starting with http(s). (Piece_Unity_URI)
- Changed the behavior of GC fallback so as to set true to the session variable "_flowExecutionExpired". (Dispatcher_Continuation plug-in)
- Changed the behavior of GC fallback so as to send HTTP status 302 Found. (Dispatcher_Continuation plug-in)

Defect Fixes
============

- Fixed a defect that a fatal error to be raised if session is not used. (Ticket #118) (Piece_Unity_Session)';

$package = new PEAR_PackageFileManager2();
$package->setOptions(array('filelistgenerator' => 'file',
                           'changelogoldtonew' => false,
                           'simpleoutput'      => true,
                           'baseinstalldir'    => '/',
                           'packagefile'       => 'package.xml',
                           'packagedirectory'  => '.',
                           'ignore'            => array('package.php', 'components/'))
                     );

$package->setPackage('Piece_Unity');
$package->setPackageType('php');
$package->setSummary('A web application framework');
$package->setDescription('Piece_Unity is a web application framework.

Piece_Unity allows stateful programming without thinking about sessions by storing and restoring states with a technology known as continuation server. It also provides high security and eases the burden of implementing security measures for applications by application flow control.');
$package->setChannel('pear.piece-framework.com');
$package->setLicense('BSD License (revised)', 'http://www.opensource.org/licenses/bsd-license.php');
$package->setAPIVersion($apiVersion);
$package->setAPIStability($apiStability);
$package->setReleaseVersion($releaseVersion);
$package->setReleaseStability($releaseStability);
$package->setNotes($notes);
$package->setPhpDep('4.3.0');
$package->setPearinstallerDep('1.4.3');
$package->addPackageDepWithChannel('required', 'Piece_Flow', 'pear.piece-framework.com', '1.16.0');
$package->addPackageDepWithChannel('required', 'Cache_Lite', 'pear.php.net', '1.7.0');
$package->addPackageDepWithChannel('required', 'PEAR', 'pear.php.net', '1.4.3');
$package->addPackageDepWithChannel('required', 'Net_URL', 'pear.php.net', '1.0.14');
$package->addPackageDepWithChannel('required', 'Piece_Right', 'pear.piece-framework.com', '1.10.0');
$package->addMaintainer('lead', 'iteman', 'KUBO Atsuhiro', 'iteman@users.sourceforge.net');
$package->addGlobalReplacement('package-info', '@package_version@', 'version');
$package->generateContents();

if (array_key_exists(1, $_SERVER['argv']) && $_SERVER['argv'][1] == 'make') {
    $package->writePackageFile();
} else {
    $package->debugPackageFile();
}

exit();

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
