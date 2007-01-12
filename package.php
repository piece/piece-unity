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

$version = '0.10.0';
$apiVersion = '0.7.0';
$releaseStability = 'beta';
$notes = 'Hi all,

A new release of Piece_Unity is now available.
This release includes a nice feature for the layout system. It can turn off the use of the layout system by the HTTP Accept header. And also other enhancements are included.

See the following release notes for details.

## Enhancements ##

### Plug-ins ###

##### Renderer_HTML #####

- Added a new configuration point \'turnOffLayoutByHTTPAccept\' on Renderer_HTML.
  If this option is true, the useLayout option is turned off by the HTTP Accept header.

##### Dispatcher_Continuation #####

- Changed the default value of bindActionsWithFlowExecution to true.

##### Interceptor_SessionStart #####

- Added a feature to import a session ID from the request parameters when a new configuration point importSessionIDFromRequest is true. (Ticket #34)

### Example Applications ###

- Updated templates and javascripts of the sample application.';

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

Piece_Unity is a framework against the background of layered architecture, as of now, focuses on the presentation layer.

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
$package->addPackageDepWithChannel('required', 'Piece_Right', 'pear.piece-framework.com', '1.4.0');
$package->addPackageDepWithChannel('optional', 'Stagehand_TestRunner', 'pear.piece-framework.com', '0.4.0');
$package->addPackageDepWithChannel('optional', 'HTML_Template_Flexy', 'pear.php.net', '1.2.4');
$package->addPackageDepWithChannel('optional', 'Smarty', 'pearified.com', '1.6.8');
$package->addPackageDepWithChannel('optional', 'HTML_AJAX', 'pear.php.net', '0.5.0');
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
