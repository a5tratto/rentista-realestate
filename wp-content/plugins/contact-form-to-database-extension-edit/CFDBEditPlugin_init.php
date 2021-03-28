<?php
/*
    "Contact Form to Database Extension Edit" Copyright (C) 2011-2013 Simpson Software Studio LLC (email : info@simpson-software-studio.com)

    This file is part of Contact Form to Database Extension Edit.

    Contact Form to Database Extension Edit is licensed under the terms of an End User License Agreement (EULA).
    You should have received a copy of the license along with Contact Form to Database Extension Edit
    (See the license.txt file).
*/

function CFDBEditPlugin_init($file) {

    require_once('CFDBEditPlugin.php');
    $aPlugin = new CFDBEditPlugin();

    // Install the plugin
    // NOTE: this file gets run each time you *activate* the plugin.
    // So in WP when you "install" the plugin, all that does it dump its files in the plugin-templates directory
    // but it does not call any of its code.
    // So here, the plugin tracks whether or not it has run its install operation, and we ensure it is run only once
    // on the first activation
    if (!$aPlugin->isInstalled()) {
        $aPlugin->install();
    }
    else {
        // Perform any version-upgrade activities prior to activation (e.g. database changes)
        $aPlugin->upgrade();
    }

    // Add callbacks to hooks
    $aPlugin->addActionsAndFilters();

    if (!$file) {
        $file = __FILE__;
    }
    // Register the Plugin Activation Hook
    register_activation_hook($file, array(&$aPlugin, 'activate'));


    // Register the Plugin Deactivation Hook
    register_deactivation_hook($file, array(&$aPlugin, 'deactivate'));

}