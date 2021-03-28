<?php
$adv_submit         =   wpestate_get_template_link('advanced_search_results.php');
$args               =   wpestate_get_select_arguments();
$action_select_list =   wpestate_get_action_select_list($args);
$categ_select_list  =   wpestate_get_category_select_list($args);
$select_city_list   =   wpestate_get_city_select_list($args); 
$select_area_list   =   wpestate_get_area_select_list($args);

$home_small_map_status              =   esc_html ( wprentals_get_option('wp_estate_home_small_map','') );
$show_adv_search_map_close          =   esc_html ( wprentals_get_option('wp_estate_show_adv_search_map_close','') );
$class                              =   'hidden';
$class_close                        =   '';
$guest_list                         =   wpestate_get_guest_dropdown();
?>

<div id="adv-search-header-mobile"> 
    <?php esc_html_e('Advanced Search','wprentals');?> 
</div>

<div class="adv-search-mobile"  id="adv-search-mobile"> 
   
    <form method="get"  id="form-search-mobile" action="<?php print esc_url($adv_submit); ?>" >
        <?php wp_nonce_field( 'wpestate_regular_search', 'wpestate_regular_search_nonce' ); ?>
        <?php
        if (function_exists('icl_translate') ){
            print do_action( 'wpml_add_language_form_field' );
        }
        
        $search_type    =   wprentals_get_option('wp_estate_adv_search_type','');
        if($search_type == 'oldtype' || $search_type=='newtype'){ 
        ?>
        

            <div class="col-md-4 map_icon">  
                 <?php   print  wpestate_search_location_field(esc_html__('Where do you want to go ?','wprentals'),'mobile');?>
            </div>

            <div class="col-md-2 has_calendar calendar_icon">
                <input type="text" id="check_in_mobile"    class="form-control " name="check_in"  placeholder="<?php esc_html_e('Check-in','wprentals');?>" value="" >       
            </div>

            <div class="col-md-2  has_calendar calendar_icon">
                <input type="text" id="check_out_mobile" disabled  class="form-control" name="check_out" placeholder="<?php esc_html_e('Check-Out','wprentals');?>" value="">
            </div>

            <div class="col-md-2">
                <div class="dropdown form-control guest_form">
                    <div data-toggle="dropdown" id="guest_no_mobile" class="filter_menu_trigger" data-value="all"> <?php esc_html_e('Guests','wprentals');?> <span class="caret caret_filter"></span> </div>           
                    <input type="hidden" name="guest_no" id="guest_no_main_mobile" value="">
                    <ul  class="dropdown-menu filter_menu"  id="guest_no_main_list_mobile" role="menu" aria-labelledby="guest_no_mobile">
                        <?php print trim($guest_list); ?>
                    </ul>        
                </div>
            </div>
        
        <?php } else {
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
                    print '<div class="col-md-12 '.str_replace(" ","_", stripslashes($search_field) ).' ">';
                        print wpestate_show_search_field_new($_REQUEST,'mobile',$search_field,$action_select_list,$categ_select_list,$select_city_list,$select_area_list,$key);

                    print '</div>'; 

                }

               include(locate_template('libs/internal_autocomplete_wpestate.php'));
        }
        ?>
        
        
        <div class="col-md-2">
            <input name="submit" type="submit" class="wpb_btn-info wpb_btn-small wpestate_vc_button  vc_button" id="advanced_submit_2_mobile" value="<?php esc_html_e('Search','wprentals');?>">
        </div>
        
       
    </form>   
</div>  
<?php include(locate_template('libs/internal_autocomplete_wpestate.php')); ?>