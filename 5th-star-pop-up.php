<?php

/**
 * Plugin Name:       5th Star
 * Plugin URI:        https://www.wp5thstar.com
 * Description:       A simple solution for increasing 5 Star Reviews and protecting your reputation. 5th Star - "The 5 Star Funnel" <br>★★★★★
 * Version:           1.5.1
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            5th Star
 * Author URI:        https://www.wp5thstar.com/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       wrpopup
 * Domain Path:       /languages
 */
defined('ABSPATH') || exit;

if (!defined('wrp_plugin_dir')) {
    define('wrp_plugin_dir', plugin_dir_path(__FILE__));
}
if (!defined('wrp_plugin_URL')) {
    define('wrp_plugin_URL', plugin_dir_url(__FILE__));
}
if (!defined('wrp_plugin_basename')) {
    define('wrp_plugin_basename', plugin_basename(__FILE__));
}
if (!defined('wrp_plugin_menu_icon')) {
    define('wrp_plugin_menu_icon', wrp_plugin_URL . 'assets/media/5th-star-icon.svg');
}


if (!defined('WRP_Admin_Init')) {
    define('WRP_Admin_Init', 'WRP_Admin_Init');
}

function wrp_wp_enqueue_scripts() {
    wp_enqueue_style('wrp_jBox_all_min_style', wrp_plugin_URL . 'assets/jBox/dist/jBox.all.min.css');
    wp_enqueue_style('wrp_plugin_style', wrp_plugin_URL . 'assets/style.css');
    wp_enqueue_script('jquery');

//    https://www.jqueryscript.net/lightbox/Versatile-jQuery-Popup-Window-Plugin-jBox.html
    wp_enqueue_script('wrp_jBox_all_min_scripts', wrp_plugin_URL . 'assets/jBox/dist/jBox.all.min.js', 'jquery');

    wp_enqueue_script('wrp_fingerprintjs_scripts', wrp_plugin_URL . 'assets/fingerprintjs.js', 'jquery');


    wp_enqueue_script('wrp_plugin_scripts', wrp_plugin_URL . 'assets/scripts.js', 'jquery');

    $get_settings = get_option('wrp_settings');
    $wrp_is_show_review_input = 'yes';
    if (!empty($get_settings['wrp_is_show_review_input'])) {
        $wrp_is_show_review_input = $get_settings['wrp_is_show_review_input'];
    }

    $wrp_is_require_visitor_email = 'no';
    if (!empty($get_settings['wrp_is_require_visitor_email'])) {
        $wrp_is_require_visitor_email = $get_settings['wrp_is_require_visitor_email'];
    }
    $wrp_popup_show_event = 'duration';
    if (!empty($get_settings['wrp_popup_show_event'])) {
        $wrp_popup_show_event = $get_settings['wrp_popup_show_event'];
    }

    $wrp_popup_show_event_value = 0;
    if (!empty($get_settings['wrp_popup_show_event_value'])) {
        $wrp_popup_show_event_value = $get_settings['wrp_popup_show_event_value'];
    }
    $wrp_review_url = 'https://google.com';
    if (!empty($get_settings['wrp_review_url'])) {
        $wrp_review_url = $get_settings['wrp_review_url'];
    }

    $wrp_popup_show_num_visits = 'no';
    if (!empty($get_settings['wrp_popup_show_num_visits'])) {
        $wrp_popup_show_num_visits = $get_settings['wrp_popup_show_num_visits'];
    }

    $wrp_popup_show_num_visits_value = 0;
    if (!empty($get_settings['wrp_popup_show_num_visits_value'])) {
        $wrp_popup_show_num_visits_value = $get_settings['wrp_popup_show_num_visits_value'];
    }

    $wrp_popup_stop_showing_num_ignore = 0;
    if (!empty($get_settings['wrp_popup_stop_showing_num_ignore'])) {
        $wrp_popup_stop_showing_num_ignore = $get_settings['wrp_popup_stop_showing_num_ignore'];
    }

    $getSubmitedStars = 'error';
    if (isset($_COOKIE['wrp_reviews_status'])) {
        $getSubmitedStars = $_COOKIE['wrp_reviews_status'];
    }
    $js_object = [];
    $js_object['admin_url'] = admin_url('admin-ajax.php');
    if ($getSubmitedStars == 'success') {
        $js_object['is_submited'] = 'yes';
    } else {
        $js_object['is_submited'] = 'no';
    }
    $js_object['is_review_field'] = $wrp_is_show_review_input;
    $js_object['is_visitor_email'] = $wrp_is_require_visitor_email;
    $js_object['review_url'] = $wrp_review_url;

    $js_object['modal_open'] = [
        'event' => $wrp_popup_show_event,
        'value' => $wrp_popup_show_event_value,
    ];
    $get_visits = wrp_get_current_user_visits();
    $js_object['current_visits'] = $get_visits;

    $js_object['is_show_on_visits'] = $wrp_popup_show_num_visits;
    $js_object['is_show_on_visits_value'] = $wrp_popup_show_num_visits_value;

    $wrp_popup_current_unshow_count = 0;
    if (isset($_COOKIE['wrp_count_ignore'])) {
        $wrp_popup_current_unshow_count = (int) $_COOKIE['wrp_count_ignore'];
    }
    $js_object['current_unshow_count'] = $wrp_popup_current_unshow_count;


    $js_object['unshow_count'] = $wrp_popup_stop_showing_num_ignore;

    wp_localize_script('wrp_plugin_scripts', 'wrp', $js_object);
}

