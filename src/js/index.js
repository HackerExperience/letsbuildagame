(function() {
  $("body").scrollspy({
    target: "#headerNavbar"
  });

  var do_process_email = function(data, form) {
      if (data.status === true) {
        var msg = document.createElement("span");
        msg.appendChild(
          document.createTextNode("E-mail successfully entered the waiting list"))
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
          list_id: 2},
        success: process_email(this)});
    });
})();
