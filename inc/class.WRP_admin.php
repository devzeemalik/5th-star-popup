<?php

class WRP_Admin_Init {

    private static $initiated = false;

    public static function init() {
        if (!self::$initiated) {
            self::init_hooks();
        }
    }

    public static function init_hooks() {
        self::$initiated = true;
        add_action('admin_enqueue_scripts', [WRP_Admin_Init, 'register_assests']);

        add_filter('plugin_action_links_' . wrp_plugin_basename, [WRP_Admin_Init, 'register_plugin_setting_url']);
        add_filter('plugin_row_meta', [WRP_Admin_Init, 'plugin_row_meta'], 10, 2);

        add_filter('manage_popups_posts_columns', [WRP_Admin_Init, 'set_shortode_column']);
        add_action('manage_popups_posts_custom_column', [WRP_Admin_Init, 'add_shortode_column'], 10, 2);
        add_action('add_meta_boxes', [WRP_Admin_Init, 'add_settings_meta_box']);

        add_action('save_post', [WRP_Admin_Init, 'save_popup']);
    }

    public static function register_assests() {

        if (is_admin()) {
            wp_enqueue_media();
        }

        wp_register_style('wrp_admin_multi_select_style', wrp_plugin_URL . 'assets/multi_select.css');
        wp_enqueue_style('wrp_admin_multi_select_style');

        wp_register_script('wrp_admin_multi_select_scripts', wrp_plugin_URL . 'assets/multi_select.js');
        wp_enqueue_script('wrp_admin_multi_select_scripts');

//https://bgrins.github.io/spectrum/
        wp_register_style('wrp_admin_spectrum_style', wrp_plugin_URL . 'assets/spectrum.css');
        wp_enqueue_style('wrp_admin_spectrum_style');
//https://bgrins.github.io/spectrum/
        wp_register_script('wrp_admin_spectrum_scripts', wrp_plugin_URL . 'assets/spectrum.js');
        wp_enqueue_script('wrp_admin_spectrum_scripts');


        wp_register_style('wrp_admin_style', wrp_plugin_URL . 'assets/admin_style.css');
        wp_enqueue_style('wrp_admin_style');

        wp_register_script('wrp_admin_scripts', wrp_plugin_URL . 'assets/admin_scripts.js');
        wp_enqueue_script('wrp_admin_scripts');
    }

    /* Restore original Post Data 
     * NB: Because we are using new WP_Query we aren't stomping on the 
     * original $wp_query and it does not need to be reset with 
     * wp_reset_query(). We just need to set the post data back up with
     * wp_reset_postdata().
     */

    public static function register_plugin_setting_url($links) {

//        $links[] = '<a href="' . admin_url('admin.php?page=wrp_popup_settings') . '">' . __('Settings', 'wrpopup') . '</a>';
        $new_links['add_new'] = '<a href="' . admin_url('post-new.php?post_type=popups') . '">' . __('Add New', 'wrpopup') . '</a>';
        $new_links['view_all'] = '<a href="' . admin_url('edit.php?post_type=popups') . '">' . __('View All', 'wrpopup') . '</a>';
        $new_links['deactivate'] = $links['deactivate'];

        return $new_links;
    }

    public static function plugin_row_meta($links, $file) {
        $row_meta = [];
        if (wrp_plugin_basename === $file) {
            $row_meta['dev'] = '<a href="' . esc_url('https://zubitechsol.com/') . '" target="_blank" aria-label="' . esc_attr__('Plugin Additional Links', 'wrpopup') . '" style="color:#bd0707;">' . esc_html__('Develop by ZTS', 'wrpopup') . '</a>';
        }

        return array_merge($links, $row_meta);
    }

    public static function get_all_pages($page_ids = []) {
        $param = [];
        $param['post_type'] = ['page', 'post'];
        $param['post_status'] = 'publish';
        $param['order'] = 'ASC';
        $param['orderby'] = 'title';

        if (!empty($page_ids)) {
            $param['post__in'] = $page_ids;
        }

//$param['post__not_in'] = [];
        $get_pages = new WP_Query($param);
        $pages = [];
        while ($get_pages->have_posts()) {
            $get_pages->the_post();
            $page_content = get_the_content();
            $pages[] = [
                'id' => get_the_ID(),
                'slug' => basename(get_permalink()),
                'title' => get_the_title(),
            ];
        }
        wp_reset_postdata();
        wp_reset_query();
        $num_index = $get_pages->found_posts + 1;
        $home = [
            'id' => 0,
            'slug' => 'home',
            'title' => 'Home',
        ];
        $pages[$num_index] = $home;
        return $pages;
    }

    public static function register_popup() {
        $labels = [
            'name' => _x('pop-ups', 'Post Type General Name', 'wrpopup'),
            'singular_name' => _x('pop-up', 'Post Type Singular Name', 'wrpopup'),
            'menu_name' => __('5th Star ★★★★★', 'wrpopup'),
            'parent_item_colon' => __('Parent pop-up', 'wrpopup'),
            'all_items' => __('All pop-ups', 'wrpopup'),
            'view_item' => __('View pop-up', 'wrpopup'),
            'add_new_item' => __('Add New pop-up', 'wrpopup'),
            'add_new' => __('Add New', 'wrpopup'),
            'edit_item' => __('Edit pop-up', 'wrpopup'),
            'update_item' => __('Update pop-up', 'wrpopup'),
            'search_items' => __('Search pop-up', 'wrpopup'),
            'not_found' => __('Not Found', 'wrpop-up'),
            'not_found_in_trash' => __('Not found in Trash', 'wrpopup'),
        ];

        $args = [
            'label' => __('pop-ups', 'wrpopup'),
            'description' => __('pop-up news and reviews', 'wrpopup'),
            'labels' => $labels,
            'supports' => [
                'title',
//                'editor',
//                'thumbnail',
                'revisions',
            ],
            'hierarchical' => FALSE,
            'public' => TRUE,
            'show_ui' => TRUE,
            'show_in_menu' => TRUE,
            'show_in_nav_menus' => FALSE,
            'show_in_admin_bar' => TRUE,
            'menu_position' => 99,
            'menu_icon' => wrp_plugin_menu_icon,
            'can_export' => TRUE,
            'has_archive' => FALSE,
            'exclude_from_search' => TRUE,
            'publicly_queryable' => FALSE,
            'show_in_rest' => TRUE,
//            'show_in_menu' => 'wrp_popup_settings'
        ];
        register_post_type('popups', $args);
    }

    public static function add_settings_meta_box() {
        $screens = ['popups'];
        foreach ($screens as $screen) {
            add_meta_box('wrp_popup_settings_meta_box', __('pop-up setting', 'wrpopup'), [WRP_Admin_Init, 'popup_settings_meta_box_callback'], $screen);
        }
    }

