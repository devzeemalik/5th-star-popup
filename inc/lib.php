<?php

function wrp_sent_mail($data) {
    if (!$data) {
        return FALSE;
    }

    $get_settings = get_option('wrp_settings');

    $wrp_admin_email = get_option('admin_email');
    if (!empty($get_settings['wrp_admin_email'])) {
        $wrp_admin_email = $get_settings['wrp_admin_email'];
    }

    $admin_email = $wrp_admin_email;

    $to = $admin_email;
    $subject = 'New Review';
    $from = 'admin@5thStar.com';

// To send HTML mail, the Content-type header must be set
    $headers = 'MIME-Version: 1.0' . "\r\n";
    $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";

// Create email headers
    $headers .= 'From: ' . $from . "\r\n" .
            'Reply-To: ' . $from . "\r\n" .
            'X-Mailer: PHP/' . phpversion();

// Compose a simple HTML email message
//    $message = '';
//    $message = '<html>';
//    $message = '<body>';
//    $message = '<head>';
//    $message = '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">';
//    $message = '</head>';
//    if (!empty($data['stars'])) {
//        $message .= '<h3 style="">Star Rting: ' . $data['stars'] . '</h3>';
//    }
//    if (!empty($data['email'])) {
//        $message .= '<h3 style="">Email: ' . $data['email'] . '</h3>';
//    }
//    if (!empty($data['reviews'])) {
//        $message .= '<h3 style="">Reviews: ' . $data['reviews'] . '</h3>';
//    }
//
//    $message .= '<span class="fa fa-star checked"></span>';
//    $message .= '<span class="fa fa-star checked"></span>';
//    $message .= '<span class="fa fa-star checked"></span>';
//    $message .= '<span class="fa fa-star "></span>';
//    $message .= '<span class="fa fa-star "></span>';
//    $message .= '</body>';
//    $message .= '</html>';
    $message = wrp_email_template($data['stars'], $data['email'], $data['reviews']);

// Sending email
    if (mail($to, $subject, $message, $headers)) {
        return TRUE;
    } else {
        return FALSE;
    }
}

function wrp_plugin_activation() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    $table_name = $wpdb->prefix . 'wrp_visitor_counts';
    $ordersSql = "CREATE TABLE IF NOT EXISTS $table_name (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `user` int(11) DEFAULT NULL,
                `ip` varchar(255) DEFAULT NULL,
                `mac` varchar(255) DEFAULT NULL,
                `nonce` varchar(255) DEFAULT NULL,
                `last_visit` varchar(255) DEFAULT NULL,
                `pages` varchar(255) DEFAULT NULL,
                `visits` int(11) DEFAULT NULL,
                PRIMARY KEY (`id`)
            ) $charset_collate;";
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($ordersSql);
}

function wrp_add_visitor_count($page) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'wrp_visitor_counts';

    $ip = $_SERVER["REMOTE_ADDR"];
    $user = is_user_logged_in() ? get_current_user_id() : 0;
    $MAC_rw = exec('getmac');
    $MAC = strtok($MAC_rw, ' ');
    $nonce = wp_create_nonce($MAC);
    $last_visit = strtotime('now');

    $get_data = $wpdb->get_results("SELECT * FROM {$table_name} WHERE ip= '$ip' and nonce = '$nonce'");
    $get_pages = [];
    $get_visits = 0;
    if ($get_data) {
        $get_pages = unserialize($get_data[0]->pages);
        array_push($get_pages, $page);
        $get_visits = $get_data[0]->visits;
    } else {
        $get_pages = [$page];
    }
    $get_pages = array_unique($get_pages);

    $pages = serialize($get_pages);
    $visit = $get_visits + 1;
    if (empty($get_data)) {
        $wpdb->query("INSERT INTO `$table_name` (`id`, `user`, `ip`, `mac`, `nonce`, `last_visit`, `pages`, `visits`) VALUES (NULL, '$user', '$ip', '$MAC', '$nonce', '$last_visit', '$pages', '$visit');");
    } else {
        $wpdb->update(
                "$table_name", [
            'last_visit' => $last_visit,
            'pages' => $pages,
            'visits' => $visit,
                ], [
            'ip' => $ip,
            'nonce' => $nonce
                ]
        );
    } // if condition
}

