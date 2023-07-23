<?php

function wrp_view_popup_callback($post_id = 0) {
    $main_popup_id = 'wrp_main_popup';
    if ($post_id > 0) {
        $get_settings = get_post_meta($post_id, 'wrp_settings', TRUE);
        $main_popup_id = 'wrp_popup_' . $post_id;
    } else {
        $get_settings = get_option('wrp_settings');
    }

    $wrp_loading_img_url = wrp_plugin_URL . "/assets/media/loader.gif";

    $wrp_popup_image_url = wrp_plugin_URL . "/assets/media/5star_icon.png";
    if (!empty($get_settings['wrp_popup_image_url'])) {
        $wrp_popup_image_url = $get_settings['wrp_popup_image_url'];
    }

    $wrp_popup_hedding_before = 'Enjoying 5th Star?';
    if (!empty($get_settings['wrp_popup_hedding_before'])) {
        $wrp_popup_hedding_before = $get_settings['wrp_popup_hedding_before'];
    }
    if ($post_id > 0) {
        $get_the_title = get_the_title($post_id);
        if ($get_the_title) {
            $wrp_popup_hedding_before = $get_the_title;
        }
    }

    $wrp_popup_hedding_after = 'Thanks for your feedback!';
    if (!empty($get_settings['wrp_popup_hedding_after'])) {
        $wrp_popup_hedding_after = $get_settings['wrp_popup_hedding_after'];
    }


    $wrp_popup_text_before = 'Let us know! Tap a star to leave us a review.';
    if (!empty($get_settings['wrp_popup_text_before'])) {
        $wrp_popup_text_before = $get_settings['wrp_popup_text_before'];
    }

    $wrp_popup_text_after = 'What could we do to improve?';
    if (!empty($get_settings['wrp_popup_text_after'])) {
        $wrp_popup_text_after = $get_settings['wrp_popup_text_after'];
    }

    $wrp_layer_background = 'rgba(0,0,0,0.4)';
    if (!empty($get_settings['wrp_layer_background'])) {
        $wrp_layer_background = $get_settings['wrp_layer_background'];
    }
    $wrp_popup_background_color = 'rgb(219 212 230)';
    if (!empty($get_settings['wrp_popup_background_color'])) {
        $wrp_popup_background_color = $get_settings['wrp_popup_background_color'];
    }
    $wrp_popup_heading_color = 'rgb(58 58 58)';
    if (!empty($get_settings['wrp_popup_heading_color'])) {
        $wrp_popup_heading_color = $get_settings['wrp_popup_heading_color'];
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
    $wrp_popup_select_template_class = 'wrp_popup_template_main';
    if ($wrp_popup_select_template == '2') {
        $wrp_popup_select_template_class = 'wrp_popup_template_compact';
    }
    ob_start();
    ?>
    <style>
        .wrp_main_popup {
            background-color: <?php echo $wrp_layer_background; ?> !important;
        }
        .wrp_main_popup_content {
            background-color: <?php echo $wrp_popup_background_color; ?> !important;
        }
        .wrp_visitor_email_field_input_label, .wrp_review_field_input_label {
            background: <?php echo $wrp_popup_background_color; ?> !important;
        }
        .wrp_popup_title{
            color: <?php echo $wrp_popup_heading_color; ?> !important;
        }
        .wrp_popup_description{
            color: <?php echo $wrp_popup_text_color; ?> !important;
        }

        .wrp_stars_rating:not(:checked) > label {
            color: <?php echo $wrp_stars_inactive_color; ?> !important;
        }
        .wrp_stars_rating > input:checked ~ label {
            color: <?php echo $wrp_stars_active_color; ?> !important;
        }
        .wrp_stars_rating:not(:checked) > label:hover, 
        .wrp_stars_rating:not(:checked) > label:hover ~ label {
            color: <?php echo $wrp_stars_hover_color; ?> !important;
        }
        .wrp_stars_rating > input:checked + label:hover,
        .wrp_stars_rating > input:checked + label:hover ~ label,
        .wrp_stars_rating > input:checked ~ label:hover,
        .wrp_stars_rating > input:checked ~ label:hover ~ label,
        .wrp_stars_rating > label:hover ~ input:checked ~ label {
            color: <?php echo $wrp_stars_hover_color; ?> !important;
        }

    </style>
    <div id="<?php echo $main_popup_id; ?>" class="wrp_main_popup <?php echo $wrp_popup_select_template_class; ?>">
        <div class="wrp_main_popup_content">
            <!--<span class="wrp_close">&times;</span>-->
            <div class="wrp_popup_logo_section">
                <img width="100px" src="<?php echo $wrp_popup_image_url; ?>">
            </div>
            <div id="wrp_request_loader" style="display: none;">
                <img src="<?php echo $wrp_loading_img_url; ?>">
            </div>
            <div class="wrp_request_content">
                <div class="wrp_details_section">
                    <div class="wrp_stars_before_review">
                        <h3 class="wrp_popup_title"><?php echo $wrp_popup_hedding_before; ?></h3>
                        <p class="wrp_popup_description"><?php echo $wrp_popup_text_before; ?></p>
                    </div>
                    <div class="wrp_stars_after_review" style="display: none;">
                        <h3 class="wrp_popup_title"><?php echo $wrp_popup_hedding_after; ?></h3>
                        <p class="wrp_popup_description"><?php echo $wrp_popup_text_after; ?></p>
                    </div>

                </div>
                <div class="wrp_stars_section">
                    <div class="wrp_stars_rating">
                        <input type="radio" class="wrp_select_star" id="wrp_star5" name="wrp_stars_rating" value="5" />
                        <label for="wrp_star5" title="text">5 stars</label>
                        <input type="radio" class="wrp_select_star" id="wrp_star4" name="wrp_stars_rating" value="4" />
                        <label for="wrp_star4" title="text">4 stars</label>
                        <input type="radio" class="wrp_select_star" id="wrp_star3" name="wrp_stars_rating" value="3" />
                        <label for="wrp_star3" title="text">3 stars</label>
                        <input type="radio" class="wrp_select_star" id="wrp_star2" name="wrp_stars_rating" value="2" />
                        <label for="wrp_star2" title="text">2 stars</label>
                        <input type="radio" class="wrp_select_star" id="wrp_star1" name="wrp_stars_rating" value="1" />
                        <label for="wrp_star1" title="text">1 star</label>
                        <div style="clear: both;"></div>
                    </div>
                    <div style="clear: both;"></div>
                </div>
                <div class="wrp_visitor_email_field_section" style="display: none;">
                    <div class="wrp_visitor_email_field_inner">
                        <label class="wrp_visitor_email_field_input">
                            <span class="wrp_visitor_email_field_input_label">Email</span>
                            <input class="wrp_visitor_email_field_input_field" type="email" placeholder="abc@example.com" value="" />
                        </label>
                    </div>
                </div>
                <div class="wrp_review_field_section" style="display: none;">
                    <div class="wrp_review_field_inner">
                        <label class="wrp_review_field_input">
                            <span class="wrp_review_field_input_label">Write Review</span>
                            <input class="wrp_review_field_input_field" type="text" placeholder="Type here..." value="" />
                        </label>
                    </div>
                </div>

            </div>
            <div class="wrp_footer_action_section">
                <div class="wrp_stars_before_review">
                    <div class="wrp_popup_btn wrp_close_popup">Not Now</div>
                </div>
                <div class="wrp_stars_after_review" style="display: none;">
                    <div class="wrp_footer_action_inner">
                        <div class="wrp_popup_btn wrp_close_popup">Cancel</div>
                        <div class="wrp_submit_reviews wrp_popup_btn">Submit</div>
                    </div>
                </div>
            </div>

        </div>
    </div>
    <?php
    $html = ob_get_contents();
    ob_end_clean();
    return $html;
}

function wrp_view_popup_shortcode_callback($args = []) {
    $post_id = 0;
    if (isset($args['id']) && !empty($args['id'])) {
        $post_id = (int) $args['id'];
    }
    wrp_add_plugin_assets($post_id);
    wp_enqueue_style('wrp_plugin_style', wrp_plugin_URL . 'assets/style_posts.css');
    $html = '';
    if (wrp_is_visible_popup(TRUE)) {
        if ($post_id > 0) {
            $html = wrp_view_popup_callback($post_id);
        } else {
            $html = wrp_view_popup_callback();
        }
    }
    return $html;
}

add_shortcode('wrp_view_popup', 'wrp_view_popup_shortcode_callback');

function wrp_add_popup_to_site() {

    $html = '';
    if (wrp_is_visible_popup()) {
        $html = wrp_view_popup_callback();
    }
    echo $html;
}

add_action('wp_footer', 'wrp_add_popup_to_site');

function wrp_is_visible_popup($is_shortcode = FALSE) {
    global $post;
    $post_slug = $post->post_name;
    if (is_front_page()) {
        $post_slug = 'home';
    }
    $get_selected_pages = get_option('wrp_selected_pages');
    if ($is_shortcode) {
        if (!in_array($post_slug, $get_selected_pages) || !in_array('all', $get_selected_pages)) {
            return TRUE;
        } else {
            return FALSE;
        }
    } else {
        if (in_array($post_slug, $get_selected_pages) || in_array('all', $get_selected_pages)) {
            return TRUE;
        } else {
            return FALSE;
        }
    }
}
