<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

// if uninstall.php is not called by WordPress, die
if (!defined('WP_UNINSTALL_PLUGIN')) {
    die;
}
global $wpdb;
$tableName1 = $wpdb->prefix . 'wrp_visitor_counts';
$wpdb->query("DROP TABLE IF EXISTS $tableName1");