function wrp_submit_reviews_callback() {
    $response = [];
    $data = [];
    $data['stars'] = isset($_POST['stars']) ? $_POST['stars'] : '';
    $data['email'] = isset($_POST['email']) ? $_POST['email'] : '';
    $data['reviews'] = isset($_POST['reviews']) ? $_POST['reviews'] : '';
    $mail_sent = wrp_sent_mail($data);
    if ($mail_sent) {
        $response['status'] = 'success';
        $response['message'] = 'emal sent successfully';
    } else {
        $response['status'] = 'error';
        $response['message'] = 'something went wrong';
    }
    echo json_encode($response, JSON_PRETTY_PRINT);

    exit();
}

add_action('wp_ajax_wrp_submit_reviews', 'wrp_submit_reviews_callback');
add_action('wp_ajax_nopriv_wrp_submit_reviews', 'wrp_submit_reviews_callback');

function wrp_get_current_user_visits() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'wrp_visitor_counts';

    $ip = $_SERVER["REMOTE_ADDR"];
    $MAC_rw = exec('getmac');
    $MAC = strtok($MAC_rw, ' ');
    $nonce = wp_create_nonce($MAC);

    $get_data = $wpdb->get_results("SELECT * FROM {$table_name} WHERE ip= '$ip' and nonce = '$nonce'");
    if ($get_data) {

        return $get_data[0]->visits;
    }
    return 0;
}

function wrp_count_user_visitors_callback() {
    global $post;
    $post_id = $post->ID;
    if (!$post_id) {
        $post_id = get_option('page_on_front');
    }
    wrp_add_visitor_count($post_id);
}

add_action('wp_footer', 'wrp_count_user_visitors_callback');

