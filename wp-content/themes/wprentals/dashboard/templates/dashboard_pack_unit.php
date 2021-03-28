<?php
global $wpestate_currency;
$postid             = $post->ID;
$pack_list          = get_post_meta($postid, 'pack_listings', true);
$pack_featured      = get_post_meta($postid, 'pack_featured_listings', true);
$pack_price         = get_post_meta($postid, 'pack_price', true);
$unlimited_lists    = get_post_meta($postid, 'mem_list_unl', true);
$biling_period      = esc_html(get_post_meta($postid, 'biling_period', true));
$billing_freq       = get_post_meta($postid, 'billing_freq', true);
$pack_time          = get_post_meta($postid, 'pack_time', true);
$unlimited_listings = get_post_meta($postid,  'mem_list_unl',true);

if($billing_freq>1){
    $biling_period.='s';
}
?>

<div class="col-md-4">
    <div class="user_dashboard_panel pack_unit_list">
        <h4 class="user_dashboard_panel_title">
           <?php echo get_the_title().' - <span class="submit-price">'.esc_html($pack_price).' '.esc_html($wpestate_currency).'</span>'; ?>
        </h4>

        <div class="pack-listing-period">
            <?php esc_html_e('Time Period: ','wprentals'); print esc_html($billing_freq).' '. wpestate_show_bill_period($biling_period); ?>
        </div>

        <?php
        if($unlimited_listings==1){
            print'<div class="pack-listing-period">'.esc_html__( 'Unlimited','wprentals').' '.esc_html__( 'listings ','wprentals').' </div>';
        }else{
            print'<div class="pack-listing-period">'.esc_html($pack_list).' '.esc_html__( 'Listings','wprentals').' </div>';
        }
        ?>

    <div class="pack-listing-period">
        <?php print esc_html($pack_featured).' '.esc_html__( 'Featured','wprentals');?>
    </div>
    </div>
</div>
