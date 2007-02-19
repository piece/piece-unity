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

$version = '0.11.0';
$apiVersion = '0.7.0';
$releaseStability = 'beta';
$notes = 'Hi all,

A new release of Piece_Unity is now available.

This release includes a feature named "Fallback View" which can be used for rendering a fallback view if an error occured while rendering with a specified view, and a feature named "Plug-in Aliases" which can be used multiple aliases as the name of a plug-in class by setting multiple prefixes.

Also this release includes a new plug-in "ConfiguratorChain" so that it can invoke multiple configurators. In the release after next, "ConfiguratorChain" will become the default configurator instead of "KernelConfigurator".

And also other enhancements and two fixes are included. See the following release notes for details.

## Enhancements ##

### Kernel ###

##### Piece_Unity_Plugin_Factory #####

- Added a feature named "Plug-in Aliases" so that this feature can be used multiple aliases as the name of a plug-in class by setting multiple prefixes. (Ticket #40)

##### Piece_Unity_URL #####

- Added removeQueryString() to remove a name/value pair from the query string.

### Plug-ins ###

##### Renderer_Flexy, Renderer_HTML, Renderer_PHP, Renderer_Smarty #####

- Added a feature named "Fallback View". (Ticket #37)
  If an error occured while rendering with a specified view and useFallback is true, a fallback view will be rendered.

##### KernelConfigurator #####

- Added a configuration point "pluginPrefixes" for using "Plug-in Aliases".
- Added two configuration point "validationValidatorPrefixes" and "validationFilterPrefixes" for using "Validator Aliases" and "Filter Aliases". (Ticket #43)

##### Renderer_Redirection #####

- Added a feature so that __eventNameKey is replaced with the event name key. (Ticket #38)

##### View #####

- Added a feature named "Self Notation" for redirection to an entry point itself. (Ticket #39)

##### ConfiguratorChain, Configurator_Env, Configurator_Event, Configurator_Plugin, Configurator_Request, Configurator_Validation #####

- Added a new plug-in "ConfiguratorChain" so that it can invoke multiple configurators. (Ticket #45)
  In the release after next, "ConfiguratorChain" will become the default configurator instead of "KernelConfigurator".

### Example Applications ###

- Changed so as to use of ConfigurationChain.

## Defect Fixes ##

### Kernel ###

##### Piece_Unity_URL #####

- Fixed the problem so that EZweb mobile phone cannot redirect with Renderer_Redirection plug-in. (Ticket #44)

### Plug-ins ###

##### Renderer_Redirection #####

- Fixed the problem that the renderer cannot redirect to HTTPS URL.';

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
$package->addPackageDepWithChannel('required', 'Piece_Right', 'pear.piece-framework.com', '1.5.0');
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
