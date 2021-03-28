<!DOCTYPE html>
<html <?php language_attributes(); ?>>
    <head>
        <meta charset="<?php bloginfo('charset'); ?>" />
        <meta name="viewport" content="width=device-width, initial-scale=1,user-scalable=no">
        <link rel="pingback" href="<?php esc_url(bloginfo('pingback_url')); ?>" />
        <?php
        if( !has_site_icon() ){
            print '<link rel="shortcut icon" href="'.esc_url(get_theme_file_uri('/img/favicon.gif')).'" type="image/x-icon" />';
        }

        $wide_class = 'boxed';
        $wide_status = esc_html(wprentals_get_option('wp_estate_wide_status'));
        if ($wide_status == 1 || $wide_status=='') {
            $wide_class = " wide ";
        }

        $wpestate_header_type    =   'header_'. esc_html(wprentals_get_option('wp_estate_logo_header_type'));
        if( esc_html(wprentals_get_option('wp_estate_logo_header_type') )=='' ){
            $wpestate_header_type    =   'header_type1';
        }
        $header_align            =   'header_align_'.  esc_html(wprentals_get_option('wp_estate_logo_header_align'));
        if( esc_html(wprentals_get_option('wp_estate_logo_header_align'))=='' ){
            $header_align            =   'header_align_left';
        }
        $header_wide             =   'header_wide_'.esc_html(wprentals_get_option('wp_estate_wide_header'));
        if(esc_html(wprentals_get_option('wp_estate_wide_header'))==''){
            $header_wide             =   'header_wide_no';
        }

        $top_menu_hover_type     =   wprentals_get_option('wp_estate_top_menu_hover_type');

        if($header_wide=='yes' ||   is_page_template( 'splash_page.php' ) ){
            $header_wide    =  "header_wide_yes";
        }

        if( !is_404() && !is_tax() && !is_category() && !is_tag() && isset($post->ID) && wpestate_check_if_admin_page($post->ID) && basename(get_page_template($post->ID)) == 'splash_page.php'){
            $wide_class = " wide ";
        }

        $wide_page_class    = '';
        $map_template       = '';
        $header_map_class   = '';

        if ( !is_search() && !is_404() && !is_tax() && !is_category()  && !is_tag() && basename(get_page_template($post->ID)) == 'property_list_half.php') {
            $header_map_class = 'google_map_list_header';
            $map_template = 1;
            $wide_class = " wide ";
        }

        if (( is_category() || is_tax() ) && wprentals_get_option('wp_estate_property_list_type') == 2) {
            $header_map_class = 'google_map_list_header';
            $map_template = 1;
            $wide_class = " wide ";
            if( !is_tax() ){
                $map_template = 2;
            }
        }

        if (is_page_template('advanced_search_results.php') && wprentals_get_option('wp_estate_property_list_type_adv') == 2) {
            $header_map_class = 'google_map_list_header';
            $map_template = 1;
            $wide_class = " wide ";
        }

    ?>

    <?php  wp_head(); ?>
    </head>

    <?php
    // set search object
    global $search_object;
    $search_object = new WpRentalsSearch();



    $transparent_menu_global        =    wprentals_get_option('wp_estate_transparent_menu');
    $transparent_class              =    ' ';
    $property_list_type_status      =    esc_html(wprentals_get_option('wp_estate_property_list_type'));
    $property_list_type_status_adv  =    esc_html(wprentals_get_option('wp_estate_property_list_type_adv'));

    if($transparent_menu_global == 'yes'){
        if(is_tax() && $property_list_type_status == 2 ){
            $transparent_class = '';
        }else{
            $transparent_class = ' transparent_header ';
        }

        if( !is_404() && !is_tax() && !is_category() && !is_tag() && isset($post->ID) && basename(get_page_template($post->ID)) == 'property_list_half.php' ){
            $transparent_class = '';
        }

        if( is_single() || is_page() ){
            if( get_post_meta($post->ID, 'transparent_status', true) === 'no' ){
                $transparent_class='';
            }
        }

    }else{

        if ( !is_search() && !is_404() && !is_tax() && !is_category() && !is_tag() && get_post_meta($post->ID, 'transparent_status', true) === 'yes' && basename(get_page_template($post->ID)) != 'property_list_half.php') {
             $transparent_class = ' transparent_header ';
        }
    }

    $is_dashboard_page='';

    if( is_page() && wpestate_check_if_admin_page($post->ID) && is_user_logged_in()  ){
        $is_dashboard_page='is_dashboard_page';
    }

    if(is_singular('estate_property')){
        $transparent_menu_listing = wprentals_get_option('wp_estate_transparent_menu_listing','');
        if( $transparent_menu_listing == 'no'){
            $transparent_class = '';
        }else{
            $transparent_class = ' transparent_header ';
        }

    }

    $topbar_show_mobile         =  esc_html( wprentals_get_option('wp_estate_show_top_bar_mobile_menu',''));
    if ( $topbar_show_mobile=='yes' ){
        $topbar_show_mobile='topbar_show_mobile_yes';
    }else{
        $topbar_show_mobile='topbar_show_mobile_no';
    }

    $search_type                =  esc_html( wprentals_get_option('wp_estate_adv_search_type',''));
    if ( $search_type!='oldtype' ){
        $search_type='is_search_type1';
    }else{
        $search_type='is_search_type2';
    }
    ?>


    <?php $wpestate_is_top_bar_class =wpestate_is_top_bar_class(); ?>

    <body <?php body_class();?> >
        <?php wp_body_open(); ?>
        <?php include(locate_template('templates/mobile_menu.php')); ?>

        <div class="website-wrapper <?php echo  'is_'.esc_attr(trim($transparent_class.$wpestate_header_type) ).' '.esc_attr($wpestate_is_top_bar_class).' '.esc_attr($search_type).' '.esc_attr($topbar_show_mobile);?>"  id="all_wrapper">
            <div class="container main_wrapper <?php print  esc_attr($wide_class); print esc_attr($is_dashboard_page); ?> ">
               <div class="master_header <?php print 'master_'.trim($transparent_class) .' '.esc_attr($wide_class).' '.esc_attr($header_map_class).' master_'. esc_attr($header_wide).' hover_type_'.esc_attr($top_menu_hover_type); ?>">


                <?php
                if (wpestate_show_top_bar() && !is_page_template( 'splash_page.php' )) {
                    include(locate_template('templates/top_bar.php'));
                }
                ?>

                 <?php include(locate_template('templates/mobile_menu_header.php')); ?>


                <div class="header_wrapper <?php print esc_attr($transparent_class . $wpestate_is_top_bar_class .' '. $wpestate_header_type .' '. $header_align .' '. $header_wide); ?>">
                    <div class="header_wrapper_inside">

                        <div class="logo">

                            <a href="<?php
                            $splash_page_logo_link = wprentals_get_option('wp_estate_splash_page_logo_link', '');
                            if (is_page_template('splash_page.php') && $splash_page_logo_link != '') {
                                print esc_url($splash_page_logo_link);
                            } else {
                                echo esc_url(home_url('', 'login'));
                            }
                            ?>">

                            <?php
                            $logo='';
                            if( trim($transparent_class)!==''){
                                $logo = wprentals_get_option('wp_estate_transparent_logo_image', 'url');
                            }else{
                                $logo = wprentals_get_option('wp_estate_logo_image', 'url');
                            }

                            if ($logo != '') {
                                print '<img src="'.esc_url($logo).'" class="img-responsive retina_ready"  alt="'.esc_html__('logo','wprentals').'"/>';
                            } else {
                                print '<img class="img-responsive retina_ready" src="' . get_template_directory_uri() . '/img/logo.png" alt="'.esc_html__('logo','wprentals').'"/>';
                            }
                            ?>


                            </a>

                        </div>

                        <?php
                        if (esc_html(wprentals_get_option('wp_estate_show_top_bar_user_login', '')) == "yes") {
                            include(locate_template('templates/top_user_menu.php'));
                        }
                        ?>

                        <nav id="access">
                            <?php wp_nav_menu(array(
                                        'theme_location'    => 'primary',
                                        'container'         => false,
                                        'walker'            => new wpestate_custom_walker()
                                    ));
                            ?>
                        </nav><!-- #access -->
                    </div>
                </div>

            </div>

