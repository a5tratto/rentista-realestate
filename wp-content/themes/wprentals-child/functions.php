<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

    
if ( !function_exists( 'wpestate_chld_thm_cfg_parent_css' ) ):
   function wpestate_chld_thm_cfg_parent_css() {

    $parent_style = 'wpestate_style'; 
    wp_enqueue_style('bootstrap',get_template_directory_uri().'/css/bootstrap.css', array(), '1.0', 'all');
    wp_enqueue_style('bootstrap-theme',get_template_directory_uri().'/css/bootstrap-theme.css', array(), '1.0', 'all');
    wp_enqueue_style( $parent_style, get_template_directory_uri() . '/style.css',array('bootstrap','bootstrap-theme'),'all' );
    wp_enqueue_style( 'wpestate-child-style',
        get_stylesheet_directory_uri() . '/style.css',
        array( $parent_style ),
        wp_get_theme()->get('Version')
    );
    
   }    
    
endif;
add_action( 'wp_enqueue_scripts', 'wpestate_chld_thm_cfg_parent_css' );
load_child_theme_textdomain('wprentals', get_stylesheet_directory().'/languages');
// END ENQUEUE PARENT ACTION


////////////////////////////// Giorgio
add_action('wpcf7_init', 'custom_add_form_tag_customlist');
function custom_add_form_tag_customlist()
{
    wpcf7_add_form_tag(array('customlist', 'customlist*'),
        'custom_customlist_form_tag_handler', true);
}

function custom_customlist_form_tag_handler($tag)
{
    $tag = new WPCF7_FormTag($tag);
    if (empty($tag->name)) {
        return '';
    }
    $customlist = '';
global $wpdb;
$querystr = "
SELECT wposts.*
FROM wp_posts wposts, wp_icl_translations wicl_translations
WHERE wicl_translations.element_id = wposts.ID
AND wicl_translations.language_code = 'it'
AND (wposts.post_type = 'estate_property' )
ORDER BY wposts.post_title ASC
"; 
$query = $wpdb->get_results($querystr, OBJECT);
foreach($query as $listaquery){
    $post_title = $listaquery->post_title;
    $pro_id = $listaquery->ID;
    $customlist .= sprintf('<option value="%1$s">%2$s</option>',
            esc_html($pro_id), esc_html($post_title));
}
    wp_reset_query();

    $customlist = sprintf(
        '<select name="%1$s" id="%2$s">%3$s</select>', $tag->name,
        $tag->name . '-options',
        $customlist);

    return $customlist;
}




 
 


