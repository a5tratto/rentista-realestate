<?php
/*
    "Contact Form to Database Extension Edit" Copyright (C) 2011-2014 Simpson Software Studio LLC (email : mike@simpson-software-studio.com)

    This file is part of Contact Form to Database Extension Edit.

    Contact Form to Database Extension Edit is licensed under the terms of an End User License Agreement (EULA).
    You should have received a copy of the license along with Contact Form to Database Extension Edit
    (See the license.txt file).
*/

require_once(dirname(dirname(__FILE__)) . '/contact-form-7-to-database-extension/CF7DBPluginLifeCycle.php');

class CFDBEditPlugin extends CF7DBPluginLifeCycle {

    /**
     * @return string name of the main plugin file that has the header section with
     * "Plugin Name", "Version", "Description", "Text Domain", etc.
     */
    protected function getMainPluginFileName() {
        return 'contact-form-to-database-extension-edit.php';
    }

    protected function getPluginDir() {
        return dirname(__FILE__);
    }

    public function addActionsAndFilters() {

        add_action('cfdb_edit_enqueue', array(&$this, 'enqueue'));
        add_action('cfdb_edit_fnDrawCallbackJSON', array(&$this, 'fnDrawCallbackJSON'));
        add_action('cfdb_edit_fnDrawCallbackJsonForSC', array(&$this, 'fnDrawCallbackJsonForSC'), 10, 3);
        add_action('cfdb_edit_setup', array(&$this, 'setup'));

        add_action('wp_ajax_nopriv_cfdb-edit', array(&$this, 'ajaxEditEntry'));
        add_action('wp_ajax_cfdb-edit', array(&$this, 'ajaxEditEntry'));

        add_action('wp_ajax_nopriv_cfdb-getvalue', array(&$this, 'ajaxGetRawEntry'));
        add_action('wp_ajax_cfdb-getvalue', array(&$this, 'ajaxGetRawEntry'));

        add_action('wp_ajax_nopriv_cfdb-coledit', array(&$this, 'ajaxEditColumn'));
        add_action('wp_ajax_cfdb-coledit', array(&$this, 'ajaxEditColumn'));

        add_action('wp_ajax_nopriv_cfdb-getcolvalue', array(&$this, 'ajaxGetRawColValue'));
        add_action('wp_ajax_cfdb-getcolvalue', array(&$this, 'ajaxGetRawColValue'));

        add_action('wp_ajax_nopriv_cfdb-addColumn', array(&$this, 'ajaxAddColumn'));
        add_action('wp_ajax_cfdb-addColumn', array(&$this, 'ajaxAddColumn'));

        add_action('wp_ajax_nopriv_cfdb-deleteColumn', array(&$this, 'ajaxDeleteColumn'));
        add_action('wp_ajax_cfdb-deleteColumn', array(&$this, 'ajaxDeleteColumn'));

        add_action('wp_ajax_nopriv_cfdb-importcsv', array(&$this, 'ajaxImportCsv'));
        add_action('wp_ajax_cfdb-importcsv', array(&$this, 'ajaxImportCsv'));

        add_action('wp_ajax_nopriv_cfdb-renameform', array(&$this, 'ajaxRenameForm'));
        add_action('wp_ajax_cfdb-renameform', array(&$this, 'ajaxRenameForm'));

    }

    public function enqueue() {
        $version = $this->getVersion();
        //wp_enqueue_script('jeditable', plugins_url('js/jquery.jeditable.js?e=$version"', __FILE__), array('jquery'));
        wp_enqueue_script('jeditable', plugins_url('js/jquery.jeditable.mini.js?e=$version', __FILE__), array('jquery'));
        wp_enqueue_script('cfdb.edit', plugins_url("js/cfdb.edit.js?e=$version", __FILE__), array('jquery', 'jeditable'));
    }

