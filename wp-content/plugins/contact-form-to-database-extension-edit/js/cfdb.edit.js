/*
 "Contact Form to Database Extension Edit" Copyright (C) 2011-2015 Simpson Software Studio LLC (email : info@simpson-software-studio.com)

 This file is part of Contact Form to Database Extension Edit.

 Contact Form to Database Extension Edit is licensed under the terms of an End User License Agreement (EULA).
 You should have received a copy of the license along with Contact Form to Database Extension Edit
 (See the license.txt file).
 */


/**
 * Admin grid view and [cfdb-datatable]
 * @param tableHtmlId html id of table element containing data to make it editable
 * @param cfdbEditUrl url to write back the new value in the table cell text area
 * @param cfdbGetValueUrl url to fetch the value of the cell into the editable textarea
 * @param cfdbColEditUrl url to write back the new value in the table column text area
 * @param cfdbGetColumnValueUrl url to fetch the value of the column into the editable textarea
 * @param loadImg image to display while fetching/saving data
 * @param columns array|null of columns to enable editing on. Null means all.
 */
function cfdbEditable(tableHtmlId, cfdbEditUrl, cfdbGetValueUrl, cfdbColEditUrl, cfdbGetColumnValueUrl, loadImg, columns) {
    cfdbEditableCells(tableHtmlId, cfdbEditUrl, cfdbGetValueUrl, loadImg, columns);
    cfdbEditableColumnHeaders(tableHtmlId, cfdbColEditUrl, cfdbGetColumnValueUrl, loadImg, columns);
}

/**
 * Admin grid view and [cfdb-datatable]
 * @param tableHtmlId html id of table element containing data to make it editable
 * @param cfdbEditUrl url to write back the new value in the table cell text area
 * @param cfdbGetValueUrl url to fetch the value of the cell into the editable textarea
 * @param loadImg image to display while fetching/saving data
 * @param columns array|null of columns to enable editing on. Null means all.
 */
function cfdbEditableCells(tableHtmlId, cfdbEditUrl, cfdbGetValueUrl, loadImg, columns) {

    var findValue = 'td:not([title="Submitted"]) > div';
    if (columns && jQuery.isArray(columns)) {
        for(var i = 0; i < columns.length; i++) {
            if(columns[i] == "Submitted") {
                columns.splice(i, 1);
                break;
            }
        }
        findValue = "td[title='" + columns.join("'] > div, td[title='") + "'] > div";
    }
    jQuery.each(jQuery('#' + tableHtmlId).find(findValue), function () {
        var self = this;
        jQuery(self).editable(
                cfdbEditUrl,
                {
                    type: 'textarea',
                    submit: 'OK',
                    indicator: '<img alt="Saving..." src="' + loadImg + '"/>',
                    height: '50px',
                    placeholder: '&nbsp;',
                    select: 'true',
                    ajaxoptions: {
                        cache: false
                    },
                    loadurl: cfdbGetValueUrl,
                    loadurlonlywhenhtml: true,  //caching
                    onerror: function (settings, original, xhr) {
                        alert("It wasn't possible to edit. Please try again. Status code: " + xhr.status);
                        console.log("XHR Status: " + xhr.status);
                    },
                    callback: function (value, settings) {
                        //console.log(this);
                        //console.log(value);
                        //console.log(settings);
                    }
                }
        );
    });

}

/**
 * Admin grid view and [cfdb-datatable]
 * @param tableHtmlId html id of table element containing data to make it editable
 * @param cfdbColEditUrl url to write back the new value in the table column text area
 * @param cfdbGetColumnValueUrl url to fetch the value of the column into the editable textarea
 * @param loadImg image to display while fetching/saving data
 * @param columns array|null of columns to enable editing on. Null means all.
 */
function cfdbEditableColumnHeaders(tableHtmlId, cfdbColEditUrl, cfdbGetColumnValueUrl, loadImg, columns) {
    var findValue = 'th:not([title="Submitted"]) > div > div';
    if (columns && jQuery.isArray(columns)) {
        for(var i = 0; i < columns.length; i++) {
            if(columns[i] == "Submitted") {
                columns.splice(i, 1);
                break;
            }
        }
        findValue = "th[title='" + columns.join("'] > div > div, th[title='") + "'] > div > div";
    }
    jQuery('#' + tableHtmlId + '_wrapper').find(findValue).editable(
            cfdbColEditUrl,
            {
                type: 'textarea',
                submit: 'OK',
                indicator: '<img alt="Saving..." src="' + loadImg + '"/>',
                height: '50px',
                placeholder: '&nbsp;',
                select: 'true',
                ajaxoptions: {
                    cache: false,
                    error: function (jqXHR, textStatus, errorThrown) {
                        alert(textStatus + ": " + errorThrown);
                    }
                    //,complete: function (jqXHR, textStatus) {
                    //    alert(textStatus);
                    //}
                },
                loadurl: cfdbGetColumnValueUrl,
                onerror : function(settings,original,xhr){
                    alert("It wasn't possible to edit. Please try again. Status code: " + xhr.status);
                    console.log("XHR Status: " + xhr.status);
                },
                callback: function (newColumnName, settings) {
                    var origColumnName = this.id.match(/,.*$/)[0].substring(1);
                    if (origColumnName != newColumnName) {
                        this.id = this.id.replace("," + origColumnName, "," + newColumnName);
                        jQuery('#' + tableHtmlId + ' td').each(function () {
                            if (jQuery(this).attr("title") == origColumnName) {
                                jQuery(this).attr("title", newColumnName);
                            }
                        });
                        jQuery('#' + tableHtmlId + ' td > div').each(function () {
                            var pos = this.id.indexOf(origColumnName);
                            if ((this.id.length - pos) == origColumnName.length) { // ends with
                                this.id = this.id.substr(0, this.id.length - origColumnName.length) + newColumnName;
                            }
                        });
                    }
                }
            });
}

/**
 * Admin single-entry view
 * @param tableHtmlId html id of table element containing data to make it editable
 * @param cfdbEditUrl url to write back the new value in the table cell text area
 * @param cfdbGetValueUrl url to fetch the value of the cell into the editable textarea
 * @param loadImg image to display while fetching/saving data
 */
function cfdbEntryEditable(tableHtmlId, cfdbEditUrl, cfdbGetValueUrl, loadImg) {
    (function ($) {
        $("#" + tableHtmlId).find("tr:not(:first-child) td:nth-child(2) div").editable(
                cfdbEditUrl,
                {
                    type: 'textarea',
                    submit: 'OK',
                    indicator: '<img alt="Saving..." src="' + loadImg + '"/>',
                    height: '50px',
                    placeholder: '&nbsp;',
                    select: 'true',
                    ajaxoptions: {
                        cache: false,
                        error: function (jqXHR, textStatus, errorThrown) {
                            alert(textStatus + ": " + errorThrown);
                        }
                        //,complete: function (jqXHR, textStatus) {
                        //    alert(textStatus);
                        //}
                    },
                    loadurl: cfdbGetValueUrl,
                    loadurlonlywhenhtml: true, //caching
                    onerror : function(settings,original,xhr){
                        alert("It wasn't possible to edit. Please try again. Status code: " + xhr.status);
                        console.log("XHR Status: " + xhr.status);
                    },
                    callback : function(value, settings) {
                        //console.log(this);
                        //console.log(value);
                        //console.log(settings);
                    }
                })
    })(jQuery);
}
