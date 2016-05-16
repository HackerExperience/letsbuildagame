(function() {
  var sess_id = "";

  var selected_teams = 0;
  var team_array = {};
  var ajax_call = function(input_data, callback) {
    $.ajax({
      type: "POST",
      url: "https://ajax.letsbuildagame.org",
      data: input_data,
      success: callback
    });
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
    console.log(submit_element);

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
        if (data.status === true){
          // Success side-effects
        } else {
          toggleSubmitLoading(submit_element);
          helper_element.text(data.msg);
        }
      }
    );
  });
})();