    /**
     * @param CF7DBPlugin $plugin
     * @return void
     */
    public function setup($plugin) {
        ?>
    <div id="AddColumnDialog" style="display:none; background-color:#EEEEEE;">
        <input id="addColumnName" type="text" size="25" value=""/><br/>
        <input type="button" value="<?php _e('Cancel', 'contact-form-7-to-database-extension') ?>"
               onclick="jQuery('#AddColumnDialog').dialog('close');"/>
        <input id="addColumnOkButton" type="button" value=""
               onclick="addColumn();"/>
    </div>
    <div id="DeleteColumnDialog" style="display:none; background-color:#EEEEEE;">
        <select id="deleteColumnSelect"></select><br/>
        <input type="button" value="<?php _e('Cancel', 'contact-form-7-to-database-extension') ?>"
               onclick="jQuery('#DeleteColumnDialog').dialog('close');"/>
        <input id="deleteColumnOkButton" type="button" value=""
               onclick="deleteColumn();"/>
    </div>
    <script type="text/javascript" language="Javascript">
        jQuery('#edit_controls').html(
                '<input id="edit_cb" type="checkbox" onclick="oTable.fnDraw();"/>&nbsp;<label for="edit_cb">' +
                        jQuery('#edit_controls > a').text() + '</label>' +
                        '&nbsp;&nbsp;' +
                        '<input id="addColumnButton" type="button" value="">' +
                        '&nbsp;&nbsp;' +
                        '<input id="deleteColumnButton" type="button" value="">'
        );
        jQuery('#addColumnOkButton').val(addColumnLabelText);
        jQuery('#addColumnButton').val(addColumnLabelText).click(
                function() {
                    jQuery("#AddColumnDialog").dialog({ autoOpen: false, title: addColumnLabelText });
                    jQuery("#AddColumnDialog").dialog('open');
                    jQuery("#addColumnName").focus();
                });

        jQuery('#deleteColumnOkButton').val(deleteColumnLabelText);
        jQuery('#deleteColumnButton').val(deleteColumnLabelText).click(
                function() {
                    jQuery("#DeleteColumnDialog").dialog({ autoOpen: false, title: deleteColumnLabelText });
                    jQuery("#DeleteColumnDialog").dialog('open');
                    var url = '<?php echo $plugin->getFormFieldsAjaxUrlBase() ?>' + '<?php echo rawurlencode(html_entity_decode(stripslashes($_REQUEST['form_name']))); ?>';
                    jQuery.getJSON(url, function(json) {
                        var optionsHtml = '';
                        jQuery(json).each(function() {
                            if (this != 'Submitted' && this != 'submit_time') { // can't delete submit_time
                                optionsHtml += '<option value="' + this + '">' + this + '</option>';
                            }
                        });
                        jQuery('#deleteColumnSelect').html(optionsHtml).focus();
                    });
                } );

        function addColumn() {
            jQuery('#addColumnOkButton').attr('disabled', 'disabled');
            var formName = '<?php echo str_replace("'", "\\'", html_entity_decode(stripslashes($_REQUEST['form_name']))); ?>';
            var colName = jQuery('#addColumnName').val();
            jQuery.ajax({
                cache: false,
                type: 'POST',
                url: '<?php echo $this->getAdminUrlPrefix('admin-ajax.php') . 'action=cfdb-addColumn' ?>',
                data: { form_name : formName, column_name : colName},
                success: function(data, textStatus, jqXHR) {
                    jQuery('#AddColumnDialog').dialog('close');
                    location.reload();
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    alert('Error ' + textStatus + ': ' + textStatus);
                    jQuery('#addColumnOkButton').removeAttr('disabled');
                }
            });
        }
        function deleteColumn() {
            jQuery('#deleteColumnOkButton').attr('disabled', 'disabled');
            var formName = '<?php echo str_replace("'", "\\'", html_entity_decode(stripslashes($_REQUEST['form_name']))); ?>';
            var colName = jQuery('#deleteColumnSelect').val();
            jQuery.ajax({
                cache: false,
                type: 'POST',
                url: '<?php echo $this->getAdminUrlPrefix('admin-ajax.php') . 'action=cfdb-deleteColumn' ?>',
                data: { form_name : formName, column_name : colName},
                success: function(data, textStatus, jqXHR) {
                    jQuery('#DeleteColumnDialog').dialog('close');
                    location.reload();
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    alert('Error ' + textStatus + ': ' + textStatus);
                    jQuery('#deleteColumnOkButton').removeAttr('disabled')
                }
            });
        }
    </script>

    <?php

    }

