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

var cur_page = $('body').attr('page');

var sess_id = localStorage.getItem("session_id");
var lbag_data = {};

var is_logged = function(){
  return sess_id !== "" && sess_id !== null;
}

if (!is_logged()) {
  window.location = '/';
}

var load_data = function() {
  lbag_data['meta'] = localStorage.getItem('meta');
  lbag_data['tasks'] = localStorage.getItem('tasks');
  lbag_data['teams'] = localStorage.getItem('teams');
  lbag_data['settings'] = localStorage.getItem('settings');
  lbag_data['notifications'] = localStorage.getItem('notifications');
  if (lbag_data['tasks']) {
    lbag_data['tasks'] = JSON.parse(lbag_data['tasks']);
  }
  if (lbag_data['teams']) {
    lbag_data['teams'] = JSON.parse(lbag_data['teams']);
  }
  if (lbag_data['settings']) {
    lbag_data['settings'] = JSON.parse(lbag_data['settings']); 
  }
  if (lbag_data['notifications']) {
    lbag_data['notifications'] = JSON.parse(lbag_data['notifications']); 
  }
  if (lbag_data['meta']) {
    lbag_data['meta'] = JSON.parse(lbag_data['meta']); 
  }
}

var refresh_data = function(callback) {
  ajax_call(
    {
      func: 'fetch-user-data',
      sess: sess_id
    },
    function(data) {

      if (data.status) {

        localStorage.setItem('meta', JSON.stringify(data.msg['meta']));
        localStorage.setItem('tasks', JSON.stringify(data.msg['tasks']));
        localStorage.setItem('teams', JSON.stringify(data.msg['teams']));
        localStorage.setItem('settings', JSON.stringify(data.msg['settings']));
        localStorage.setItem('notifications', JSON.stringify(data.msg['notifications']));
        
        load_data();
        
        if (typeof(callback) !== "undefined") {
          callback();
        }

        updateNavUsername(lbag_data['meta']['username']);

      }
    }
  )
}

var update_local_data = function() {
  localStorage.setItem('meta', JSON.stringify(lbag_data['meta']));
  localStorage.setItem('tasks', JSON.stringify(lbag_data['tasks']));
  localStorage.setItem('teams', JSON.stringify(lbag_data['teams']));
  localStorage.setItem('settings', JSON.stringify(lbag_data['settings']));
  localStorage.setItem('notifications', JSON.stringify(lbag_data['notifications']));
}

var updateNavUsername = function(username) {
  if(typeof(username) === "undefined") return;
  $('.nav-welcome a').text('Welcome, ' + username);
}

load_data();

(function() {

  if (cur_page != 'panel') return;

  var renderDashboardTasks = function() {
    
    if (!lbag_data['tasks']) return;

    tasks_length = lbag_data['tasks'].length;

    parent_element = $('#actions');

    for(var i = 0; i < tasks_length; i++) {
      // Skip tags (non-actionable tasks)
      if(lbag_data['tasks'][i].indexOf('tag-') !== -1) {
        continue;
      }

      if (!(lbag_data['tasks'][i] in panel_actions)) return;

      
      action_name = panel_actions[lbag_data['tasks'][i]]['name'];
      action_desc = panel_actions[lbag_data['tasks'][i]]['desc'];

      parent_element.append('<p><strong>'+ action_name +'</strong> - '+ action_desc +'</p>');

    }

  }

  refresh_data(renderDashboardTasks);

})();

(function() {

  if (cur_page != 'panel_tasks') return;

  var toggleSubmitLoading = function(elem) {
    spinner = elem.parent().find("i.input-loading");

    if(spinner.is(":visible")) {
      spinner.hide();
      elem.show();
    } else {
      elem.hide();
      spinner.show();
    }
  }

  var task_generic_subscribe = function(element, action, team_id, task_id) {
    ajax_call(
      {
        func: action,
        task_id: task_id,
        team_id: team_id,
        sess: sess_id
      },
      function(data) {
        if (!data.status) {
          var helper = element.parent().find('.error-helper')

          if (!helper.length) {
            return false;
          }

          helper.html(data.msg);

          if (element.hasClass('task-selected')) {
            element.removeClass('task-selected');
          }
        }
      }
    );
  }

  var task_subscribe = function(element, team_id, task_id) {
    task_generic_subscribe(element, 'subscribe-task', team_id, task_id);
    lbag_data['tasks'].push(task_id)
    update_local_data();
  }

  var task_unsubscribe = function(element, team_id, task_id) {
    task_generic_subscribe(element, 'unsubscribe-task', team_id, task_id);
    lbag_data['tasks'].splice(lbag_data['tasks'].indexOf(task_id), 1);
    update_local_data();
  }

  // Toggles all active tasks
  lbag_data['tasks'].reduce(
    function(prev, cur) {
      return $("#task-" + cur).addClass("task-selected");
    }, "");

  $("#form-select-tasks .task").click(function() {
    // Enable/disable team
    element = $(this);

    task_id = element.prop("id").slice(5);
    team_id = (element.closest("div[id^=tasks-]").prop("id")).slice(6);

    if (element.hasClass("task-selected")){
      task_unsubscribe(element, team_id, task_id);
    } else {
      task_subscribe(element, team_id, task_id);
    }

    element.toggleClass("task-selected");
  });
})();

