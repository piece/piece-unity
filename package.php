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
 * @author     KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @copyright  2006-2007 KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    SVN: $Id$
 * @link       http://piece-framework.com/piece-unity/
 * @see        PEAR_PackageFileManager2
 * @since      File available since Release 0.1.0
 */

require_once 'PEAR/PackageFileManager2.php';

PEAR::staticPushErrorHandling(PEAR_ERROR_CALLBACK, create_function('$error', 'var_dump($error); exit();'));

$version = '0.12.0';
$apiVersion = '0.7.0';
$releaseStability = 'beta';
$notes = 'A new release of Piece_Unity is now available.

What\'s New in Piece_Unity 0.12.0

 * Configurator_AppRoot plug-in: A plug-in for setting the directory and the URL path that form the top of the document tree of an application visible from the web.
 * Configurator_PieceORM plug-in: A plug-in for Piece_ORM.
 * Several defect fixes: A defect in fallback view rendering has been fixed. And also a defect in Plug-in Aliases has been fixed. And other defects have been fixed.

See the following release notes for details.

Enhancements
============ 

Plug-ins:

- Added the plugins_dir configuration point. (Renderer_Smarty)
- Updated "Self Notation" so as to set true to the configuration point "addFlowExecutionTicket" of Renderer_Redirection plug-in. (View)
- Added "Configurator_PieceORM" plug-in for Piece_ORM.
- Added "Configurator_Proxy" plug-in for proxy.
- Changed the default value for the configuration point "configurator" from "KernelConfigurator" to "ConfiguratorChain". (Root)
- Added "Configurator_AppRoot" plug-in for setting the directory and the URL path that form the top of the document tree of an application visible from the web. (Ticket #56)
- Removed the configuration point "importSessionIDFromRequest". (Interceptor_SessionStart)

Kernel:

- Updated pushPEARError() so that the params field in the repackage array contains "userinfo" and "debuginfo" fields, which contains each of the return values from getUserInfo() and getDebugInfo(). (Piece_Unity_Error)
- Updated the constructor so as to receive a prefix for determining the plug-in name. (Piece_Unity_Plugin_Common)
- Changed the error type on all Piece_Unity_Error::pushError() calls from "warning" to "exception". (Piece_Unity_URL)
- Added getRemoteAddr() for getting an IP address (or IP addresses) of the client making the request. (Piece_Unity_Context)
- Added _getExtension()/_getConfiguration(). (Piece_Unity_Plugin_Common)
  getExtension()/getConfiguration() are deprecated since Piece_Unity 0.12.0. (Ticket #54)

Example Applications:

- Changed the function for URI encoding from escape() to encodeURIcomponent(). (ahah.js)
- Replaced all "__basePath" with "__appRootPath".

Defect Fixes
============ 

Plug-ins:

- Fixed a defect in fallback view rendering that caused a fallback view to be always rendered in spite of success in HTML rendering if one or more "warning" level errors raised before rendering. (Ticket #57)

Kernel:

- Fixed the problem that a plug-in with Plug-in Aliases cannot get the current configuration. (Ticket #53)
- Fixed the problem that the getExtension()/getConfiguration() cannot work with an empty prefix. (Piece_Unity_Plugin_Common)

Example Applications:

- Fixed the problem that all radio elements are transferred regardless of whether they are checked or not. (ahah.js)';

$package = new PEAR_PackageFileManager2();
$package->setOptions(array('filelistgenerator' => 'svn',
                           'changelogoldtonew' => false,
                           'simpleoutput'      => true,
                           'baseinstalldir'    => '/',
                           'packagefile'       => 'package2.xml',
                           'packagedirectory'  => '.',
                           'dir_roles'         => array('data' => 'data',
                                                        'tests' => 'test',
                                                        'docs' => 'doc'))
                     );

$package->setPackage('Piece_Unity');
$package->setPackageType('php');
$package->setSummary('A stateful and secure web application framework for PHP');
$package->setDescription('Piece_Unity is a stateful and secure web application framework for PHP.

Piece_Unity allows stateful programming without thinking about sessions by storing and restoring states with a technology known as continuation server. It also provides high security and eases the burden of implementing security measures for applications by application flow control.');
$package->setChannel('pear.piece-framework.com');
$package->setLicense('BSD License (revised)',
                     'http://www.opensource.org/licenses/bsd-license.php'
                     );
$package->setAPIVersion($apiVersion);
$package->setAPIStability('beta');
$package->setReleaseVersion($version);
$package->setReleaseStability($releaseStability);
$package->setNotes($notes);
$package->setPhpDep('4.3.0');
$package->setPearinstallerDep('1.4.3');
$package->addPackageDepWithChannel('required', 'Piece_Flow', 'pear.piece-framework.com', '1.8.0');
$package->addPackageDepWithChannel('required', 'Cache_Lite', 'pear.php.net', '1.7.0');
$package->addPackageDepWithChannel('required', 'PEAR', 'pear.php.net', '1.4.3');
$package->addPackageDepWithChannel('required', 'Net_URL', 'pear.php.net', '1.0.14');
$package->addPackageDepWithChannel('required', 'Piece_Right', 'pear.piece-framework.com', '1.5.0');
$package->addPackageDepWithChannel('optional', 'Stagehand_TestRunner', 'pear.piece-framework.com', '0.4.0');
$package->addPackageDepWithChannel('optional', 'HTML_Template_Flexy', 'pear.php.net', '1.2.4');
$package->addPackageDepWithChannel('optional', 'Smarty', 'pearified.com', '1.6.8');
$package->addPackageDepWithChannel('optional', 'HTML_AJAX', 'pear.php.net', '0.5.0');
$package->addPackageDepWithChannel('optional', 'Piece_ORM', 'pear.piece-framework.com', '0.3.0');
$package->addExtensionDep('optional', 'json');
$package->addMaintainer('lead', 'iteman', 'KUBO Atsuhiro', 'iteman@users.sourceforge.net');
$package->addMaintainer('developer', 'csakatoku', 'Chihiro Sakatoku', 'csakatoku@users.sourceforge.net');
$package->addMaintainer('developer', 'kumatch', 'KUMAKURA Yousuke', 'kumatch@users.sourceforge.net');
$package->addIgnore(array('package.php', 'package.xml', 'package2.xml'));
$package->addGlobalReplacement('package-info', '@package_version@', 'version');
$package->generateContents();
$package1 = &$package->exportCompatiblePackageFile1();

if (array_key_exists(1, $_SERVER['argv'])
    && $_SERVER['argv'][1] == 'make'
    ) {
    $package->writePackageFile();
    $package1->writePackageFile();
} else {
    $package->debugPackageFile();
    $package1->debugPackageFile();
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
?>
