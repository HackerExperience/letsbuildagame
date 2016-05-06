(function() {
  var popover_layout = function(title, content) {
    return {
      title: title,
      content: content,
      container: ".timeline-graph",
      trigger: "click hover"}}

  $("#timeline-iv").popover(
    popover_layout("", ""));

  $("#timeline-cor-vi").popover(
    popover_layout("", ""));

  $("#timeline-web-vi-a").popover(
    popover_layout("", ""));

  $("#timeline-web-vi-b").popover(
    popover_layout("", ""));

  $("#timeline-mob-vi").popover(
    popover_layout("", ""));
})();
