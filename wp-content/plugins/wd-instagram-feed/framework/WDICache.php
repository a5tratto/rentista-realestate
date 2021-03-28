<?php

/**
 * Class WDICache
 */
class WDICache {

  private $wdi_options = NULL;

  /**
   * WDICache constructor.
   */
  function __construct() {
    global $wdi_options;
    $this->wdi_options = $wdi_options;
  }

  /**
   * Get cache data.
   *
   * @param      $name
   * @param bool $debug
   *
   * @return array
   */
  public function get_cache_data( $name, $debug = FALSE ) {
    $transient_key = "wdi_cache_" . md5($name);
    $cache_data = get_transient($transient_key);

    if ( isset($cache_data) && $cache_data != FALSE && isset($cache_data["cache_response"]) ) {
      $wdi_debugging = FALSE;
      $wdi_debugging_data = array();
      if ( $debug ) {
        $wdi_debugging = TRUE;
        $current_date = (date('Y-m-d H:i:s'));
        $cache_date = $cache_data["wdi_debugging_data"]["cache_date"];
        $wdi_transient_time = $cache_data["wdi_debugging_data"]["wdi_transient_time"];
        $current_date_strtotime = strtotime($current_date);
        $cache_date_strtotime = strtotime($cache_date);
        $seconds_diff = $current_date_strtotime - $cache_date_strtotime;
        $date_diff_min = $seconds_diff / 60;
        $wdi_debugging_data = array(
          'current_date' => $current_date,
          'cache_date' => $cache_date,
          'date_diff_min' => $date_diff_min,
          'transient_key' => WDILibrary::get('wdi_cache_name'),
          'wdi_transient_time' => $wdi_transient_time,
        );
      }
      $cache_data = stripslashes($cache_data["cache_response"]);
      $return_data = array(
        "success" => TRUE,
        "wdi_debugging" => $wdi_debugging,
        "wdi_debugging_data" => $wdi_debugging_data,
        "cache_data" => $cache_data,
      );

      return $return_data;
    }
    else {
      return array("success" => FALSE);
    }
  }

  /**
   * Set cache data.
   *
   * @param $name
   * @param $response
   *
   * @return array|bool
   */
  public function set_cache_data($name, $response){
    if ( isset($this->wdi_options["wdi_transient_time"]) ) {
      $wdi_transient_time = intval($this->wdi_options["wdi_transient_time"]);
    }
    else {
      $wdi_transient_time = 60;
    }
    $cache_date = (date('Y-m-d H:i:s'));
    $wdi_cache_response = $response;
    $transient_key = "wdi_cache_" . md5($name);
    $expiration_time = $wdi_transient_time * 60;
    if ( !seems_utf8($wdi_cache_response) ) {
      $wdi_cache_response = utf8_encode($wdi_cache_response);
    }
    $data = array(
      'cache_response' => $wdi_cache_response,
      'wdi_debugging_data' => array(
        'cache_date' => $cache_date,
        'wdi_transient_time' => $wdi_transient_time,
      ),
    );
    $data = set_transient($transient_key, $data, $expiration_time);

    return $data;
  }

  /**
   * Reset cache.
   *
   * @return bool|int
   */
  public function reset_cache() {
    global $wpdb;
    $data = $wpdb->query('DELETE FROM ' . $wpdb->prefix . 'options WHERE option_name LIKE "%wdi_cache_%"');

    return $data;
  }
}