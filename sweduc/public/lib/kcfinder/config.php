<?php

session_start();
/** This file is part of KCFinder project
  *
  *      @desc Base configuration file
  *   @package KCFinder
  *   @version 2.51
  *    @author Pavel Tzonkov <pavelc@users.sourceforge.net>
  * @copyright 2010, 2011 KCFinder Project
  *   @license http://www.opensource.org/licenses/gpl-2.0.php GPLv2
  *   @license http://www.opensource.org/licenses/lgpl-2.1.php LGPLv2
  *      @link http://kcfinder.sunhater.com
  */

// IMPORTANT!!! Do not remove uncommented settings in this file even if
// you are using session configuration.
// See http://kcfinder.sunhater.com/install for setting descriptions

$_CONFIG = [
    'disabled' => false,
    'denyZipDownload' => true,
    'denyUpdateCheck' => false,
    'denyExtensionRename' => true,
    'deniedExts' => "exe com msi bat php phps phtml php2 php3 php4 php5 php7 cgi pl",
    'theme' => "oxygen",
    'uploadURL' => $_SESSION['KCFINDER']['uploadURL'],
    'uploadDir' => "",
    'dirPerms' => 0755,
    'filePerms' => 0644,
    'access' => ['files' => ['upload' => false, 'delete' => true, 'copy' => true, 'move' => true, 'rename' => true], 'dirs' => ['create' => true, 'delete' => true, 'rename' => true]],
    'types' => [
        // CKEditor & FCKEditor types
        // 'files'   =>  "",
        // 'flash'   =>  "",
        'images'  =>  "*img",
    ],
    'filenameChangeChars' => [],
    'dirnameChangeChars' => [],
    'mime_magic' => "",
    'maxImageWidth' => 0,
    'maxImageHeight' => 0,
    'thumbWidth' => 100,
    'thumbHeight' => 100,
    'thumbsDir' => ".thumbs",
    'jpegQuality' => 90,
    'cookieDomain' => "",
    'cookiePath' => "",
    'cookiePrefix' => 'KCFINDER_',
    // THE FOLLOWING SETTINGS CANNOT BE OVERRIDED WITH SESSION CONFIGURATION
    '_check4htaccess' => false,
    //'_tinyMCEPath' => "/tiny_mce",
    '_sessionVar' => &$_SESSION['KCFINDER'],
    '_sessionLifetime' => 30,
    '_sessionDir' => "/lib/kcfinder",
    '_sessionDomain' => ".managerlearn.com.br",
    '_sessionPath' => "/lib/kcfinder",
];
