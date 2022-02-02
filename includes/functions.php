<?php

function trun_unenroll_form($course_id, $user_groups, $open_course, $class = '') {

	$button_text = ($open_course) ?  TRUN_OPEN_COURSE_TEXT_BUTTON : TRUN_UNENROLL_TEXT;
	$disabled = ($open_course && TRUN_OPEN_COURSE == 'disabled') ? 'disabled' : '';
	$class_to_add_form = 'trun-unenroll-form trun-unenroll-single-course';
	$class_to_add_form_button = 'trun-unenroll-submit-input-single-course';
	if(!empty(trim(TRUN_SINGLE_CLASS))) {
		$class_to_add_form_button .= ' ' . trim(TRUN_SINGLE_CLASS);
	}
	$open_course_value = ($open_course) ? "1" : "";

	$form = '<form method="POST" data-course-id="' . $course_id . '" id="trun-form-unenroll-' . $course_id . '" class="' . $class_to_add_form . '">';
	$form .= wp_nonce_field( 'trun_unenroll_course_'  . $course_id, 'trun_nonce_'  . $course_id, false, false); 
	$form .= '<input type="hidden" name="trun_action" id="trun_action_'  . $course_id .  '" value="unenroll">';
	$form .= '<input type="hidden" name="open_course" id="open_course_'  . $course_id .  '" value="' . esc_attr($open_course_value) . '">';
	$form .= '<input type="hidden" name="user_groups" id="user_groups_'  . $course_id .  '" value="' . esc_attr(json_encode($user_groups)) . '">';
	$form .= '<input type="submit" value="' . esc_html($button_text) . '" class="' . $class_to_add_form_button .  '" ' . esc_attr($disabled) . '>';
	$form .= '</form>';
  
	return $form;
}

//Listening to the unenroll form submit
function trun_listen_unenroll_from_course() {
	
	if( !isset( $_POST['trunAction'] ) || 'unenroll' !== $_POST['trunAction'] ) {
		wp_die('Error: trunAction');
	}
	
	if( empty( $_POST['courseId'] ) ) {
		wp_die('Error: courseId');
	}
	$course_id = intval($_POST['courseId']);

	if ( !isset( $_POST['_wpnonce'] ) || !wp_verify_nonce( $_POST['_wpnonce'], 'trun_unenroll_course_'  . $course_id ) ) {
		wp_die('Error: _wpnonce não existe');
	} 
	
	//get current user
	$user = wp_get_current_user();
 	$user_id = $user->ID;
	 //check if has admin role
	$admin_roles = array(
		'administrator'
	);
	$has_admin_role = array_intersect($admin_roles, $user->roles );

	if($has_admin_role) {
		wp_die('Error: admin');
	}
	
	$user_groups = ( !empty($_POST['userGroups']) && is_array($_POST['userGroups']) ) ? (array)$_POST['userGroups'] : [];
	
	ld_update_course_access($user_id, $course_id, $remove = true);
	if( !sfwd_lms_has_access( $course_id, $user_id ) ) {
		//done
		wp_die('Success');
	}

	//User still has access. Course must be admin or part of a group
	if(empty($user_groups)) {
		//very strange...that should not happen
		wp_die('Error: no groups');
	}

	foreach($user_groups as $group) {
		// wp_die(json_encode($group));
		ld_update_group_access( $user_id, intval($group['id']), $remove = true );
	}
	//Now that user was removed from group, we can remove (again) course access
	ld_update_course_access($user_id, $course_id, $remove = true);

	wp_die('Success');
}

function trun_get_user_groups($course_id) {
	$user_groups = [];
	$user_id = get_current_user_id();
	if(empty($user_id)) {
		return $user_groups;
	}
	$group_ids = learndash_get_course_groups( $course_id );
	$user_groups = [];
	if($group_ids) {
		foreach($group_ids as $gid) {
			if(learndash_is_user_in_group($user_id, $gid)) {
				$user_groups[] = [
						'id' => $gid,
						'title' => get_the_title($gid) 
				]; 
			}
		}
	}
	return $user_groups;
}

function trun_is_user_enrolled($course_id, $user_id = 0) {
	$user_id = (empty($user_id)) ? get_current_user_id() : $user_id;
	return !empty($user_id) && sfwd_lms_has_access( $course_id, $user_id );
}

function trun_get_unenroll_form($course_id, $class = '') {
	if(get_post_type($course_id) !== 'sfwd-courses') {
		return '';
	}
	$user_id = get_current_user_id();
	if( !sfwd_lms_has_access( $course_id, $user_id ) ) {
		return '';
	}
	$open_course = trun_is_open_course($course_id);
	if( $open_course && TRUN_OPEN_COURSE == 'none' ) {
		return '';
	}
	$user_groups = trun_get_user_groups($course_id);
	$form = trun_unenroll_form($course_id, $user_groups, $open_course, $class);
	if(!empty($form)) {
		return $form;
	}
	return '';
}

function trun_unenroll_echo_form( $course_id ) {
	$form = trun_get_unenroll_form($course_id);
	if(!empty($form)) {
		echo $form;
		return;
	}
	return;
}