    public function ajaxEditEntry() {
        $this->clearDebugOutput();
        header('Content-Type: text/plain; charset=UTF-8');
        $this->headerNoCache();
        if (!isset($_REQUEST['id']) || !$_REQUEST['id']) {
            echo '';
            die(1);
        }
        $value = stripslashes($_REQUEST['value']);
        try {
            require_once(dirname(dirname(__FILE__)) . '/contact-form-7-to-database-extension/ExportToHtmlTable.php');
            $export = new ExportToHtmlTable;

            if ($export->plugin->canUserDoRoleOption('CanChangeSubmitData')) {
                $key = explode(',', stripslashes($_REQUEST['id'])); // submit_time = $key[0], field_name = $key[1]
                $tableName = $export->plugin->getSubmitsTableName();

                global $wpdb;
                // Use "like" below to address: http://bugs.mysql.com/bug.php?id=30485
                $sql = "update `$tableName` set `field_value` = %s where `submit_time` like %s and `field_name` = %s";
                $sql = $wpdb->prepare($sql, $value, $key[0], $key[1]);
                $rowsUpdated = $wpdb->query($sql);
                if ($rowsUpdated === false) {
                    error_log(sprintf('CFDB Error: %s', $wpdb->last_error));
                }
                else if ($rowsUpdated === 0) {
                    $sql = "select distinct `form_name` as 'form_name' from `$tableName` where `submit_time` = %s limit 1";
                    $row = $wpdb->get_row($wpdb->prepare($sql, $key[0]));

                    $sql = "insert into `$tableName` (`submit_time`, `form_name`, `field_name`, `field_value`) values (%s, %s, %s, %s)";
                    $sql = $wpdb->prepare($sql, $key[0], $row->form_name, $key[1], $value);
                    $wpdb->query($sql);
                }

                $sql = "select `field_value`, `form_name`, `file` is not null and length(`file`) > 0 as 'is_file' from `$tableName` where `submit_time` like %s and `field_name` = %s";
                $sql = $wpdb->prepare($sql, $key[0], $key[1]);
                $row = $wpdb->get_row($sql);
                if ($row) {
                    $value = $export->rawValueToPresentationValue(
                            $row->field_value,
                            ($export->plugin->getOption('ShowLineBreaksInDataTable') != 'false'),
                            ($row->is_file != 0),
                            $key[0],
                            $row->form_name,
                            $key[1]);
                }
            }
            else {
                die(1);
            }
        }
        catch (Exception $ex) {
            error_log(sprintf('CFDB Error: %s:%s %s  %s', $ex->getFile(), $ex->getLine(), $ex->getMessage(), $ex->getTraceAsString()));
        }

        echo $value;
        die();
    }

    public function ajaxGetRawEntry() {
        $this->clearDebugOutput();
        header('Content-Type: text/plain; charset=UTF-8');
        $this->headerNoCache();

        if (!isset($_REQUEST['id']) || !$_REQUEST['id']) {
            echo '';
            die(1);
        }
        $value = '';
        try {
            require_once(dirname(dirname(__FILE__)) . '/contact-form-7-to-database-extension/CF7DBPlugin.php');
            $cfdb = new CF7DBPlugin;
            if ($cfdb->canUserDoRoleOption('CanChangeSubmitData')) {
                global $wpdb;
                $delim = (strpos($_REQUEST['id'], ',') !== false) ? ',' : '%2C';
                $key = explode($delim, stripslashes($_REQUEST['id']));

                $tableName = $cfdb->getSubmitsTableName();
                $sql = "select `field_value` from `$tableName` where `submit_time` = %F and `field_name` = '%s'";

                //$value = $wpdb->get_var($wpdb->prepare($sql, $key[0], $key[1]));
                $sql = $wpdb->prepare($sql, $key[0], $key[1]);
                $rows = $wpdb->get_results($sql);
                foreach ($rows as $aRow) {
                    if ($aRow->field_value != '') {
                        $value = $aRow->field_value;
                        break;
                    }
                }
            }
            else {
                die(1);
            }
        }
        catch (Exception $ex) {
            error_log(sprintf('CFDB Error: %s:%s %s  %s', $ex->getFile(), $ex->getLine(), $ex->getMessage(), $ex->getTraceAsString()));
        }

        echo $value;
        die();
    }

