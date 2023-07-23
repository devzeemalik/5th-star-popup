/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
function WRPsetCookie(cname, cvalue, exdays) {
    const d = new Date();
    d.setTime(d.getTime() + (exdays * 24 * 60 * 60 * 1000));
    let expires = "expires=" + d.toUTCString();
    document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
}

function WRPgetCookie(cname) {
    let name = cname + "=";
    let ca = document.cookie.split(';');
    for (let i = 0; i < ca.length; i++) {
        let c = ca[i];
        while (c.charAt(0) == ' ') {
            c = c.substring(1);
        }
        if (c.indexOf(name) == 0) {
            return c.substring(name.length, c.length);
        }
    }
    return "";
}

jQuery(document).ready(function ($) {
    var AdminAjax = wrp.admin_url;
    var wrp_post_id = parseInt(wrp.post_id);
    var wrp_get_modal_setup = wrp.modal_open;
    var wrp_is_submited = wrp.is_submited;
    var wrp_is_review_field = wrp.is_review_field;
    var wrp_is_visitor_email = wrp.is_visitor_email;
    var wrp_review_url = wrp.review_url;
    var wrp_current_visits = wrp.current_visits;
    var wrp_is_show_on_visits = wrp.is_show_on_visits;
    var wrp_is_show_on_visits_value = wrp.is_show_on_visits_value;
    var wrp_unshow_count = parseInt(wrp.unshow_count);
    var wrp_current_unshow_count = parseInt(wrp.current_unshow_count);
    var wrp_popup_select_template = parseInt(wrp.popup_select_template);

    var is_ignored = 'no';
    if (wrp_unshow_count > 0) {
        if (wrp_current_unshow_count >= wrp_unshow_count) {
            is_ignored = 'yes';
        }
    }

//    if (wrp_is_show_on_visits === 'yes') {
//        wrp_is_show_on_visits_value = wrp.is_show_on_visits_value;
//    }
    console.log('%cCount: ' + wrp_current_visits, "color: Green; font-size: 20px");
    var modal;
    if (wrp_post_id > 0) {
        modal = $("#wrp_popup_" + wrp_post_id);
    } else {
        modal = $("#wrp_main_popup");
    }

    var btn = $("#wrp_main_popup_btn");
    var close_x = $(".wrp_close");
    var close_popup = $(".wrp_close_popup");

    var SelectStar = $(".wrp_select_star");
    var SelectStarSection = $(".wrp_stars_section");

    var beforeReviewsSelect = $('.wrp_stars_before_review');
    var afterReviewsSelect = $('.wrp_stars_after_review');

    var ReviewInput = $('.wrp_review_field_section');
    var VisitorEmailInput = $('.wrp_visitor_email_field_section');


    var getDefaultHeight = $(document).height();
    var getReviewStatus = WRPgetCookie('wrp_reviews_status');


    if (modal) {
        if (wrp_is_submited == 'no') {
            if (is_ignored === 'no') {
                if (wrp_current_visits >= wrp_is_show_on_visits_value) {
                    if (wrp_get_modal_setup.event == 'scroll') {
                        var SelectorHeight;
                        SelectorHeight = (getDefaultHeight * wrp_get_modal_setup.value) / 100;
                        $(document).scroll(function () {
                            var y = window.scrollY;
                            if (y >= SelectorHeight) {
                                modal.show();
                            }
                        });
                    }
                    if (wrp_get_modal_setup.event == 'duration') {
                        if (wrp_get_modal_setup.value) {
                            var getDuration = 1000 * wrp_get_modal_setup.value;
                            setTimeout(function () {
                                modal.show();
                            }, getDuration);

                        } else {
                            modal.show();
                        }
                    }
//
//                if (wrp_get_modal_setup.event == 'num_visits') {
//                    if (wrp_current_visits >= wrp_get_modal_setup.value) {
//                        modal.show();
//                    }
//                }

                    $(btn).on('click', function () {
                        modal.show();
                    });
                }
            }
        }
    }
    $('#wrp_popup_preview').on('click', function (e) {
        e.preventDefault();
        modal.show();
    });

    $(close_popup).on('click', function () {
        modal.hide();
        beforeReviewsSelect.show();
        afterReviewsSelect.hide();

        if (wrp_popup_select_template == 2) {
            SelectStarSection.show();
        }
        ReviewInput.hide();
        var setIgnoreCount = 1 + wrp_current_unshow_count;

        WRPsetCookie('wrp_count_ignore', setIgnoreCount, 365);
    });

    $(SelectStar).on('click', function (e) {
        var getStar = $(this).val();
        if (getStar && getStar > 0) {
            beforeReviewsSelect.hide();
            afterReviewsSelect.show();
        } else {
            beforeReviewsSelect.show();
            afterReviewsSelect.hide();
        }
        if (wrp_is_review_field == 'yes') {
            if (getStar > 0 && getStar <= 4) {
                if (wrp_popup_select_template == 2) {
                    SelectStarSection.hide();
                }
                ReviewInput.show();
            } else {

                WRPsetCookie('wrp_stars', getStar, 365);
                WRPsetCookie('wrp_reviews_status', 'success', 365);
                if (wrp_popup_select_template == 2) {
                    SelectStarSection.show();
                }
                beforeReviewsSelect.show();
                afterReviewsSelect.hide();
                ReviewInput.hide();
                modal.hide();
                window.open(wrp_review_url, '_blank');
            }
        }
        if (wrp_is_visitor_email == 'yes') {
            VisitorEmailInput.show();
        }
    });
    $('.wrp_submit_reviews').click(function () {
        var getStar = $('.wrp_select_star:radio:checked').val();
        var getEmail = $('.wrp_visitor_email_field_input_field').val();
        var getReviews = $('.wrp_review_field_input_field').val();
        if (getStar > 4 && wrp_review_url) {

            WRPsetCookie('wrp_stars', getStar, 365);
            WRPsetCookie('wrp_email', getEmail, 365);
            WRPsetCookie('wrp_reviews', getReviews, 365);
            WRPsetCookie('wrp_reviews_status', 'success', 365);
            beforeReviewsSelect.show();
            afterReviewsSelect.hide();
            ReviewInput.hide();
            modal.hide();

            window.open(wrp_review_url, '_blank');
        } else {
            var data = new FormData();
            data.append('stars', getStar);
            data.append('email', getEmail);
            data.append('reviews', getReviews);
            data.append('action', 'wrp_submit_reviews');

            var LoaderOB = $('#wrp_request_loader');
            var LoaderCon = $('.wrp_request_content');
            LoaderOB.show();
            LoaderCon.hide();
            $.ajax({
                type: "post",
                url: AdminAjax,
                data: data,
                dataType: 'JSON',
                cache: false,
                processData: false,
                contentType: false,
                beforeSend: function (before) {},
                success: function (res) {
                    WRPsetCookie('wrp_stars', getStar, 365);
                    WRPsetCookie('wrp_email', getEmail, 365);
                    WRPsetCookie('wrp_reviews', getReviews, 365);
                    if (res.status == 'success') {
                        WRPsetCookie('wrp_reviews_status', 'success', 365);
                        window.location.reload();
                    } else {
                        WRPsetCookie('wrp_reviews_status', 'error', 365);
                    }

                    beforeReviewsSelect.show();
                    afterReviewsSelect.hide();
                    ReviewInput.hide();
                    LoaderOB.hide();
                    LoaderCon.show();
                    modal.hide();

                }
            });
        }
    });

});


