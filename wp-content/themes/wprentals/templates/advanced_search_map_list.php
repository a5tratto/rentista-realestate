<?php
$adv_submit             =   wpestate_get_template_link('advanced_search_results.php');
$guest_list             =   wpestate_get_guest_dropdown();
$local_args_search_map_list = wpestate_get_select_arguments();
$allowed_html = array();
$allowed_html_list =    array('li' => array(
                                        'data-value'        =>array(),
                                        'role'              => array(),
                                        'data-parentcity'   =>array(),
                                        'data-value2'       =>array()
                        ) );
$action_select_list =   wpestate_get_action_select_list($local_args_search_map_list);
$categ_select_list  =   wpestate_get_category_select_list($local_args_search_map_list);
$select_city_list   =   wpestate_get_city_select_list($local_args_search_map_list);
$select_area_list   =   wpestate_get_area_select_list($local_args_search_map_list);
$select_county_state_list = array();
$min_price_slider   =   floatval(wprentals_get_option('wp_estate_show_slider_min_price',''));
$max_price_slider   =   floatval(wprentals_get_option('wp_estate_show_slider_max_price',''));
$wpestate_where_currency     =   esc_html( wprentals_get_option('wp_estate_where_currency_symbol', '') );
$wpestate_currency  =   esc_html( wprentals_get_option('wp_estate_currency_label_main', '') );
       
if(isset($_GET['price_max'])){
    $max_price_slider = floatval($_GET['price_max']);
}

if(isset($_GET['price_low'])){
    $min_price_slider = floatval($_GET['price_low']);
}


if ($wpestate_where_currency == 'before') {
    $price_slider_label = esc_html($wpestate_currency) . number_format($min_price_slider).' '.esc_html__( 'to','wprentals').' '.esc_html($wpestate_currency) . number_format($max_price_slider);
}else {
    $price_slider_label =  number_format($min_price_slider).esc_html($wpestate_currency).' '.esc_html__( 'to','wprentals').' '.number_format($max_price_slider).esc_html($wpestate_currency);
} 

?>

