var current_fs, next_fs, previous_fs; //fieldsets
var left, opacity, scale; //fieldset properties which we will animate
var animating; //flag to prevent quick multi-click glitches

function ajax_call(input_data, callback){
    console.log(input_data)
    $.ajax({
        type: "POST",
        url: "http://localhost/lbag/ajax.php",
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

function form_validator(element_name, endpoint){
    $(element_name).focusout(function(){
        var input = $(this);
        var helper = $(element_name + '-help');
        var input_name = this.name;

        if (typeof(endpoint) === 'undefined'){
            validator_add_success(input, helper);
            return;
        }

        ajax_call({
            func: endpoint,
            data: this.value
        },
        function(data){
            if (data.status === true) {
                validator_add_success(input, helper);
            } else {
                validator_add_error(input, helper, data.msg);
            }
        });
    })
}

////////////////////////////////////////////////////////////////////////////////
// ACCOUNT SETUP FORM
////////////////////////////////////////////////////////////////////////////////

form_validator('#form-setup-acc .input-username', 'validate-user');
form_validator('#form-setup-acc .input-email', 'validate-email');
form_validator('#form-setup-acc .input-password')

$('#form-setup-acc').submit(function(ev){
    ev.preventDefault();

    helper = $(this).find('.fs-error')

    window.form_username = $('#form-setup-acc .input-username')[0].value
    window.form_email = $('#form-setup-acc .input-email')[0].value

    ajax_call(
        {
            func: "register-user",
            username: $('#form-setup-acc .input-username')[0].value,
            email: $('#form-setup-acc .input-email')[0].value,
            password: $('#form-setup-acc .input-password')[0].value
        },
        function(data){

            if (data.status === true){
                event_registration_success()
            } else {
                event_registration_failed(helper, data.msg)
            }
        }
    )
});

function event_registration_failed(helper, msg){

    helper.text(msg)

    if (msg.toLowerCase().indexOf('username') > -1){
        validator_add_error($('#form-setup-acc .input-username'))
    } else if(msg.toLowerCase().indexOf('email') > -1){
        validator_add_error($('#form-setup-acc .input-email'))
    } else if(msg.toLowerCase().indexOf('password') > -1){
        validator_add_error($('#form-setup-acc .input-password'))
    }

}

function event_registration_success(){

    if(animating) return false;
    animating = true;
    
    $('#form-verify-email .fs-subtitle').html(
        $('#form-verify-email .fs-subtitle')[0].innerHTML.replace(
            "you", "<strong>"+ window.form_email +"</strong>"
        )
    )

    current_fs = $('#form-setup-acc');
    next_fs = $('#form-verify-email');

    next_fs.show(); 
    current_fs.animate({opacity: 0}, {
        step: function(now, mx) {
            //as the opacity of current_fs reduces to 0 - stored in "now"
            //1. scale current_fs down to 80%
            scale = 1 - (1 - now) * 0.2;
            //2. bring next_fs from the right(50%)
            left = (now * 50)+"%";
            //3. increase opacity of next_fs to 1 as it moves in
            opacity = 1 - now;
            current_fs.css({'transform': 'scale('+scale+')'});
            next_fs.css({'top': 0, 'left': left, 'opacity': opacity});
        }, 
        duration: 800, 
        complete: function(){
            current_fs.hide();
            animating = false;
        }, 
        //this comes from the custom easing plugin
        easing: 'easeInOutBack'
    });

}


////////////////////////////////////////////////////////////////////////////////
// VERIFICATION EMAIL FORM
////////////////////////////////////////////////////////////////////////////////


$('#form-verify-email').submit(function(event){

    event.preventDefault();

    helper = $(this).find('.fs-error');

    ajax_call(

        {
            func: 'verify-email',
            code: $('#form-verify-email .input-email-code')[0].value
        },
        function(data){
            if (data.status === true){
                event_verification_success()
            } else {
                event_verification_failed(helper)
            }
        }

    );

});

function event_verification_success(){

    if(animating) return false;
    animating = true;

    current_fs = $('#form-verify-email');
    next_fs = $('#form-select-contrib');

    next_fs.show(); 
    current_fs.animate({opacity: 0}, {
        step: function(now, mx) {
            //as the opacity of current_fs reduces to 0 - stored in "now"
            //1. scale current_fs down to 80%
            scale = 1 - (1 - now) * 0.2;
            //2. bring next_fs from the right(50%)
            left = (now * 50)+"%";
            //3. increase opacity of next_fs to 1 as it moves in
            opacity = 1 - now;
            current_fs.css({'transform': 'scale('+scale+')'});
            next_fs.css({'top': 0, 'left': left, 'opacity': opacity});
        }, 
        duration: 800, 
        complete: function(){
            current_fs.hide();
            animating = false;
        }, 
        //this comes from the custom easing plugin
        easing: 'easeInOutBack'
    });

}

function event_verification_failed(helper){
    helper.text('Ops! No match was found with the code you inserted.')
    validator_add_error($('#form-verify-email .input-email-code'));
}

$('#form-verify-email .input-email-code').keyup(function(event){
    this.value = this.value.toUpperCase();
});

////////////////////////////////////////////////////////////////////////////////
// CONTRIBUTION SELECTOR
////////////////////////////////////////////////////////////////////////////////

var selected_teams = 0;
var team_array = {};

$('#form-select-contrib .team').click(function(){
    // Enable/disable team
    element = $('#'+$(this)[0].id)
    input = $('#form-select-contrib .next')
    if (element.hasClass('team-selected')){
        element.removeClass('team-selected')
        selected_teams--;
        team_array[element[0].id] = false;
    } else {
        element.addClass('team-selected')
        selected_teams++;
        team_array[element[0].id] = true;
    }

    // Enable/disable button
    if (selected_teams > 0) {
        if (input.hasClass('disabled')) {
            input.removeClass('disabled')
            $('#form-select-contrib').find('.fs-error').text('')
        }
    } else {
        if (!input.hasClass('disabled')) {
            input.addClass('disabled')
        }
    }
});

$('#form-select-contrib').submit(function(event){

    event.preventDefault();
    helper = $(this).find('.fs-error');

    if (selected_teams <= 0){
        helper.text("Please select at least one team")
        return;
    }

    ajax_call(
        {
            func: 'register-teams',
            data: team_array
        },
        function(data){
            console.log(data)
            if (data.status === true){
                event_verification_success()
            } else {
                event_verification_failed(helper)
            }
        }
    );

});