add_action('wp_enqueue_scripts', 'wrp_wp_enqueue_scripts', 10);

function wrp_add_plugin_assets($post_id = 0) {
    if ($post_id > 0) {
        $get_settings = get_post_meta($post_id, 'wrp_settings', TRUE);
    } else {
        $get_settings = get_option('wrp_settings');
    }

    wp_enqueue_script('wrp_plugin_scripts_posts', wrp_plugin_URL . 'assets/scripts_posts.js', 'jquery');
    $wrp_is_show_review_input = 'yes';
    if (!empty($get_settings['wrp_is_show_review_input'])) {
        $wrp_is_show_review_input = $get_settings['wrp_is_show_review_input'];
    }

    $wrp_is_require_visitor_email = 'no';
    if (!empty($get_settings['wrp_is_require_visitor_email'])) {
        $wrp_is_require_visitor_email = $get_settings['wrp_is_require_visitor_email'];
    }
    $wrp_popup_show_event = 'duration';
    if (!empty($get_settings['wrp_popup_show_event'])) {
        $wrp_popup_show_event = $get_settings['wrp_popup_show_event'];
    }

    $wrp_popup_show_event_value = 0;
    if (!empty($get_settings['wrp_popup_show_event_value'])) {
        $wrp_popup_show_event_value = $get_settings['wrp_popup_show_event_value'];
    }
    $wrp_review_url = 'https://google.com';
    if (!empty($get_settings['wrp_review_url'])) {
        $wrp_review_url = $get_settings['wrp_review_url'];
    }

    $wrp_popup_show_num_visits = 'no';
    if (!empty($get_settings['wrp_popup_show_num_visits'])) {
        $wrp_popup_show_num_visits = $get_settings['wrp_popup_show_num_visits'];
    }

    $wrp_popup_show_num_visits_value = 0;
    if (!empty($get_settings['wrp_popup_show_num_visits_value'])) {
        $wrp_popup_show_num_visits_value = $get_settings['wrp_popup_show_num_visits_value'];
    }

    $wrp_popup_stop_showing_num_ignore = 0;
    if (!empty($get_settings['wrp_popup_stop_showing_num_ignore'])) {
        $wrp_popup_stop_showing_num_ignore = $get_settings['wrp_popup_stop_showing_num_ignore'];
    }

    $wrp_popup_select_template = '1';
    if (!empty($get_settings['wrp_popup_select_template'])) {
        $wrp_popup_select_template = $get_settings['wrp_popup_select_template'];
    }

    $getSubmitedStars = 'error';
    if (isset($_COOKIE['wrp_reviews_status'])) {
        $getSubmitedStars = $_COOKIE['wrp_reviews_status'];
    }
    $js_object = [];
    $js_object['admin_url'] = admin_url('admin-ajax.php');
    $js_object['post_id'] = $post_id;
    if ($getSubmitedStars == 'success') {
        $js_object['is_submited'] = 'yes';
    } else {
        $js_object['is_submited'] = 'no';
    }
    $js_object['is_review_field'] = $wrp_is_show_review_input;
    $js_object['is_visitor_email'] = $wrp_is_require_visitor_email;
    $js_object['review_url'] = $wrp_review_url;

    $js_object['modal_open'] = [
        'event' => $wrp_popup_show_event,
        'value' => $wrp_popup_show_event_value,
    ];
    $get_visits = wrp_get_current_user_visits();
    $js_object['current_visits'] = $get_visits;

    $js_object['is_show_on_visits'] = $wrp_popup_show_num_visits;
    $js_object['is_show_on_visits_value'] = $wrp_popup_show_num_visits_value;

    $wrp_popup_current_unshow_count = 0;
    if (isset($_COOKIE['wrp_count_ignore'])) {
        $wrp_popup_current_unshow_count = (int) $_COOKIE['wrp_count_ignore'];
    }
    $js_object['current_unshow_count'] = $wrp_popup_current_unshow_count;


    $js_object['unshow_count'] = $wrp_popup_stop_showing_num_ignore;

    $js_object['popup_select_template'] = $wrp_popup_select_template;

    wp_localize_script('wrp_plugin_scripts_posts', 'wrp', $js_object);
}

function wrp_admin_enqueue_scripts() {
    if (is_admin()) {
        wp_enqueue_media();
    }
    wp_enqueue_script('wrp_plugin_scripts', wrp_plugin_URL . 'assets/admin_scripts.js', 'jquery');
}

add_action('admin_enqueue_scripts', 'wrp_admin_enqueue_scripts');

require_once 'inc/lib.php';
require_once 'inc/helper.php';
require_once 'inc/class.WRP_admin.php';
require_once 'inc/shortcodes.php';

register_activation_hook(__FILE__, 'wrp_plugin_activation');

add_action('admin_init', [WRP_Admin_Init, 'init']);

//add_action('admin_menu', [WRP_Admin_Init, 'register_admin_menu']);


add_action('init', [WRP_Admin_Init, 'register_popup']);

function wrp_remove_popup_meta_boxes() {
    remove_meta_box('astra_settings_meta_box', 'popups', 'side');
}

add_action('admin_head', 'wrp_remove_popup_meta_boxes', 999);