    public function ajaxEditColumn() {
        $this->clearDebugOutput();
        header('Content-Type: text/plain; charset=UTF-8');
        $this->headerNoCache();
        $delim = (strpos($_REQUEST['id'], ',') !== false) ? ',' : '%2C';
        $key = explode($delim, stripslashes($_REQUEST['id'])); // form_name = $key[0], field_name = $key[1]
        $returnName = $key[1];
        try {
            require_once(dirname(dirname(__FILE__)) . '/contact-form-7-to-database-extension/CF7DBPlugin.php');
            $cfdb = new CF7DBPlugin;
            if ($cfdb->canUserDoRoleOption('CanChangeSubmitData')) {
                global $wpdb;

                $tableName = $cfdb->getSubmitsTableName();
                $sql = "update `$tableName` set `field_name` = %s where `form_name` = %s and `field_name` = %s";
                if ($wpdb->query($wpdb->prepare($sql, stripslashes($_REQUEST['value']), $key[0], $key[1]))) {
                    $returnName = stripslashes($_REQUEST['value']);
                }
            }
            else {
                die(1);
            }
        }
        catch (Exception $ex) {
            error_log(sprintf('CFDB Error: %s:%s %s  %s', $ex->getFile(), $ex->getLine(), $ex->getMessage(), $ex->getTraceAsString()));
        }

        echo $returnName;
        die();

    }

    public function ajaxGetRawColValue() {
        $this->clearDebugOutput();
        header('Content-Type: text/plain; charset=UTF-8');
        $this->headerNoCache();

        $id = stripslashes($_REQUEST['id']);
        $delim = (strpos($id, ',') !== false) ? ',' : '%2C';
        $colName = substr($id, strpos($id, $delim) + count($delim));

        echo $colName;
        die();
    }

    public function ajaxAddColumn() {
        $this->clearDebugOutput();
        $this->headerNoCache();

        $formName = html_entity_decode(stripslashes($_REQUEST['form_name']));
        $columnName = html_entity_decode(stripslashes($_REQUEST['column_name']));
        if (!$formName || !$columnName) {
            die(1);
        }
        try {
            require_once(dirname(dirname(__FILE__)) . '/contact-form-7-to-database-extension/CF7DBPlugin.php');
            $cfdb = new CF7DBPlugin;
            if ($cfdb->canUserDoRoleOption('CanChangeSubmitData')) {
                global $wpdb;
                $tableName = $cfdb->getSubmitsTableName();
                $sql = "insert into `$tableName` (`submit_time`, `form_name`, `field_name`, `field_value` ) select distinct `submit_time`, `form_name`, '$columnName', '' from `$tableName` where `form_name` = %s";
                $wpdb->query($wpdb->prepare($sql, $formName, $columnName));
            }
            else {
                die(1);
            }
        }
        catch (Exception $ex) {
            error_log(sprintf('CFDB Error: %s:%s %s  %s', $ex->getFile(), $ex->getLine(), $ex->getMessage(), $ex->getTraceAsString()));
            die(1);
        }
        die();
    }

    public function ajaxDeleteColumn() {
        $this->clearDebugOutput();
        $this->headerNoCache();

        $formName = html_entity_decode(stripslashes($_REQUEST['form_name']));
        $columnName = html_entity_decode(stripslashes($_REQUEST['column_name']));
        if (!$formName || !$columnName) {
            die(1);
        }
        try {
            require_once(dirname(dirname(__FILE__)) . '/contact-form-7-to-database-extension/CF7DBPlugin.php');
            $cfdb = new CF7DBPlugin;
            if ($cfdb->canUserDoRoleOption('CanChangeSubmitData')) {
                global $wpdb;
                $tableName = $cfdb->getSubmitsTableName();
                $sql = "delete from `$tableName` where `form_name` = %s and `field_name` = %s";
                $wpdb->query($wpdb->prepare($sql, $formName, $columnName));
            }
            else {
                die(1);
            }
        }
        catch (Exception $ex) {
            error_log(sprintf('CFDB Error: %s:%s %s  %s', $ex->getFile(), $ex->getLine(), $ex->getMessage(), $ex->getTraceAsString()));
            die(1);
        }
        die();
    }

