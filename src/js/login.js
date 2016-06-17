function ajax_call(input_data, callback){
    $.ajax({
        type: "POST",
        //url: "https://ajax.letsbuildagame.org",
        url: "http://localhost/lbag/index.php",
        data: input_data,
        success: function(data){
            callback(data);
        }
    });
}

function validator_add_error(element, helper, msg){
    if (element.hasClass('has-success')){
        element.removeClass('has-success');
    }
    if (!element.hasClass('has-error')){
        element.addClass('has-error');
    }
    if (typeof(helper) !== 'undefined'){
        helper.text(msg);
    }
}

function validator_add_success(element, helper){
    // Remove error class if exists
    if (element.hasClass('has-error')) {
        element.removeClass('has-error');
        helper.text('');
    }

    // Add success class if not exists
    if (!element.hasClass('has-success')){
        element.addClass('has-success');
    }
}

function toggleSubmitLoading(element){

    spinner = element.parent().find('i.input-loading');
    
    if(spinner.is(':visible')){
        spinner.hide();
        element.show();
    } else {
        element.hide();
        spinner.show();
    }

}

$('#form-login').submit(function(ev){

    ev.preventDefault();

    helper_element = $(this).find('.fs-error');
    submit_element = $(this).find('input[type="submit"]');    

    input_username = $('#form-login .input-username');
    input_password = $('#form-login .input-password');

    window.username = input_username[0].value;

    has_error = false;

    if (input_password[0].value == "") {
        validator_add_error(input_password);
        has_error = true;
    }
    if (window.username == "") {
        validator_add_error(input_username);
        has_error = true;
    }

    if (has_error) return;

    toggleSubmitLoading(submit_element);

    ajax_call(
        {
            func: "login",
            username: input_username[0].value,
            password: input_password[0].value
        },
        function(data){

            if (data.status === true){
                sess_id = data.msg;
                localStorage.setItem('session_id', sess_id);
                window.location = "panel.html";
            } else {
                toggleSubmitLoading(submit_element);
            }
        }
    )
});
