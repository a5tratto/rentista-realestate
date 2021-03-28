<?php

class WDIInstagram {
  private $wdi_options = NULL;
  private $cache = NULL;
  private $wdi_authenticated_users_list = NULL;
  private $account_data = NULL;

  public $feed_id;
  public $conditions = array();
  public $iter;

  function __construct() {
    require_once("WDICache.php");
    $this->cache = new WDICache();
    $this->wdi_options = get_option("wdi_instagram_options");
    if ( isset($this->wdi_options["wdi_authenticated_users_list"]) ) {
      $this->wdi_authenticated_users_list = json_decode($this->wdi_options["wdi_authenticated_users_list"], TRUE);
    }

    $this->iter = WDILibrary::get('iter', 0, 'intval', 'POST' );
    $this->feed_id = WDILibrary::get('feed_id', 0, 'intval', 'POST' );
    $this->conditions = $this->get_filters( $this->feed_id );
  }

  /**
   * Get condition filters for feed
   *
   * @param integer $feed_id
   *
   * @return array current feed filter data
   */
  public function get_filters( $feed_id ) {
	return array();
  }

  /**
   * Filter media data according to the filter conditions
   *
   * @param array $medias all medias got from endpoint
   * @param array $filters hashtag conditions for feed
   * @param string $type condition type AND / OR
   *filterUserMedia
   * @return array filtered media
   */
  public function filterUserMedia( $response, $filters, $type ) {
    return array();
  }

  public function getUserMedia( $user_name ) {
    if ( isset($this->wdi_authenticated_users_list) && is_array($this->wdi_authenticated_users_list) && isset($this->wdi_authenticated_users_list[$user_name]) ) {

      $next_url = WDILibrary::get('next_url', '', 'esc_url_raw', 'POST' );
      if ( $next_url != '' ) {
        $baseUrl = $next_url;
      }
      else {
        $this->account_data = $this->wdi_authenticated_users_list[$user_name];
        $user_id = $this->account_data["user_id"];
        $access_token = $this->account_data["access_token"];
        $api_url = 'https://graph.instagram.com/v1.0/';
        $media_fields = 'id,media_type,media_url,permalink,thumbnail_url,username,caption,timestamp';
        if ( $this->account_data["type"] === "business" ) {
          $api_url = 'https://graph.facebook.com/v8.0/';
          $media_fields = 'id,media_type,media_url,permalink,thumbnail_url,username,caption,timestamp,ig_id,is_comment_enabled,like_count,owner,shortcode';
        }
        $baseUrl = $api_url . $user_id . '/media/?fields=' . $media_fields . '&limit=100&access_token=' . $access_token;
      }
      $cache_data = $this->cache->get_cache_data($baseUrl);
      if ( isset($cache_data) && $cache_data["success"] && isset($cache_data["cache_data"]) ) {
        return base64_decode($cache_data["cache_data"]);
      }
      $args = array();
      $response = wp_remote_get($baseUrl, $args);
      if ( !isset($response->errors) && is_array($response) && isset($response["body"]) ) {
        $data = json_decode($response["body"], TRUE);
        if ( !empty($data['data']) ) {
          $return_data = $this->convertPersonalData($data);
          $return_data = json_encode($return_data);
          $this->cache->set_cache_data($baseUrl, base64_encode($return_data));
          return $return_data;
        }
      }
      $return_data = '{"error":{"message":"cURL error","type":"http_request_failed"}}';
      return $return_data;
    }
  }