    public function fnDrawCallbackJSON($tableHtmlId) {
        $urlPrefix = $this->getAdminUrlPrefix('admin-ajax.php');
        $cfdbEditUrl = $urlPrefix . 'action=cfdb-edit';
        $cfdbGetValueUrl = $urlPrefix . 'action=cfdb-getvalue';
        $cfdbColEditUrl = $urlPrefix . 'action=cfdb-coledit';
        $cfdbGetColumnValueUrl = $urlPrefix . 'action=cfdb-getcolvalue';
        $loadImg = plugins_url('img/load.gif', __FILE__);
        ?>
         ,  "fnDrawCallback" : function() {
                if (jQuery('#edit_cb').is(':checked')) {
                    cfdbEditable(
                        <?php echo "'$tableHtmlId'" ?>,
                        <?php echo "'$cfdbEditUrl'" ?>,
                        <?php echo "'$cfdbGetValueUrl'" ?>,
                        <?php echo "'$cfdbColEditUrl'" ?>,
                        <?php echo "'$cfdbGetColumnValueUrl'" ?>,
                        <?php echo "'$loadImg'" ?>
                    );
                }
                else {
                    jQuery('#<?php echo $tableHtmlId ?> td:not([title="Submitted"]) > div').addClass('non_edit').removeClass('edit').unbind('click.editable');
                    jQuery('#<?php echo $tableHtmlId ?> th:not([title="Submitted"]) > div > div').addClass('non_edit').removeClass('edit').unbind('click.editable');
                }
            }
        <?php
    }

    /**
     * @param $tableHtmlId string datatable id to make cells editable in
     * @param $editMode string 'cells' to make cells editable but not column headers, 'true' to make both editable
     * @param $editColumns null|array of column names to set as editable
     */
    public function fnDrawCallbackJsonForSC($tableHtmlId, $editMode = 'true', $editColumns = null) {
        $urlPrefix = $this->getAdminUrlPrefix('admin-ajax.php');
        $cfdbEditUrl = $urlPrefix . 'action=cfdb-edit';
        $cfdbGetValueUrl = $urlPrefix . 'action=cfdb-getvalue';
        $cfdbColEditUrl = $urlPrefix . 'action=cfdb-coledit';
        $cfdbGetColumnValueUrl = $urlPrefix . 'action=cfdb-getcolvalue';
        $loadImg = plugins_url('img/load.gif', __FILE__);
        $columns = null;
        if (is_array($editColumns) && !empty($editColumns)) {
            $columns = $editColumns;
        }
        $columns = json_encode($columns);
        
        if ($editMode == 'cells') {
            echo ",  \"fnDrawCallback\" : function() { cfdbEditableCells('$tableHtmlId','$cfdbEditUrl','$cfdbGetValueUrl','$loadImg', $columns); }";
        } else {
            echo ",  \"fnDrawCallback\" : function() { cfdbEditable('$tableHtmlId','$cfdbEditUrl','$cfdbGetValueUrl','$cfdbColEditUrl','$cfdbGetColumnValueUrl','$loadImg', $columns); }";
        }
    }

