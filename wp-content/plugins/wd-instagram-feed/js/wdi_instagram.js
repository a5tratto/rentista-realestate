/**
 * WDIInstagram is jQuery based plugin which handles communication
 * with instagram API endpoints
 *
 * Plugin Version: 1.0.0
 * Author: Melik Karapetyan
 * License: GPLv2 or later
 *
 *
 *
 *
 *
 * Methods:
 *    getSelfInfo = function( args ) : Get information about the owner of the access_token.
 *    searchForUsersByName = function( username, args ) : Get a list of users matching the query.
 *      searchForTagsByName = function(tagname, args) : Search for tags by name.
 *      getTagRecentMedia = function(tagname, args) : Gets recent media based on tagname
 *
 */

/**
 * WDIInstagram object constructor
 * @param {Object} args
 *
 * @param {Array}           [args.access_tokens] [array of lavid instagram access tokens]
 * @param {Array}           [args.filters] [array of object defining filters]
 * @param {Object}          [args.filters[i] ] [ filter object which contain 'where' : 'what' pair ]
 * @param {String}          [args.filters.filter[i].where] [name of function where filter must be applied]
 * @param {String or Array} [args.filters.filter[i].what] [name of filtering function,
 *                       if function is in global scope then it should be name of the funtion
 *                         else if function in method of some object then it should be an array
 *                                ['parent_object_name','filtering_function_name']]
 */