function wrp_email_template($stars = 0, $email = '', $reviews = '') {

    ob_start();
    ?>
    <html>
        <head>
            <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <meta name="x-apple-disable-message-reformatting">
            <meta http-equiv="X-UA-Compatible" content="IE=edge">
            <title></title>
            <style type="text/css">
                table, td { color: #000000; } @media only screen and (min-width: 520px) {
                    .u-row {
                        width: 500px !important;
                    }
                    .u-row .u-col {
                        vertical-align: top;
                    }
                    .u-row .u-col-33p33 {
                        width: 166.65px !important;
                    }
                    .u-row .u-col-66p67 {
                        width: 333.35px !important;
                    }
                    .u-row .u-col-100 {
                        width: 500px !important;
                    }
                }
                @media (max-width: 520px) {
                    .u-row-container {
                        max-width: 100% !important;
                        padding-left: 0px !important;
                        padding-right: 0px !important;
                    }
                    .u-row .u-col {
                        min-width: 320px !important;
                        max-width: 100% !important;
                        display: block !important;
                    }
                    .u-row {
                        width: calc(100% - 40px) !important;
                    }
                    .u-col {
                        width: 100% !important;
                    }
                    .u-col > div {
                        margin: 0 auto;
                    }
                }
                body {
                    margin: 0;
                    padding: 0;
                }
                table,
                tr,
                td {
                    vertical-align: top;
                    border-collapse: collapse;
                }

                .ie-container table,
                .mso-container table {
                    table-layout: fixed;
                }

                * {
                    line-height: inherit;
                }

                a[x-apple-data-detectors='true'] {
                    color: inherit !important;
                    text-decoration: none !important;
                }

            </style>
        </head>
        <body class="clean-body u_body" style="margin: 0;padding: 0;-webkit-text-size-adjust: 100%;background-color: #e7e7e7;color: #000000">
            <table style="border-collapse: collapse;table-layout: fixed;border-spacing: 0;mso-table-lspace: 0pt;mso-table-rspace: 0pt;vertical-align: top;min-width: 320px;Margin: 0 auto;background-color: #e7e7e7;width:100%" cellpadding="0" cellspacing="0">
                <tbody>
                    <tr style="vertical-align: top">
                        <td style="word-break: break-word;border-collapse: collapse !important;vertical-align: top">
                            <div class="u-row-container" style="padding: 0px;background-color: transparent">
                                <div class="u-row" style="Margin: 0 auto;min-width: 320px;max-width: 500px;overflow-wrap: break-word;word-wrap: break-word;word-break: break-word;background-color: transparent;">
                                    <div style="border-collapse: collapse;display: table;width: 100%;background-color: transparent;">
                                        <div class="u-col u-col-100" style="max-width: 320px;min-width: 500px;display: table-cell;vertical-align: top;">
                                            <div style="width: 100% !important;">
                                                <div style="padding: 0px;border-top: 0px solid transparent;border-left: 0px solid transparent;border-right: 0px solid transparent;border-bottom: 0px solid transparent;"><!--<![endif]-->
                                                    <table style="font-family:arial,helvetica,sans-serif;" role="presentation" cellpadding="0" cellspacing="0" width="100%" border="0">
                                                        <tbody>
                                                            <tr>
                                                                <td style="overflow-wrap:break-word;word-break:break-word;padding:10px;font-family:arial,helvetica,sans-serif;" align="left">
                                                                    <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                                                        <tr>
                                                                            <td style="padding-right: 0px;padding-left: 0px;" align="center">
                                                                                <img align="center" border="0" src="<?php echo wrp_plugin_URL; ?>assets/media/banner.png" alt="" title="" style="outline: none;text-decoration: none;-ms-interpolation-mode: bicubic;clear: both;display: inline-block !important;border: none;height: auto;float: none;width: 100%;max-width: 480px;" width="480"/>
                                                                            </td>
                                                                        </tr>
                                                                    </table>
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="u-row-container" style="padding: 0px;background-color: transparent">
                                <div class="u-row" style="Margin: 0 auto;min-width: 320px;max-width: 500px;overflow-wrap: break-word;word-wrap: break-word;word-break: break-word;background-color: transparent;">
                                    <div style="border-collapse: collapse;display: table;width: 100%;background-color: transparent;">
                                        <div class="u-col u-col-100" style="max-width: 320px;min-width: 500px;display: table-cell;vertical-align: top;">
                                            <div style="width: 100% !important;border-radius: 0px;-webkit-border-radius: 0px; -moz-border-radius: 0px;">
                                                <div style="padding: 0px;border-top: 0px solid transparent;border-left: 0px solid transparent;border-right: 0px solid transparent;border-bottom: 0px solid transparent;border-radius: 0px;-webkit-border-radius: 0px; -moz-border-radius: 0px;"><!--<![endif]-->
                                                    <table style="font-family:arial,helvetica,sans-serif;" role="presentation" cellpadding="0" cellspacing="0" width="100%" border="0">
                                                        <tbody>
                                                            <tr>
                                                                <td style="overflow-wrap:break-word;word-break:break-word;padding:10px;font-family:arial,helvetica,sans-serif;" align="left">
                                                                    <h1 style="margin: 0px; line-height: 140%; text-align: center; word-wrap: break-word; font-weight: normal; font-family: arial,helvetica,sans-serif; font-size: 22px;">
                                                                        5th Star reviews
                                                                    </h1>
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="u-row-container" style="padding: 0px;background-color: transparent">
                                <div class="u-row" style="Margin: 0 auto;min-width: 320px;max-width: 500px;overflow-wrap: break-word;word-wrap: break-word;word-break: break-word;background-color: transparent;">
                                    <div style="border-collapse: collapse;display: table;width: 100%;background-color: transparent;">
                                        <div class="u-col u-col-33p33" style="max-width: 320px;min-width: 167px;display: table-cell;vertical-align: top;">
                                            <div style="width: 100% !important;border-radius: 0px;-webkit-border-radius: 0px; -moz-border-radius: 0px;">
                                                <div style="padding: 0px;border-top: 0px solid transparent;border-left: 0px solid transparent;border-right: 0px solid transparent;border-bottom: 0px solid transparent;border-radius: 0px;-webkit-border-radius: 0px; -moz-border-radius: 0px;"><!--<![endif]-->
                                                    <table style="font-family:arial,helvetica,sans-serif;" role="presentation" cellpadding="0" cellspacing="0" width="100%" border="0">
                                                        <tbody>
                                                            <tr>
                                                                <td style="overflow-wrap:break-word;word-break:break-word;padding:10px;font-family:arial,helvetica,sans-serif;" align="left">
                                                                    <h3 style="margin: 0px; line-height: 140%; text-align: left; word-wrap: break-word; font-weight: normal; font-family: arial,helvetica,sans-serif; font-size: 18px;">
                                                                        Star Rating:
                                                                    </h3>
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                    <table style="font-family:arial,helvetica,sans-serif;" role="presentation" cellpadding="0" cellspacing="0" width="100%" border="0">
                                                        <tbody>
                                                            <tr>
                                                                <td style="overflow-wrap:break-word;word-break:break-word;padding:10px;font-family:arial,helvetica,sans-serif;" align="left">
                                                                    <table align="center" border="0" cellpadding="0" cellspacing="0" width="100%" style="border-collapse: collapse;table-layout: fixed;border-spacing: 0;mso-table-lspace: 0pt;mso-table-rspace: 0pt;vertical-align: top;border-top: 1px solid #BBBBBB;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%">
                                                                        <tbody>
                                                                            <tr style="vertical-align: top">
                                                                                <td style="word-break: break-word;border-collapse: collapse !important;vertical-align: top;font-size: 0px;line-height: 0px;mso-line-height-rule: exactly;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%">
                                                                                    <span>&#160;</span>
                                                                                </td>
                                                                            </tr>
                                                                        </tbody>
                                                                    </table>
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                    <table style="font-family:arial,helvetica,sans-serif;" role="presentation" cellpadding="0" cellspacing="0" width="100%" border="0">
                                                        <tbody>
                                                            <tr>
                                                                <td style="overflow-wrap:break-word;word-break:break-word;padding:10px;font-family:arial,helvetica,sans-serif;" align="left">
                                                                    <h3 style="margin: 0px; line-height: 140%; text-align: left; word-wrap: break-word; font-weight: normal; font-family: arial,helvetica,sans-serif; font-size: 18px;">
                                                                        Reviews:
                                                                    </h3>
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                    <table style="font-family:arial,helvetica,sans-serif;" role="presentation" cellpadding="0" cellspacing="0" width="100%" border="0">
                                                        <tbody>
                                                            <tr>
                                                                <td style="overflow-wrap:break-word;word-break:break-word;padding:10px;font-family:arial,helvetica,sans-serif;" align="left">

                                                                    <table align="center" border="0" cellpadding="0" cellspacing="0" width="100%" style="border-collapse: collapse;table-layout: fixed;border-spacing: 0;mso-table-lspace: 0pt;mso-table-rspace: 0pt;vertical-align: top;border-top: 1px solid #BBBBBB;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%">
                                                                        <tbody>
                                                                            <tr style="vertical-align: top">
                                                                                <td style="word-break: break-word;border-collapse: collapse !important;vertical-align: top;font-size: 0px;line-height: 0px;mso-line-height-rule: exactly;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%">
                                                                                    <span>&#160;</span>
                                                                                </td>
                                                                            </tr>
                                                                        </tbody>
                                                                    </table>

                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                    <table style="font-family:arial,helvetica,sans-serif;" role="presentation" cellpadding="0" cellspacing="0" width="100%" border="0">
                                                        <tbody>
                                                            <tr>
                                                                <td style="overflow-wrap:break-word;word-break:break-word;padding:10px;font-family:arial,helvetica,sans-serif;" align="left">
                                                                    <h3 style="margin: 0px; line-height: 140%; text-align: left; word-wrap: break-word; font-weight: normal; font-family: arial,helvetica,sans-serif; font-size: 18px;">
                                                                        Email:
                                                                    </h3>
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                    <table style="font-family:arial,helvetica,sans-serif;" role="presentation" cellpadding="0" cellspacing="0" width="100%" border="0">
                                                        <tbody>
                                                            <tr>
                                                                <td style="overflow-wrap:break-word;word-break:break-word;padding:10px;font-family:arial,helvetica,sans-serif;" align="left">
                                                                    <table align="center" border="0" cellpadding="0" cellspacing="0" width="100%" style="border-collapse: collapse;table-layout: fixed;border-spacing: 0;mso-table-lspace: 0pt;mso-table-rspace: 0pt;vertical-align: top;border-top: 1px solid #BBBBBB;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%">
                                                                        <tbody>
                                                                            <tr style="vertical-align: top">
                                                                                <td style="word-break: break-word;border-collapse: collapse !important;vertical-align: top;font-size: 0px;line-height: 0px;mso-line-height-rule: exactly;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%">
                                                                                    <span>&#160;</span>
                                                                                </td>
                                                                            </tr>
                                                                        </tbody>
                                                                    </table>
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="u-col u-col-66p67" style="max-width: 320px;min-width: 333px;display: table-cell;vertical-align: top;">
                                            <div style="width: 100% !important;border-radius: 0px;-webkit-border-radius: 0px; -moz-border-radius: 0px;">
                                                <div style="padding: 0px;border-top: 0px solid transparent;border-left: 0px solid transparent;border-right: 0px solid transparent;border-bottom: 0px solid transparent;border-radius: 0px;-webkit-border-radius: 0px; -moz-border-radius: 0px;"><!--<![endif]-->
                                                    <table style="font-family:arial,helvetica,sans-serif;" role="presentation" cellpadding="0" cellspacing="0" width="100%" border="0">
                                                        <tbody>
                                                            <tr>
                                                                <td style="overflow-wrap:break-word;word-break:break-word;padding:10px;font-family:arial,helvetica,sans-serif;" align="left">
                                                                    <h3 style="margin: 0px; line-height: 140%; text-align: left; word-wrap: break-word; font-weight: normal; font-family: arial,helvetica,sans-serif; font-size: 18px;">
                                                                        <?php
                                                                        if ($stars >= 5) {
                                                                            ?>⭐⭐⭐⭐⭐<?php
                                                                        } elseif ($stars >= 4) {
                                                                            ?>⭐⭐⭐⭐<?php
                                                                        } elseif ($stars >= 3) {
                                                                            ?>⭐⭐⭐<?php
                                                                        } elseif ($stars >= 2) {
                                                                            ?>⭐⭐<?php
                                                                        } elseif ($stars >= 1) {
                                                                            ?>⭐<?php
                                                                        }
                                                                        ?>
                                                                    </h3>
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                    <table style="font-family:arial,helvetica,sans-serif;" role="presentation" cellpadding="0" cellspacing="0" width="100%" border="0">
                                                        <tbody>
                                                            <tr>
                                                                <td style="overflow-wrap:break-word;word-break:break-word;padding:10px;font-family:arial,helvetica,sans-serif;" align="left">
                                                                    <table align="center" border="0" cellpadding="0" cellspacing="0" width="100%" style="border-collapse: collapse;table-layout: fixed;border-spacing: 0;mso-table-lspace: 0pt;mso-table-rspace: 0pt;vertical-align: top;border-top: 1px solid #BBBBBB;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%">
                                                                        <tbody>
                                                                            <tr style="vertical-align: top">
                                                                                <td style="word-break: break-word;border-collapse: collapse !important;vertical-align: top;font-size: 0px;line-height: 0px;mso-line-height-rule: exactly;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%">
                                                                                    <span>&#160;</span>
                                                                                </td>
                                                                            </tr>
                                                                        </tbody>
                                                                    </table>
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                    <table style="font-family:arial,helvetica,sans-serif;" role="presentation" cellpadding="0" cellspacing="0" width="100%" border="0">
                                                        <tbody>
                                                            <tr>
                                                                <td style="overflow-wrap:break-word;word-break:break-word;padding:10px;font-family:arial,helvetica,sans-serif;" align="left">

                                                                    <h3 style="margin: 0px; line-height: 140%; text-align: left; word-wrap: break-word; font-weight: normal; font-family: arial,helvetica,sans-serif; font-size: 18px;">
                                                                        <?php echo!empty($reviews) ? $reviews : ''; ?>
                                                                    </h3>

                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                    <table style="font-family:arial,helvetica,sans-serif;" role="presentation" cellpadding="0" cellspacing="0" width="100%" border="0">
                                                        <tbody>
                                                            <tr>
                                                                <td style="overflow-wrap:break-word;word-break:break-word;padding:10px;font-family:arial,helvetica,sans-serif;" align="left">
                                                                    <table align="center" border="0" cellpadding="0" cellspacing="0" width="100%" style="border-collapse: collapse;table-layout: fixed;border-spacing: 0;mso-table-lspace: 0pt;mso-table-rspace: 0pt;vertical-align: top;border-top: 1px solid #BBBBBB;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%">
                                                                        <tbody>
                                                                            <tr style="vertical-align: top">
                                                                                <td style="word-break: break-word;border-collapse: collapse !important;vertical-align: top;font-size: 0px;line-height: 0px;mso-line-height-rule: exactly;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%">
                                                                                    <span>&#160;</span>
                                                                                </td>
                                                                            </tr>
                                                                        </tbody>
                                                                    </table>
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                    <table style="font-family:arial,helvetica,sans-serif;" role="presentation" cellpadding="0" cellspacing="0" width="100%" border="0">
                                                        <tbody>
                                                            <tr>
                                                                <td style="overflow-wrap:break-word;word-break:break-word;padding:10px;font-family:arial,helvetica,sans-serif;" align="left">
                                                                    <h3 style="margin: 0px; line-height: 140%; text-align: left; word-wrap: break-word; font-weight: normal; font-family: arial,helvetica,sans-serif; font-size: 18px;">
                                                                        <?php echo!empty($email) ? $email : ''; ?>
                                                                    </h3>
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                    <table style="font-family:arial,helvetica,sans-serif;" role="presentation" cellpadding="0" cellspacing="0" width="100%" border="0">
                                                        <tbody>
                                                            <tr>
                                                                <td style="overflow-wrap:break-word;word-break:break-word;padding:10px;font-family:arial,helvetica,sans-serif;" align="left">

                                                                    <table align="center" border="0" cellpadding="0" cellspacing="0" width="100%" style="border-collapse: collapse;table-layout: fixed;border-spacing: 0;mso-table-lspace: 0pt;mso-table-rspace: 0pt;vertical-align: top;border-top: 1px solid #BBBBBB;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%">
                                                                        <tbody>
                                                                            <tr style="vertical-align: top">
                                                                                <td style="word-break: break-word;border-collapse: collapse !important;vertical-align: top;font-size: 0px;line-height: 0px;mso-line-height-rule: exactly;-ms-text-size-adjust: 100%;-webkit-text-size-adjust: 100%">
                                                                                    <span>&#160;</span>
                                                                                </td>
                                                                            </tr>
                                                                        </tbody>
                                                                    </table>
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </body>
    </html>

    <?php
    $html = ob_get_contents();
    ob_end_clean();
    return $html;
}