<div id="advanced_search_map_list">
    <div class="advanced_search_map_list_container">
        <?php 

        if( wprentals_get_option('wp_estate_use_geo_location','')=='yes'){

        $radius_measure = wprentals_get_option('wp_estate_geo_radius_measure','');
        $radius_value   = wprentals_get_option('wp_estate_initial_radius','');

        ?>
            <div class="col-md-12 radius_wrap">
                <input type="text" id="geolocation_search" class="form-control" name="geolocation_search" placeholder="<?php esc_html_e('Location','wprentals');?>" value="">
                <input type="hidden" id="geolocation_lat" name="geolocation_lat">
                <input type="hidden" id="geolocation_long" name="geolocation_lat">
            </div>  
        
            <div class="col-md-3 slider_radius_wrap">
                <div class="label_radius"><?php esc_html_e('Radius:','wprentals');?> <span class="radius_value"><?php print esc_html($radius_value.' '.$radius_measure);?></span></div>
            </div>

            <div class="col-md-9 slider_radius_wrap">
                <div id="wpestate_slider_radius"></div>
                <input type="hidden" id="geolocation_radius" name="geolocation_radius" value="<?php print esc_html($radius_value);?>">
            </div>
        <?php
        }
        ?>
        
        <div class="advanced_search_map_list_container_trigger">
        
        
        <?php
        $search_type    =   wprentals_get_option('wp_estate_adv_search_type','');
 
        if($search_type == 'oldtype' || $search_type=='newtype'){ ?>
        
  
            <div class="col-md-6 map_icon">
                <?php
                $show_adv_search_general            =   wprentals_get_option('wp_estate_wpestate_autocomplete','');
                $wpestate_internal_search           =   '';
                $search_location_tax                =   'tax';
                $search_location                    =   '';
                $advanced_city                      =   '';
                $advanced_area                      =   '';
                $advanced_country                   =   '';
                $property_admin_area                =   '';

               
                if(isset($_GET['search_location'])){
                    $search_location = sanitize_text_field($_GET['search_location']);
                }

                if(isset($_GET['stype']) && $_GET['stype']=='meta'){
                    $search_location_tax = 'meta';
                }

                if(isset($_GET['advanced_city']) ){
                    $advanced_city = sanitize_text_field($_GET['advanced_city']);
                }

                if(isset($_GET['advanced_area']) ){
                    $advanced_area = sanitize_text_field($_GET['advanced_area']);
                }

                if(isset($_GET['advanced_country']) ){
                    $advanced_country = sanitize_text_field($_GET['advanced_country']);
                }

                 if(isset($_GET['property_admin_area']) ){
                    $property_admin_area = sanitize_text_field($_GET['property_admin_area']);
                }
    
    
                if($show_adv_search_general=='no'){
                    $wpestate_internal_search='_autointernal';
                    print '<input type="hidden" class="stype" id="stype" name="stype" value="'.esc_attr($search_location_tax).'">';
                }
                $wpestate_autocomplete_use_list             =   wprentals_get_option('wp_estate_wpestate_autocomplete_use_list','');  
                if ($wpestate_autocomplete_use_list=='yes' && $show_adv_search_general=='no'){
                   print wprentals_location_custom_dropwdown($_REQUEST,esc_html__('Where do you want to go ?','wprentals'));
                }else{
                    print '<input type="text"    id="search_location'.esc_attr($wpestate_internal_search).'"      class="form-control" name="search_location" placeholder="'.esc_html__('Where do you want to go ?','wprentals').'" value="'.esc_attr($search_location).'"  >';              
                } 
           
                ?>
               <input type="hidden" id="search_location_city" value="<?php if(isset( $_GET['advanced_city'] )){echo wp_kses( esc_attr($_GET['advanced_city']),$allowed_html);}?>" >
                <input type="hidden" id="search_location_area" value="<?php if(isset( $_GET['advanced_area'] )){echo wp_kses ( esc_attr($_GET['advanced_area']),$allowed_html);}?>" >
                <input type="hidden" id="search_location_country"    value="<?php if(isset( $_GET['advanced_country'] )){echo wp_kses ( esc_attr($_GET['advanced_country']),$allowed_html);}?>" >              
                <input type="hidden" id="property_admin_area" name="property_admin_area"  value="<?php if(isset( $_GET['property_admin_area'] )){echo wp_kses ( esc_attr($_GET['property_admin_area']),$allowed_html);}?>" >

            </div>

        
                        
        <div class="col-md-3 has_calendar calendar_icon ">
            <input type="text" id="check_in"        class="form-control" name="check_in"  placeholder="<?php esc_html_e('Check-In','wprentals');?>" value="<?php if(isset( $_GET['check_in'] )){echo wp_kses (  esc_attr($_GET['check_in']),$allowed_html);}?>" >       
        </div> 


        <div class="col-md-3 has_calendar calendar_icon ">
            <input type="text" id="check_out"       class="form-control" name="check_out" placeholder="<?php esc_html_e('Check-Out','wprentals');?>" value="<?php if(isset( $_GET['check_out'] )){echo wp_kses( esc_attr($_GET['check_out']),$allowed_html);}?>">
        </div>

        <div class="col-md-3">
            <div class="dropdown form-control guest_form" >
                <div data-toggle="dropdown" id="guest_no_drop" class="filter_menu_trigger" data-value="all">
                <?php 
                if(isset($_GET['guest_no']) && $_GET['guest_no']!=''){
                    echo wp_kses( esc_html($_GET['guest_no']), $allowed_html);
                }else{
                    esc_html_e('Guests','wprentals');
                }
                ?> 
                    
                    
               <span class="caret caret_filter"></span> </div>           
                <input type="hidden" name="guest_no" id="guest_no" value="<?php if(isset( $_GET['guest_no'] )){echo wp_kses ( esc_attr($_GET['guest_no']),$allowed_html);}?>">
                <ul  class="dropdown-menu filter_menu" role="menu" aria-labelledby="guest_no_input">
                    <?php print trim($guest_list);?>
                </ul>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="dropdown form-control rooms_icon" >
                <div data-toggle="dropdown" id="rooms_no" class="filter_menu_trigger" data-value="all"> <?php esc_html_e('Rooms','wprentals');?> <span class="caret caret_filter"></span> </div>           
                <input type="hidden" name="property_rooms"  id="property_rooms" value="">
                <ul  class="dropdown-menu filter_menu" role="menu" aria-labelledby="rooms_no">
                    <?php echo wpestate_get_rooms_dropdown();?>
                </ul>
            </div>
        </div>
            
        
        <div class="col-md-3">
            <div class="dropdown form-control types_icon" id="categ_list" >
                <div data-toggle="dropdown" id="adv_categ" class="filter_menu_trigger" data-value="all"> <?php  echo wpestate_category_labels_dropdowns('main');?> <span class="caret caret_filter"></span> </div>           
                <input type="hidden" name="property_category" id="property_category" value="">
                <ul  class="dropdown-menu filter_menu" role="menu" aria-labelledby="adv_categ">
                    <?php  print wp_kses($categ_select_list,$allowed_html_list); ?>
                </ul>        
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="dropdown form-control actions_icon">
                <div data-toggle="dropdown" id="adv_actions" class="filter_menu_trigger" data-value="all"> <?php echo wpestate_category_labels_dropdowns('second');?> <span class="caret caret_filter"></span> </div>           
                <input type="hidden" name="property_action_category" id="property_action_category" value="">
                <ul  class="dropdown-menu filter_menu" role="menu" aria-labelledby="adv_actions">
                    <?php print wp_kses($action_select_list,$allowed_html_list);?>
                </ul>        
            </div>
        </div>
     
        <div class="col-md-3">
            <div class="dropdown form-control bedrooms_icon" >
                <div data-toggle="dropdown" id="beds_no" class="filter_menu_trigger" data-value="all"><?php echo  esc_html__( 'Bedrooms','wprentals');?> <span class="caret caret_filter"></span> </div>           
                <input type="hidden" name="property_bedrooms" id="property_bedrooms" value="">
                <ul  class="dropdown-menu filter_menu" role="menu" aria-labelledby="beds_no">
                    <?php echo wpestate_get_bedrooms_dropdown(); ?>
                </ul>
            </div>
        </div>
                 
        <div class="col-md-3">
            <div class="dropdown form-control baths_icon" >
                <div data-toggle="dropdown" id="baths_no" class="filter_menu_trigger" data-value="all"><?php echo esc_html__( 'Baths','wprentals');?> <span class="caret caret_filter"></span> </div>           
                <input type="hidden" name="property_bathrooms" id="property_bathrooms"  value="">
                <ul  class="dropdown-menu filter_menu" role="menu" aria-labelledby="baths_no">
                    <?php echo wpestate_get_baths_dropdown(); ?>
                </ul>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="adv_search_slider">
                <p>
                    <label><?php esc_html_e('Price range:','wprentals');?></label>
                    <span id="amount"  style="border:0; color:#f6931f; font-weight:bold;"><?php print wpestate_show_price_label_slider($min_price_slider,$max_price_slider,$wpestate_currency,$wpestate_where_currency);?></span>
                </p>
                <div id="slider_price"></div>
                <input type="hidden" id="price_low"  name="price_low"  value="<?php echo wpestate_price_default_convert ($min_price_slider);?>" />
                <input type="hidden" id="price_max"  name="price_max"  value="<?php echo wpestate_price_default_convert ($max_price_slider);?>" />
            </div>
        </div>
        
        <?php    
        $extended_search= wprentals_get_option('wp_estate_show_adv_search_extended','');
        if($extended_search=='yes'){
            wpestate_show_extended_search('');
            print '<div class="adv_extended_options_text" id="adv_extended_options_text_adv" data-pageid="'.esc_attr( $post->ID).'">'.esc_html__('More Options','wprentals').'</div>';
      
        } 
        ?>
     
       
        <?php include(locate_template('libs/internal_autocomplete_wpestate.php')); ?>
       
        <?php } else{
            // for type 3 and 4

            if($search_type=='type4'){
                wpestate_search_type_inject($categ_select_list,$action_select_list,'half');
            }
         
            $adv_search_what            =  wprentals_get_option('wp_estate_adv_search_what');
        
            foreach($adv_search_what as $key=>$search_field){
                $search_col         =   3;
                $search_col_price   =   6;
                if($search_field=='property_price'){
                    $search_col=$search_col_price;
                }
                if(strtolower($search_field)=='location' ){
                    $search_col=$search_col_price;
                }
                print '<div class="halfx col-md-'.esc_attr($search_col).' '.str_replace(" ","_", stripslashes($search_field) ).' ">';
                    print wpestate_show_search_field_new($_REQUEST,'half',$search_field,$action_select_list,$categ_select_list,$select_city_list,$select_area_list,$key);
                  
                print '</div>'; 
            
            }
            
          
            $extended_search= wprentals_get_option('wp_estate_show_adv_search_extended','');
            if($extended_search=='yes'){
                wpestate_show_extended_search('');
                if( isset($post->ID) ){
                    $post_id=$post->ID;
                }else{
                    $post_id='';
                }
                print '<div class="adv_extended_options_text" id="adv_extended_options_text_adv" data-pageid="'.esc_attr($post_id).'">'.esc_html__('More Options','wprentals').'</div>';
            } 
           include(locate_template('libs/internal_autocomplete_wpestate.php'));
        } ?>
        </div>
    </div>
</div>


<div id="advanced_search_map_list_hidden">
    <div class="col-md-2">
        <div class="show_filters" id="adv_extended_options_show_filters"><?php esc_html_e('Search Options','wprentals')?></div>
    </div>  
</div>