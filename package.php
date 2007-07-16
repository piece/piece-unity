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
 * @copyright  2006-2007 KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    SVN: $Id$
 * @since      File available since Release 0.1.0
 */

require_once 'PEAR/PackageFileManager2.php';

PEAR::staticPushErrorHandling(PEAR_ERROR_CALLBACK, create_function('$error', 'var_dump($error); exit();'));

$releaseVersion = '1.0.0';
$releaseStability = 'stable';
$apiVersion = '0.7.0';
$apiStability = 'stable';
$notes = 'This is the first stable release of Piece_Unity.

What\'s New in Piece_Unity 1.0.0

 * Components: A component is a package that consists of plug-ins, services, GUI elements (HTML templates, images, scripts, etc.), flow definition files, action classes, and entry points, etc.. Many plug-ins are extracted as each Piece_Unity_Component_Xxx package. See the following release notes for details.
 * Services: A service is one or more classes that provides useful operations for client use.
 * The Piece_Unity_Service_FlowAction class: The Piece_Unity_Service_FlowAction class can be used as the base class for Piece_Flow actions.

See the following release notes for details.

Enhancements
============ 

Plug-ins:

- Added error handling. (Interceptor_SessionStart)
- Moved the process of preloading Dispatcher_Continuation plug-in from Configurator_Plugin to Interceptor_SessionStart.
- A improvement for restoring action instances in session. (Dispatcher_Continuation, Interceptor_SessionStart, Piece_Unity_Session_Preload)
- Changed the error type on all Piece_Unity_Error::pushError() calls from "warning" to "exception". (ConfiguratorChain, InterceptorChain, OutputBufferStack)
- Moved the following plug-ins to components/. These plug-ins will be packaged as each Piece_Unity_Component_Xxx package.
  * Interceptor_Authentication
  * OutputFilter_ContentLength
  * Renderer_Flexy
  * Renderer_JSON
  * OutputFilter_JapaneseZ2H
  * KernelConfigurator (obsoleted)
  * Interceptor_NullByteAttackPreventation
  * Configurator_PieceORM
  * Interceptor_ProxyBasePath (obsoleted)
  * Renderer_Smarty

Services:

- Added the base class for Piece_Flow actions. (Piece_Unity_Service_FlowAction)

Kernel:

- Changed factory() to throw an exception if the configuration directory or the configuration file not found. (Ticket #66) (Piece_Unity_Config_Factory)
- Added a class loader. (Piece_Unity_ClassLoader)
- Updated an exception to be raised when an undefined extension point is used. (Ticket #72) (Piece_Unity_Plugin_Common)
- Updated getResults() so that a Piece_Right_Results object can be get by a validation set name. (Ticket #70) (Piece_Unity_Validation)
- Improved error handling so as to recognize the place where the exception is raised. (Piece_Unity_Plugin_Common)
- Added hasResults() to check whether or not the Piece_Right_Results object of the given validation set or the latest validation exists. (Piece_Unity_Validation)';

$package = new PEAR_PackageFileManager2();
$package->setOptions(array('filelistgenerator' => 'svn',
                           'changelogoldtonew' => false,
                           'simpleoutput'      => true,
                           'baseinstalldir'    => '/',
                           'packagefile'       => 'package.xml',
                           'packagedirectory'  => '.',
                           'dir_roles'         => array('data' => 'data',
                                                        'tests' => 'test',
                                                        'docs' => 'doc'),
                           'ignore'            => array('package.php', 'package.xml', 'components/'))
                     );

$package->setPackage('Piece_Unity');
$package->setPackageType('php');
$package->setSummary('A stateful and secure web application framework for PHP');
$package->setDescription('Piece_Unity is a stateful and secure web application framework for PHP.

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
$package->addPackageDepWithChannel('required', 'Piece_Flow', 'pear.piece-framework.com', '1.9.0');
$package->addPackageDepWithChannel('required', 'Cache_Lite', 'pear.php.net', '1.7.0');
$package->addPackageDepWithChannel('required', 'PEAR', 'pear.php.net', '1.4.3');
$package->addPackageDepWithChannel('required', 'Net_URL', 'pear.php.net', '1.0.14');
$package->addPackageDepWithChannel('required', 'Piece_Right', 'pear.piece-framework.com', '1.5.0');
$package->addPackageDepWithChannel('optional', 'Stagehand_TestRunner', 'pear.piece-framework.com', '0.5.0');
$package->addPackageDepWithChannel('optional', 'PHPUnit', 'pear.phpunit.de', '1.3.2', '1.3.2');
$package->addMaintainer('lead', 'iteman', 'KUBO Atsuhiro', 'iteman@users.sourceforge.net');
$package->addGlobalReplacement('package-info', '@package_version@', 'version');
$package->generateContents();

if (array_key_exists(1, $_SERVER['argv'])
    && $_SERVER['argv'][1] == 'make'
    ) {
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
?>