  public function getTagRecentMedia( $tagname, $endpoint, $next_url = NULL, $wdiTagId = FALSE, $user_name = NULL ) {
    $this->account_data = $this->wdi_authenticated_users_list[$user_name];
    $return_data = array();
    if ( isset($next_url) ) {
      $baseUrl = $next_url;
    }
    else {
      $baseUrl = 'https://graph.facebook.com/{tagid}/' . $endpoint . '/?fields=media_url,caption,id,media_type,comments_count,like_count,permalink,children{media_url,id,media_type,permalink}&access_token=' . $this->account_data["access_token"] . '&limit=50&user_id=' . $this->account_data["user_id"];
      if ( $wdiTagId !== FALSE ) {
        $baseUrl = str_replace("{tagid}", $wdiTagId, $baseUrl);
      }
      else {
        $data = $this->getHastagDataUrl($tagname, $baseUrl);
        if ( isset($data["id"]) && isset($data["url"]) ) {
          $baseUrl = $data["url"];
          $return_data["tag_data"] = array(
            'id' => "#" . $tagname,
            'username' => "#" . $tagname,
            'tag_id' => $data["id"],
          );
        }
      }
    }
    /***********************************************************/
    $args = array();
    $cache_data = $this->cache->get_cache_data($baseUrl);
    if ( isset($cache_data) && $cache_data["success"] && isset($cache_data["cache_data"]) ) {
      $cache_data_json = base64_decode($cache_data["cache_data"]);
      if ( isset($cache_data_json) && $cache_data_json !== "null" ) {
        return $cache_data_json;
      }
    }

    $response = wp_remote_get($baseUrl, $args);
    if ( !isset($response->errors) && is_array($response) && isset($response["body"]) ) {
      $response_arr = json_decode( $response["body"], 1 );
      $return_data["response"] = $response_arr;
      if ( !empty($this->conditions) ) {
          $this->cache->set_cache_data($baseUrl."&iter=".$this->iter, base64_encode(json_encode($return_data)));
          $response_arr['data'] = $this->filterUserMedia( $response_arr, $this->conditions['conditional_filters'], $this->conditions['conditional_filter_type'] );
          $response_arr['iter'] = $this->iter;
          $return_data["response"] = $response_arr;
      } else {
          $this->cache->set_cache_data($baseUrl, base64_encode(json_encode($return_data)));
      }
      return json_encode($return_data);
    }
    $return_data = '{"error":{"message":"cURL error","type":"http_request_failed"}}';
    return $return_data;
  }

  private function getHastagDataUrl( $tagname, $baseUrl ) {
    $url = 'https://graph.facebook.com/ig_hashtag_search/?user_id=' . $this->account_data["user_id"] . '&q=' . $tagname . '&access_token=' . $this->account_data["access_token"];
    $return_data = array();
    $args = array();
    $cache_data = $this->cache->get_cache_data($url);
    if ( isset($cache_data) && $cache_data["success"] && isset($cache_data["cache_data"]) ) {
      return json_decode($cache_data["cache_data"], TRUE);
    }
    $response = wp_remote_get($url, $args);
    if ( !isset($response->errors) && is_array($response) && isset($response["body"]) ) {
      $response = json_decode($response["body"]);
      if ( isset($response->data) && isset($response->data[0]) && isset($response->data[0]->id) ) {
        $hashtag_id = $response->data[0]->id;
        $baseUrl = str_replace("{tagid}", $hashtag_id, $baseUrl);
        $return_data["id"] = $hashtag_id;
        $return_data["url"] = $baseUrl;
        $this->cache->set_cache_data($url, json_encode($return_data, TRUE));
      }
    }

    return $return_data;
  }

