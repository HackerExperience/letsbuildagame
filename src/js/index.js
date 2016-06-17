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

(function() {

  var sess_id = localStorage.getItem("session_id");

  var is_logged = function(){
    if (sess_id !== "" && sess_id !== null) {
      check_session_state();
      return true;
    }
    return false;
  }

  var check_session_state = function() {
    ajax_call(
      {
        func: 'check-session',
        sess: sess_id 
      },
      function(data) {
        if(data.status === false) {
          logout();
        }
      }
    );
  }

  var logout = function() {

    // Remove stored session
    sess_id = "";
    localStorage.removeItem("session_id");
    localStorage.removeItem("teams");
    localStorage.removeItem("tasks");

    // Redirect to index
    if (window.location.pathname != "/") {
      window.location = "/";
    } else {
      $(".nav-user").hide();
      $(".nav-contrib").show();
    }
  }

  $("body").scrollspy({
    target: "#headerNavbar"
  });

  if (is_logged()) {
    $(".nav-user").show();
    $(".nav-contrib").hide();
  }

  $('.nav-logout').click(function(ev){
    ev.preventDefault();
    logout();
  });

  var do_process_email = function(data, form) {
      if (data.status === true) {
        var msg = document.createElement("span");
        msg.appendChild(
          document.createTextNode("Thank you! Please verify your email inbox."))
        $(form).parent().append(msg);
        $(form).remove();
      } else {
      }
    }

    var process_email = function(form) {
      return function(data) {
        do_process_email(data, form)
      }
    }

    $('.form-waiting-list').submit(function(e) {
      e.preventDefault();

      $.ajax({
        type: "POST",
        url: "https://subscribe.hackerexperience.com",
        data: {
          func: "subscribe",
          email: $(this).find("input").val(),
          list_id: list_id},
        success: process_email(this)});
    });
})();
