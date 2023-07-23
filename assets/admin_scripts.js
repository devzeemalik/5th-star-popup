/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

jQuery(document).ready(function ($) {
    $('.wrp_main_popup').addClass('wrp_main_popup_admin_area');
    $('#wrp_select_pages').multiSelect({
        selectableHeader: "<div class='wrp_selection_list_headers'>Select Pages</div>",
        selectionHeader: "<div class='wrp_selection_list_headers'>Selected Pages</div>",
    });

    $('#wrp_select-all').click(function () {
        $('#wrp_select_pages').multiSelect('select_all');
        return false;
    });
    $('#wrp_deselect-all').click(function () {
        $('#wrp_select_pages').multiSelect('deselect_all');
        return false;
    });
    $('#wrp_refresh').on('click', function () {
        $('#wrp_select_pages').multiSelect('refresh');
        return false;
    });

    $(".wrp_color_picker").spectrum({
        showAlpha: true,
        showInput: true,
        preferredFormat: "rgb",
        color: function () {
            var getThisID = $(this).attr("id");
            var GetDefault = $('#' + getThisID).attr('value');
        },
        change: function (color) {
            var get_color = color.toRgbString();
            var getThisID = $(this).attr("id");
            $('#' + getThisID).attr('value', get_color);
        }
    });
    $(".wrp_color_picker_flat").spectrum({
        flat: false,
        allowEmpty: true,
        showAlpha: true,
        showInput: true,
        preferredFormat: "rgb",
        move: function (color) {
            var get_color = color.toRgbString();
            var getThisID = $(this).attr("id");
            $('#' + getThisID).attr('value', get_color);
        },
        change: function (color) {
            var get_color = color.toRgbString();
            var getThisID = $(this).attr("id");
            $('#' + getThisID).attr('value', get_color);
        }
    });
    $('#wrp_popup_show_event').change(function () {
        var getVal = $(this).val();
        if (getVal == 'scroll') {
            $('.wrp_popup_show_event_value_label').html('Percent');
            $('.wrp_popup_show_event_value_label').attr('max', '100');
        }
        if (getVal == 'duration') {
            $('.wrp_popup_show_event_value_label').html('Seconds');
            $('.wrp_popup_show_event_value_label').removeAttr('max');
        }
    });
//    $('.wrp_popup_show_num_visits').click(function () {
//        var getRVal = $(this).val();
//        if (getRVal == 'yes') {
//            $('#wrp_popup_show_num_visits_value_row').show();
//        } else {
//            $('#wrp_popup_show_num_visits_value_row').hide();
//        }
//
//    });
    if ($('.wrp_popup_image_url').length > 0) {
        if (typeof wp !== 'undefined' && wp.media && wp.media.editor) {
            $('.wrp_popup_image_url').on('click', function (e) {
                e.preventDefault();
                var button = $(this);
                var id = button.prev();
                wp.media.editor.send.attachment = function (props, attachment) {
                    $('.wrp_image_upload_preview').html('<img src="' + attachment.url + '" style=" height: 100%; max-height: 200px; width: auto; object-fit: contain;"  alt="image preview">');
                    id.val(attachment.id);
                };
                wp.media.editor.open(button);
                return false;
            });
        }
    }

    $('#wrp_popup_preview').click(function (e) {
        e.preventDefault();
        $('.wrp_main_popup').removeClass('wrp_main_popup_admin_area');
    });



});