function trun_unenroll_echo_form_filter($content) {
	if( !is_singular() && !is_main_query() ) { 
		return $content;
	}
	global $post;
	$course_id = $post->ID;
	$form = trun_get_unenroll_form($course_id, $class = '');
	if(empty($form)) {
		return $content;
	}
	if(TRUN_WHERE === 'the_content_end') {
		return $content.$form;
	}
	return $form.$content;
}

function trun_is_open_course($course_id) {
	$access_type = get_post_meta($course_id, '_ld_price_type', true);
	return !empty($access_type) && $access_type == 'open';
}

function trun_course_volume_on_grid($item_html, $post) { 

	if($post->post_type !== 'sfwd-courses') {
		return $item_html;
	}

	if(TRUN_GRID_WHERE == 'none') {
		return $item_html;
	}
	
	$classes_to_find = [
		TRUN_GRID_WHERE
	];

	$course_id = $post->ID;

	$class_to_add_form = 'trun-unenroll-form trun-unenroll-grid-item';
	$class_to_add_form_button = 'trun-unenroll-submit-input-grid-item';
	if(!empty(trim(TRUN_GRID_CLASS))) {
		$class_to_add_form_button .= ' ' . trim(TRUN_GRID_CLASS);
	}

	// $style_to_add = trun_get_grid_style();
	$style_to_add = '';

	$item_html = mb_convert_encoding($item_html, 'HTML-ENTITIES', "UTF-8");
	@$dom = new DOMDocument();
	@$dom->loadHTML($item_html);
	if(!$dom) {
		return $item_html;
	}	
	$xpath = new DomXPath($dom);
	if(!$xpath) {
		return $item_html;
	}
	foreach ($classes_to_find as $cl) {
		$nodeList = $xpath->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' $cl ')]");
		if($nodeList && $nodeList->length) { 
			break;
		}	
	}
	
	if(!$nodeList || !$nodeList->length) {
		return $item_html;
	}

	$open_course = trun_is_open_course($course_id);
	if( $open_course && TRUN_OPEN_COURSE == 'none' ) {
		return $item_html;
	}
	if(!trun_is_user_enrolled($course_id)) {
		return $item_html;
	}

	$button_text = ($open_course) ?  TRUN_OPEN_COURSE_TEXT_BUTTON : TRUN_UNENROLL_TEXT;
	$disabled = $open_course && TRUN_OPEN_COURSE == 'disabled';
	$user_groups = trun_get_user_groups($course_id);
	$open_course_value = ($open_course) ? "1" : "";

	$target_element = $nodeList->item(0);
	$form = $dom->createElement('form');
	$form->setAttribute('method', 'POST');
	$form->setAttribute('id', 'trun-form-unenroll-' . $course_id);
	$form->setAttribute('data-course-id', $course_id);
	$form->setAttribute('class', $class_to_add_form);
	//TODO: add style in the future versions
	if(!empty($style_to_add) && empty(TRUN_GRID_CLASS)) {
		$form->setAttribute('style', $style_to_add);
	}
		

    $input_nonce = $dom->createElement('input');
    $input_nonce->setAttribute('type', 'hidden');
	$input_nonce->setAttribute('name', 'trun_nonce_'  . $course_id);
	$input_nonce->setAttribute('id', 'trun_nonce_'  . $course_id);
	$input_nonce->setAttribute('value', wp_create_nonce( 'trun_unenroll_course_'  . $course_id ) );	
	$form->appendChild($input_nonce);

    $input_action = $dom->createElement('input');
    $input_action->setAttribute('type', 'hidden');
	$input_action->setAttribute('name', 'trun_action');
	$input_action->setAttribute('id', 'trun_action_' . $course_id);
	$input_action->setAttribute('value', 'unenroll' );
	$form->appendChild($input_action);

	$input_open_course = $dom->createElement('input');
    $input_open_course->setAttribute('type', 'hidden');
	$input_open_course->setAttribute('name', 'open_course');
	$input_open_course->setAttribute('id', 'open_course_' . $course_id);
	$input_open_course->setAttribute('value', $open_course_value);
	$form->appendChild($input_open_course);

	$input_user_groups = $dom->createElement('input');
    $input_user_groups->setAttribute('type', 'hidden');
	$input_user_groups->setAttribute('name', 'user_groups');
	$input_user_groups->setAttribute('id', 'user_groups_' . $course_id);
	$input_user_groups->setAttribute('value', json_encode($user_groups));
	$form->appendChild($input_user_groups);

	$input_submit = $dom->createElement('input');
    $input_submit->setAttribute('type', 'submit');
	$input_submit->setAttribute('class', $class_to_add_form_button);
	$input_submit->setAttribute('value', $button_text);
	if($disabled) {
		$input_submit->setAttribute('disabled', 'disabled');
	}
	$form->appendChild($input_submit);


	$parent = $target_element->parentNode;
	if(!$parent) {
		return $item_html;
	}
	$parent->insertBefore($form, $target_element);
	
	return $dom->saveHTML();
} //end function