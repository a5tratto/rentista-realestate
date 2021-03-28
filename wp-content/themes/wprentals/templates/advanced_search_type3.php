<?php 
global $post;
global $adv_search_type;
$adv_search_what            =   wprentals_get_option('wp_estate_adv_search_what');
$adv_search_icon            =   wprentals_get_option('wp_estate_search_field_label');
$show_adv_search_visible    =   wprentals_get_option('wp_estate_show_adv_search_visible','');
$close_class                =   '';

if($show_adv_search_visible=='no'){
    $close_class='adv-search-1-close';
}

$extended_search    =   wprentals_get_option('wp_estate_show_adv_search_extended','');
$extended_class     =   '';

if ($adv_search_type==2){
     $extended_class='adv_extended_class2';
}

if ( $extended_search =='yes' ){
    $extended_class='adv_extended_class';
    if($show_adv_search_visible=='no'){
        $close_class='adv-search-1-close-extended';
    }     
}

?>

<div class="adv-search-3" id="adv-search-3" > 
    <?php 
        $adv_search_label_for_form            =    ( esc_html( wprentals_get_option('wp_estate_adv_search_label_for_form') ) );  
        if($adv_search_label_for_form!=''){
            print '<div id="adv-search-header-3">'.esc_html($adv_search_label_for_form).'</div>';
        }
    ?>
    
    <form role="search" method="get"   action="<?php print esc_url($adv_submit); ?>" >
        <?php
        if (function_exists('icl_translate') ){
            print do_action( 'wpml_add_language_form_field' );
        }
        ?>   
         
        <div class="adv3-holder">
            <?php
            $custom_advanced_search         =   wprentals_get_option('wp_estate_custom_advanced_search','');
            $adv_search_fields_no_per_row   =   ( floatval( wprentals_get_option('wp_estate_search_fields_no_per_row') ) );
    
                foreach($adv_search_what as $key=>$search_field){
                    $search_col         =   3;
                    $search_col_price   =   6;
                    if($adv_search_fields_no_per_row==2){
                        $search_col         =   6;
                        $search_col_price   =   12;
                    }else  if($adv_search_fields_no_per_row==3){
                        $search_col         =   4;
                        $search_col_price   =   8;
                    }
                    
                    $search_col_submit = $search_col;
                    
                    if($search_field=='property_price' ){
                        $search_col=$search_col_price;
                    }
                    if(strtolower($search_field)=='location' ){
                        $search_col=$search_col_price;
                    }
                   
                    print '<div class="col-md-'.esc_attr($search_col).' '.str_replace(" ","_", stripslashes($search_field) ).' ">';
                    print wpestate_show_search_field_new($_REQUEST,'mainform',$search_field,$action_select_list,$categ_select_list,$select_city_list,$select_area_list,$key);
                    print '</div>';
                    
                }
        
                print '<div class="col-md-'.esc_attr($search_col_submit).' '.str_replace(" ","_",$search_field).'">';
                print '<input name="submit" type="submit" class="wpb_btn-info wpb_btn-small wpestate_vc_button  vc_button" id="advanced_submit_3" value="'.__('Search','wprentals').'">';
                print '</div>';
        

            if($extended_search=='yes'){
               wprentals_show_extended_search('adv');
            }
            ?>
        </div>
        <?php wp_nonce_field( 'wpestate_regular_search', 'wpestate_regular_search_nonce' ); ?>
    </form>   
    <div style="clear:both;"></div>
</div>  