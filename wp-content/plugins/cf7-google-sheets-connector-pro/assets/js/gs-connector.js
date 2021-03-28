jQuery(document).ready(function () {
     
     /**
    * verify the api code
    * @since 1.0
    */
    jQuery(document).on('click', '#save-gs-code', function () {
        jQuery(this).parent().children(".loading-sign").addClass( "loading" );
        var data = {
        action: 'verify_gs_integation',
        code: jQuery('#gs-code').val(),
        security: jQuery('#gs-ajax-nonce').val()
      };
      jQuery.post(ajaxurl, data, function (response ) {
         if ( response == -1 ) {
            return false; // Invalid nonce
         }
         
         if( ! response.success ) { 
           jQuery( ".loading-sign" ).removeClass( "loading" );
           jQuery( "#gs-validation-message" ).empty();
           jQuery("<span class='error-message'>Access code Can't be blank</span>").appendTo('#gs-validation-message');
         } else {
           jQuery( ".loading-sign" ).removeClass( "loading" );
           jQuery( "#gs-validation-message" ).empty();
           jQuery("<span class='gs-valid-message'>Access Code Saved. But do check the debug log for invalid access code.</span>").appendTo('#gs-validation-message'); 
         }
      });
      
    }); 
    
   /**
    * Clear debug
    */
   jQuery(document).on('click', '.debug-clear', function () {
      jQuery(".clear-loading-sign").addClass("loading");
      var data = {
         action: 'gs_clear_log',
         security: jQuery('#gs-ajax-nonce').val()
      };
      jQuery.post(ajaxurl, data, function ( response ) {
         if (response == -1) {
            return false; // Invalid nonce
         }
         
         if (response.success) {
            jQuery(".clear-loading-sign").removeClass("loading");
            jQuery("#gs-validation-message").empty();
            jQuery("<span class='gs-valid-message'>Logs are cleared.</span>").appendTo('#gs-validation-message');
         }
      });
   });
   
   /**
    * Sync with google account to fetch latest sheet and tab name list.
    */
   jQuery(document).on('click', '#gs-sync', function () {
      jQuery(this).parent().children(".loading-sign").addClass( "loading" );
      var integration = jQuery(this).data("init");
      var data = {
         action: 'sync_google_account',
         isajax: 'yes',
         isinit : integration,
         security: jQuery('#gs-ajax-nonce').val()
      };
      
      jQuery.post(ajaxurl, data, function ( response ) {
         if (response == -1) {
            return false; // Invalid nonce
         }
         
         if ( response.data.success === "yes" ) {
            jQuery(".loading-sign").removeClass( "loading" );
            jQuery( "#gs-validation-message" ).empty();
            jQuery("<span class='gs-valid-message'>Fetched all sheet details.</span>").appendTo('#gs-validation-message'); 
         } else {
            jQuery(this).parent().children(".loading-sign").removeClass( "loading" );
            location.reload(); // simply reload the page
         }
      });
   });
    
   /** 
    * Get tab name list 
    */
   jQuery(document).on("change", "#gs-sheet-name", function () {
      var sheetname = jQuery(this).val();
      jQuery(this).parent().children(".loading-sign").addClass( "loading" );
      var data = {
         action: 'get_tab_list',
         sheetname: sheetname,
         security: jQuery('#gs-ajax-nonce').val()
      };
      
      jQuery.post(ajaxurl, data, function ( response ) {
         if (response == -1) {
            return false; // Invalid nonce
         }
         if ( response.success ) {
            jQuery('#gs-sheet-tab-name').html( html_decode(response.data) );
            jQuery( ".loading-sign" ).removeClass( "loading" );
         }
      });      
   });
   
   // TODO : Combine into one
   jQuery(document).on("change", "#gs-sheet-name", function () {
      var sheetname = jQuery(this).val();
      jQuery(this).parent().children(".loading-sign").addClass( "loading" );
      var data = {
         action: 'get_sheet_id',
         sheetname: sheetname,
         security: jQuery('#gs-ajax-nonce').val()
      };
      
      jQuery.post(ajaxurl, data, function ( response ) {
         if (response == -1) {
            return false; // Invalid nonce
         }
         
         if ( response.success ) {
            jQuery('#sheet-url').html( html_decode(response.data) );
            jQuery( ".loading-sign" ).removeClass( "loading" );
         }
      });      
   });

   
   jQuery("#ckbCheckAll").click(function () {
        jQuery(".checkBoxClass").prop('checked', jQuery(this).prop('checked'));
    });
	
	jQuery("#fckbCheckAll").click(function () {
        jQuery(".fcheckBoxClass").prop('checked', jQuery(this).prop('checked'));
    });
      
   function html_decode(input){
		var doc = new DOMParser().parseFromString(input, "text/html");
		return doc.documentElement.textContent;
	}

});
