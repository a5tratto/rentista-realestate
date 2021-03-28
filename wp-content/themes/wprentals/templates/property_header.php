<?php
global $post;
global $property_action_terms_icon;
global $property_action;
global $property_category_terms_icon;
global $property_category;
global $guests;
global $bedrooms;
global $favorite_text;
global $favorite_class;
global $wpestate_options;
$rental_type=wprentals_get_option('wp_estate_item_rental_type');
?>--><div class="property_header">
        <div class="property_categs ">
            
            <div class="property_header_wrapper 
                <?php 
                if ( $wpestate_options['content_class']=='col-md-12' || $wpestate_options['content_class']=='none'){
                    print 'col-md-8';
                }else{
                   print  esc_attr($wpestate_options['content_class']); 
                }?> 
            ">
            
                <div class="category_wrapper ">
                    <div class="category_icon_wrapper">
                        <div class="category_icon">
                            <img src="<?php print esc_url($property_action_terms_icon);?>" alt="<?php esc_html_e('category','wprentals');?>" class="retina_ready"  height="25"></div>
                        <div class="category_text"><?php print trim($property_action); ?></div>
                    </div>

                    <div class="category_icon_wrapper">
                        <div class="category_icon"><img src="<?php print esc_url($property_category_terms_icon);?>" alt="<?php esc_html_e('category','wprentals');?>" height="25" class="retina_ready"></div>
                        <div class="category_text"><?php print trim($property_category);?></div>
                    </div>

                    <div class="category_icon_wrapper">
                        <div class="category_icon"><img src="<?php print get_stylesheet_directory_uri().'/img/guest_icon.png';?>" height="25" alt="<?php esc_html_e('category','wprentals');?>" class="retina_ready"></div>
                        <div class="category_text"><?php print esc_html($guests).' '. esc_html__( 'Guests','wprentals');?></div>
                    </div>

                    <div class="category_icon_wrapper">
                        <div class="category_icon"><img src="<?php print get_stylesheet_directory_uri().'/img/beds_icon.png';?>" height="25" alt="<?php esc_html_e('category','wprentals');?>" class="retina_ready"></div>
                        <div class="category_text"><?php print esc_html($bedrooms).' '.esc_html__( 'Bedrooms','wprentals');?></div>
                    </div>
                </div>
                
                
                <div id="listing_description">
                <?php
                    $content = get_the_content();
                    $content = apply_filters('the_content', $content);
                    $content = str_replace(']]>', ']]&gt;', $content);
                    
                    $wpestate_property_description_text         =  wprentals_get_option('wp_estate_property_description_text');
                    if (function_exists('icl_translate') ){
                        $wpestate_property_description_text     =   icl_translate('wprentals','wp_estate_property_description_text', esc_html( wprentals_get_option('wp_estate_property_description_text') ) );
                    }
                    
                    if($content!=''){   
                        print '<h4 class="panel-title-description">'.esc_html($wpestate_property_description_text).'</h4>';
                        print '<div class="panel-body" id="listing_description_content" >'.$content;
                        get_template_part ('/templates/download_pdf');
                        print '</div>';  //escaped above     
                    }
                ?>
                </div>
                <div id="view_more_desc"><?php esc_html_e('View more','wprentals');?></div>            
        </div>
    <?php  
        $post_id=$post->ID; 
        $guest_list= wpestate_get_guest_dropdown('noany');
    ?>

    <div class="booking_form_request  
        <?php
        if($wpestate_options['sidebar_class']=='' || $wpestate_options['sidebar_class']=='none' ){
            print ' col-md-4 '; 
        }else{
            print esc_attr($wpestate_options['sidebar_class']);
        }
        ?>
         " id="booking_form_request">
        <div id="booking_form_request_mess"></div>
            <h3 ><?php esc_html_e('Book Now','wprentals');?></h3>
             
                <div class="has_calendar calendar_icon ">
                    <input type="text" id="start_date" placeholder="<?php  echo wpestate_show_labels('check_in',$rental_type); ?>"  class="form-control calendar_icon" size="40" name="start_date" value="">
                </div>

                <div class=" has_calendar calendar_icon">
                    <input type="text" id="end_date" disabled placeholder="<?php  echo wpestate_show_labels('check_out',$rental_type); ?>" class="form-control calendar_icon" size="40" name="end_date" value="">
                </div>

                <div class=" has_calendar guest_icon ">
                    <?php 
                    $max_guest = get_post_meta($post_id,'guest_no',true);
                    print '
                    <div class="dropdown form-control">
                        <div data-toggle="dropdown" id="booking_guest_no_wrapper" class="filter_menu_trigger" data-value="all">
                            '. esc_html__( 'Guests','wprentals').' 
                            <span class="caret caret_filter"></span>
                        </div>           
                        <input type="hidden" name="booking_guest_no"  value="">
                        <ul  class="dropdown-menu filter_menu" role="menu" aria-labelledby="booking_guest_no_wrapper" id="booking_guest_no_wrap
                        '.trim($guest_list).'
                        </ul>        
                    </div>';
                    ?> 
                </div>
            
                <?php
                wpestate_show_extra_options_booking($post_id)
                ?>

                <p class="full_form " id="add_costs_here"></p>            
                <input type="hidden" id="listing_edit" name="listing_edit" value="<?php print intval($post_id);?>" />

           
                <?php wpestate_show_booking_button($post_id);?>
                
                
                <div class="third-form-wrapper">
                    <div class="col-md-6 reservation_buttons">
                        <div id="add_favorites" class=" <?php print esc_attr($favorite_class);?>" data-postid="<?php esc_attr(the_ID());?>">
                            <?php print trim($favorite_text);?>
                        </div>                 
                    </div>

                    <div class="col-md-6 reservation_buttons">
                        <div id="contact_host" class="col-md-6"  data-postid="<?php esc_attr(the_ID());?>">
                            <?php esc_html_e('Contact Owner','wprentals');?>
                        </div>  
                    </div>
                </div>
                
                <?php 
                echo wpestate_share_unit_desing($post_id);
                ?>        
        </div> 
     </div>
</div>