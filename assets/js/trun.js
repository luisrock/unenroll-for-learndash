const ajaxUrl = trun_js_object.ajaxurl;
//const wpnonce = trun_js_object._wpnonce;
const unenrollText = trun_js_object.unenrollText;
const removeFromGroup = trun_js_object.removeFromGroup;
const confirmationAlertTitle = trun_js_object.confirmationAlertTitle;
const confirmationAlertText = trun_js_object.confirmationAlertText;
const confirmationAlertYesButton = trun_js_object.confirmationAlertYesButton;
const confirmationAlertNoButton = trun_js_object.confirmationAlertNoButton;
const cancelAlertTitle = trun_js_object.cancelAlertTitle;
const cancelAlertText = trun_js_object.cancelAlertText;
const cancelAlertDismissButton = trun_js_object.cancelAlertDismissButton;
const successAlertTitle = trun_js_object.successAlertTitle;
const successAlertText = trun_js_object.successAlertText;
const errorAlertTitle = trun_js_object.errorAlertTitle;
const errorAlertText = trun_js_object.errorAlertText;
const errorAlertAdminText = trun_js_object.errorAlertAdminText;
const alertReloadButton = trun_js_object.alertReloadButton;
const cancelAlertTextGroup = trun_js_object.cancelAlertTextGroup;
const cancelAlertTextOpenCourse = trun_js_object.cancelAlertTextOpenCourse;
const confirmationAlertTextGroup = trun_js_object.confirmationAlertTextGroup;
const confirmationAlertTextGroups = trun_js_object.confirmationAlertTextGroups;
const confirmationAlertTextGroupProceed = trun_js_object.confirmationAlertTextGroupProceed;

function trunObjectLength( object ) {
    var length = 0;
    for( var key in object ) {
        if( object.hasOwnProperty(key) ) {
            ++length;
        }
    }
    return length;
};

jQuery(document).ready(function ($) {

    $("form.trun-unenroll-form").submit(function (event) {
        event.preventDefault();
        const form = $(this);
        const courseId = form.attr('data-course-id');
        const wpnonce = $("#trun_nonce_" + courseId, form).val();
        const trunAction = $("#trun_action_" + courseId, form).val();
        const userGroups = $("#user_groups_" + courseId, form).val();
        const openCourse = $("#open_course_" + courseId, form).val();
        const alertTitle = confirmationAlertTitle;
        let alertText = confirmationAlertText;
        let groupTitles, groupText;
        const groups = JSON.parse(userGroups);
        
        if(groups.length > 0) {
            if(!removeFromGroup) {
                Swal.fire(
                    cancelAlertTitle,
                    cancelAlertTextGroup,
                    'error'
                ) 
                return false;
            }
            groupText = (trunObjectLength(groups) > 1) ? confirmationAlertTextGroups : confirmationAlertTextGroup;
            groupTitles = $.map(groups, function(v){
                return v.title;
            }).join(', ');
            alertText = groupText;
            alertText += ' ' + groupTitles + '. ';
            alertText += confirmationAlertTextGroupProceed;
        }
            
        if(openCourse && openCourse === '1' ) {
            Swal.fire(
                cancelAlertTitle,
                cancelAlertTextOpenCourse,
                'error'
            ) 
            return false;
        }

        //Tem certeza?
        Swal.fire({
            title: alertTitle,
            text: alertText,
            type: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: confirmationAlertYesButton,
            cancelButtonText: confirmationAlertNoButton
            }).then((result) => {
                if (result.value) {
                    
                    $.ajax({
                        url: ajaxUrl,
                        type: 'post',
                        data: {
                            'action': 'trun_listen_unenroll_from_course',
                            '_wpnonce': wpnonce,
                            'courseId': courseId,
                            'trunAction': trunAction,
                            'openCourse': openCourse,
                            'userGroups': JSON.parse(userGroups),
                            'removeFromGroup': removeFromGroup
                        },
                        success: function (response) {
                            if(response == 'Success') {
                                responseTitle = successAlertTitle;
                                responseText = successAlertText;
                                responseType = 'success';
                            } else {
                                responseTitle = errorAlertTitle;
                                responseText = errorAlertText;
                                responseType = 'warning';
                                //check if response has 'admin' substring in it
                                if(response.indexOf('admin') > -1) {
                                    responseText = errorAlertAdminText;
                                }
                            }
                            console.log(response);

                            Swal.fire({
                                title: responseTitle,
                                text: responseText,
                                type: responseType,
                                showCancelButton: false,
                                confirmButtonColor: '#3085d6',
                                cancelButtonColor: '#d33',
                                confirmButtonText: alertReloadButton
                            }).then((result) => { 
                                window.location.href = window.location.href;
                                return false;
                            });
                        } //end success callback

                    }); //end ajax call 

                } else {
                    //result = {dismiss: "cancel"} => cancelou
                    Swal.fire(
                    cancelAlertTitle,
                    cancelAlertText,
                    'error'
                    )
                }
            }) //end Swal

    }); //end form submit   

}); //end jquery