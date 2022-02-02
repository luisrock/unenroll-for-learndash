<?php

$options_array = [
    'trun_unenroll_text' => [
        'group' => 'A',
        'group_title' => __('Button Text', 'learndash-unenroll'),
        'type' => 'text',
        'kind' => '',
        'default' => __('Unenroll', 'learndash-unenroll'),
        'description'=> __('Text for the unenroll button', 'learndash-unenroll'),
        'label' => '',
        'obs' => '',
        'final' => '',
        'order' => 1,
    ],
    'trun_where' => [
        'group' => 'B',
        'group_title' => __('Single Course Page', 'learndash-unenroll'),
        'type' => 'select',
        'kind' => '',
        'default' => 'none',
        'options' => [
            'none' => __('Do not show on course page', 'learndash-unenroll'),
            // 'the_title' => __('On the title', 'learndash-unenroll'),
            'learndash-course-before' => __('After the title', 'learndash-unenroll'),
            'the_content_beggining' => __('On the content area (beginning)', 'learndash-unenroll'),
            'the_content_end' => __('On the content area (end)', 'learndash-unenroll'),
            'learndash-course-content-list-before' => __('Before the lessons list', 'learndash-unenroll'),
            'learndash-course-content-list-after' => __('After the lessons list', 'learndash-unenroll'),
        ],
        'description'=> __('Select where to place the \'Unenroll\' button on the single course page', 'learndash-unenroll'),
        'label' => '',
        'obs' => '',
        'final' => '',
        'order' => 2,
    ],
    'trun_single_class' => [
        'group' => 'B',
        'group_title' => '',
        'type' => 'text',
        'kind' => '',
        'default' => '',
        'description'=> __('Define a class for CSS styling the unenroll form button on the course page', 'learndash-unenroll'),
        'obs' => '',
        'final' => '',
        'order' => 3, 
    ],
    'trun_grid_where' => [
        'group' => 'C',
        'group_title' => __('Course Grid', 'learndash-unenroll'),
        'type' => 'select',
        'kind' => '',
        'options' => [
            'none' => __('Do not show on the grid', 'learndash-unenroll'),
            'entry-title' => __('Before the title', 'learndash-unenroll'),
            // 'next-title' => __('Next to the title', 'learndash-unenroll'),
            'entry-content' => __('After the title', 'learndash-unenroll'),
            'ld_course_grid_button' => __('After the description', 'learndash-unenroll'),
            'learndash-widget' => __('After the button', 'learndash-unenroll'), 
            'ld-progress-bar' => __('Before the progress bar', 'learndash-unenroll'), 
        ],
        'default' => 'none',
        'description'=> __('Select where to place the \'Unenroll\' button on the grid', 'learndash-unenroll'),
        'obs' => __('Be aware that the \'Unenroll\' button will be placed on the grid only when the grid shortcode [ld_course_list] is used. If you plan to use the new Course Grid Block feature, the \'Unenroll\' button will not be displayed on the grid, leaving you with the option to show the button on the single course page.', 'learndash-unenroll'),
        'final' => '',
        'order' => 4, 
    ],
    'trun_grid_class' => [
        'group' => 'C',
        'group_title' => '',
        'type' => 'text',
        'kind' => '',
        'default' => '',
        'description'=> __('Define a class for CSS styling the unenroll form button on the grid', 'learndash-unenroll'),
        'obs' => '',
        'final' => '',
        'order' => 5, 
    ],
    'trun_remove_from_group' => [
        'group' => 'D',
        'group_title' => __('Course in Group', 'learndash-unenroll'),
        'type' => 'checkbox',
        'kind' => '',
        'default' => '',
        'description'=> __('If the course is associated with a group, allow the student to leave the group by clicking the "unenroll" button (and confirming when prompted to do so)' , 'learndash-unenroll'),
        'label' => '',
        'obs' => '',
        'final' => __('If not checked, users who are part of groups associated with the course will see a message saying that they cannot unenroll.', 'learndash-unenroll'),
        'order' => 6,
    ],
    'trun_open_course' => [
        'group' => 'E',
        'group_title' => __('Open Course', 'learndash-unenroll'),
        'type' => 'select',
        'kind' => '',
        'options' => [
            'none' => __('Don\'t show any button', 'learndash-unenroll'),
            'disabled' => __('Show a disabled button', 'learndash-unenroll'),
            'alert' => __('Alert user on click', 'learndash-unenroll'),
        ],
        'default' => 'none',
        'description'=> __('Select what to do for courses with access type set to "open"', 'learndash-unenroll'),
        'obs' => __('If it\'s an open course, LearnDash makes all registered users automatically enrolled, with no option to unenroll.', 'learndash-unenroll'),
        'final' => '',
        'order' => 7, 
    ],
    'trun_open_course_text_button' => [
        'group' => 'E',
        'group_title' => '',
        'type' => 'text',
        'kind' => '',
        'default' => __('open course', 'learndash-unenroll'),
        'description'=> __('Define text for the open course button (if you selected above to display it)', 'learndash-unenroll'),
        'obs' => '',
        'final' => '',
        'order' => 8, 
    ],
];

define("TRUN_OPTIONS_ARRAY", $options_array);
foreach(TRUN_OPTIONS_ARRAY as $op => $vals) {
    $option = (get_option($op)) ? get_option($op) : $vals['default'];
    define(strtoupper($op),$option);
}

