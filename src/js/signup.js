function ajax_call(input_data, callback){
    console.log(input_data);
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
        if (typeof(endpoint) === "function") {
            return endpoint(input, helper);
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


var animating = false;
function animate_next(current_fs, next_fs){

    if(animating) return false;
    animating = true;

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

////////////////////////////////////////////////////////////////////////////////
// ACCOUNT SETUP FORM
////////////////////////////////////////////////////////////////////////////////

form_validator('#form-setup-acc .input-username', 'validate-user');
form_validator('#form-setup-acc .input-email', 'validate-email');
form_validator('#form-setup-acc .input-password', assert_password_policy);

$('#form-setup-acc').submit(function(ev){

    ev.preventDefault();

    helper_element = $(this).find('.fs-error');
    submit_element = $(this).find('input[type="submit"]');

    toggleSubmitLoading(submit_element);

    window.form_username = $('#form-setup-acc .input-username')[0].value;
    window.form_email = $('#form-setup-acc .input-email')[0].value;

    ajax_call(
        {
            func: "register-user",
            username: $('#form-setup-acc .input-username')[0].value,
            email: $('#form-setup-acc .input-email')[0].value,
            password: $('#form-setup-acc .input-password')[0].value
        },
        function(data){

            if (data.status === true){
                event_registration_success();
            } else {
                toggleSubmitLoading(submit_element);
                event_registration_failed(helper_element, data.msg);
            }
        }
    )
});

function event_registration_failed(helper, msg){

    helper.text(msg);

    if (msg.toLowerCase().indexOf('username') > -1){
        validator_add_error($('#form-setup-acc .input-username'));
    } else if(msg.toLowerCase().indexOf('email') > -1){
        validator_add_error($('#form-setup-acc .input-email'));
    } else if(msg.toLowerCase().indexOf('password') > -1){
        validator_add_error($('#form-setup-acc .input-password'));
    }

}

function event_registration_success(){
    $('#form-verify-email .fs-subtitle').html(
        $('#form-verify-email .fs-subtitle')[0].innerHTML.replace(
            "you", "<strong>"+ window.form_email +"</strong>"
        )
    );

    animate_next($('#form-setup-acc'), $('#form-verify-email'));
}

function assert_password_policy(input, helper){

    if (input[0].value.length < 6) {
        validator_add_error(input, helper, "Please insert a password with 6 or more characters");
    } else {
        validator_add_success(input, helper);
    }

}

////////////////////////////////////////////////////////////////////////////////
// VERIFICATION EMAIL FORM
////////////////////////////////////////////////////////////////////////////////


$('#form-verify-email').submit(function(event){

    event.preventDefault();

    helper_element = $(this).find('.fs-error');
    submit_element = $(this).find('input[type="submit"]');

    toggleSubmitLoading(submit_element);

    ajax_call(
        {
            func: 'verify-email',
            code: $('#form-verify-email .input-email-code')[0].value
        },
        function(data){
            if (data.status === true){
                event_verification_success();
            } else {
                toggleSubmitLoading(submit_element);
                event_verification_failed(helper_element);
            }
        }

    );

});

function event_verification_success(){
    animate_next($('#form-verify-email'), $('#form-select-contrib'));
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
    element = $('#'+$(this)[0].id);
    input = $('#form-select-contrib .next');
    if (element.hasClass('team-selected')){
        element.removeClass('team-selected');
        selected_teams--;
        team_array[element[0].id] = false;
    } else {
        element.addClass('team-selected');
        selected_teams++;
        team_array[element[0].id] = true;
    }

    // Enable/disable button
    if (selected_teams > 0) {
        if (input.hasClass('disabled')) {
            input.removeClass('disabled');
            $('#form-select-contrib').find('.fs-error').text('');
        }
    } else {
        if (!input.hasClass('disabled')) {
            input.addClass('disabled');
        }
    }
});

$('#form-select-contrib').submit(function(event){

    event.preventDefault();

    helper_element = $(this).find('.fs-error');
    submit_element = $(this).find('input[type="submit"]');

    if (selected_teams <= 0){
        helper_element.text("Please select at least one team");
        return;
    }

    toggleSubmitLoading(submit_element);

    ajax_call(
        {
            func: 'register-teams',
            data: team_array
        },
        function(data){
            if (data.status === true){
                event_teams_success();
            } else {
                toggleSubmitLoading(submit_element);
                event_teams_failed(helper_element, data.msg);
            }
        }
    );

});

function event_teams_success(){

    function switchIcon(element){
        element.toggleClass('fa-chevron-down');
        element.toggleClass('fa-chevron-right');
    }

    function addToggle(team_id){        
        $('#cat-' + team_id).click(function(event){
            event.preventDefault();
            $('#tasks-' + team_id).slideToggle();
            switchIcon($('#cat-' + team_id + ' i'));
        });
    }

    animate_next($('#form-select-contrib'), $('#form-select-tasks'));

    all_teams = ['dev', 'art', 'gd', 'mgt', 'student', 'translation', 'gamer', 'patron', 'other'];

    for (var i = 0; i < all_teams.length; i++) {
        team_id = all_teams[i];

        addToggle(team_id);

        // Hide teams which the user is not a member
        if (!( ('team-' + team_id) in team_array)) {
            $('#tasks-' + team_id).hide();            
        } else if (!(team_array['team-' + team_id])){
            $('#tasks-' + team_id).hide();
        } else {
            switchIcon($('#cat-' + team_id + ' i'));
        }

        // Go to the top
        window.scrollTo(0, 0);
    }

}

function event_teams_failed(helper, msg){
    helper.text(msg);
}

////////////////////////////////////////////////////////////////////////////////
// TASKS SELECTOR
////////////////////////////////////////////////////////////////////////////////


$('#form-select-tasks .task').click(function(){
    // Enable/disable team
    element = $('#'+$(this)[0].id);

    task_id = element[0].id.slice(5)
    team_id = (element.closest('div[id^="tasks-"]')[0].id).slice(6);

    if (element.hasClass('task-selected')){
        element.removeClass('task-selected');
        task_unsubscribe(element, team_id, task_id);
    } else {
        element.addClass('task-selected');
        task_subscribe(element, team_id, task_id);
    }
});

function task_generic_subscribe(element, action, team_id, task_id){
    ajax_call(
        {
            func: action,
            task_id: task_id,
            team_id: team_id
        },
        function(data){

            if (data.status === false){

                if (data.msg) {

                    var helper = element.parent().find('.error-helper')
                    if (!(helper.length)) return;

                    helper.html(data.msg);

                    if(element.hasClass('task-selected')){
                        element.removeClass('task-selected');
                    }

                }
            }
        }
    );
}

function task_subscribe(element, team_id, task_id){
    task_generic_subscribe(element, 'subscribe-task', team_id, task_id);
}

function task_unsubscribe(element, team_id, task_id){
    task_generic_subscribe(element, 'unsubscribe-task', team_id, task_id);
}

$('#form-select-tasks').submit(function(event){

    event.preventDefault();

    animate_next($('#form-select-tasks'), $('#form-select-preferences'));

    // Go to the top
    window.scrollTo(0, 0);

});

////////////////////////////////////////////////////////////////////////////////
// PREFERENCES
////////////////////////////////////////////////////////////////////////////////

$('.notification-input').click(function(){
    if (this.checked) {
        notification_subscribe(this.value);
    } else {
        notification_unsubscribe(this.value);
    }
});

function notification_generic_subscribe(action, desc){
    ajax_call(
        {
            func: action,
            notification_desc: desc
        },
        function(data){}
    );
} 


function notification_subscribe(notification_desc){
    notification_generic_subscribe('subscribe-notification', notification_desc);
}

function notification_unsubscribe(notification_desc){
    notification_generic_subscribe('unsubscribe-notification', notification_desc);
}

$('#form-select-preferences').submit(function(event){

    event.preventDefault();

    // Assert all checkbox were sent to the server
    $('.notification-input').each(function(i){
        if(this.checked){
            notification_subscribe(this.value);
        }
    });

    ajax_call(
        {
            func: 'update-settings',
            setting_name: 'notifications_frequency',
            setting_value: $('.email-frequency :selected').val()
        },
        function(data){}
    );
    
});