(function() {

  if (cur_page != 'panel_teams') return;

  var selected_teams = 0;
  var team_array = {};

  console.log(lbag_data['teams'])
  console.log(team_array)

  $(".team").each(function() {
    is_member = lbag_data['teams'].indexOf(this.id.slice(5)) !== -1;
    if (is_member) {
      $(this).addClass('team-selected');
      selected_teams++;
      team_array[this.id] = true;
    }
  });

  var toggleSubmitLoading = function(elem) {
    spinner = elem.parent().find("i.input-loading");

    if(spinner.is(":visible")) {
      spinner.hide();
      elem.show();
    } else {
      elem.hide();
      spinner.show();
    }
  }

  $("#form-select-contrib .team").click(function(el) {
    // Enable/disable team
    element = $(this);
    input = $("#form-select-contrib .next");

    if (element.hasClass("team-selected")) {
      element.removeClass("team-selected");
      selected_teams--;
      team_array[element.prop("id")] = false;
    } else {
      element.addClass("team-selected");
      selected_teams++;
      team_array[element.prop("id")] = true;
    }

    // Enable/disable button
    if (selected_teams > 0) {
      if (input.hasClass("disabled")) {
        input.removeClass("disabled");
        $("#form-select-contrib").find(".fs-error").text("");
      }
    } else {
      if (!input.hasClass("disabled")) {
        input.addClass("disabled");
      }
    }
  });

  $("#form-select-contrib").submit(function(event) {
    event.preventDefault();

    helper_element = $(this).find(".fs-error");
    submit_element = $(this).find('input[type="submit"]');

    if (selected_teams < 1) {
      helper_element.text("Please select at least one team");
      return false;
    }

    toggleSubmitLoading(submit_element);

    ajax_call(
      {
        func: "register-teams",
        data: team_array,
        sess: sess_id
      },
      function(data){
        if (data.status){
          toggleSubmitLoading(submit_element);
          helper_element.text('Saved!');
          refresh_data();
        } else {          
          helper_element.text(data.msg);
        }
      }
    );
  });
})();

(function() {

  if (cur_page != 'panel_preferences') return;

  // Update notification frequency dropdown with user data
  // -----------------------------------------------------
  $('.email-frequency option').each(function() {
    if(this.value == lbag_data['settings']['notifications_frequency']) {
      if (!this.selected) {
        this.selected = true;
      }
    } else {
      if(this.selected) {
        this.selected = false;
      }
    }
  });

  // Update notification subscriptions with user data
  // ------------------------------------------------
  $('.check-sub').each(function() {
    is_subscribed = lbag_data['notifications'].indexOf(this.value) !== -1
    if (is_subscribed) {
      this.checked = true;
    } else {
      this.checked = false;
    }
  });

  $('.notification-input').click(function(){
    helper = $('#form-select-preferences').find(".fs-error");
    if (this.checked) {
      notification_subscribe(this.value, helper);
    } else {
      notification_unsubscribe(this.value, helper);
    }
  });

  function notification_generic_subscribe(action, desc, helper){
      ajax_call(
          {
              sess: sess_id,
              func: action,
              notification_desc: desc
          },
          function(data){
            if(!data.status) {
              if (typeof(helper) !== "undefined"){
                helper.text(data.msg);
              }
            }
          }
      );
  } 


  function notification_subscribe(notification_desc, helper){
      notification_generic_subscribe('subscribe-notification', notification_desc, helper);
      lbag_data['notifications'].push(notification_desc);
      update_local_data();
  }

  function notification_unsubscribe(notification_desc){
      notification_generic_subscribe('unsubscribe-notification', notification_desc, helper);
      lbag_data['notifications'].splice(lbag_data['notifications'].indexOf(notification_desc), 1);
      update_local_data();
  }

  $('#form-select-preferences').submit(function(event){

      event.preventDefault();

      // Assert all checkbox were sent to the server
      $('.notification-input').each(function(i){
          if(this.checked){
              notification_subscribe(this.value);
          }
      });

      notification_frequency = $('.email-frequency :selected').val();

      ajax_call(
          {
              sess: sess_id,
              func: 'update-settings',
              setting_name: 'notifications_frequency',
              setting_value: notification_frequency
          },
          function(data){
            if(data.status) {
              lbag_data['settings']['notifications_frequency'] = notification_frequency;
              update_local_data();
            }
          }
      );
      
  });

})();
