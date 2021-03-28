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

  /**
   * Get user media from cache
   *
   * @param string $user_name connected username
   * @param integer $fee_id
   *
   * @return string comments data
   */
  public function getUserMedia( $user_name, $feed_id ) {
    if ( isset($this->wdi_authenticated_users_list) && is_array($this->wdi_authenticated_users_list) && isset($this->wdi_authenticated_users_list[$user_name]) ) {
      $cache_data = $this->cache->get_cache_data($feed_id);
      if ( isset($cache_data) && $cache_data["success"] && isset($cache_data["cache_data"]) ) {

        return base64_decode($cache_data["cache_data"]);
      } else {
        return json_encode(array('data'=>''));
      }
    }
  }

  /**
   * Get comments from endpoint or from cache
   *
   * @param string $user_name connected username
   * @param string $media_id media id
   *
   * @return string comments data
   */
  public function getRecentMediaComments( $user_name, $media_id ) {
      $data['data'] = '';
      $data['meta']['code'] = 200;
      return json_encode($data);
  }

  /**
   * Get medias from cache
   *
   * @param string $feed_id
   *
   * @return string
   */
  public function getTagRecentMedia( $feed_id ) {
    $cache_data = $this->cache->get_cache_data($feed_id);
    if ( isset($cache_data) && $cache_data["success"] && isset($cache_data["cache_data"]) ) {

      return base64_decode($cache_data["cache_data"]);
    } else {
      return json_encode(array('data'=>''));
    }
  }

  public function wdi_getHashtagId( $tagname = '', $user_name='' ) {
    $this->account_data = $this->wdi_authenticated_users_list[$user_name];
    $url = 'https://graph.facebook.com/v9.0/ig_hashtag_search/?user_id=' . $this->account_data["user_id"] . '&q=' . $tagname . '&access_token=' . $this->account_data["access_token"];
    $args = array();
    $response = wp_remote_get($url, $args);

    $hashtag_id = '';
    if ( !isset($response->errors) && is_array($response) && isset($response["body"]) ) {
      $response = json_decode($response["body"], true);
      if ( !empty($response['data']) && !empty($response['data'][0]) && !empty($response['data'][0]['id']) ) {
        $hashtag_id = $response['data'][0]['id'];
        $return_data['meta']['code'] = 200;
      }
    } else {

    }
    $return_data["tag_id"] = $hashtag_id;
    //$return_data['meta']['code'] = $hashtag_id;
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
              "count" => isset($media["like_count"]) ? $media["like_count"] : 0, // media.like_count
            ),
            "tags" => array(),
            "filter" => "Normal",
            "comments" => array(
              "count" => isset($media["comments_count"]) ? $media["comments_count"] : 0, // media.comments_count
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
                "url" => (isset($media["media_url"]) ? $media["media_url"] : ""),
              ),
              "low_bandwidth" => array(
                "width" => 480,
                "height" => 600,
                "url" => (isset($media["media_url"]) ? $media["media_url"] : ""),
              ),
              "low_resolution" => array(
                "width" => 480,
                "height" => 600,
                "url" => (isset($media["media_url"]) ? $media["media_url"] : ""),
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
   * Convert hashtag data
   *
   * @param data array
   *
   * @return array
  */
  private function convertHashtagData( $data ) {
    $converted_data = array(
      'data' => array(),
      'pagination' => array(),
    );

    return $converted_data;
  }

  /**
   * Get media children id by gallery id.
   *
   * @param media_id =>  Media id
   *
   * @return array of founded child media data
   */
  private function getMediaChildren( $media_id ) {

    $carousel_media = array();
    $api_url = 'https://graph.instagram.com/v1.0/';
    if ( $this->account_data["type"] === "business" ) {
      $api_url = 'https://graph.facebook.com/v8.0/';
    }
    $api_url .= $media_id . '/children?access_token=' . $this->account_data["access_token"];
    $fields = 'id,media_type,media_url,permalink,thumbnail_url,username,timestamp';
    $api_url .= '&fields='.$fields;
    $response = wp_remote_get($api_url, array());
    if ( is_array($response) && isset($response["body"]) && $this->isJson($response["body"]) ) {
      $medias = json_decode($response["body"], TRUE);
      if ( is_array($medias) && isset($medias["data"]) ) {
        foreach ( $medias["data"] as $media_data ) {
          if ( isset($media_data["media_type"]) && $media_data["media_type"] == "IMAGE" ) {
            $child_media = array(
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
            $child_media = array(
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
          array_push($carousel_media, $child_media);

        }
      }
    }
    return $carousel_media;
  }

  public function wdi_set_preload_cache_data( $user_name, $feed_id, $endpoint, $tag_id, $tag_name ) {
    if ( $tag_id === 'false' || (isset($this->wdi_authenticated_users_list) && is_array($this->wdi_authenticated_users_list) && isset($this->wdi_authenticated_users_list[$user_name])) ) {
      $next_url = WDILibrary::get('next_url', '', 'esc_url_raw', 'POST');
      $iter = WDILibrary::get('iter', 0, 'intval');
      if ( $next_url != '' ) {
        $baseUrl = $next_url;
        $this->account_data = $this->wdi_authenticated_users_list[$user_name];
      }
      else {
        $this->account_data = $this->wdi_authenticated_users_list[$user_name];
        $user_id = $this->account_data["user_id"];
        $access_token = $this->account_data["access_token"];
        if( $tag_id === 'false' ) {
            $api_url = 'https://graph.instagram.com/v1.0/';
            $media_fields = 'id,media_type,media_url,permalink,thumbnail_url,username,caption,timestamp';
            if ( $this->account_data["type"] === "business" ) {
              $api_url = 'https://graph.facebook.com/v8.0/';
              $media_fields = 'id,media_type,media_url,permalink,thumbnail_url,username,caption,timestamp,ig_id,is_comment_enabled,like_count,comments_count,owner,shortcode';
            }
            $baseUrl = $api_url . $user_id . '/media/?fields=' . $media_fields . '&limit=100&access_token=' . $access_token;
        } else {
            $baseUrl = $this->wdi_getHashtagData($user_name, $endpoint, $tag_id);
        }
      }
      $args = array(
        'timeout' => 60,
        'sslverify' => FALSE
      );
      $response = wp_remote_get($baseUrl, $args);

      if ( !isset($response->errors) && is_array($response) && isset($response["body"]) ) {
        $data = json_decode($response["body"], TRUE);
        if ( !empty($data['data']) ) {
          if ( !empty($this->conditions) ) {
              $temp_data = $data;
              $temp_data['data'] = $this->filterUserMedia( $data, $this->conditions['conditional_filters'], $this->conditions['conditional_filter_type'] );
              if($tag_id !== 'false') {
                $current_data = $this->convertHashtagData($temp_data);
              } else {
                $current_data = $this->convertPersonalData($temp_data);
              }
          } elseif( $tag_id !== 'false' ) {
              $current_data = $this->convertHashtagData($data);
          } else {
              $current_data = $this->convertPersonalData($data);
          }
          /* Remove current feed cache data */
          if ( $iter == 0 ) {
              $this->cache->reset_feed_cache( $feed_id );
          } else {
              $cache_data = $this->cache->get_cache_data($feed_id);
              if ( isset($cache_data) && $cache_data["success"] && isset($cache_data["cache_data"]) ) {
                $cache_data = base64_decode($cache_data["cache_data"]);
                $cache_data = json_decode($cache_data, 1);
                $current_data['data'] = array_merge( $cache_data['data'], $current_data['data'] );
              }
          }

          $current_data = json_encode($current_data);

          $this->cache->set_cache_data($feed_id, base64_encode($current_data));
          $return_data['next_url'] = '';
          if ( isset($data['paging']['next']) && $data['paging']['next'] != '' ) {
            $return_data['next_url'] = $data['paging']['next'];
            $return_data['iter'] = $iter;
          }

          return json_encode($return_data);
        }
        else {
          $return_data['next_url'] = '';
          $return_data['iter'] = $iter;
          return json_encode($return_data);
        }
      }
    }
  }

  public function wdi_getHashtagData($user_name, $endpoint, $tag_id) {
    return '';
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