function trun_admin_menu() {
    global $trun_settings_page;
    $trun_settings_page = add_submenu_page(
                            'learndash-lms', //The slug name for the parent menu
                            __( 'Unenroll', 'learndash-unenroll' ), //Page title
                            __( 'Unenroll', 'learndash-unenroll' ), //Menu title
                            'manage_options', //capability
                            'learndash-unenroll', //menu slug 
                            'trun_admin_page' //function to output the content
                        );
}
add_action( 'admin_menu', 'trun_admin_menu' );


function trun_register_plugin_settings() {
    foreach(TRUN_OPTIONS_ARRAY as $op => $vals) {
        register_setting( 'trun-settings-group', $op );
    } 
}
//call register settings function
add_action( 'admin_init', 'trun_register_plugin_settings' );


function trun_admin_page() {
?>

<div class="trun-head-panel">
    <h1><?php esc_html_e( 'Unenroll Plugin', 'learndash-unenroll' ); ?></h1>
    <h3><?php esc_html_e( 'Allow your students to unenroll from courses.', 'learndash-unenroll' ); ?></h3>
</div>

<div class="wrap trun-wrap-grid">

    <form method="post" action="options.php">

        <?php settings_fields( 'trun-settings-group' ); ?>
        <?php do_settings_sections( 'trun-settings-group' ); ?>

        <div class="trun-form-fields">

            <div class="trun-settings-title">
                <?php esc_html_e( 'Unenroll Plugin - Settings', 'learndash-unenroll' ); ?>
            </div>

            <?php 
            foreach(TRUN_OPTIONS_ARRAY as $op => $vals) { ?>

                <?php if(!empty($vals['group_title'])) {
                            if($vals['group'] !== 'A') { ?>
                    <hr>
                        <?php } ?> 
                    <h3 class="trun-group-title"><?php echo esc_html($vals['group_title']); ?></h3>
                <?php } ?>

                <div class="trun-form-fields-label">
                    <?php esc_html_e( $vals['description'], 'learndash-unenroll' ); ?>
                    <?php if(!empty($vals['obs'])) { ?>
                        <span>* <?php esc_html_e( $vals['obs'], 'learndash-unenroll' ); ?></span>
                    <?php } ?>
                </div>
                <div class="trun-form-fields-group">
                    <?php if($vals['type'] === 'select') { ?>
                        <!-- select -->
                        <div class="trun-form-div-select">
                            <label>
                                <select name="<?php echo ($vals['kind'] === 'multiple') ? esc_attr( $op ) . '[]' : esc_attr( $op ); ?>"
                                        <?php echo esc_attr($vals['kind']); ?>
                                >
                                    <?php if(empty($vals['options'])) {$vals['options'] = $vals['get_options']();} 
                                    foreach($vals['options'] as $key => $pt) { ?>
                                        <option value="<?php echo esc_attr($key); ?>"
                                        <?php
                                            if( empty(get_option($op)) && $vals['default'] === $key ) {
                                                echo esc_attr('selected');
                                            } else if( $vals['kind'] === 'multiple' ) {
                                                if( is_array(get_option($op)) && in_array($key,get_option($op)) ) {
                                                    echo esc_attr('selected');
                                                }
                                            } else {
                                                selected($key, get_option($op), true);
                                            }
                                        ?>
                                        >     
                                            <?php echo esc_html($pt); ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </label>
                        </div>
                    <?php } else if ($vals['type'] === 'text') { ?>
                        <!-- text -->
                        <input type="text" placeholder="<?php echo esc_attr($vals['default']); ?>" class=""
                            value="<?php echo esc_attr( get_option($op) ); ?>"
                            name="<?php echo esc_attr( $op ); ?>">
                    <?php } else if ($vals['type'] === 'textarea') { ?>
                        <!-- textarea -->
                        <textarea class="large-text"
                                  cols="80"
                                  rows="10"
                                  name="<?php echo esc_attr( $op ); ?>"><?php echo esc_html( get_option($op) ); ?></textarea>
                    <?php } else if ($vals['type'] === 'checkbox') { ?>
                        <!-- checkbox -->
                        <div class="trun-form-div-checkbox">
                            <label>
                                <input  class="trun-checkbox" 
                                        type="checkbox" 
                                        name="<?php echo esc_attr( $op ); ?>"
                                        value="1"
                                        <?php checked(1, get_option( $op ), true); ?> 
                                        >
                                <?php if(!empty($vals['label'])) { ?>
                                    <span class="trun-form-fields-style-label">
                                        <?php esc_html_e( $vals['label'], 'trun-grid-button' ); ?>
                                    </span>
                                <?php } ?>
                            </label>
                        </div>                    
                    <?php } ?>

                    <?php if(!empty($vals['final'])) { ?>
                        <span>* <?php esc_html_e($vals['final'], 'learndash-unenroll' ); ?></span>
                    <?php } ?>
                </div>
                <?php } //end foreach TRUN_OPTIONS_ARRAY ?>
               

            <?php submit_button(); ?>

            <div style="float:right; margin-bottom:20px">
              Contact Luis Rock, the author, at 
              <a href="mailto:lurockwp@gmail.com">
                lurockwp@gmail.com
              </a>
            </div>

        </div> <!-- end form fields -->
    </form>
</div> <!-- end trun-wrap-grid -->
<?php } ?>