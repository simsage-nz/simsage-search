
const error_html = '<div id="[id]" class="notice notice-error settings-error is-dismissible"><p><strong>[m]</strong></p>' +
                   '<div class="notice-dismiss" onclick="hide_message(\'[id]\')"><span class="screen-reader-text">Dismiss this notice.</span>' +
                   '</div></div>';

const info_html = '<div id="[id]" class="notice notice-info settings-error is-dismissible"><p><strong>[m]</strong></p>' +
                  '<div class="notice-dismiss" onclick="hide_message(\'[id]\')"><span class="screen-reader-text">Dismiss this notice.</span>' +
                  '</div></div>';

function hide_message(id) {
    const ctrl = document.getElementById(id);
    if (ctrl) ctrl.style.display = 'none';
}

let message_id = 1;

function display_messages(data) {
    if (data && data.length) {
        const ctrl = document.getElementById('message-area');
        for (const item of data) {
            const id = 'msg_' + message_id;
            if (item.success) {
                console.log('info: ' + item.message);
                const str = info_html.replace(/\[m]/g, item.message).replace(/\[id]/g, id);
                ctrl.innerHTML += str;
            } else {
                console.log('error: ' + item.message);
                const str = error_html.replace(/\[m]/g, item.message).replace(/\[id]/g, id);
                ctrl.innerHTML += str;
            }
            message_id += 1;
        }
    }
}

// try and connect to SimSage
function do_sign_in() {
    // set ajax data
    jQuery(function($) {
        const btn = $("#sign-in-button");
        btn.attr('disabled', true);
        const data = {
            'action': 'sign_in',
            'simsage_registration_key': $("#simsage_registration_key").val(),
        };
        $.post(ajax_url, data, function (response) {
            display_messages(response.data);
        }).always( function() {
            btn.attr('disabled', false);
        })
    });
}


// close your SimSage account
function do_close_account() {
    // set ajax data
    jQuery(function($) {
        const data = {
            'action': 'close_account',
            'simsage_password': $("#simsage_password").val(),
        };
        $.post(ajax_url, data, function (response) {
            display_messages(response.data);
        })
    });
}


