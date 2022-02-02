<?php
/**
 * Plugin Name:  Unenroll For Learndash
 * Plugin URI: https://wptrat.com/learndash-unenroll /
 * Description:  Unenroll For Learndash is the best way to allow students to unenroll from courses.
 * Author: Luis Rock
 * Author URI: https://wptrat.com/
 * Version: 1.0.1
 * Text Domain: learndash-unenroll
 * Domain Path: /languages
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package   Unenroll For Learndash
 */


if ( ! defined( 'ABSPATH' ) ) exit;
		
// Requiring plugin files
require_once('admin/trun-settings.php');
require_once('includes/functions.php');

add_action( 'init', 'trun_load_textdomain' );
function trun_load_textdomain() {
  load_plugin_textdomain( 'learndash-unenroll', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' ); 
}


//Admin CSS
function trun_enqueue_admin_script( $hook ) {
    global $trun_settings_page;
    if( $hook != $trun_settings_page ) {
        return;
    }
    wp_enqueue_style('trun_admin_style', plugins_url('assets/css/trun-admin.css',__FILE__ ));
}
add_action( 'admin_enqueue_scripts', 'trun_enqueue_admin_script' );

function trun_enqueue_scripts_and_styles() {
    wp_register_script('trun_js', plugins_url('assets/js/trun.js',__FILE__ ), [],'1.0.0',true);
    wp_register_style('trun_css', plugins_url('assets/css/trun.css',__FILE__ ));
    wp_register_script( 'sweetalert2_js', plugins_url( 'assets/sweetalert2/sweetalert2.min.js', __FILE__ ), array( 'jquery' ), '1.0', false );
    wp_register_style( 'sweetalert2_css', plugins_url( 'assets/sweetalert2/sweetalert2.min.css', __FILE__ ) );

    wp_enqueue_script( 'trun_js' );
    wp_enqueue_style( 'trun_css' );

    wp_enqueue_script( 'sweetalert2_js' );
    wp_enqueue_style( 'sweetalert2_css' );

    wp_localize_script( 'trun_js', 'trun_js_object',
        array( 
            'ajaxurl' => admin_url( 'admin-ajax.php' ),
            'unenrollText' => TRUN_UNENROLL_TEXT,
            'removeFromGroup' => TRUN_REMOVE_FROM_GROUP,
            /* translators: Title for the confirmation alert box */
            'confirmationAlertTitle' => esc_html__( 'Are you sure?', 'learndash-unenroll' ),
            /* translators: Text for the confirmation alert box */
            'confirmationAlertText' => esc_html__( 'Do you really want to unenroll from this course?', 'learndash-unenroll' ),
            /* translators: Text for the confirmation alert box yes button */
            'confirmationAlertYesButton' => esc_html__( 'Yes', 'learndash-unenroll' ),
            /* translators: Text for the confirmation alert box no button */
            'confirmationAlertNoButton' => esc_html__( 'Cancel', 'learndash-unenroll' ),
            /* translators: Title for the cancellation alert box */
            'cancelAlertTitle' => esc_html__( 'Action Canceled!', 'learndash-unenroll' ),
            /* translators: Text for the cancellation alert box */
            'cancelAlertText' => esc_html__( 'You are still enrolled.', 'learndash-unenroll' ),
            /* translators: Text for the cancellation alert box dismiss button */
            'cancelAlertDismissButton' => esc_html__( 'OK', 'learndash-unenroll' ),
            /* translators: Title for the open course alert box */
            'openAlertTitle' => esc_html__( 'Open Course', 'learndash-unenroll' ),
            /* translators: Text for the open course alert box */
            'openAlertText' => esc_html__( 'Sorry, you cannot unenroll from an open course.', 'learndash-unenroll' ),
            /* translators: Title for the success alert box */
            'successAlertTitle' => esc_html__( 'Success!', 'learndash-unenroll' ),
            /* translators: Text for the success alert box */
            'successAlertText' => esc_html__( 'You were unenrolled.', 'learndash-unenroll' ),
            /* translators: Title for the (maybe) error alert box */
            'errorAlertTitle' => esc_html__( 'Ooops...', 'learndash-unenroll' ),
            /* translators: Text for the (maybe) error alert box */
            'errorAlertText' => esc_html__( 'We could not check the result.', 'learndash-unenroll' ),
            /* translators: Text for when an admin is trying to enroll from a course (which is impossible) */
            'errorAlertAdminText' => esc_html__( 'You are a site admin, so you cannot unenroll.', 'learndash-unenroll' ),
            /* translators: Text for the alert box reload button */
            'alertReloadButton' => esc_html__( 'Reload', 'learndash-unenroll' ),
            /* translators: Text for the cancellation alert box when leaving a group is not allowed */
            'cancelAlertTextGroup' => esc_html__( 'You cannot unenroll because this course is associated with a group you\'re in.', 'learndash-unenroll' ),
            /* translators: Text for the cancellation alert box when course is set to open (so unenroll is not possible) */
            'cancelAlertTextOpenCourse' => esc_html__( 'You cannot unenroll because this is an open course.', 'learndash-unenroll' ),
            /* translators: Text for the confirmation alert box when leaving a group (singular) */
            'confirmationAlertTextGroup' => esc_html__( 'To unenroll from this course, you must leave the following group:', 'learndash-unenroll' ),
            /* translators: Text for the confirmation alert box when leaving more then one group (plural) */
            'confirmationAlertTextGroups' => esc_html__( 'To unenroll from this course, you must leave the following groups:', 'learndash-unenroll' ),
            /* translators: Ask for permission to proceed with the leaving group(s) process */
            'confirmationAlertTextGroupProceed' => esc_html__( 'Proceed?', 'learndash-unenroll' ),
        )
    );
} //end function
add_action( 'wp_enqueue_scripts', 'trun_enqueue_scripts_and_styles' );


if ( is_admin() ) {
    add_action( 'wp_ajax_trun_listen_unenroll_from_course', 'trun_listen_unenroll_from_course' );
}

if(TRUN_WHERE && TRUN_WHERE !== 'none') {

    if(strpos(TRUN_WHERE, 'the_content') !== false) {
        add_filter('the_content', 'trun_unenroll_echo_form_filter');

    } else {
        add_action( TRUN_WHERE, 'trun_unenroll_echo_form', 10 );
    }

}

//add hooks and actions
add_filter(
    'learndash_course_grid_html_output', 
    'trun_course_volume_on_grid',
    999,
    2
);