<?php
if (!is_search() && !is_tag() && !is_404() && !is_tax() && !is_category() && ( basename(get_page_template($post->ID)) === 'property_list_half.php' || get_post_type() === 'estate_property' )) {
    //do nothing for now
} else if (( is_category() || is_tax() ) && wprentals_get_option('wp_estate_property_list_type', '') ==  2 ) {
    if( !is_tax() ){
        include(locate_template('header_media.php'));
    }

} else if (is_page_template('advanced_search_results.php') && wprentals_get_option('wp_estate_property_list_type_adv', '') == 2) {
    //do nothing for now
} else {
    include(locate_template('header_media.php'));
}

if (get_post_type() === 'estate_property' && !is_tax() && !is_search()) {
    include(locate_template('templates/property_menu_hidden.php'));
}
?>



<?php

if ($map_template === 1) {
    print '  <div class="full_map_container">';
} else {
    if (!is_404() && !is_tax() && !is_category() && !is_search() && !is_tag()) {
        if ( wpestate_check_if_admin_page($post->ID)) {
            print '  <div class="container content_wrapper_dashboard">';
        } else {
            if ('estate_property' == get_post_type()) {
                if ( is_404()) {
                    print '<div class="content_wrapper  ' .esc_attr($wide_page_class).' row ">';
                } else {
                    print '<div itemscope itemtype="http://schema.org/RentAction"  class="content_wrapper listing_wrapper ' .esc_attr($wide_page_class).' row ">';
                }
            } else {
                if ( is_singular('estate_agent') ) {
                    include(locate_template('templates/owner_details_header.php'));
                }
                print '  <div class="content_wrapper ' .esc_attr($wide_page_class). ' row ">';
            }
        }
    } else {
        print '  <div class="content_wrapper ' .esc_attr($wide_page_class). 'row ">';
    }
}