    public function ajaxImportCsv() {
        $this->clearDebugOutput();
        $this->headerNoCache();

        $cfdb = new CF7DBPlugin;
        if (!$cfdb->canUserDoRoleOption('CanChangeSubmitData')) {
            echo "Permission denied";
            die(1);
        }

        // Array ( [action] => cfdb-importcsv [file] => alanine.csv [into] => into [newformname] =>       [form] => File Upload [Import] => import )
        // Array ( [action] => cfdb-importcsv [file] => alanine.csv [into] => new  [newformname] => mike  [form] =>             [Import] => import )

        //print_r($_REQUEST);
        //print_r($_FILES);

        if (!isset($_FILES['file']) || !$_FILES['file']) {
            echo 'No file uploaded';
            die(1);
        }

        if (!isset($_REQUEST['into']) || !$_REQUEST['into']) {
            echo 'Missing input to indicate new or existing form';
            die(1);
        }

        $formName = null;
        if ($_REQUEST['into'] == 'new') {
            if (!isset($_REQUEST['newformname']) || !$_REQUEST['newformname']) {
                echo 'No new form name set';
                die(1);
            }
            $formName = stripslashes($_REQUEST['newformname']);
        }

        if ($_REQUEST['into'] == 'into') {
            if (!isset($_REQUEST['form']) || !$_REQUEST['form']) {
                echo 'No form chosen';
                die(1);
            }
            $formName = stripslashes($_REQUEST['form']);
        }

        $delim = ',';
        if (isset($_REQUEST['delim']) && $_REQUEST['delim']) {
            $delim = stripslashes($_REQUEST['delim']);
        }
        $encl = '"';
        if (isset($_REQUEST['encl']) && $_REQUEST['encl']) {
            $encl = stripslashes($_REQUEST['encl']);
        }
        $esc = '\\';
        if (isset($_REQUEST['esc']) && $_REQUEST['esc']) {
            $esc = stripslashes($_REQUEST['esc']);
        }

        if (!$formName) {
            echo 'No form name';
            die(1);
        }

        // Array ( [file] => Array ( [name] => alanine.csv [type] => application/octet-stream [tmp_name] => /tmp/phpJW3RIm [error] => 0 [size] => 1214 ) )
        ini_set('auto_detect_line_endings', true); // recognize Mac \r as a valid line ending
        $handle = fopen($_FILES['file']['tmp_name'], 'r');
        if (!$handle) {
            echo 'Cannot read file: ' . esc_html($_FILES['file']['tmp_name']);
            die(1);
        }

        global $wpdb;
        require_once(dirname(dirname(__FILE__)) . '/contact-form-7-to-database-extension/CF7DBPlugin.php');
        $cfdb = new CF7DBPlugin();
        $tableName = $cfdb->getSubmitsTableName();
        $parametrizedQuery = "INSERT INTO `$tableName` (`submit_time`, `form_name`, `field_name`, `field_value`, `field_order`) VALUES (%s, %s, %s, %s, %s)";

        $row = 1;
        $headerRow = null;
        $headerCount = 0;
        $generatedSubmitTime = function_exists('microtime') ? microtime(true) : time();
        $seenSubmitTimes = array();
        while (($data = fgetcsv($handle, null, $delim, $encl, $esc)) !== false) {
            $rowSize = count($data);
            if (!$data[$rowSize - 1]) {
                // bogus blank at end of row due to trailing comma
                array_pop($data);
                $rowSize--;
            }

            if ($row == 1) {
                $headerRow = $data;
                $headerCount = $rowSize;
                //echo "header: " ; print_r($headerRow); echo "<br/>";
            }
            else {
                $importData = array('submit_time' => '');
                for ($idx = 0; $idx < $rowSize; $idx++) {
                    $fieldName = ($idx < $headerCount) ? $headerRow[$idx] : 'field' . $idx;
                    $importData[$fieldName] = $data[$idx];
                }
                if ($importData['submit_time'] == '') {
                    $generatedSubmitTime += 0.001;
                    $importData['submit_time'] = $generatedSubmitTime;
                } else {
                    $importData['submit_time'] = $this->deduplicateSubmitTime($importData['submit_time'], $seenSubmitTimes);
                }

                //print_r($importData); echo "\n<br/>";
                $order = -1;

                $bomSubmitted = chr(239) . chr(187) . chr(191) . '"Submitted"'; // Case of importing an exported file
                foreach ($importData as $fieldName => $value) {
                    if ($fieldName == 'submit_time' || $fieldName == 'Submitted' || $fieldName == $bomSubmitted) {
                        continue;
                    }

                    ++$order;
                    $fieldOrder = $order;
                    if ($fieldName == 'Submitted Login') {
                        $fieldOrder = ($order < 9999) ? 9999 : $order; // large order num to try to make it always next-to-last

                    }
                    else if ($fieldName == 'Submitted From') {
                        $fieldOrder = ($order < 10000) ? 10000 : $order; // large order num to try to make it always last
                    }

                    //echo $importData['submit_time'] . " $formName:$fieldOrder : $fieldName = $value<br/>";
                    $wpdb->query($wpdb->prepare($parametrizedQuery,
                        $importData['submit_time'],
                        $formName,
                        $fieldName,
                        $value,
                        $fieldOrder));
                }
            }
            $row++;
        }
        fclose($handle);

        $url = $this->getAdminUrlPrefix('admin.php') . 'page=CF7DBPluginSubmissions&form_name=' . urlencode($formName);
        echo ($row - 2) . " rows processed into form <a href=\"$url\">" . esc_html($formName) . "</a>";
        $backUrl = $this->getAdminUrlPrefix('admin.php') . 'page=CF7DBPluginImport';
        printf('<br/><a href="%s">%s</a>', $backUrl, 'Back');

        die();
    }

