/* global clinicDoctorSorting */
jQuery(function ($) {
    var $table = $('#the-list');
    if (!$table.length) {
        return;
    }

    var $notice = $('<div class="clinic-order-notice" />').insertAfter('.wrap h1');

    function setNotice(text, type) {
        $notice
            .removeClass('is-success is-error')
            .addClass(type ? 'is-' + type : '')
            .text(text);
    }

    $table.sortable({
        items: 'tr',
        axis: 'y',
        handle: '.clinic-order-handle',
        helper: function (e, ui) {
            ui.children().each(function () {
                $(this).width($(this).width());
            });
            return ui;
        },
        update: function () {
            var order = [];

            $table.find('tr').each(function () {
                var id = $(this).attr('id');
                if (!id) {
                    return;
                }
                id = id.replace('post-', '');
                if ($.isNumeric(id)) {
                    order.push(id);
                }
            });

            if (!order.length) {
                return;
            }

            setNotice(clinicDoctorSorting.strings.saving);

            $.post(
                clinicDoctorSorting.ajaxUrl,
                {
                    action: 'clinic_save_doctor_order',
                    nonce: clinicDoctorSorting.nonce,
                    order: order
                }
            )
                .done(function (response) {
                    if (response && response.success) {
                        setNotice(clinicDoctorSorting.strings.saved, 'success');
                    } else {
                        setNotice(clinicDoctorSorting.strings.error, 'error');
                    }
                })
                .fail(function () {
                    setNotice(clinicDoctorSorting.strings.error, 'error');
                });
        }
    });
});
