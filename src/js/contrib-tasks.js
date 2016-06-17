(function() {

  var active_tasks = [];

  array_length = lbag_data['tasks'].length;
  for (var i = 0; i < array_length; i++) {
    active_tasks.push(lbag_data['tasks'][i]);
  }

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
    refresh_data();
  }

  var task_subscribe = function(element, team_id, task_id) {
    task_generic_subscribe(element, 'subscribe-task', team_id, task_id);
    active_tasks.push(task_id);
  }

  var task_unsubscribe = function(element, team_id, task_id) {
    task_generic_subscribe(element, 'unsubscribe-task', team_id, task_id);
    active_tasks.splice(active_tasks.indexOf(task_id), 1);
  }

  // Toggles all active tasks
  active_tasks.reduce(
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