  private function convertPersonalData( $data ) {
    $carousel_media_ids = array();
    $converted_data = array(
      "data" => array(),
      "pagination" => array(),
    );
    if ( is_array($data) ) {
      if ( isset($data["paging"]) ) {
        $converted_data["pagination"] = array(
          "cursors" => array(
            "after" => $data["paging"]["cursors"]["after"],
          ),
          "next_url" => (isset($data["paging"]["next"]) ? $data["paging"]["next"] : ""),
        );
      }
      if ( isset($data["data"]) ) {
        foreach ( $data["data"] as $key => $media ) {
          if ( $media["media_type"] == "IMAGE" ) {
            $media_type = "image";
          }
          elseif ( $media["media_type"] == "VIDEO" ) {
            $media_type = "video";
          }
          else {
            $media_type = "carousel";
          }
          if ( isset($media["like_count"]) ) {
            $like_count = intval($media["like_count"]);
          }
          else {
            $like_count = 0;
          }
          $converted = array(
            "id" => (isset($media["id"]) ? $media["id"] : ""),
            "user" => array(
              "id" => "",
              "full_name" => "",
              "profile_picture" => "",
              "username" => "",
            ),
            "images" => array(
              "thumbnail" => array(
                "width" => 150,
                "height" => 150,
                "url" => (isset($media["media_url"]) ? $media["media_url"] : ""),
              ),
              "low_resolution" => array(
                "width" => 320,
                "height" => 320,
                "url" => (isset($media["media_url"]) ? $media["media_url"] : ""),
              ),
              "standard_resolution" => array(
                "width" => 1080,
                "height" => 1080,
                "url" => (isset($media["media_url"]) ? $media["media_url"] : ""),
              ),
            ),
            "created_time" => (isset($media["timestamp"]) ? $media["timestamp"] : ""),
            "caption" => array(
              "id" => "",
              "text" => (isset($media["caption"]) ? $media["caption"] : ""),
              "created_time" => "",
              "from" => array(
                "id" => "",
                "full_name" => "",
                "profile_picture" => "",
                "username" => "",
              ),
            ),
            "user_has_liked" => ($like_count > 0),
            "likes" => array(
              "count" => 0, // media.like_count
            ),
            "tags" => array(),
            "filter" => "Normal",
            "comments" => array(
              "count" => 0, // media.comments_count
            ),
            "media_type" => $media["media_type"],
            "type" => $media_type,
            "link" => (isset($media["permalink"]) ? $media["permalink"] : ""),
            "location" => NULL,
            "attribution" => NULL,
            "users_in_photo" => array(),
          );
          if ( $media["media_type"] === "VIDEO" ) {
            $converted["videos"] = array(
              "standard_resolution" => array(
                "width" => 640,
                "height" => 800,
                "url" => $media["thumbnail_url"],
              ),
              "low_bandwidth" => array(
                "width" => 480,
                "height" => 600,
                "url" => $media["thumbnail_url"],
              ),
              "low_resolution" => array(
                "width" => 480,
                "height" => 600,
                "url" => $media["thumbnail_url"],
              ),
            );
          }

          /**
           * Set to global media object the carousel media data as key carousel-media.
           *
           * @param response               =>  Global media object
           * @param carusel_media_ids      =>  Child ids
           * @param ind                    =>  index counter
           *
           */
          if ( $media["media_type"] === "CAROUSEL_ALBUM" ) {
            $carousel_media = $this->getMediaChildren($media["id"]);
            $converted["carousel_media"] = $carousel_media;
            array_push($carousel_media_ids, array( 'index' => $key, "media_id" => $media["id"] ));
          }
          array_push($converted_data["data"], $converted);
        }
      }
    }

    return $converted_data;
  }

  /**
   * Get media children id by gallery id.
   *
   * @param media_id =>  Media id
   *
   * @return object of founded media only child media ids
   */
  private function getMediaChildren( $media_id ) {
    $carousel_media = array();
    $api_url = 'https://graph.instagram.com/v1.0/';
    if ( $this->account_data["type"] === "business" ) {
      $api_url = 'https://graph.facebook.com/v8.0/';
    }
    $api_url .= $media_id . '/children?access_token=' . $this->account_data["access_token"];
    $response = wp_remote_get($api_url, array());
    if ( is_array($response) && isset($response["body"]) && $this->isJson($response["body"]) ) {
      $media_ids = json_decode($response["body"], TRUE);
      if ( is_array($media_ids) && isset($media_ids["data"]) ) {
        foreach ( $media_ids["data"] as $id ) {
          if ( isset($id["id"]) ) {
            $media = $this->getChildMediaById($id["id"]);
            if(isset($media) && is_array($media)){
              array_push($carousel_media, $media);
            }
          }
        }
      }
    }
    return $carousel_media;
  }

