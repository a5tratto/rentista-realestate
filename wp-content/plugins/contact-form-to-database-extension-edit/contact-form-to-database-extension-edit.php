<?php
/*
   Plugin Name: Contact Form DB Editor
   Plugin URI: http://wordpress.org/extend/plugins/contact-form-7-to-database-extension/
   Version: 1.5.2
   Author: Michael Simpson
   Description: Adds editing capability to Contact Form 7 to Database Extension
   Text Domain: contact-form-7-to-database-extension-edit
   License: EULA. See license.txt
  */

/*
    "Contact Form to Database Extension Edit" Copyright (C) 2011-2014 Simpson Software Studio LLC (email : info@simpson-software-studio.com)

    This file is part of Contact Form to Database Extension Edit.

    Contact Form to Database Extension Edit is licensed under the terms of an End User License Agreement (EULA).
    You should have received a copy of the license along with Contact Form to Database Extension Edit
    (See the license.txt file).
*/

$CF7DBEditPlugin_minCfdbVersion = 2.0;

function CF7DBEditPlugin_noticeCfdbNotInstalled() {
    echo '<div class="updated fade">' .
    __('Error: plugin "Contact Form to DB Extension <u>Edit</u>" requires that plugin "Contact Form to DB Extension" to be installed and activated: ' .
            '<a href="https://github.com/mdsimpson/contact-form-7-to-database-extension/releases" target="_blank>">Download CFDB</a>',
       'contact-form-7-to-database-extension') .
    '</div>';
}

function CF7DBEditPlugin_noticeCfdbWrongDirectory() {
    echo '<div class="updated fade">' .
    __('Error: plugin "Contact Form to DB Extension <u>Edit</u>" found that plugin "Contact Form to DB Extension" is installed in the wrong directory. This happens when the wrong .zip package is downloaded from GitHub. To fix, please remove CFDB, then download and install a prepared released from: ' .
            '<a href="https://github.com/mdsimpson/contact-form-7-to-database-extension/releases" target="_blank>">CFDB Release Downloads</a>',
       'contact-form-7-to-database-extension') .
    '</div>';
}

function CF7DBEditPlugin_noticeCfdbVersionWrong() {
    global $CF7DBEditPlugin_minCfdbVersion;
    echo '<div class="updated fade">' .
    __('Error: plugin "Contact Form to DB Extension <u>Edit</u>" requires that plugin "Contact Form to DB Extension" be upgraded to version ',
       'contact-form-7-to-database-extension') . $CF7DBEditPlugin_minCfdbVersion .
    '</div>';
}

function CF7DBEditPlugin_CheckDeps() {

    $minimalRequiredPhpVersion = '5.0';
    if (version_compare(phpversion(), $minimalRequiredPhpVersion) < 0) {
        return false;
    }

    $pluginsDir = dirname(dirname(__FILE__));
    $cfdbDir = $pluginsDir . '/contact-form-7-to-database-extension';
    if (!is_dir($cfdbDir)) {
        $cfdbDir_master = $pluginsDir . '/contact-form-7-to-database-extension-master'; // GitHub branch
        if (is_dir($cfdbDir_master)) {
            add_action('admin_notices', 'CF7DBEditPlugin_noticeCfdbWrongDirectory');
        }
        else {
            add_action('admin_notices', 'CF7DBEditPlugin_noticeCfdbNotInstalled');
        }
        return false;
    }

    global $CF7DBEditPlugin_minCfdbVersion;
    $cfdbVersion = get_option('CF7DBPlugin__version');
    if (version_compare($cfdbVersion, $CF7DBEditPlugin_minCfdbVersion) < 0) {
        add_action('admin_notices', 'CF7DBEditPlugin_noticeCfdbVersionWrong');
        return false;
    }

    return true;
}

/**
 * Initialize internationalization (i18n) for this plugin.
 * References:
 *      http://codex.wordpress.org/I18n_for_WordPress_Developers
 *      http://www.wdmac.com/how-to-create-a-po-language-translation#more-631
 * @return void
 */
//function CF7DBPluginEdit_i18n_init() {
//    $pluginDir = dirname(plugin_basename(__FILE__));
//    load_plugin_textdomain('contact-form-7-to-database-extension-edit', false, $pluginDir . '/languages/');
//}


//////////////////////////////////
// Run initialization
/////////////////////////////////

// First initialize i18n
//CF7DBPluginEdit_i18n_init();


// Check Dependencies.
// If it is successful, continue with initialization for this plugin
if (CF7DBEditPlugin_CheckDeps()) {
    // Only load and run the init function if we know PHP version can parse it
    include_once('CFDBEditPlugin_init.php');
    CFDBEditPlugin_init(__FILE__);
}