    public static function register_admin_menu() {

        $parent_slug = 'edit.php?post_type=popups';
        $page_title = 'Settings';
        $menu_title = 'Settings';
        $menu_slug = 'wrp_popup_settings';
        $capability = 'manage_options';
        $function = [WRP_Admin_Init, 'settings_page'];
        $icon_url = wrp_plugin_menu_icon;
        $position = 99;

//        add_menu_page($page_title, $menu_title, $capability, $menu_slug, $function, $icon_url, $position);
        add_submenu_page($parent_slug, $page_title, $menu_title, $capability, $menu_slug, $function);

        $add_new_popup = get_option('wrp_add_new_popup');
        if (!$add_new_popup) {
            $add_new_popup = 'no';
        }
        if ($add_new_popup === 'yes') {
            
        }
    }

    public static function settings_page() {
        $response = [];
        if (isset($_POST['wrp_rest_visitor_visits'])) {
            global $wpdb;
            $tbl_name = $wpdb->prefix . 'wrp_visitor_counts';
            $res = $wpdb->query("TRUNCATE TABLE `$tbl_name`");
            if ($res) {
                $response['success'] = __('Vists Clear Successfully!', 'wrpopup');
            }
        }
        if (isset($_POST['wrp_add_new_popup'])) {
            update_option('wrp_add_new_popup', 'yes');
            $response['status'] = 'added';
        }
        if (isset($_POST['wrp_settings_submit'])) {
            if (isset($_POST['wrp_select_pages']) && !empty($_POST['wrp_select_pages'])) {
                update_option('wrp_selected_pages', $_POST['wrp_select_pages'], TRUE);
            }
            $popup_image_id = 0;
            $popup_image_url = wrp_plugin_URL . "/assets/media/5star_icon.png";
            if (isset($_POST['wrp_popup_image_url'])) {
                $popup_image_id = $_POST['wrp_popup_image_url'];
                $popup_image_url = wp_get_attachment_url($_POST['wrp_popup_image_url']);
            }
            $setting = [
                'wrp_admin_email' => isset($_POST['wrp_admin_email']) ? $_POST['wrp_admin_email'] : '',
                'wrp_is_show_review_input' => isset($_POST['wrp_is_show_review_input']) ? $_POST['wrp_is_show_review_input'] : '',
                'wrp_is_require_visitor_email' => isset($_POST['wrp_is_require_visitor_email']) ? $_POST['wrp_is_require_visitor_email'] : '',
                'wrp_review_url' => isset($_POST['wrp_review_url']) ? $_POST['wrp_review_url'] : '',
                'wrp_popup_show_event' => isset($_POST['wrp_popup_show_event']) ? $_POST['wrp_popup_show_event'] : '',
                'wrp_popup_show_event_value' => isset($_POST['wrp_popup_show_event_value']) ? $_POST['wrp_popup_show_event_value'] : '',
                'wrp_popup_show_num_visits' => isset($_POST['wrp_popup_show_num_visits']) ? $_POST['wrp_popup_show_num_visits'] : '',
                'wrp_popup_show_num_visits_value' => isset($_POST['wrp_popup_show_num_visits_value']) ? $_POST['wrp_popup_show_num_visits_value'] : '',
                'wrp_popup_stop_showing_num_ignore' => isset($_POST['wrp_popup_stop_showing_num_ignore']) ? $_POST['wrp_popup_stop_showing_num_ignore'] : 0,
                'wrp_layer_background' => isset($_POST['wrp_layer_background']) ? $_POST['wrp_layer_background'] : '',
                'wrp_popup_background_color' => isset($_POST['wrp_popup_background_color']) ? $_POST['wrp_popup_background_color'] : '',
                'wrp_popup_image_id' => $popup_image_id,
                'wrp_popup_image_url' => $popup_image_url,
                'wrp_popup_hedding_before' => isset($_POST['wrp_popup_hedding_before']) ? $_POST['wrp_popup_hedding_before'] : '',
                'wrp_popup_hedding_after' => isset($_POST['wrp_popup_hedding_after']) ? $_POST['wrp_popup_hedding_after'] : '',
                'wrp_popup_heading_color' => isset($_POST['wrp_popup_heading_color']) ? $_POST['wrp_popup_heading_color'] : '',
                'wrp_popup_text_before' => isset($_POST['wrp_popup_text_before']) ? $_POST['wrp_popup_text_before'] : '',
                'wrp_popup_text_after' => isset($_POST['wrp_popup_text_after']) ? $_POST['wrp_popup_text_after'] : '',
                'wrp_popup_text_color' => isset($_POST['wrp_popup_text_color']) ? $_POST['wrp_popup_text_color'] : '',
                'wrp_stars_inactive_color' => isset($_POST['wrp_stars_inactive_color']) ? $_POST['wrp_stars_inactive_color'] : '',
                'wrp_stars_active_color' => isset($_POST['wrp_stars_active_color']) ? $_POST['wrp_stars_active_color'] : '',
                'wrp_stars_hover_color' => isset($_POST['wrp_stars_hover_color']) ? $_POST['wrp_stars_hover_color'] : '',
                'wrp_popup_select_template' => isset($_POST['wrp_popup_select_template']) ? $_POST['wrp_popup_select_template'] : '1',
            ];
            update_option('wrp_settings', $setting, TRUE);
            $response['success'] = __('Settings saved successfully!', 'wrpopup');
        }
        $pages = self::get_all_pages();
        $get_selected_pages = get_option('wrp_selected_pages');
//        if($get_settings){
//            $sel_pages = self::get_all_pages($get_settings);
//        }
        $get_settings = get_option('wrp_settings');

        $wrp_admin_email = get_option('admin_email');
        if (!empty($get_settings['wrp_admin_email'])) {
            $wrp_admin_email = $get_settings['wrp_admin_email'];
        }
        $wrp_is_show_review_input = 'yes';
        if (!empty($get_settings['wrp_is_show_review_input'])) {
            $wrp_is_show_review_input = $get_settings['wrp_is_show_review_input'];
        }

        $wrp_is_require_visitor_email = 'no';
        if (!empty($get_settings['wrp_is_require_visitor_email'])) {
            $wrp_is_require_visitor_email = $get_settings['wrp_is_require_visitor_email'];
        }

        $wrp_review_url = 'https://google.com';
        if (!empty($get_settings['wrp_review_url'])) {
            $wrp_review_url = $get_settings['wrp_review_url'];
        }

        $wrp_popup_show_event = 'duration';
        if (!empty($get_settings['wrp_popup_show_event'])) {
            $wrp_popup_show_event = $get_settings['wrp_popup_show_event'];
        }

        $wrp_popup_show_event_value = 0;
        if (!empty($get_settings['wrp_popup_show_event_value'])) {
            $wrp_popup_show_event_value = $get_settings['wrp_popup_show_event_value'];
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

        $wrp_layer_background = 'rgba(0,0,0,0.4)';
        if (!empty($get_settings['wrp_layer_background'])) {
            $wrp_layer_background = $get_settings['wrp_layer_background'];
        }

        $wrp_popup_background_color = 'rgb(219 212 230)';
        if (!empty($get_settings['wrp_popup_background_color'])) {
            $wrp_popup_background_color = $get_settings['wrp_popup_background_color'];
        }

        $wrp_popup_image_url = wrp_plugin_URL . "/assets/media/5star_icon.png";
        if (!empty($get_settings['wrp_popup_image_url'])) {
            $wrp_popup_image_url = $get_settings['wrp_popup_image_url'];
        }

        $wrp_popup_hedding_before = 'Enjoying 5th Star?';
        if (!empty($get_settings['wrp_popup_hedding_before'])) {
            $wrp_popup_hedding_before = $get_settings['wrp_popup_hedding_before'];
        }

        $wrp_popup_hedding_after = 'Thanks for your feedback!';
        if (!empty($get_settings['wrp_popup_hedding_after'])) {
            $wrp_popup_hedding_after = $get_settings['wrp_popup_hedding_after'];
        }

        $wrp_popup_heading_color = 'rgb(58 58 58)';
        if (!empty($get_settings['wrp_popup_heading_color'])) {
            $wrp_popup_heading_color = $get_settings['wrp_popup_heading_color'];
        }

        $wrp_popup_text_before = 'Let us know! Tap a star to leave us a review.';
        if (!empty($get_settings['wrp_popup_text_before'])) {
            $wrp_popup_text_before = $get_settings['wrp_popup_text_before'];
        }

        $wrp_popup_text_after = 'What could we do to improve?';
        if (!empty($get_settings['wrp_popup_text_after'])) {
            $wrp_popup_text_after = $get_settings['wrp_popup_text_after'];
        }

        $wrp_popup_text_color = 'rgb(58 58 58)';
        if (!empty($get_settings['wrp_popup_text_color'])) {
            $wrp_popup_text_color = $get_settings['wrp_popup_text_color'];
        }

        $wrp_stars_inactive_color = 'rgb(44, 124, 245)';
        if (!empty($get_settings['wrp_stars_inactive_color'])) {
            $wrp_stars_inactive_color = $get_settings['wrp_stars_inactive_color'];
        }

        $wrp_stars_active_color = 'rgb(44 124 245)';
        if (!empty($get_settings['wrp_stars_active_color'])) {
            $wrp_stars_active_color = $get_settings['wrp_stars_active_color'];
        }

        $wrp_stars_hover_color = 'rgb(26 95 197)';
        if (!empty($get_settings['wrp_stars_hover_color'])) {
            $wrp_stars_hover_color = $get_settings['wrp_stars_hover_color'];
        }

        $wrp_popup_select_template = '1';
        if (!empty($get_settings['wrp_popup_select_template'])) {
            $wrp_popup_select_template = $get_settings['wrp_popup_select_template'];
        }

        $wrp_add_new_popup = get_option('wrp_add_new_popup');
        if (!$wrp_add_new_popup) {
            $wrp_add_new_popup = 'no';
        }
        ?>
        <div class="wrap">
            <img width="100%" class="header-logo"src="https://wp5thstar.com/wp-content/uploads/2021/10/5th-Star-Banner.png" alt="Logo">
            <h2><strong>5th Star Settings</strong></h2>
            <?php if ($wrp_add_new_popup === 'yes'): ?>
                <a class="wrp_btn" href="">Edit POP-UP</a>

            <?php else: ?>
                <form action="" method="POST">
                    <input class="wrp_btn" type="submit" name="wrp_add_new_popup" value="Add New!">
                </form>
            <?php endif; ?>
            <?php
            if (!empty($response)) {
                if (isset($response['status']) && $response['status'] === 'added') {
                    $plugin_url = site_url() . '/wp-admin/admin.php?page=wrp_popup_settings_2';
                    echo '<script>window.location.replace("' . $plugin_url . '");</script>';
                }
                if (isset($response['success'])) {
                    ?>
                    <div class="notice notice-success is-dismissible">
                        <p><strong>Success: </strong><?php echo $response['success'] ?></p>
                    </div>
                    <?php
                }
                if (isset($response['error'])) {
                    ?>
                    <div class="notice notice-error is-dismissible">
                        <p><strong>Error: </strong><?php echo $response['error'] ?></p>
                    </div>
                    <?php
                }
            }
            ?>
            <div>
                <form action="" method="POST">
                    <table class="form-table">
                        <tr>
                            <th colspan="2">
                                <h3><label for="wrp_select_pages"><?php _e('Select where you want to display the 5th Star Pop-up. <br>*If the page is not listed, add this shortcode to the page [wrp_view_popup]', 'wrpopup'); ?></label></h3>
                                <hr>
                            </th>
                        </tr>
                        <tr>
                            <td colspan="2">
                                <div class="wrp_select_list_header">
                                    <a href='#' id='wrp_select-all'>Select all</a>
                                    <a href='#' id='wrp_deselect-all'>Deselect all</a>
                                </div>
                                <select name="wrp_select_pages[]" id="wrp_select_pages" class="multiple ltr" multiple='multiple'>
                                    <?php
                                    foreach ($pages as $page) {
                                        if (!empty($page)) {
                                            if (in_array($page['slug'], $get_selected_pages)) {
                                                echo '<option value="' . $page['slug'] . '" selected>' . $page['title'] . '</option>';
                                            } else {
                                                echo '<option value="' . $page['slug'] . '" >' . $page['title'] . '</option>';
                                            }
                                        }
                                    }
                                    ?>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th colspan="2">
                                <h3><?php _e('Pop-up Settings', 'wrpopup'); ?></h3><hr>
                            </th>
                        </tr>
                        <tr>
                            <th>
                                <label for="wrp_admin_email">Admin Email</label>
                            </th>
                            <td>
                                <input type="email" id="wrp_admin_email" name="wrp_admin_email" value="<?php echo $wrp_admin_email; ?>" placeholder="">
                            </td>
                        </tr>
                        <tr>
                            <th colspan="2">
                                <h3><?php _e('Pop-up Settings', 'wrpopup'); ?></h3><hr>
                            </th>
                        </tr>
                        <tr>
                            <th>
                                <label for="wrp_popup_template_select">Select Pop-up Template</label>
                            </th>
                            <td>
                                <?php echo self::popup_template_field($wrp_popup_select_template); ?>
                            </td>
                        </tr>
                        <tr>
                            <th>
                                <label for="wrp_is_show_review_input">Show/Hide Review Field</label>
                            </th>
                            <td>
                                <select id="wrp_is_show_review_input" name="wrp_is_show_review_input">
                                    <option value="yes" <?php echo $wrp_is_show_review_input == 'yes' ? 'selected="selected"' : ''; ?>>Show</option>
                                    <option value="no" <?php echo $wrp_is_show_review_input == 'no' ? 'selected="selected"' : ''; ?>>Hide</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th>
                                <label for="wrp_is_require_visitor_email">Email Address Required?</label>
                            </th>
                            <td>
                                <select id="wrp_is_require_visitor_email" name="wrp_is_require_visitor_email">
                                    <option value="no" <?php echo $wrp_is_require_visitor_email == 'no' ? 'selected="selected"' : ''; ?>>No</option>
                                    <option value="yes" <?php echo $wrp_is_require_visitor_email == 'yes' ? 'selected="selected"' : ''; ?>>Yes</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th>
                                <label for="wrp_review_url">5th Star Review Link</label>
                            </th>
                            <td>
                                <input type="url" id="wrp_review_url" name="wrp_review_url" value="<?php echo $wrp_review_url; ?>" placeholder="https://google.com/examples">
                                <p>
                                    Need help? Check out our FAQs and Tutorials <a href="https://wp5thstar.com/help/">Click here</a>
                                </p>
                            </td>
                        </tr>
                        <tr>
                            <th>
                                <label for="wrp_popup_show_event">Pop-up Trigger</label>
                            </th>
                            <td>
                                <select id="wrp_popup_show_event" name="wrp_popup_show_event">
                                    <option value="duration" <?php echo $wrp_popup_show_event == 'duration' ? 'selected="selected"' : ''; ?>>Duration</option>
                                    <option value="scroll" <?php echo $wrp_popup_show_event == 'scroll' ? 'selected="selected"' : ''; ?>>Scroll</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th>
                                <label for="wrp_popup_show_event_value">Trigger Value (<span class="wrp_popup_show_event_value_label">Seconds</span>)</label>
                            </th>
                            <td>
                                <input type="number" id="wrp_popup_show_event_value" name="wrp_popup_show_event_value"  value="<?php echo $wrp_popup_show_event_value ?>">
                            </td>
                        </tr>
        <!--                        <tr>
                            <th>
                                <label for="">Is pop-up Show on Number of Visits</label>
                            </th>
                            <td>
                                <label for="wrp_popup_show_num_visits_no"> No <input type="radio" id="wrp_popup_show_num_visits_no" name="wrp_popup_show_num_visits" class="wrp_popup_show_num_visits" value="no" <?php //echo $wrp_popup_show_num_visits == 'no' ? 'checked="checked"' : '';                                                                                                                                                                                                                            ?>></label>
                                <label for="wrp_popup_show_num_visits_yes"> Yes <input type="radio" id="wrp_popup_show_num_visits_yes" name="wrp_popup_show_num_visits" class="wrp_popup_show_num_visits" value="yes" <?php //echo $wrp_popup_show_num_visits == 'yes' ? 'checked="checked"' : '';                                                                                                                                                                                                                            ?>></label>
                            </td>
                        </tr>-->
                        <tr id="wrp_popup_show_num_visits_value_row">
                            <th>
                                <label for="wrp_popup_show_num_visits_value">Show pop-up after x amount of visits</label>
                            </th>
                            <td>
                                <input type="number" id="wrp_popup_show_num_visits_value" name="wrp_popup_show_num_visits_value"  value="<?php echo $wrp_popup_show_num_visits_value ?>">
                            </td>
                        </tr>
                        <tr id="wrp_popup_show_num_visits_value_row">
                            <th> 
                                <label for="wrp_popup_stop_showing_num_ignore">STOP showing pop-up after X number of times</label>
                            </th>
                            <td>
                                <input type="number" id="wrp_popup_stop_showing_num_ignore" name="wrp_popup_stop_showing_num_ignore"  value="<?php echo $wrp_popup_stop_showing_num_ignore; ?>">
                                <p>
                                    (0) means unlimited
                                </p>
                            </td>
                        </tr>
                        <tr><td colspan="2"><hr></td></tr>
                        <tr>
                            <th>
                                <label for="wrp_layer_background">Background Overlay Color</label>
                            </th>
                            <td>
                                <input type="text" class="wrp_color_picker_flat"  name="wrp_layer_background" value="<?php echo $wrp_layer_background; ?>" id="wrp_layer_background">
                            </td>
                        </tr>
                        <tr>
                            <th>
                                <label for="wrp_popup_background_color">Pop-up Background Color</label>
                            </th>
                            <td>
                                <input type="text" class="wrp_color_picker_flat" name="wrp_popup_background_color" value="<?php echo $wrp_popup_background_color; ?>" id="wrp_popup_background_color">
                            </td>
                        </tr>
                        <tr><td colspan="2"><hr></td></tr>
                        <tr>
                            <th>
                                <label for="wrp_popup_icon">Upload Icon</label>
                            </th>
                            <td>
                                <p class="wrp_image_upload_preview">
                                    <?php echo!empty($wrp_popup_image_url) ? '<img src="' . $wrp_popup_image_url . '" style=" height: 100%; max-height: 200px; width: auto; object-fit: contain;"  alt="image preview">' : ''; ?>
                                </p>
                                <input type="hidden" value="" class="regular-text process_custom_images" id="wrp_popup_icon" name="wrp_popup_image_url" max="" min="1" step="1">
                                <button class="wrp_popup_image_url button">Upload Image</button>
                            </td>
                        </tr>
                        <tr><td colspan="2"><hr></td></tr>
                        <tr>
                            <th>
                                <label for="wrp_popup_hedding_before">Header Text Before Review</label>
                            </th>
                            <td>
                                <input type="text" name="wrp_popup_hedding_before" value="<?php echo $wrp_popup_hedding_before; ?>" id="wrp_popup_hedding_before">
                            </td>
                        </tr>
                        <tr>
                            <th>
                                <label for="wrp_popup_hedding_after">Header Text After Review</label>
                            </th>
                            <td>
                                <input type="text" name="wrp_popup_hedding_after" value="<?php echo $wrp_popup_hedding_after; ?>" id="wrp_popup_hedding_after">
                            </td>
                        </tr>
                        <tr>
                            <th>
                                <label for="wrp_popup_heading_color">Header Text Color</label>
                            </th>
                            <td>
                                <input type="text" class="wrp_color_picker_flat" name="wrp_popup_heading_color" value="<?php echo $wrp_popup_heading_color; ?>" id="wrp_popup_heading_color">
                            </td>
                        </tr>
                        <tr><td colspan="2"><hr></td></tr>
                        <tr>
                            <th>
                                <label for="wrp_popup_text_before">Body Text Before Review</label>
                            </th>
                            <td>
                                <input type="text" name="wrp_popup_text_before" value="<?php echo $wrp_popup_text_before; ?>" id="wrp_popup_text_before">
                            </td>
                        </tr>
                        <tr>
                            <th>
                                <label for="wrp_popup_text_after">Body Text After Review</label>
                            </th>
                            <td>
                                <input type="text" name="wrp_popup_text_after" value="<?php echo $wrp_popup_text_after; ?>" id="wrp_popup_text_after">
                            </td>
                        </tr>
                        <tr>
                            <th>
                                <label for="wrp_popup_text_color">Body Text Color</label>
                            </th>
                            <td>
                                <input type="text" class="wrp_color_picker_flat" name="wrp_popup_text_color" value="<?php echo $wrp_popup_text_color; ?>" id="wrp_popup_text_color">
                            </td>
                        </tr>
                        <tr><td colspan="2"><hr></td></tr>
                        <tr>
                            <th>
                                <label for="wrp_stars_inactive_color">Inactive Star Color</label>
                            </th>
                            <td>
                                <input type="text" class="wrp_color_picker_flat" name="wrp_stars_inactive_color" value="<?php echo $wrp_stars_inactive_color; ?>" id="wrp_stars_inactive_color">
                            </td>
                        </tr>
                        <tr>
                            <th>
                                <label for="wrp_stars_active_color">Active Star Color</label>
                            </th>
                            <td>
                                <input type="text" class="wrp_color_picker_flat" name="wrp_stars_active_color" value="<?php echo $wrp_stars_active_color; ?>" id="wrp_stars_active_color">
                            </td>
                        </tr>
                        <tr>
                            <th>
                                <label for="wrp_stars_hover_color">Star Hover Color</label>
                            </th>
                            <td>
                                <input type="text" class="wrp_color_picker_flat" name="wrp_stars_hover_color" value="<?php echo $wrp_stars_hover_color; ?>" id="wrp_stars_hover_color">
                            </td>
                        </tr>
                        <tr><td colspan="2"><hr></td></tr>
                        <tr>
                            <td colspan="2">
                                <input type="submit" class="wrp_btn" name="wrp_settings_submit" value="Save">
                                <button id="wrp_popup_preview" class="wrp_btn">Preview</button>
                            </td>
                        </tr>
                    </table>
                </form>
                <?php ?>
                <form action=""  method="POST">
                    <label for="wrp_rest_visitor_visits ">Clear all visits records from database</label>
                    <input type="submit" id="wrp_rest_visitor_visits" name="wrp_rest_visitor_visits" value="Clear Now!">
                </form>
                <div>
                    <h4></h4>
                </div>
            </div>
        </div>
        <?php
        echo do_shortcode('[wrp_view_popup]');
//        echo wrp_email_template(0);
    }

    public static function post_settings_html($post = null) {
        $post_id = 0;
        if ($post) {
            $post_id = $post->ID;
        }
        $response = [];
        $pages = self::get_all_pages();
        $get_selected_pages = get_post_meta($post_id, 'wrp_selected_pages', TRUE);
//        if($get_settings){
//            $sel_pages = self::get_all_pages($get_settings);
//        }
        $get_settings = get_post_meta($post_id, 'wrp_settings', TRUE);

        $wrp_admin_email = get_option('admin_email');
        if (!empty($get_settings['wrp_admin_email'])) {
            $wrp_admin_email = $get_settings['wrp_admin_email'];
        }
        $wrp_is_show_review_input = 'yes';
        if (!empty($get_settings['wrp_is_show_review_input'])) {
            $wrp_is_show_review_input = $get_settings['wrp_is_show_review_input'];
        }

        $wrp_is_require_visitor_email = 'no';
        if (!empty($get_settings['wrp_is_require_visitor_email'])) {
            $wrp_is_require_visitor_email = $get_settings['wrp_is_require_visitor_email'];
        }

        $wrp_review_url = 'https://google.com';
        if (!empty($get_settings['wrp_review_url'])) {
            $wrp_review_url = $get_settings['wrp_review_url'];
        }

        $wrp_popup_show_event = 'duration';
        if (!empty($get_settings['wrp_popup_show_event'])) {
            $wrp_popup_show_event = $get_settings['wrp_popup_show_event'];
        }

        $wrp_popup_show_event_value = 0;
        if (!empty($get_settings['wrp_popup_show_event_value'])) {
            $wrp_popup_show_event_value = $get_settings['wrp_popup_show_event_value'];
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

        $wrp_layer_background = 'rgba(0,0,0,0.4)';
        if (!empty($get_settings['wrp_layer_background'])) {
            $wrp_layer_background = $get_settings['wrp_layer_background'];
        }

        $wrp_popup_background_color = 'rgb(219 212 230)';
        if (!empty($get_settings['wrp_popup_background_color'])) {
            $wrp_popup_background_color = $get_settings['wrp_popup_background_color'];
        }

        $wrp_popup_image_url = wrp_plugin_URL . "/assets/media/5star_icon.png";
        if (!empty($get_settings['wrp_popup_image_url'])) {
            $wrp_popup_image_url = $get_settings['wrp_popup_image_url'];
        }

        $wrp_popup_hedding_before = 'Enjoying 5th Star?';
        if (!empty($get_settings['wrp_popup_hedding_before'])) {
            $wrp_popup_hedding_before = $get_settings['wrp_popup_hedding_before'];
        }

        $wrp_popup_hedding_after = 'Thanks for your feedback!';
        if (!empty($get_settings['wrp_popup_hedding_after'])) {
            $wrp_popup_hedding_after = $get_settings['wrp_popup_hedding_after'];
        }

        $wrp_popup_heading_color = 'rgb(58 58 58)';
        if (!empty($get_settings['wrp_popup_heading_color'])) {
            $wrp_popup_heading_color = $get_settings['wrp_popup_heading_color'];
        }

        $wrp_popup_text_before = 'Let us know! Tap a star to leave us a review.';
        if (!empty($get_settings['wrp_popup_text_before'])) {
            $wrp_popup_text_before = $get_settings['wrp_popup_text_before'];
        }

        $wrp_popup_text_after = 'What could we do to improve?';
        if (!empty($get_settings['wrp_popup_text_after'])) {
            $wrp_popup_text_after = $get_settings['wrp_popup_text_after'];
        }

        $wrp_popup_text_color = 'rgb(58 58 58)';
        if (!empty($get_settings['wrp_popup_text_color'])) {
            $wrp_popup_text_color = $get_settings['wrp_popup_text_color'];
        }

        $wrp_stars_inactive_color = 'rgb(44, 124, 245)';
        if (!empty($get_settings['wrp_stars_inactive_color'])) {
            $wrp_stars_inactive_color = $get_settings['wrp_stars_inactive_color'];
        }

        $wrp_stars_active_color = 'rgb(44 124 245)';
        if (!empty($get_settings['wrp_stars_active_color'])) {
            $wrp_stars_active_color = $get_settings['wrp_stars_active_color'];
        }

        $wrp_stars_hover_color = 'rgb(26 95 197)';
        if (!empty($get_settings['wrp_stars_hover_color'])) {
            $wrp_stars_hover_color = $get_settings['wrp_stars_hover_color'];
        }

        $wrp_popup_select_template = '1';
        if (!empty($get_settings['wrp_popup_select_template'])) {
            $wrp_popup_select_template = $get_settings['wrp_popup_select_template'];
        }

        $is_show_pages_setting = FALSE;
        ?>
        <div class="wrap">
            <img width="100%" class="header-logo"src="https://wp5thstar.com/wp-content/uploads/2021/10/5th-Star-Banner.png" alt="Logo">
        <!--<h2><strong>5th Star 2nd pop-up Settings</strong></h2>-->
            <?php
            if (!empty($response)) {
                if (isset($response['status']) && $response['status'] === 'deleted') {
                    $plugin_url = site_url() . '/wp-admin/admin.php?page=wrp_popup_settings';
                    echo '<script>window.location.replace("' . $plugin_url . '");</script>';
                }
                if (isset($response['success'])) {
                    ?>
                    <div class="notice notice-success is-dismissible">
                        <p><strong>Success: </strong><?php echo $response['success'] ?></p>
                    </div>
                    <?php
                }
                if (isset($response['error'])) {
                    ?>
                    <div class="notice notice-error is-dismissible">
                        <p><strong>Error: </strong><?php echo $response['error'] ?></p>
                    </div>
                    <?php
                }
            }
            ?>
            <script>
                function wrp_copy_text(id = 'wrp_popup_shortcode_text') {
                    /* Get the text field */
                    var copyText = document.getElementById(id);
                    navigator.clipboard.writeText(copyText.value);
                    document.getElementById('wrp_copy_shortcode').innerHTML = '<span class="dashicons dashicons-saved">Copied</span>';
                    setTimeout(
                            function () {
                                document.getElementById('wrp_copy_shortcode').innerHTML = '<span class="dashicons dashicons-welcome-add-page">Copy</span>';
                            }, 2000);

                }
            </script>
            <div>
                <?php echo do_shortcode('[wrp_view_popup id="' . $post_id . '"]'); ?>
                <button id="wrp_popup_preview" class="wrp_btn">Preview</button>
                <table class="form-table">
                    <tr>
                        <th colspan="2">
                            <h3><?php _e('add this shortcode to the page', 'wrpopup'); ?> <span class="wrp_popup_shortcode_text">[wrp_view_popup id="<?= $post_id ?>"]</span> <span id="wrp_copy_shortcode" onclick="wrp_copy_text('wrp_popup_shortcode_text');"><span class="dashicons dashicons-welcome-add-page">Copy</span></span></h3>
                            <input type="hidden" value='[wrp_view_popup id="<?= $post_id ?>"]' id="wrp_popup_shortcode_text">
                            <hr>
                        </th>
                    </tr>
                    <?php if ($is_show_pages_setting): ?>
                        <tr>
                            <td colspan="2">
                                <div class="wrp_select_list_header">
                                    <a href='#' id='wrp_select-all'>Select all</a>
                                    <a href='#' id='wrp_deselect-all'>Deselect all</a>
                                </div>
                                <select name="wrp_select_pages[]" id="wrp_select_pages" class="multiple ltr" multiple='multiple'>
                                    <?php
                                    foreach ($pages as $page) {
                                        if (!empty($page)) {
                                            if (in_array($page['slug'], $get_selected_pages)) {
                                                echo '<option value="' . $page['slug'] . '" selected>' . $page['title'] . '</option>';
                                            } else {
                                                echo '<option value="' . $page['slug'] . '" >' . $page['title'] . '</option>';
                                            }
                                        }
                                    }
                                    ?>
                                </select>
                            </td>
                        </tr>
                    <?php endif; ?>
                    <tr>
                        <th colspan="2">
                            <h3><?php _e('Pop-up Settings', 'wrpopup'); ?></h3><hr>
                        </th>
                    </tr>
                    <tr>
                        <th>
                            <label for="wrp_popup_template_select">Select Pop-up Template</label>
                        </th>
                        <td>
                            <?php echo self::popup_template_field($wrp_popup_select_template); ?>
                        </td>
                    </tr>
                    <tr>
                        <th>
                            <label for="wrp_admin_email">Admin Email</label>
                        </th>
                        <td>
                            <input type="email" id="wrp_admin_email" name="wrp_admin_email" value="<?php echo $wrp_admin_email; ?>" placeholder="">
                        </td>
                    </tr>
                    <tr>
                        <th colspan="2">
                            <h3><?php _e('Pop-up Settings', 'wrpopup'); ?></h3><hr>
                        </th>
                    </tr>
                    <tr>
                        <th>
                            <label for="wrp_is_show_review_input">Show/Hide Review Field</label>
                        </th>
                        <td>
                            <select id="wrp_is_show_review_input" name="wrp_is_show_review_input">
                                <option value="yes" <?php echo $wrp_is_show_review_input == 'yes' ? 'selected="selected"' : ''; ?>>Show</option>
                                <option value="no" <?php echo $wrp_is_show_review_input == 'no' ? 'selected="selected"' : ''; ?>>Hide</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th>
                            <label for="wrp_is_require_visitor_email">Email Address Required?</label>
                        </th>
                        <td>
                            <select id="wrp_is_require_visitor_email" name="wrp_is_require_visitor_email">
                                <option value="no" <?php echo $wrp_is_require_visitor_email == 'no' ? 'selected="selected"' : ''; ?>>No</option>
                                <option value="yes" <?php echo $wrp_is_require_visitor_email == 'yes' ? 'selected="selected"' : ''; ?>>Yes</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th>
                            <label for="wrp_review_url">5th Star Review Link</label>
                        </th>
                        <td>
                            <input type="url" id="wrp_review_url" name="wrp_review_url" value="<?php echo $wrp_review_url; ?>" placeholder="https://google.com/examples">
                            <p>
                                Need help? Check out our FAQs and Tutorials <a href="https://wp5thstar.com/help/">Click here</a>
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <th>
                            <label for="wrp_popup_show_event">Pop-up Trigger</label>
                        </th>
                        <td>
                            <select id="wrp_popup_show_event" name="wrp_popup_show_event">
                                <option value="duration" <?php echo $wrp_popup_show_event == 'duration' ? 'selected="selected"' : ''; ?>>Duration</option>
                                <option value="scroll" <?php echo $wrp_popup_show_event == 'scroll' ? 'selected="selected"' : ''; ?>>Scroll</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th>
                            <label for="wrp_popup_show_event_value">Trigger Value (<span class="wrp_popup_show_event_value_label">Seconds</span>)</label>
                        </th>
                        <td>
                            <input type="number" id="wrp_popup_show_event_value" name="wrp_popup_show_event_value"  value="<?php echo $wrp_popup_show_event_value ?>">
                        </td>
                    </tr>
        <!--                        <tr>
                        <th>
                            <label for="">Is pop-up Show on Number of Visits</label>
                        </th>
                        <td>
                            <label for="wrp_popup_show_num_visits_no"> No <input type="radio" id="wrp_popup_show_num_visits_no" name="wrp_popup_show_num_visits" class="wrp_popup_show_num_visits" value="no" <?php //echo $wrp_popup_show_num_visits == 'no' ? 'checked="checked"' : '';                                                                                                                                                                                                                            ?>></label>
                            <label for="wrp_popup_show_num_visits_yes"> Yes <input type="radio" id="wrp_popup_show_num_visits_yes" name="wrp_popup_show_num_visits" class="wrp_popup_show_num_visits" value="yes" <?php //echo $wrp_popup_show_num_visits == 'yes' ? 'checked="checked"' : '';                                                                                                                                                                                                                            ?>></label>
                        </td>
                    </tr>-->
                    <tr id="wrp_popup_show_num_visits_value_row">
                        <th>
                            <label for="wrp_popup_show_num_visits_value">Show pop-up after x amount of visits</label>
                        </th>
                        <td>
                            <input type="number" id="wrp_popup_show_num_visits_value" name="wrp_popup_show_num_visits_value"  value="<?php echo $wrp_popup_show_num_visits_value ?>">
                        </td>
                    </tr>
                    <tr id="wrp_popup_show_num_visits_value_row">
                        <th> 
                            <label for="wrp_popup_stop_showing_num_ignore">STOP showing pop-up after X number of times</label>
                        </th>
                        <td>
                            <input type="number" id="wrp_popup_stop_showing_num_ignore" name="wrp_popup_stop_showing_num_ignore"  value="<?php echo $wrp_popup_stop_showing_num_ignore; ?>">
                            <p>
                                (0) means unlimited
                            </p>
                        </td>
                    </tr>
                    <tr><td colspan="2"><hr></td></tr>
                    <tr>
                        <th>
                            <label for="wrp_layer_background">Background Overlay Color</label>
                        </th>
                        <td>
                            <input type="text" class="wrp_color_picker_flat"  name="wrp_layer_background" value="<?php echo $wrp_layer_background; ?>" id="wrp_layer_background">
                        </td>
                    </tr>
                    <tr>
                        <th>
                            <label for="wrp_popup_background_color">Pop-up Background Color</label>
                        </th>
                        <td>
                            <input type="text" class="wrp_color_picker_flat" name="wrp_popup_background_color" value="<?php echo $wrp_popup_background_color; ?>" id="wrp_popup_background_color">
                        </td>
                    </tr>
                    <tr><td colspan="2"><hr></td></tr>
                    <tr>
                        <th>
                            <label for="wrp_popup_icon">Upload Icon</label>
                        </th>
                        <td>
                            <p class="wrp_image_upload_preview">
                                <?php echo!empty($wrp_popup_image_url) ? '<img src="' . $wrp_popup_image_url . '" style=" height: 100%; max-height: 200px; width: auto; object-fit: contain;"  alt="image preview">' : ''; ?>
                            </p>
                            <input type="hidden" value="" class="regular-text process_custom_images" id="wrp_popup_icon" name="wrp_popup_image_url" max="" min="1" step="1">
                            <button class="wrp_popup_image_url button">Upload Image</button>
                        </td>
                    </tr>
                    <tr><td colspan="2"><hr></td></tr>
                    <tr>
                        <th>
                            <label for="wrp_popup_hedding_before">Header Text Before Review</label>
                        </th>
                        <td>
                            <input type="text" name="wrp_popup_hedding_before" value="<?php echo $wrp_popup_hedding_before; ?>" id="wrp_popup_hedding_before">
                        </td>
                    </tr>
                    <tr>
                        <th>
                            <label for="wrp_popup_hedding_after">Header Text After Review</label>
                        </th>
                        <td>
                            <input type="text" name="wrp_popup_hedding_after" value="<?php echo $wrp_popup_hedding_after; ?>" id="wrp_popup_hedding_after">
                        </td>
                    </tr>
                    <tr>
                        <th>
                            <label for="wrp_popup_heading_color">Header Text Color</label>
                        </th>
                        <td>
                            <input type="text" class="wrp_color_picker_flat" name="wrp_popup_heading_color" value="<?php echo $wrp_popup_heading_color; ?>" id="wrp_popup_heading_color">
                        </td>
                    </tr>
                    <tr><td colspan="2"><hr></td></tr>
                    <tr>
                        <th>
                            <label for="wrp_popup_text_before">Body Text Before Review</label>
                        </th>
                        <td>
                            <input type="text" name="wrp_popup_text_before" value="<?php echo $wrp_popup_text_before; ?>" id="wrp_popup_text_before">
                        </td>
                    </tr>
                    <tr>
                        <th>
                            <label for="wrp_popup_text_after">Body Text After Review</label>
                        </th>
                        <td>
                            <input type="text" name="wrp_popup_text_after" value="<?php echo $wrp_popup_text_after; ?>" id="wrp_popup_text_after">
                        </td>
                    </tr>
                    <tr>
                        <th>
                            <label for="wrp_popup_text_color">Body Text Color</label>
                        </th>
                        <td>
                            <input type="text" class="wrp_color_picker_flat" name="wrp_popup_text_color" value="<?php echo $wrp_popup_text_color; ?>" id="wrp_popup_text_color">
                        </td>
                    </tr>
                    <tr><td colspan="2"><hr></td></tr>
                    <tr>
                        <th>
                            <label for="wrp_stars_inactive_color">Inactive Star Color</label>
                        </th>
                        <td>
                            <input type="text" class="wrp_color_picker_flat" name="wrp_stars_inactive_color" value="<?php echo $wrp_stars_inactive_color; ?>" id="wrp_stars_inactive_color">
                        </td>
                    </tr>
                    <tr>
                        <th>
                            <label for="wrp_stars_active_color">Active Star Color</label>
                        </th>
                        <td>
                            <input type="text" class="wrp_color_picker_flat" name="wrp_stars_active_color" value="<?php echo $wrp_stars_active_color; ?>" id="wrp_stars_active_color">
                        </td>
                    </tr>
                    <tr>
                        <th>
                            <label for="wrp_stars_hover_color">Star Hover Color</label>
                        </th>
                        <td>
                            <input type="text" class="wrp_color_picker_flat" name="wrp_stars_hover_color" value="<?php echo $wrp_stars_hover_color; ?>" id="wrp_stars_hover_color">
                        </td>
                    </tr>
                    <tr><td colspan="2"><hr></td></tr>
                    <!-- <tr>
                        <td colspan="2">
                            <input type="submit" class="wrp_btn" name="wrp_settings_submit" value="Save">
                        </td>
                    </tr> -->
                </table>
            </div>
        </div>
        <?php
    }

    public static function popup_template_field($value = '') {
        ob_start();
        ?>
        <div class="wrp_select_popup_templates_section">
            <div class="wrp_select_popup_template_compact">
                <input type="radio" name="wrp_popup_select_template" id="wrp_popup_select_template_compact" class="wrp_radio_checkox_style" value="2" <?php echo $value == '2' ? 'checked' : ''; ?>>
                <label for="wrp_popup_select_template_compact">
                    <img width="100%" src="<?php echo wrp_plugin_URL ?>/assets/media/compact_layer_template.png">
                </label>
            </div>
            <div class="wrp_select_popup_template_main">
                <input type="radio" name="wrp_popup_select_template" id="wrp_popup_select_template_main" class="wrp_radio_checkox_style" value="1"  <?php echo $value == '2' ? '' : 'checked'; ?>>
                <label for="wrp_popup_select_template_main">
                    <img width="100%" src="<?php echo wrp_plugin_URL ?>/assets/media/main_layer_template.png">
                </label>
            </div>
            <div class="wrp_clearfix"></div>

        </div>
        <?php
        $html = ob_get_contents();
        ob_get_clean();
        return $html;
    }

    public static function set_shortode_column($columns) {
        $columns['shortcode'] = __('Shortcode', 'wrpopup');
        return $columns;
    }

    public static function add_shortode_column($column, $post_id) {
        switch ($column) {
            case 'shortcode' :
                echo '[wrp_view_popup id="' . $post_id . '"]';
                break;
        }
    }

    public static function popup_settings_meta_box_callback($post) {
        self::post_settings_html($post);
    }

    public static function save_popup($post_id) {
        if (isset($_POST['wrp_select_pages']) && !empty($_POST['wrp_select_pages'])) {
            update_post_meta($post_id, 'wrp_selected_pages', $_POST['wrp_select_pages']);
        }
        $popup_image_id = 0;
        $popup_image_url = wrp_plugin_URL . "/assets/media/5star_icon.png";
        if (isset($_POST['wrp_popup_image_url'])) {
            $popup_image_id = $_POST['wrp_popup_image_url'];
            $popup_image_url = wp_get_attachment_url($_POST['wrp_popup_image_url']);
        }
        $setting = [
            'wrp_admin_email' => isset($_POST['wrp_admin_email']) ? $_POST['wrp_admin_email'] : '',
            'wrp_is_show_review_input' => isset($_POST['wrp_is_show_review_input']) ? $_POST['wrp_is_show_review_input'] : '',
            'wrp_is_require_visitor_email' => isset($_POST['wrp_is_require_visitor_email']) ? $_POST['wrp_is_require_visitor_email'] : '',
            'wrp_review_url' => isset($_POST['wrp_review_url']) ? $_POST['wrp_review_url'] : '',
            'wrp_popup_show_event' => isset($_POST['wrp_popup_show_event']) ? $_POST['wrp_popup_show_event'] : '',
            'wrp_popup_show_event_value' => isset($_POST['wrp_popup_show_event_value']) ? $_POST['wrp_popup_show_event_value'] : '',
            'wrp_popup_show_num_visits' => isset($_POST['wrp_popup_show_num_visits']) ? $_POST['wrp_popup_show_num_visits'] : '',
            'wrp_popup_show_num_visits_value' => isset($_POST['wrp_popup_show_num_visits_value']) ? $_POST['wrp_popup_show_num_visits_value'] : '',
            'wrp_popup_stop_showing_num_ignore' => isset($_POST['wrp_popup_stop_showing_num_ignore']) ? $_POST['wrp_popup_stop_showing_num_ignore'] : 0,
            'wrp_layer_background' => isset($_POST['wrp_layer_background']) ? $_POST['wrp_layer_background'] : '',
            'wrp_popup_background_color' => isset($_POST['wrp_popup_background_color']) ? $_POST['wrp_popup_background_color'] : '',
            'wrp_popup_image_id' => $popup_image_id,
            'wrp_popup_image_url' => $popup_image_url,
            'wrp_popup_hedding_before' => isset($_POST['wrp_popup_hedding_before']) ? $_POST['wrp_popup_hedding_before'] : '',
            'wrp_popup_hedding_after' => isset($_POST['wrp_popup_hedding_after']) ? $_POST['wrp_popup_hedding_after'] : '',
            'wrp_popup_heading_color' => isset($_POST['wrp_popup_heading_color']) ? $_POST['wrp_popup_heading_color'] : '',
            'wrp_popup_text_before' => isset($_POST['wrp_popup_text_before']) ? $_POST['wrp_popup_text_before'] : '',
            'wrp_popup_text_after' => isset($_POST['wrp_popup_text_after']) ? $_POST['wrp_popup_text_after'] : '',
            'wrp_popup_text_color' => isset($_POST['wrp_popup_text_color']) ? $_POST['wrp_popup_text_color'] : '',
            'wrp_stars_inactive_color' => isset($_POST['wrp_stars_inactive_color']) ? $_POST['wrp_stars_inactive_color'] : '',
            'wrp_stars_active_color' => isset($_POST['wrp_stars_active_color']) ? $_POST['wrp_stars_active_color'] : '',
            'wrp_stars_hover_color' => isset($_POST['wrp_stars_hover_color']) ? $_POST['wrp_stars_hover_color'] : '',
            'wrp_popup_select_template' => isset($_POST['wrp_popup_select_template']) ? $_POST['wrp_popup_select_template'] : '1',
        ];
        update_post_meta($post_id, 'wrp_settings', $setting);
    }

}