function WDIInstagram(args) {

  this.user = {};
  this.access_tokens = [];
  this.filters = [];
  if (typeof args != 'undefined') {
    if (typeof args.access_tokens != 'undefined') {
      this.access_tokens = args.access_tokens;
    }
    if (typeof args.filters != 'undefined') {
      this.filters = args.filters;
    }
  }


  var _this = this;

  /**
   * Default object for handling status codes
   * @type {Object}
   */
  this.statusCode = {
    429: function ()
    {
      console.log(' 429: Too many requests. Try after one hour');
    },
  }

  /**
   * gets filter function defined for specific method
   * this function is internal function and cannot be called outside of this object
   *
   * @param  {String} methodName   [name of WDIInstagram method]
   * @return {Function}            [filtering function for {methodName}]
   */
  this.getFilter = function (methodName) {
    var filters = _this.filters;
    if (typeof filters == "undefined") {
      return false;
    }
    for (var i = 0; i < filters.length; i++) {
      if (filters[i].where == methodName) {

        if (typeof filters[i].what == 'object' && filters[i].what.length == 2) {
          if (typeof window[filters[i].what[0]] != 'undefined') {
            if (typeof window[filters[i].what[0]][filters[i].what[1]] == 'function') {
              return window[filters[i].what[0]][filters[i].what[1]];
            }
          }
        } else
        if (typeof filters[i].what == 'string') {
          if (typeof window[filters[i].what] == 'function') {
            return window[filters[i].what];
          }
        } else
        if (typeof filters[i].what == 'function') {
          return filters[i].what;
        } else {
          return false;
        }
      }
    }
    return false;
  }

  function getUserId() {
    if ( typeof _this.user !== 'undefined' && typeof _this.user.user_id !== 'undefined' ) {
      return _this.user.user_id
    }
    else if ( typeof wdi_object !== 'undefined'  && typeof wdi_object.user !== 'undefined' ) {
      return wdi_object.user.user_id;
    }
    else {
      return '';
    }
  }
  function getUserName() {
    if ( typeof _this.user !== 'undefined' && typeof _this.user.user_name !== 'undefined' ) {
      return _this.user.user_name
    }
    else if ( typeof wdi_object !== 'undefined'  && typeof wdi_object.user !== 'undefined' ) {
      return wdi_object.user.user_name;
    }
    else {
      return '';
    }
  }

  function getAccessToken() {
    if ( typeof _this.user !== 'undefined' && typeof _this.user.access_token !== 'undefined' ) {
      return _this.user.access_token
    }
    else if ( typeof wdi_object !== 'undefined'  && typeof wdi_object.user !== 'undefined' && typeof wdi_object.user.access_token !== 'undefined' ) {
      return wdi_object.user.access_token;
    }
    else {
      return '';
    }
  }

  function getGraphAcessToken() {
    if ( typeof _this.user !== 'undefined' && typeof _this.user.access_token !== 'undefined' ) {
      return _this.user.access_token
    }
    if ( typeof wdi_object !== 'undefined'  && typeof wdi_object.user !== 'undefined' ) {
      return wdi_object.user.access_token;
    }
    if ( typeof wdi_options !== 'undefined' ) {
      return wdi_options.fb_token;
    }
    else if ( typeof wdi_ajax !== 'undefined' ) {
      return wdi_ajax.fb_token;
    }
    else {
      return "";
    }
  }

  /**
   * Adds access token to this.access_tokens array
   * non string values are not allowed
   * @param {String} token [Instagram API access token]
   */
  this.addToken = function (token)
  {
    if (typeof token == 'string') {
      _this.access_tokens.push(token);
    }
  }

  this.resetTokens = function ()
  {
    _this.access_tokens = [];
  }


  /* counter for break cycle */
  var errorItercount = 0;
  var filterResponse = {data:new Array(), paging:{}} ;

  /**
   * Gets recent media based on tagname
   *
   *
   * @definition success_callback => which function to call in case of success
   * @definition error_callback   => which function to call in case of error
   * @definition media_count      => number of media to request
   * @definition min_tag_id       => Return media before this min_tag_id.
   * @definition max_tag_id       => Return media after this max_tag_id.
   * @definition statusCode       => StatusCode object.
   *
   * @param tagname               =>  A valid tag name without a leading #. (eg. snowy, nofilter)
   * @param args = {
   *       success    :   'success_callback',
   *       error      :   'error_callback',
   *       statusCode :   'statusCode',
   *       count      :   'media_count',
   *       min_tag_id :   'min_tag_id',
   *       max_tag_id :   'max_tag_id',
   *       args : arguments to be passed to filtering function
   *  }
   *
   *
   * if callback function is property of any other object just give it as array [ 'parent_object', 'callback_function']
   * or you can pass as callback function an anonymous function
   *
   *
   * @return object of founded media
   */
  this.getTagRecentMedia = function (tagname, args, next_url, endpoint, iter = 0) {
    var instagram = this,
      noArgument = false,
      successFlag = false,
      statusCode = this.statusCode,
      errorFlag = false,
      argFlag = false,
      filter = this.getFilter('getTagRecentMedia');

    endpoint = (parseInt(endpoint) === 0) ? "top_media" : "recent_media";

    if (typeof args == 'undefined' || args.length === 0) {
      noArgument = true;
    }
    else {

      if ('success' in args) {
        successFlag = true;
      }
      if ('statusCode' in args) {
        statusCode = args['statusCode'];
      }
      if ('error' in args) {
        errorFlag = true;
      }

      if ('args' in args) {
        argFlag = true;
      } else {
        args.args = {};
      }

      if ('count' in args) {
        args['count'] = parseInt(args['count']);
        if (!Number.isInteger(args['count']) || args['count'] <= 0) {
          args.count = 33;
        }
      } else {
        args.count = 33;
      }
    }
    var wdiTagId = this.getTagId(tagname);
    jQuery.ajax({
      type: "POST",
      url: wdi_ajax.ajax_url,
      dataType:"json",
      data: {
        iter: iter,
        tagname: tagname,
        endpoint: endpoint,
        wdi_nonce: wdi_ajax.wdi_nonce,
        wdiTagId: wdiTagId,
        next_url: next_url,
        user_name: getUserName(),
        action: "wdi_getTagRecentMedia",
        feed_id: wdi_ajax.feed_id,
      },
      success: function (response) {
        if ( typeof response.error !== 'undefined' && errorItercount < 5 ) {
          /* counter for break cycle */
          errorItercount++;
          _this.getTagRecentMedia(tagname, args, next_url, endpoint, iter)
        }
        else if ( typeof response.response !== 'undefined' && typeof response.response.iter !== 'undefined' && filterResponse.data.length < parseInt(wdi_ajax.number_of_photos) && iter < 10 && response.response.paging.next != '' ) {
          next_url = response.response.paging.next;
          filterResponse.data = filterResponse.data.concat(response.response.data);
          filterResponse.paging = response.response.paging;
          _this.getTagRecentMedia(tagname, args, next_url, endpoint, response.response.iter)
        }
        else {
          if ( filterResponse.data.length != 0 ) {
            response.response = filterResponse;
          }
          if (wdiTagId === false) {
            wdiTagId = "";
          }
          if ( typeof response.tag_data !== "undefined" ) {
            var tag_data = response.tag_data;
            if (typeof tag_data.tag_id !== "undefined") {
              wdiTagId = tag_data.tag_id;
            }
            var all_tags = [];
            if (typeof window['wdi_all_tags'] !== "undefined") {
              all_tags = window['wdi_all_tags'];
            }
            all_tags[tag_data.tag_id] = tag_data;
            window['wdi_all_tags'] = all_tags;
          }
          response = response.response;
          response = _this.convertHashtagData(response);
          response.meta = {"code": 200};
          response.tag_id = wdiTagId;
          success(response)
        }
      },
      error: function (response) {
        if (errorFlag) {
          if (typeof args['error'] == 'object' && args['error'].length == 2) {
            if (typeof window[args['error'][0]][args['error'][1]] == 'function') {
              window[args['error'][0]][args['error'][1]](response);
            }
          }
          else if (typeof args['error'] == 'string') {
            if (typeof window[args['error']] == 'function') {
              window[args['error']](response);
            }
          }
          else if (typeof args['error'] == 'function') {
            args['error'](response);
          }
        }
      },
      statusCode: statusCode
    });

    function success(response) {
      if ( typeof response["data"] === 'undefined' ) response["data"] = [];
      if ( successFlag ) {
        if (typeof args.success == 'object' && args.success.length == 2) {
          if (typeof window[args.success[0]] != 'undefined') {
            if (typeof window[args.success[0]][args.success[1]] == 'function') {

              window[args.success[0]][args.success[1]](response);
            }
          }
        } else if ( typeof args.success == 'string' ) {
          if ( typeof window[args.success] == 'function' ) {

            window[args.success](response);
          }
        }
        else if ( typeof args.success == 'function' ) {

          args.success(response);
        }
      }
    }
  }

  this.getTagId = function (tagname) {

    var feed_users = [];
    if ( typeof wdi_controller !== 'undefined' ) {
      feed_users = wdi_controller.feed_users;
      if (feed_users.length === 0) {
        var json = jQuery('#WDI_feed_users').val();
        if (typeof json !== 'undefined' && json !== '' ) {
          feed_users = JSON.parse(json);
        }
      }
    }
    else if(typeof window['wdi_all_tags'] !== "undefined"){
      feed_users = window['wdi_all_tags'];
    }

    for (var i in feed_users) {
      if (tagname === feed_users[i].username || "#" + tagname === feed_users[i].username) {
        if (typeof feed_users[i].tag_id !== "undefined") {
          return feed_users[i].tag_id;
        }
        return false;
      }
    }

    return false;
  };

  this.convertHashtagData = function ( data ) {
    var converted_data = {
      data: [],
      pagination: {}
    };
    if ( typeof data !== 'undefined' ) {
      if ( typeof data.paging !== "undefined" ) {
        converted_data.pagination = {
          cursors: { after: data.paging.cursors.after },
          next_url: data.paging.next
        }
      }
    }
    if ( typeof data != 'undefined' && typeof data.data != 'undefined' ) {
      for ( var i in data.data ) {
        var media = data.data[i];
        var media_type;
        if ( media.media_type === "IMAGE" ) {
          media_type = "image";
        }
        else if ( media.media_type === "VIDEO" ) {
          media_type = "video";
        }
        else {
          media_type = "carousel";
        }
        var converted = {
          "id": media.id,
          "user": {
            "id": "",
            "full_name": "",
            "profile_picture": "",
            "username": ""
          },
          "images": {
            "thumbnail": {
              "width": 150,
              "height": 150,
              "url": media.media_url
            },
            "low_resolution": {
              "width": 320,
              "height": 320,
              "url": media.media_url
            },
            "standard_resolution": {
              "width": 1080,
              "height": 1080,
              "url": media.media_url
            }
          },
          "created_time": media.timestamp,
          "caption": {
            "id": "",
            "text": media.caption,
            "created_time": "",
            "from": {
              "id": "",
              "full_name": "",
              "profile_picture": "",
              "username": ""
            }
          },
          "user_has_liked": (media.like_count > 0),
          "likes": {
            "count": media.like_count
          },
          "tags": [],
          "filter": "Normal",
          "comments": {
            "count": media.comments_count
          },
          "type": media_type,
          "link": media.permalink,
          "location": null,
          "attribution": null,
          "users_in_photo": []
        };
        if ( media.media_type === "IMAGE" || media.media_type === "CAROUSEL_ALBUM" ) {
          converted.images = {
            "thumbnail": {
              "width": 150,
              "height": 150,
              "url": media.media_url
            },
            "low_resolution": {
              "width": 320,
              "height": 320,
              "url": media.media_url
            },
            "standard_resolution": {
              "width": 1080,
              "height": 1080,
              "url": media.media_url
            }
          };
        }
        else if ( media.media_type === "VIDEO" ) {
          converted.videos = {
            "standard_resolution": {
              "width": 640,
              "height": 800,
              "url": media.thumb_url,
            },
            "low_bandwidth": {
              "width": 480,
              "height": 600,
              "url": media.thumb_url,
            },
            "low_resolution": {
              "width": 480,
              "height": 600,
              "url": media.thumb_url,
            }
          };
        }
        if ( media.media_type === "CAROUSEL_ALBUM" ) {
          converted.carousel_media = [];
          for ( var j in media.children.data ) {
            if ( media.children.data[j].media_type === "IMAGE" ) {
              var child = {
                "images": {
                  "thumbnail": {
                    "width": 150,
                    "height": 150,
                    "url": media.children.data[j].media_url
                  },
                  "low_resolution": {
                    "width": 320,
                    "height": 320,
                    "url": media.children.data[j].media_url
                  },
                  "standard_resolution": {
                    "width": 640,
                    "height": 640,
                    "url": media.children.data[j].media_url
                  }
                },
                "users_in_photo": [],
                "type": "image"
              };
            }
            else {
              var child = {
                "videos": {
                  "standard_resolution": {
                    "width": 640,
                    "height": 800,
                    "url": media.children.data[j].media_url,
                    "id": media.children.data[j].id
                  },
                  "low_bandwidth": {
                    "width": 480,
                    "height": 600,
                    "url": media.children.data[j].media_url,
                    "id": media.children.data[j].id
                  },
                  "low_resolution": {
                    "width": 480,
                    "height": 600,
                    "url": media.children.data[j].media_url,
                    "id": media.children.data[j].id
                  }
                },
                "users_in_photo": [],
                "type": "video"
              };
            }
            converted.carousel_media.push(child);
          }
        }
        converted_data.data.push(converted);
      }
    }
    return converted_data;
  };

  /**
   * Search for tags by name.
   *
   *
   * @definition success_callback => which function to call in case of success
   * @definition error_callback   => which function to call in case of error
   * @definition statusCode       => StatusCode object.
   *
   * @param tagname               =>  A valid tag name without a leading #. (eg. snowy, nofilter)
   * @param args = {
   *       success: 'success_callback',
   *       error:   'error_callback',
   *       statusCode : statusCode,
   *  }
   *
   *
   * if callback function is property of any other object just give it as array [ 'parent_object', 'callback_function']
   * or you can pass as callback function an anonymous function
   *
   *
   * @return object of founded media
   */

  this.searchForTagsByName = function (tagname, args) {
    var instagram = this,
      noArgument = false,
      successFlag = false,
      statusCode = this.statusCode,
      errorFlag = false;
    filter = this.getFilter('searchForTagsByName');

    if (typeof args == 'undefined' || args.length === 0) {
      noArgument = true;
    } else {
      if ('success' in args) {
        successFlag = true;
      }
      if ('error' in args) {
        errorFlag = true;
      }
      if ('statusCode' in args) {
        statusCode = args['statusCode'];
      }
    }

    /**
     * ToDo replace this by https://developers.facebook.com/docs/instagram-api/reference/ig-hashtag-search
     *
     * 1. business user flow
     * 2. no additional permission needed for app (?)
     *
     *
     * ***/

    var req_url = 'https://api.instagram.com/v1/tags/search?q=' + tagname + '&access_token=' + getAccessToken();
    var wdi_callback = function (cache_data) {
      if(cache_data === false){
        jQuery.ajax({
          type: 'POST',
          url: req_url,
          dataType: 'jsonp',
          success: function (response)
          {
            _this.setDataToCache(req_url,response);
            success(response);
          },
          error: function (response)
          {
            if (errorFlag) {
              if (typeof args['error'] == 'object' && args['error'].length == 2) {
                if (typeof window[args['error'][0]][args['error'][1]] == 'function') {
                  window[args['error'][0]][args['error'][1]](response);
                }
              } else
              if (typeof args['error'] == 'string') {
                if (typeof window[args['error']] == 'function') {
                  window[args['error']](response);
                }
              } else
              if (typeof args['error'] == 'function') {
                args['error'](response);
              }
            }
          },
          statusCode: statusCode
        });
      }else{
        success(cache_data);
      }

      function success(response) {
        if (successFlag) {
          if (typeof args.success == 'object' && args.success.length == 2) {
            if (typeof window[args.success[0]] != 'undefined') {
              if (typeof window[args.success[0]][args.success[1]] == 'function') {
                if (filter) {
                  response = filter(response, instagram.filterArguments);
                }
                window[args.success[0]][args.success[1]](response);
              }
            }
          } else
          if (typeof args.success == 'string') {
            if (typeof window[args.success] == 'function') {
              if (filter) {
                response = filter(response, instagram.filterArguments);
              }
              window[args.success](response);
            }
          } else
          if (typeof args.success == 'function') {
            if (filter) {
              response = filter(response, instagram.filterArguments);
            }
            args.success(response);
          }
        }
      }
    }
    _this.getDataFromCache(wdi_callback, req_url);


  }

  /*deprecated API*/
  /**
   * ToDo method called from updateUsersIfNecessary, not used anymore
   * TOREMOVE ?
   * ***/

  this.searchForUsersByName = function (username, args)
  {
    var instagram = this,
      noArgument = false,
      successFlag = false,
      statusCode = this.statusCode,
      errorFlag = false,
      filter = this.getFilter('searchForUsersByName');

    if (typeof args == 'undefined' || args.length === 0) {
      noArgument = true;
    } else {
      if ('success' in args) {
        successFlag = true;
      }
      if ('error' in args) {
        errorFlag = true;
      }
      if ('statusCode' in args) {
        statusCode = args['statusCode'];
      }
    }

    jQuery.ajax({
      type: 'POST',
      dataType: 'jsonp',
      url: 'https://api.instagram.com/v1/users/search?q=' + username + '&access_token=' + getAccessToken(),
      success: function (response)
      {
        if (successFlag) {
          if (typeof args.success == 'object' && args.success.length == 2) {
            if (typeof window[args.success[0]] != 'undefined') {
              if (typeof window[args.success[0]][args.success[1]] == 'function') {
                if (filter) {
                  response = filter(response, instagram.filterArguments);
                }
                response.args = args;
                window[args.success[0]][args.success[1]](response);
              }
            }
          } else
          if (typeof args.success == 'string') {
            if (typeof window[args.success] == 'function') {
              if (filter) {
                response = filter(response, instagram.filterArguments);
              }
              response.args = args;
              window[args.success](response);
            }
          } else
          if (typeof args.success == 'function') {
            if (filter) {
              response = filter(response, instagram.filterArguments);
            }
            response.args = args;
            args.success(response);
          }
        }
      },
      error: function (response)
      {
        if (errorFlag) {
          if (typeof args['error'] == 'object' && args['error'].length == 2) {
            if (typeof window[args['error'][0]][args['error'][1]] == 'function') {
              window[args['error'][0]][args['error'][1]](response);
            }
          } else
          if (typeof args['error'] == 'string') {
            if (typeof window[args['error']] == 'function') {
              window[args['error']](response);
            }
          } else
          if (typeof args['error'] == 'function') {
            args['error'](response);
          }
        }
      },
      statusCode: this.statusCode

    });
  }

  /**
   * Get the list of recent media liked by the owner of the access_token.
   *
   *
   * @definition success_callback => which function to call in case of success
   * @definition error_callback   => which function to call in case of error
   * @definition statusCode       => StatusCode object.
   * @param args = {
   *       success: 'success_callback',
   *       error:   'error_callback',
   *       statusCode : statusCode
   *  }
   *
   *
   * if callback function is property of any other object just give it as array [ 'parent_object', 'callback_function']
   * or you can pass as callback function an anonymous function
   *
   * @return object of founded media
   */
  /**
   * ToDo check if we still use liked media , or if this is allowed. Most probably thre is no endpoint for liked media on Graph API.
   * */
  this.getRecentLikedMedia = function (args)
  {
    var instagram = this,
      noArgument = false,
      successFlag = false,
      statusCode = this.statusCode,
      errorFlag = false,
      filter = this.getFilter('getRecentLikedMedia'),
      baseUrl = 'https://api.instagram.com/v1/users/self/media/liked?access_token=' + getAccessToken();

    if (typeof args == 'undefined' || args.length === 0) {
      noArgument = true;
    } else {
      if ('success' in args) {
        successFlag = true;
      }
      if ('error' in args) {
        errorFlag = true;
      }
      if ('statusCode' in args) {
        statusCode = args['statusCode'];
      }
      if ('args' in args) {
        argFlag = true;
      } else {
        args.args = {};
      }

      if ('count' in args) {
        args['count'] = parseInt(args['count']);
        if (!Number.isInteger(args['count']) || args['count'] <= 0) {
          args.count = 20;
        }
      } else {
        args.count = 20;
      }

      baseUrl += '&count=' + args.count;

      if ('next_max_like_id' in args) {
        baseUrl += '&next_max_like_id=' + args.next_max_like_id;
      }
    }

    jQuery.ajax({
      type: 'POST',
      dataType: 'jsonp',
      url: baseUrl,
      success: function (response)
      {
        if (successFlag) {
          if (typeof args.success == 'object' && args.success.length == 2) {
            if (typeof window[args.success[0]] != 'undefined') {
              if (typeof window[args.success[0]][args.success[1]] == 'function') {
                if (filter) {
                  response = filter(response, instagram.filterArguments, args.args);
                }
                window[args.success[0]][args.success[1]](response);
              }
            }
          } else
          if (typeof args.success == 'string') {
            if (typeof window[args.success] == 'function') {
              if (filter) {
                response = filter(response, instagram.filterArguments, args.args);
              }
              window[args.success](response);
            }
          } else
          if (typeof args.success == 'function') {
            if (filter) {
              response = filter(response, instagram.filterArguments, args.args);
            }
            args.success(response);
          }
        }
      },
      error: function (response)
      {
        if (errorFlag) {
          if (typeof args['error'] == 'object' && args['error'].length == 2) {
            if (typeof window[args['error'][0]][args['error'][1]] == 'function') {
              window[args['error'][0]][args['error'][1]](response);
            }
          } else
          if (typeof args['error'] == 'string') {
            if (typeof window[args['error']] == 'function') {
              window[args['error']](response);
            }
          } else
          if (typeof args['error'] == 'function') {
            args['error'](response);
          }
        }
      },
      statusCode: statusCode

    });
  }

  /* deprecated API */
  /**
   * Get the most recent media published by a user.
   * This endpoint requires the public_content scope if the user-id is not the owner of the access_token.
   *
   *
   * @definition success_callback => which function to call in case of success
   * @definition error_callback   => which function to call in case of error
   * @definition media_count      => number of media to request
   * @definition min_id           => Return media before this min_id.
   * @definition max_id           => Return media after this max_id.
   * @definition statusCode       => StatusCode object.
   *
   * @param args = {
   *       success : 'success_callback',
   *       error   : 'error_callback',
   *       statusCode : statusCode,
   *       count   : 'media_count',
   *       min_id  : 'min_id',
   *     max_id  : 'max_id',
   *     args: arguments to be passed to filtering function
   *  }
   *
   *
   * if callback function is property of any other object just give it as array [ 'parent_object', 'callback_function']
   * or you can pass as callback function an anonymous function
   *
   *
   * @return object of founded media
   */

  /**
   * ToDo replace this by two endpoints, depending on whether this user is business or not
   * business - https://developers.facebook.com/docs/instagram-api/reference/user/media#get-media
   * personal - https://developers.facebook.com/docs/instagram-basic-display-api/reference/user/media#reading   - requires more permissions
   * */
  this.getUserRecentMedia = function (user_id, args)
  {
    var instagram = this,
      noArgument = false,
      successFlag = false,
      argFlag = false,
      //internal default object for statusCode handling
      statusCode = this.statusCode,
      errorFlag = false,
      filter = this.getFilter('getUserRecentMedia'),
      baseUrl = 'https://api.instagram.com/v1/users/' + user_id + '/media/recent/?access_token=' + getAccessToken();

    if (typeof args == 'undefined' || args.length === 0) {
      noArgument = true;
    } else {
      if ('success' in args) {
        successFlag = true;
      }

      if ('statusCode' in args) {
        statusCode = args['statusCode'];
      }

      if ('args' in args) {
        argFlag = true;
      } else {
        args.args = {};
      }

      if ('error' in args) {
        errorFlag = true;
      }

      if ('count' in args) {
        args['count'] = parseInt(args['count']);
        if (!Number.isInteger(args['count']) || args['count'] <= 0) {
          args.count = 33;
        }
      } else {
        args.count = 33;
      }

      baseUrl += '&count=' + args.count;

      if ('min_id' in args) {
        baseUrl += '&min_id=' + args.min_id;
      }

      if ('max_id' in args) {
        baseUrl += '&max_id=' + args.max_id;
      }
    }
    jQuery.ajax({
      type: 'POST',
      dataType: 'jsonp',
      url: baseUrl,
      success: function (response)
      {
        if (typeof response["data"] === "undefined") response["data"] = [];

        if (successFlag) {
          if (typeof args.success == 'object' && args.success.length == 2) {
            if (typeof window[args.success[0]] != 'undefined') {
              if (typeof window[args.success[0]][args.success[1]] == 'function') {
                if (filter) {
                  response = filter(response, instagram.filterArguments, args.args);
                }
                window[args.success[0]][args.success[1]](response);
              }
            }
          } else
          if (typeof args.success == 'string') {
            if (typeof window[args.success] == 'function') {
              if (filter) {
                response = filter(response, instagram.filterArguments, args.args);
              }
              window[args.success](response);
            }
          } else
          if (typeof args.success == 'function') {
            if (filter) {
              response = filter(response, instagram.filterArguments, args.args);
            }
            args.success(response);
          }
        }
      },
      error: function (response)
      {
        if (errorFlag) {
          if (typeof args['error'] == 'object' && args['error'].length == 2) {
            if (typeof window[args['error'][0]][args['error'][1]] == 'function') {
              window[args['error'][0]][args['error'][1]](response);
            }
          } else
          if (typeof args['error'] == 'string') {
            if (typeof window[args['error']] == 'function') {
              window[args['error']](response);
            }
          } else
          if (typeof args['error'] == 'function') {
            args['error'](response);
          }
        }
      },
      statusCode: statusCode

    });
  }


  /**
   * Get the most recent media published by the owner of the access_token.
   *
   *
   * @definition success_callback => which function to call in case of success
   * @definition error_callback   => which function to call in case of error
   * @definition media_count      => number of media to request
   * @definition min_id           => Return media before this min_id.
   * @definition max_id           => Return media after this max_id.
   * @definition statusCode       => StatusCode object.
   *
   * @param args = {
   *       success : 'success_callback',
   *       error   : 'error_callback',
   *       count   : 'media_count',
   *       min_id  : 'min_id'
   *       max_id  : 'max_id'
   *      statusCode : statusCode
   *
   *  }
   *
   * @param next_url
   * @param iter
   *
   *
   * if callback function is property of any other object just give it as array [ 'parent_object', 'callback_function']
   * or you can pass as callback function an anonymous function
   *
   *
   * @return object of founded media
   */

  /**
   *  ToDo, we should not use this anymore, get user_id for current user and get user_id/media with graph or basic API
   */

  this.getUserMedia = function (args, next_url = '', iter = 0) {
    var instagram = this,
      noArgument = false,
      successFlag = false,
      statusCode = this.statusCode,
      errorFlag = false,
      argFlag = false,
      filter = this.getFilter('getUserMedia');

    if (typeof args == 'undefined' || args.length === 0) {
      noArgument = true;
    } else {
      if ('success' in args) {
        successFlag = true;
      }

      if ('error' in args) {
        errorFlag = true;
      }

      if ('statusCode' in args) {
        statusCode = args['statusCode'];
      }
      if ('args' in args) {
        argFlag = true;
      } else {
        args.args = {};
      }

      if ('count' in args) {
        args['count'] = parseInt(args['count']);
        if (!Number.isInteger(args['count']) || args['count'] <= 0) {
          args.count = 20;
        }
      } else {
        args.count = 20;
      }
    }

    jQuery.ajax({
      type: "POST",
      url: wdi_ajax.ajax_url,
      dataType:"json",
      data: {
        wdi_nonce:wdi_ajax.wdi_nonce,
        action:"wdi_getUserMedia",
        user_name:getUserName(),
        feed_id: wdi_ajax.feed_id,
        next_url:next_url,
        iter:iter,
      },
      success: function (response)
      {
        if (typeof response.error !== 'undefined' && errorItercount < 5) {
          errorItercount++;
          _this.getUserMedia(args, next_url, response.iter)
          return;
        }

        if( typeof response.iter !== 'undefined' && filterResponse.data.length < parseInt(wdi_ajax.number_of_photos) && iter < 20 && response.pagination.next_url != '') {
          next_url = response.pagination.next_url;
          filterResponse.data = filterResponse.data.concat(response.data);
          filterResponse.pagination = response.pagination;
          _this.getUserMedia( args, next_url, response.iter)
        } else {
          if(filterResponse.data.length != 0) {
            response = filterResponse;
          }
          response.meta = {"code": 200};
          if (successFlag) {
            if (typeof args.success == 'object' && args.success.length == 2) {
              if (typeof window[args.success[0]] != 'undefined') {
                if (typeof window[args.success[0]][args.success[1]] == 'function') {
                  if (filter) {
                    response = _this.addTags(response);
                    response = filter(response, instagram.filterArguments, args);
                  }
                  window[args.success[0]][args.success[1]](response);
                }
              }
            }
            else if (typeof args.success == 'string') {
              if (typeof window[args.success] == 'function') {
                if (filter) {
                  response = _this.addTags(response);
                  response = filter(response, instagram.filterArguments, args);
                }
                window[args.success](response);
              }
            }
            else if (typeof args.success == 'function') {
              args.success(response);
            }
          }
        }

      },
      error: function (response)
      {
        if (errorFlag) {
          if (typeof args['error'] == 'object' && args['error'].length == 2) {
            if (typeof window[args['error'][0]][args['error'][1]] == 'function') {
              window[args['error'][0]][args['error'][1]](response);
            }
          } else
          if (typeof args['error'] == 'string') {
            if (typeof window[args['error']] == 'function') {
              window[args['error']](response);
            }
          } else
          if (typeof args['error'] == 'function') {
            args['error'](response);
          }
        }
      },
      statusCode: statusCode
    });
  }

  /* deprecated API */
  /**
   * Get information about a user.
   * This endpoint requires the public_content scope if the user-id is not the owner of the access_token.
   *
   *
   * @definition success_callback => which function to call in case of success
   * @definition error_callback   => which function to call in case of error
   * @definition statusCode       => StatusCode object.
   *
   * @param args = {
   *       success : 'success_callback',
   *       error   : 'error_callback'
   *       statusCode : statusCode
   *  }
   *
   *
   * if callback function is property of any other object just give it as array [ 'parent_object', 'callback_function']
   * or you can pass as callback function an anonymous function
   *
   *
   * @return object of founded info
   */

  /*
  * ToDo use to get user's ID having his token. https://developers.facebook.com/docs/instagram-basic-display-api/reference/user
  * should be used for both business and personal accounts
  * */
  this.getUserInfo = function (user_id, args) {
    var instagram = this,
      noArgument = false,
      successFlag = false,
      statusCode = this.statusCode,
      errorFlag = false,
      filter = this.getFilter('getUserInfo');

    if (typeof args == 'undefined' || args.length === 0) {
      noArgument = true;
    } else {
      if ('success' in args) {
        successFlag = true;
      }

      if ('error' in args) {
        errorFlag = true;
      }

      if ('statusCode' in args) {
        statusCode = args['statusCode'];
      }
    }

    jQuery.ajax({
      type: 'POST',
      dataType: 'jsonp',
      url: 'https://api.instagram.com/v1/users/' + user_id + '/?access_token=' + getAccessToken(),
      success: function (response)
      {
        if (successFlag) {
          if (typeof args.success == 'object' && args.success.length == 2) {
            if (typeof window[args.success[0]] != 'undefined') {
              if (typeof window[args.success[0]][args.success[1]] == 'function') {
                if (filter) {
                  response = filter(response, instagram.filterArguments);
                }
                window[args.success[0]][args.success[1]](response);
              }
            }
          } else
          if (typeof args.success == 'string') {
            if (typeof window[args.success] == 'function') {
              if (filter) {
                response = filter(response, instagram.filterArguments);
              }
              window[args.success](response);
            }
          } else
          if (typeof args.success == 'function') {
            if (filter) {
              response = filter(response, instagram.filterArguments);
            }
            args.success(response);
          }
        }
      },
      error: function (response)
      {
        if (errorFlag) {
          if (typeof args['error'] == 'object' && args['error'].length == 2) {
            if (typeof window[args['error'][0]][args['error'][1]] == 'function') {
              window[args['error'][0]][args['error'][1]](response);
            }
          } else
          if (typeof args['error'] == 'string') {
            if (typeof window[args['error']] == 'function') {
              window[args['error']](response);
            }
          } else
          if (typeof args['error'] == 'function') {
            args['error'](response);
          }
        }
      },
      statusCode: statusCode

    });
  }

  /**
   * Get information about the owner of the access_token.
   *
   *
   * @definition success_callback => which function to call in case of success
   * @definition error_callback   => which function to call in case of error
   * @definition statusCode       => StatusCode object.
   *
   * @param args = {
   *       success : 'success_callback',
   *       error   : 'error_callback'
   *       statusCode : statusCode
   *  }
   *
   *
   * if callback function is property of any other object just give it as array [ 'parent_object', 'callback_function']
   * or you can pass as callback function an anonymous function
   *
   *
   * @return object of founded info
   */

  /**
   * ToDo to remove
   * this part has been replaced with php․
   * **/
  this.getSelfInfo = function (args) {
    var instagram = this,
      noArgument = false,
      successFlag = false,
      statusCode = this.statusCode,
      errorFlag = false,
      filter = this.getFilter('getSelfInfo');
    if (typeof args == 'undefined' || args.length === 0) {
      noArgument = true;
    }
    else {
      if ('success' in args) {
        successFlag = true;
      }
      if ('error' in args) {
        errorFlag = true;
      }
      if ('statusCode' in args) {
        statusCode = args['statusCode'];
      }
    }
    var req_url = 'https://graph.facebook.com/v3.2/' + getUserId() + '?fields=id,ig_id,username,name,biography,profile_picture_url,followers_count,follows_count,media_count,website&access_token=' + getAccessToken();
    var wdi_callback = function (cache_data) {
      if (cache_data === false) {
        jQuery.ajax({
          type: 'POST',
          dataType: 'jsonp',
          url: req_url,
          statusCode: statusCode,
          success: function (response) {
            _this.setDataToCache(req_url, response);
            if (successFlag) {
              if (typeof args.success == 'object' && args.success.length == 2) {
                if (typeof window[args.success[0]] != 'undefined') {
                  if (typeof window[args.success[0]][args.success[1]] == 'function') {
                    if (filter) {
                      response.meta = {"code": 200};
                      response = filter(response, instagram.filterArguments);
                    }
                    window[args.success[0]][args.success[1]](response);
                  }
                }
              }
              else {
                if (typeof args.success == 'string') {
                  if (typeof window[args.success] == 'function') {
                    if (filter) {
                      response.meta = {"code": 200};
                      response = filter(response, instagram.filterArguments);
                    }
                    window[args.success](response);
                  }
                }
                else {
                  if (typeof args.success == 'function') {
                    if (filter) {
                      response.meta = {"code": 200};
                      response = filter(response, instagram.filterArguments);
                    }
                    args.success(response);
                  }
                }
              }
            }
          },
          error: function (response) {
            if (errorFlag) {
              if (typeof args['error'] == 'object' && args['error'].length == 2) {
                if (typeof window[args['error'][0]][args['error'][1]] == 'function') {
                  window[args['error'][0]][args['error'][1]](response);
                }
              }
              else if (typeof args['error'] == 'string') {
                if (typeof window[args['error']] == 'function') {
                  window[args['error']](response);
                }
              }
              else if (typeof args['error'] == 'function') {
                args['error'](response);
              }
            }
          }
        });
      }
      else {
        success(cache_data);
      }

      function success(response) {
        if (successFlag) {
          if (typeof args.success == 'object' && args.success.length == 2) {
            if (typeof window[args.success[0]] != 'undefined') {
              if (typeof window[args.success[0]][args.success[1]] == 'function') {
                if (filter) {
                  response = filter(response, instagram.filterArguments);
                }
                window[args.success[0]][args.success[1]](response);
              }
            }
          }
          else {
            if (typeof args.success == 'string') {
              if (typeof window[args.success] == 'function') {
                if (filter) {
                  response = filter(response, instagram.filterArguments);
                }
                window[args.success](response);
              }
            }
            else {
              if (typeof args.success == 'function') {
                if (filter) {
                  response = filter(response, instagram.filterArguments);
                }
                args.success(response);
              }
            }
          }
        }
      }
    }
    _this.getDataFromCache(wdi_callback, req_url);
  }


  /**
   * Get a list of recent comments on a media object.
   * The public_content permission scope is required to get comments for a media
   * that does not belong to the owner of the access_token.
   *
   * @media_id                    => id of the media which comments must be getted
   * @definition success_callback => which function to call in case of success
   * @definition error_callback   => which function to call in case of error
   * @definition statusCode       => StatusCode object.
   *
   * @param args = {
   *       success : 'success_callback',
   *       error   : 'error_callback'
   *       statusCode : statusCode
   *  }
   *
   *
   * if callback function is property of any other object just give it as array [ 'parent_object', 'callback_function']
   * or you can pass as callback function an anonymous function
   *
   *
   * @return object of founded comments
   */

  /**
   * ToDo
   * only for business - https://developers.facebook.com/docs/instagram-api/reference/media/comments#comments - get comments for given media
   * we may need it later . disabled now ?
   * may require custom permissions
   * */
  this.getRecentMediaComments = function (media_id, args)
  {
    var instagram = this,
      noArgument = false,
      successFlag = false,
      statusCode = this.statusCode,
      errorFlag = false,
      filter = this.getFilter('getRecentMediaComments');
    if (typeof args == 'undefined' || args.length === 0) {
      noArgument = true;
    } else {
      if ('success' in args) {
        successFlag = true;
      }

      if ('error' in args) {
        errorFlag = true;
      }

      if ('statusCode' in args) {
        statusCode = args['statusCode'];
      }
    }

    var req_url = 'https://api.instagram.com/v1/media/' + media_id + '/comments?access_token=' + getAccessToken();
    var wdi_callback = function (cache_data) {
      if(cache_data === false){
        jQuery.ajax({
          type: 'POST',
          dataType: 'jsonp',
          url: req_url,
          success: function (response)
          {
            _this.setDataToCache(req_url,response);
            success(response);
          },
          error: function (response)
          {
            if (errorFlag) {
              if (typeof args['error'] == 'object' && args['error'].length == 2) {
                if (typeof window[args['error'][0]][args['error'][1]] == 'function') {
                  window[args['error'][0]][args['error'][1]](response);
                }
              } else
              if (typeof args['error'] == 'string') {
                if (typeof window[args['error']] == 'function') {
                  window[args['error']](response);
                }
              } else
              if (typeof args['error'] == 'function') {
                args['error'](response);
              }
            }
          },
          statusCode: statusCode

        });
      }else{
        success(cache_data);
      }
      function success(response) {
        if (successFlag) {
          if (typeof args.success == 'object' && args.success.length == 2) {
            if (typeof window[args.success[0]] != 'undefined') {
              if (typeof window[args.success[0]][args.success[1]] == 'function') {
                if (filter) {
                  response = filter(response, instagram.filterArguments);
                }
                window[args.success[0]][args.success[1]](response);
              }
            }
          } else
          if (typeof args.success == 'string') {
            if (typeof window[args.success] == 'function') {
              if (filter) {
                response = filter(response, instagram.filterArguments);
              }
              window[args.success](response);
            }
          } else
          if (typeof args.success == 'function') {
            if (filter) {
              response = filter(response, instagram.filterArguments);
            }
            args.success(response);
          }
        }
      }
    }
    _this.getDataFromCache(wdi_callback, req_url);
  }


  /**
   * Get a list of users who have liked this media.
   *
   * @media_id                    => id of the media which comments must be getted
   * @definition success_callback => which function to call in case of success
   * @definition error_callback   => which function to call in case of error
   * @definition statusCode       => StatusCode object.
   *
   * @param args = {
   *       success : 'success_callback',
   *       error   : 'error_callback'
   *       statusCode : statusCode
   *  }
   *
   *
   * if callback function is property of any other object just give it as array [ 'parent_object', 'callback_function']
   * or you can pass as callback function an anonymous function
   *
   *
   * @return object of founded comments
   */

  /**
   * ToDo
   * Probably we do not need this. We should get likes number in /media/media_id/
   *
   * */
  this.getRecentMediaLikes = function (media_id, args)
  {
    var instagram = this,
      noArgument = false,
      successFlag = false,
      statusCode = this.statusCode,
      errorFlag = false,
      filter = this.getFilter('getRecentMediaLikes');

    if (typeof args == 'undefined' || args.length === 0) {
      noArgument = true;
    } else {
      if ('success' in args) {
        successFlag = true;
      }

      if ('error' in args) {
        errorFlag = true;
      }

      if ('statusCode' in args) {
        statusCode = args['statusCode'];
      }
    }

    jQuery.ajax({
      type: 'POST',
      dataType: 'jsonp',
      url: 'https://api.instagram.com/v1/media/' + media_id + '/likes?access_token=' + getAccessToken(),
      success: function (response)
      {
        if (successFlag) {
          if (typeof args.success == 'object' && args.success.length == 2) {
            if (typeof window[args.success[0]] != 'undefined') {
              if (typeof window[args.success[0]][args.success[1]] == 'function') {
                if (filter) {
                  response = filter(response, instagram.filterArguments);
                }
                window[args.success[0]][args.success[1]](response);
              }
            }
          } else
          if (typeof args.success == 'string') {
            if (typeof window[args.success] == 'function') {
              if (filter) {
                response = filter(response, instagram.filterArguments);
              }
              window[args.success](response);
            }
          } else
          if (typeof args.success == 'function') {
            if (filter) {
              response = filter(response, instagram.filterArguments);
            }
            args.success(response);
          }
        }
      },
      error: function (response)
      {
        if (errorFlag) {
          if (typeof args['error'] == 'object' && args['error'].length == 2) {
            if (typeof window[args['error'][0]][args['error'][1]] == 'function') {
              window[args['error'][0]][args['error'][1]](response);
            }
          } else
          if (typeof args['error'] == 'string') {
            if (typeof window[args['error']] == 'function') {
              window[args['error']](response);
            }
          } else
          if (typeof args['error'] == 'function') {
            args['error'](response);
          }
        }
      },
      statusCode: statusCode

    });
  }


  /**
   * make an ajax request based on url
   *
   *
   * @definition success_callback => which function to call in case of success
   * @definition error_callback   => which function to call in case of error
   * @definition statusCode       => StatusCode object.
   *
   * @param args = {
   *       success : 'success_callback',
   *       error   : 'error_callback',
   *       statusCode : statusCode,
   *       args.args : arguments to be passed to filter function
   *  }
   *
   *
   * if callback function is property of any other object just give it as array [ 'parent_object', 'callback_function']
   * or you can pass as callback function an anonymous function
   *
   *
   * @return object of founded media
   */
  this.requestByUrl = function (requestUrl, args)
  {
    var instagram = this,
      noArgument = false,
      successFlag = false,
      errorFlag = false,
      argFlag = false,
      statusCode = this.statusCode,
      filter = this.getFilter('requestByUrl'),
      urlParts,
      urlPart;

    //changing access token to random one
    urlParts = requestUrl.split('?')[1].split('&');
    for (var i = 0; i < urlParts.length; i++) {
      urlParts[i] = urlParts[i].split('=');
      if (urlParts[i][0] == 'access_token') {
        urlParts[i][1] = getAccessToken();
      }
      urlParts[i] = urlParts[i].join('=');
    }
    urlParts = urlParts.join('&');
    requestUrl = requestUrl.split('?')[0] + '?' + urlParts;


    if (typeof args == 'undefined' || args.length === 0) {
      noArgument = true;
    } else {
      if ('success' in args) {
        successFlag = true;
      }

      if ('args' in args) {
        argFlag = true;
      } else {
        args.args = {};
      }


      if ('error' in args) {
        errorFlag = true;
      }

      if ('statusCode' in args) {
        statusCode = args['statusCode'];
      }
    }

    var wdi_callback = function (cache_data) {
      if(cache_data === false){
        jQuery.ajax({
          type: 'POST',
          dataType: 'jsonp',
          url: requestUrl,
          success: function (response)
          {
            _this.setDataToCache(requestUrl,response);
            if (successFlag) {
              if (typeof args.success == 'object' && args.success.length == 2) {
                if (typeof window[args.success[0]] != 'undefined') {
                  if (typeof window[args.success[0]][args.success[1]] == 'function') {
                    if (filter) {
                      response = filter(response, instagram.filterArguments, args.args);
                    }
                    window[args.success[0]][args.success[1]](response);
                  }
                }
              } else
              if (typeof args.success == 'string') {
                if (typeof window[args.success] == 'function') {
                  if (filter) {
                    response = filter(response, instagram.filterArguments, args.args);
                  }
                  window[args.success](response);
                }
              } else
              if (typeof args.success == 'function') {
                if (filter) {
                  response = filter(response, instagram.filterArguments, args.args);
                }
                args.success(response);
              }
            }
          },
          error: function (response)
          {
            if (errorFlag) {
              if (typeof args['error'] == 'object' && args['error'].length == 2) {
                if (typeof window[args['error'][0]][args['error'][1]] == 'function') {
                  window[args['error'][0]][args['error'][1]](response);
                }
              } else
              if (typeof args['error'] == 'string') {
                if (typeof window[args['error']] == 'function') {
                  window[args['error']](response);
                }
              } else
              if (typeof args['error'] == 'function') {
                args['error'](response);
              }
            }
          },
          statusCode: statusCode
        });
      }
      else{
        success(cache_data);
      }
      function success(response) {
        if (successFlag) {
          if (typeof args.success == 'object' && args.success.length == 2) {
            if (typeof window[args.success[0]] != 'undefined') {
              if (typeof window[args.success[0]][args.success[1]] == 'function') {
                if (filter) {
                  response = filter(response, instagram.filterArguments, args.args);
                }
                window[args.success[0]][args.success[1]](response);
              }
            }
          } else
          if (typeof args.success == 'string') {
            if (typeof window[args.success] == 'function') {
              if (filter) {
                response = filter(response, instagram.filterArguments, args.args);
              }
              window[args.success](response);
            }
          } else
          if (typeof args.success == 'function') {
            if (filter) {
              response = filter(response, instagram.filterArguments, args.args);
            }
            args.success(response);
          }
        }
      }
    }
    _this.getDataFromCache(wdi_callback, requestUrl);
  }

  this.getDataFromCache = function (callback, cache_name, async) {
    if(typeof async === "undefined"){
      async = true;
    }

    jQuery.ajax({
      type: "POST",
      async: async,
      url: wdi_ajax.ajax_url,
      dataType:"json",
      data: {
        wdi_cache_name:cache_name,
        wdi_nonce:wdi_ajax.wdi_nonce,
        WDI_MINIFY:wdi_ajax.WDI_MINIFY,
        task:"get",
        action:"wdi_cache",
      },
      success: function(data){
        if(data["success"]){
          if(typeof data["cache_data"] !== "undefined" && data["cache_data"] !== null){
            var json_data = JSON.parse(data["cache_data"]);
            callback(json_data);
          }else{
            callback(false);
          }
        }else {
          callback(false);
        }
      }
    });
  }

  this.setDataToCache = function (cache_name, response) {
    jQuery.ajax({
      type: "POST",
      url: wdi_ajax.ajax_url,
      dataType:"json",
      data: {
        wdi_cache_name:cache_name,
        wdi_cache_response:JSON.stringify(response),
        wdi_nonce:wdi_ajax.wdi_nonce,
        task:"set",
        action:"wdi_cache",
      },
      success: function(data){

      }
    });
  }
}