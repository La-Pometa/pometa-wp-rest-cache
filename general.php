<?php




add_action('admin_bar_menu', 'add_toolbar_items', 100);
function add_toolbar_items($admin_bar){
	$admin_bar->add_menu( array(
		'id'    => 'pRest_AdminBar',
		'title' => 'RestAPI - Esborrar cache', // Your menu title
		'href'  => add_query_arg(array("page"=>"prest","action"=>"cache-clean"),admin_url("options-general.php")), // URL
	));

  

}


register_activation_hook(__FILE__, 'pRest_plugin_activate');
register_deactivation_hook(__FILE__,'pRest_plugin_deactivate');

function pRest_plugin_activate() {
   
    if (! wp_next_scheduled ( 'pRestCacheCron' )) {
	    wp_schedule_event(time(), '10min', 'pRestCacheCron');
    }
}
function pRest_plugin_deactivate() {
    wp_clear_scheduled_hook( 'pRestCacheCron' );
}

function pRest_cron_schedules($schedules){
    if(!isset($schedules["10min"])){
        $schedules["10min"] = array(
            'interval' => 10*60,
            'display' => __('Cada 10 Minuts')
        );
        
    } 
    
    return $schedules;
}
add_filter('cron_schedules','pRest_cron_schedules');




add_action('pRestCacheCron', 'pRest_Cache_Cron_Expired');

function pRest_Cache_Cron_Expired() {
    pRest_routes_cache_expire();
}