  /**
   * Get media info by id.
   *
   * @param media_id               =>  Media id
   * @param ind                    =>  index for object
   *
   *
   * @return object of founded media
   */
  private function getChildMediaById( $media_id ) {
    $api_url = 'https://graph.instagram.com/v1.0/';
    $fields = 'id,media_type,media_url,permalink,thumbnail_url,username,timestamp';
    if ( $this->account_data["type"] === "business" ) {
      $api_url = 'https://graph.facebook.com/v8.0/';
      $fields = 'id,ig_id,media_type,media_url,permalink,thumbnail_url,username,timestamp,shortcode';
    }
    $api_url = $api_url . $media_id . '/?fields=' . $fields . '&access_token=' . $this->account_data["access_token"];
    $response = wp_remote_get($api_url, array());
    if ( is_array($response) && isset($response["body"]) && $this->isJson($response["body"]) ) {
      $media_data = json_decode($response["body"], TRUE);
      if( isset($media_data['error']) ) {
        return FALSE;
      }

      if ( isset($media_data["media_type"]) && $media_data["media_type"] == "IMAGE" ) {
        $return_data = array(
          "images" => array(
            "thumbnail" => array(
              "width" => 150,
              "height" => 150,
              "url" => $media_data["media_url"],
            ),
            "low_resolution" => array(
              "width" => 320,
              "height" => 320,
              "url" => $media_data["media_url"],
            ),
            "standard_resolution" => array(
              "width" => 640,
              "height" => 640,
              "url" => $media_data["media_url"],
            ),
          ),
          "users_in_photo" => array(),
          "type" => "image",
        );
      }
      else {
        $return_data = array(
          "videos" => array(
            "standard_resolution" => array(
              "width" => 640,
              "height" => 800,
              "url" => esc_url_raw($media_data["media_url"]),
              "id" => $media_data["id"],
            ),
            "low_bandwidth" => array(
              "width" => 480,
              "height" => 600,
              "url" => esc_url_raw($media_data["media_url"]),
              "id" => $media_data["id"],
            ),
            "low_resolution" => array(
              "width" => 640,
              "height" => 800,
              "url" => esc_url_raw($media_data["media_url"]),
              "id" => $media_data["id"],
            ),
          ),
          "users_in_photo" => array(),
          "type" => "video",
        );
      }
      return $return_data;
    }

    return FALSE;
  }

  public function wdi_preload_cache($data=NULL) {
    if(isset($data)){
      $feed_list = $this->get_feed_list($data, FALSE);
    }else{
      $this->cache->reset_cache();
      global $wpdb;
      $row = $wpdb->get_results("SELECT id, feed_users ,hashtag_top_recent FROM " . $wpdb->prefix . WDI_FEED_TABLE . " WHERE published=1 ORDER BY `feed_name` ASC");
      $feed_list = $this->get_feed_list($row, TRUE);
    }
    if(isset($feed_list)){
      foreach ($feed_list as $user_feed){
        if(isset($user_feed["feed_list"])){
          foreach ($user_feed["feed_list"] as $data){
            if($data["type"] === "user"){
              $this->getUserMedia($data["tag_name"]);
            }else{
              $this->getTagRecentMedia($data["tag_name"], 1, NULL, FALSE, $user_feed["user_name"]);
            }
          }
        }
      }
    }
  }

  private function get_feed_list($data, $cron = TRUE){
    $feed_list = array();
    if($cron){
      if ( isset($data) && is_array($data) ) {
        foreach ( $data as $feed ) {
          if ( isset($feed->feed_users) ) {
            $endpoint = $feed->hashtag_top_recent;
            $feed_users = json_decode($feed->feed_users);
            $feed_data = array(
              "feed_list"=>array(),
            );
            if ( is_array($feed_users)) {
              foreach ( $feed_users as $user ) {
                if ( $user->username[0] === "#" ) {
                  $tag_name = str_replace("#", "", $user->username);
                  $feed_arr = array(
                    "tag_name" =>$tag_name,
                    "type" =>"tag",
                    "endpoint" =>$endpoint,
                  );
                }else{
                  $feed_arr = array(
                    "tag_name" =>$user->username,
                    "type" =>"user",
                    "endpoint" =>$endpoint,
                  );
                  $feed_data["user_name"] = $user->username;
                }
                array_push($feed_data["feed_list"], $feed_arr);
              }
            }
            array_push($feed_list, $feed_data);
          }
        }
      }
    }else{
      $feed_data = array(
        "feed_list"=>array(),
      );
      if ( is_array($data)) {
        foreach ( $data as $user ) {
          if ( $user["username"][0] === "#" ) {
            $tag_name = str_replace("#", "", $user["username"]);
            $feed_arr = array(
              "tag_name" =>$tag_name,
              "type" =>"tag",
              "endpoint" =>1,
            );
          }else{
            $feed_arr = array(
              "tag_name" =>$user["username"],
              "type" =>"user",
              "endpoint" =>1,
            );
            $feed_data["user_name"] = $user["username"];
          }
          array_push($feed_data["feed_list"], $feed_arr);
        }
      }
      array_push($feed_list, $feed_data);
    }
    return $feed_list;
  }

  private function isJson( $string ) {
    json_decode($string);

    return (json_last_error() == JSON_ERROR_NONE);
  }
}