    /**
     * @param $submitTime float
     * @param $seenSubmitTimes array
     * @return float
     */
    public function deduplicateSubmitTime($submitTime, &$seenSubmitTimes) {
        while (in_array($submitTime, $seenSubmitTimes)) {
            $submitTime += 0.001;
        }
        $cfdb = new CF7DBPlugin;
        $tableName = $cfdb->getSubmitsTableName();
        $parametrizedQuery = "SELECT COUNT(`submit_time`) FROM `$tableName` WHERE `submit_time` = %F LIMIT 1";
        global $wpdb;
        $count = $wpdb->get_var($wpdb->prepare($parametrizedQuery,$submitTime));
        if ($count > 0) {
            $seenSubmitTimes[] = $submitTime;
            return $this->deduplicateSubmitTime($submitTime + 0.001, $seenSubmitTimes);
        } else {
            $seenSubmitTimes[] = $submitTime;
        }
        return $submitTime;
    }

    public function ajaxRenameForm() {
        $this->clearDebugOutput();
        $this->headerNoCache();

        $cfdb = new CF7DBPlugin;
        if (!$cfdb->canUserDoRoleOption('CanChangeSubmitData')) {
            die(1);
        }

        if (!isset($_REQUEST['form']) || !$_REQUEST['form']) {
            echo 'No form name set';
            die(1);
        }
        if (!isset($_REQUEST['newformname']) || !$_REQUEST['newformname']) {
            echo 'No new form name set';
            die(1);
        }

        global $wpdb;
        $tableName = $cfdb->getSubmitsTableName();
        $parametrizedQuery = "UPDATE `$tableName` SET `form_name` = %s WHERE `form_name` = %s";
        $result =  $wpdb->query($wpdb->prepare($parametrizedQuery, stripslashes($_REQUEST['newformname']), stripslashes($_REQUEST['form'])));
        if ($result == false) {
            echo 'Failed to update';
        } else {
            $url = $this->getAdminUrlPrefix('admin.php') . 'page=CF7DBPluginSubmissions&form_name=' . stripslashes($_REQUEST['newformname']);
            printf('Form "%s" renamed to <a href="%s">"%s"</a>.', htmlentities(stripslashes($_REQUEST['form'])), $url, htmlentities(stripslashes($_REQUEST['newformname'])));
            $backUrl = $this->getAdminUrlPrefix('admin.php') . 'page=CF7DBPluginImport';
            printf('<br/><a href="%s">%s</a>', $backUrl, 'Back');
        }

        die();
    }

    public function getAdminUrlPrefix($path) {
        $url = admin_url($path);
        if (strpos($url, '?') === false) {
            return $url . '?';
        } else {
            return $url . '&';
        }
    }

    public function clearDebugOutput() {
        if (ob_get_length()) {
            // eliminate any debug output from polluting the ajax return value
            // https://codex.wordpress.org/AJAX_in_Plugins
            ob_clean();
        }
    }

    public function headerNoCache() {
        header("Pragma: no-cache");
        header("Expires: Thu, 01 Jan 1970 00:00:00 GMT");
    }

}