<?php 
global $post;
$adv_search_what            =   wprentals_get_option('wp_estate_adv_search_what');
$show_adv_search_visible    =   wprentals_get_option('wp_estate_show_adv_search_visible','');
$close_class                =   '';

if($show_adv_search_visible=='no'){
    $close_class='adv-search-1-close';
}

if(isset( $post->ID)){
    $post_id = $post->ID;
}else{
    $post_id = '';
}

$extended_search    =   wprentals_get_option('wp_estate_show_adv_search_extended','');
$extended_class     =   '';

if ( $extended_search =='yes' ){
    $extended_class='adv_extended_class';
    if($show_adv_search_visible=='no'){
        $close_class='adv-search-1-close-extended';
    }      
}
    
$wpestate_header_type                   =   get_post_meta ( $post->ID, 'header_type', true);
$wpestate_global_header_type   =   wprentals_get_option('wp_estate_header_type','');

$google_map_lower_class='';
 if (!$wpestate_header_type==0){  // is not global settings
    if ($wpestate_header_type==5){ 
        $google_map_lower_class='adv_lower_class';
    }
}else{    // we don't have particular settings - applt global header          
    if($wpestate_global_header_type==4){
        $google_map_lower_class='adv_lower_class';
    }
} // end if header
    
    
?>

 
<div class="adv-1-wrapper"> 
</div>  

<div class="adv-search-1 <?php print esc_attr($google_map_lower_class.' '.$close_class.' '.$extended_class);?>" id="adv-search-1" data-postid="<?php print esc_attr($post_id); ?>"> 

  
    <form  method="get"  id="main_search" action="<?php print esc_url($adv_submit); ?>" >
        <?php
        if (function_exists('icl_translate') ){
            print do_action( 'wpml_add_language_form_field' );
        }
        ?>
        <div class="col-md-4 map_icon">    
            <?php 
            $show_adv_search_general            =   wprentals_get_option('wp_estate_wpestate_autocomplete','');
            $wpestate_internal_search           =   '';
            $search_location                    =   '';
            $search_location_tax                =   'tax';
            
            if(isset($_GET['search_location'])){
                $search_location = sanitize_text_field($_GET['search_location']);
            }

            if(isset($_GET['stype']) && $_GET['stype']=='meta'){
                $search_location_tax = 'meta';
            }

            
            if($show_adv_search_general=='no'){
                $wpestate_internal_search='_autointernal';
                print '<input type="hidden" class="stype" id="stype" name="stype" value="'.esc_attr($search_location_tax).'">';
            }
            
            $wpestate_autocomplete_use_list             =   wprentals_get_option('wp_estate_wpestate_autocomplete_use_list','');  
            if ($wpestate_autocomplete_use_list=='yes' && $show_adv_search_general=='no'){
                print wprentals_location_custom_dropwdown($_GET,esc_html__('Where do you want to go ?','wprentals'));
            }else{
                print '<input type="text"    id="search_location'.esc_attr($wpestate_internal_search).'"      class="form-control" name="search_location" placeholder="'.esc_html__('Where do you want to go ?','wprentals').'" value="'.esc_attr($search_location).'" >';              
            } 

            ?>
            
            <?php
            $advanced_city='';
            if(isset($_GET['advanced_city']) && $_GET['advanced_city']!=''){
                $advanced_city = sanitize_text_field($_GET['advanced_city']);
            }
            $advanced_area='';
            if(isset($_GET['advanced_area']) && $_GET['advanced_area']!=''){
                $advanced_area = sanitize_text_field($_GET['advanced_area']);
            }
            $advanced_country='';
            if(isset($_GET['advanced_country']) && $_GET['advanced_country']!=''){
                $advanced_country = sanitize_text_field($_GET['advanced_country']);
            }
            $property_admin_area='';
            if(isset($_GET['property_admin_area']) && $_GET['property_admin_area']!=''){
                $property_admin_area = sanitize_text_field($_GET['property_admin_area']);
            }
            ?>
                
            <input type="hidden" id="advanced_city"      class="form-control" name="advanced_city" data-value=""   value="<?php echo  esc_html($advanced_city); ?>" >              
            <input type="hidden" id="advanced_area"      class="form-control" name="advanced_area"   data-value="" value="<?php echo  esc_html($advanced_area); ?>" >              
            <input type="hidden" id="advanced_country"   class="form-control" name="advanced_country"   data-value="" value="<?php echo  esc_html($advanced_country); ?>" >              
            <input type="hidden" id="property_admin_area" name="property_admin_area" value="<?php echo  esc_html($property_admin_area); ?>">
      
            
        </div>
        <div style="display:none;" id="searchmap"></div>
        
        <div class="col-md-2 has_calendar calendar_icon">
            <input type="text" id="check_in"    class="form-control " name="check_in"  placeholder="<?php esc_html_e('Check-In','wprentals');?>" 
                value="<?php  
                if(isset($_GET['check_in'])){
                    echo esc_attr($_GET['check_in']);
                }?>" >       
        </div>
        
        <div class="col-md-2  has_calendar calendar_icon">
            <input type="text" id="check_out"   disabled class="form-control" name="check_out" placeholder="<?php esc_html_e('Check-Out','wprentals');?>" 
                value="<?php  
                if(isset($_GET['check_out'])){
                    echo esc_attr($_GET['check_out']);
                }?>">
        </div>
        
        <div class="col-md-2">
            <div class="dropdown form-control">
                <div data-toggle="dropdown" id="guest_no" class="filter_menu_trigger" 
                    data-value="<?php  
                    if(isset($_GET['guest_no'])){
                        echo intval(esc_attr($_GET['guest_no']));
                    }else{
                        echo 'all';
                    }
                ?>"> 
                <?php  
                if( isset($_GET['guest_no']) && intval($_GET['guest_no'])!=0 ){
                   echo intval($_GET['guest_no']).' '.esc_html__('guests','wprentals');
                }else{
                    esc_html_e('Guests','wprentals');
                }?>     
                <span class="caret caret_filter"></span> </div>           
                <input type="hidden" name="guest_no" id="guest_no_main" 
                    value="<?php  
                    if(isset($_GET['guest_no'])){
                        echo intval(esc_attr($_GET['guest_no']));
                    }?>">
                <ul  class="dropdown-menu filter_menu"  id="guest_no_main_list" role="menu" aria-labelledby="guest_no">
                    <?php print trim($guest_list);?>
                </ul>        
            </div>
        </div>
        
        <div class="col-md-2">
        <input name="submit" type="submit" class="wpb_btn-info wpb_btn-small wpestate_vc_button  vc_button" id="advanced_submit_2" value="<?php esc_html_e('Search','wprentals');?>">
        </div>
              
        <div id="results">
            <?php esc_html_e('We found ','wprentals')?> <span id="results_no">0</span> <?php esc_html_e('results.','wprentals'); ?>  
            <span id="showinpage"> <?php esc_html_e('Do you want to load the results now ?','wprentals');?> </span>
        </div>
        <?php wp_nonce_field( 'wpestate_regular_search', 'wpestate_regular_search_nonce' ); ?>
    </form>   

</div>  
<?php include(locate_template('libs/internal_autocomplete_wpestate.php')); ?>