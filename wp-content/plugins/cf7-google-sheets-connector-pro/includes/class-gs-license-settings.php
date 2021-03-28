<?php
/**
 * Settings class for License settings
 * @since 1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
   exit;
}

/**
 * GS_License_Settings Class
 * @since 1.0
 */

class GS_License_Settings {

	// GS Connector license key option name
	protected $gs_license_key_option_name = "gs_license_key";

	// GS Connector license status option name
	protected $gs_license_status_option_name = "gs_license_status";

	/**
	 * Set things up.
	 * @since 1.0
	 */
	public function __construct() {
		add_action( 'admin_init', array( $this, 'init_settings' ) );
	}

	// White list our options using the Settings API
	public function init_settings() {
		register_setting( 'gs-settings-license', $this->gs_license_key_option_name, array( $this, 'validate_gs_license_key') );
	}

	/**
	 * sanitize and validate the input (if required)
	 * @since 1.0
	 */
	public function validate_gs_license_key( $license_key ) {
		$license_key = sanitize_text_field( $license_key );
		$gs_license_service = new GS_License_Service();

		if ( isset( $_POST['gs_license_deactivate'] ) ) { // user is trying to deactivate the license
			$status = $gs_license_service->deactivate_license( $license_key,
					$this->gs_license_status_option_name, GS_CONNECTOR_PRO_PRODUCT_NAME );

			if ( $status == GS_License_Service::FAILED ) {
				add_settings_error(
						'gs-settings-license',
						'gs-settings-license-gs-license-key',
						"Google Sheet Connector Pro " . __( "License cannot be deactivated. Either the license key is invalid or the licensing server cannot be reached." , "gsconnector" ),
						'error'
				);
				// since there was an error, revert to the license key to the value from the DB
				$license_key = trim( get_option( $this->gs_license_key_option_name ) );

			} else { // looks like we have a successful de-activation, so let's clear the license key
				$license_key = "";
			}
		} elseif ( ! empty( $license_key ) ) { // user is trying to activate the license
			$status = $gs_license_service->activate_license( $license_key,
								$this->gs_license_status_option_name, GS_CONNECTOR_PRO_PRODUCT_NAME );
         
			if ( $status == GS_License_Service::INVALID ) {
				add_settings_error(
						'gs-settings-license',
						'gs-settings-license-gs-license-key',
						"Google Sheet Connector Pro " . __( "License cannot be activated. Either the license key is invalid or your activation limit is reached." , "gsconnector" ),
						'error'
				);
			}
		}

		return $license_key;
	}

	/*
	 * generate the page
	 *
	 * @since 2.0
	 */
	public function add_settings_page() {
		$gs_license_key = get_option( $this->gs_license_key_option_name );
		$gs_license_status = get_option( $this->gs_license_status_option_name );
		?>
		<form id="gs_settings_form" method="post" action="options.php">
    	<?php
    	// adds nonce and option_page fields for the settings page
    	settings_fields('gs-settings-license');
      settings_errors();
    	?>
			<div class="wrap gs-form">
				<div class="card" id="googlesheet">
					<div class="select-info full-width">
						<div>
							<label class="settings-title" for="gs_license_key"><?php echo("Google Sheet Connector Pro ");?><?php _e( 'license key', "gsconnector" ); ?>:</label>
						</div>
                  <div class="gs-margin-top">
							<input type="text" class="regular-text" name="<?php echo $this->gs_license_key_option_name; ?>" value="<?php echo $gs_license_key; ?>" />
				         <?php if ( $gs_license_status !== false && $gs_license_status == 'valid' ) { ?>
								<input type="submit" class="button-secondary" name="gs_license_deactivate" value="<?php _e( 'Deactivate License', "gsconnector" ); ?>"/>
							<?php }	?>
						</div>
						<br class="clear">
					</div>					
					<div class="select-info full-width">
						<input type="submit" class="button button-primary button-large" name="gs_license_activate" value="<?php echo __( "Save", "gsconnector" ); ?>"/>
					</div>
					<br class="clear">
				</div>
			</div>
			<?php wp_nonce_field( 'gs_license_nonce', 'gs_license_nonce' ); ?>
		</form>
	<?php
	}
}
$gs_license_settings = new GS_License_Settings();
?>