bower_filter = (type, component, relative_dir_path) ->
  switch component
    when "jquery" then ""
    when "bootstrap-sass"
      switch type
        when "sass"
          "bootstrap/" + relative_dir_path["assets/stylesheets/".length..]
        when "js"
          "bootstrap/" + relative_dir_path["assets/javascripts/".length..]
    when "font-awesome-sass"
      switch type
        when "sass"
          "font-awesome/" + relative_dir_path["assets/stylesheets/".length..]
        when "font"
          "font-awesome/"
    when "semantic-ui-sass"
      switch type
        when "sass"
          "semantic-ui/" + relative_dir_path["app/assets/stylesheets/".length..]
        when "js"
          "semantic-ui/" + relative_dir_path["app/assets/javascripts/".length..]
    else
      relative_dir_path

uglify_vendor_files = () ->
  folder = "tmp/bower/js/"
  dist =
  {
    "dist/assets/js/vendor/jquery.min.js": [folder + "jquery.min.js"],
    "dist/assets/js/vendor/bootstrap.min.js": [],
    "dist/assets/js/vendor/semantic.min.js": []}

bower_path_builder = (type, component, full_path) ->
  # The position of the start of the relative folders
  a = (full_path.indexOf component) + component.length + 1

  relative_path =
    if full_path.match ///.*\.[a-zA-Z0-9]{1,8}/// # is a file
      # Gets the position where the directory path ends
      b = full_path.split("/")
      b.pop()
      b = b.join("/").length

      full_path[a..b]
    else # is a directory
      full_path[a..]

  type + "/" + bower_filter type, component, relative_path

module.exports = (grunt) ->
  grunt.initConfig
    bower:
      install:
        options:
          copy: false
      realloc:
        options:
          install: false
          targetDir: "tmp/bower"
          layout: bower_path_builder
    clean:
      build:
        src: "dist"
      bower:
        src: "tmp/bower"
    compress:
      prod:
        options:
          archive: "dist/release.tar"
          mode: "tar"
        files: [
          expand: true
          cwd: "dist"
          src: ["**/**"]]
    copy:
      font:
        files: [
          {
            expand: true
            cwd: "src/font"
            src: "**/*.{eot,woff,woff2,ttf,svg}"
            dest: "dist/assets/font"},
          {
            expand: true
            cwd: "tmp/bower/font"
            src: "**/*.{eot,woff,woff2,ttf,svg}"
            dest: "dist/assets/font"}]
    express:
      live:
        options:
          bases: ["dist"]
          livereload: true
    imagemin:
      dev:
        options:
          optimizationLevel: 0
        files: x = [
          expand: true
          cwd: "src/img"
          src: ["**/*.{png,jpg,jpeg,gif,svg}"]
          dest: "dist/assets/img"]
      prod:
        options:
          optimizationLevel: 7
          progressive: false
          interlaced: false
        files: x
    jade:
      dev:
        options:
          pretty: true
          data: {}
        files: x  = [
          expand: true
          cwd: "src/jade"
          src: ["**/*.jade", "!includes/**"]
          dest: "dist"
          ext: ".html"]
      prod:
        options:
          options:
            pretty: false
            compileDebug: false
            data: {}
        files: x
    parallel:
      dev:
        options: x =
          grunt: true
        tasks: ["copy:font", "imagemin:dev", "jade:dev", "sass:dev", "uglify:dev", "uglify:vendor"]
      prod:
        options: x
        tasks: ["copy:font", "imagemin:prod", "jade:prod", "sass:prod", "uglify:prod", "uglify:vendor"]
    sass:
      dev:
        options:
          style: "nested"
          sourcemap: "file"
          trace: true
          unixNewlines: true
          compass: true
          loadPath: x = ["tmp/bower/sass"]
        files: y = [
          expand: true
          cwd: "src/sass"
          src: ["**/*.scss"]
          dest: "dist/assets/css"
          ext: ".css"]
      prod:
        options:
          style: "compressed"
          sourcemap: "none"
          unixNewlines: true
          compass: true
          loadPath: x
        files: y
    uglify:
      dev:
        options:
          preserveComments: "all"
          beautify: true
          sourceMap: true
          sourceMapIncludeSources: true
        files: x = [
          expand: true
          cwd: "src/js"
          src: ["**/*.js"]
          dest: "dist/assets/js"]
      prod:
        options:
          preserveComments: "some"
          beautify: false
          compress:
            drop_console: true
        files: x
      vendor:
        options:
          preserveComments: "some"
          beautify: false
        files: uglify_vendor_files()
    watch:
      bower:
        options: x =
          spawn: false
        files: ["bower.json"]
        tasks: ["bower", "sass:dev", "uglify:vendor", "copy:font"]
      font:
        options: x
        files: ["tmp/bower/font/**/*.{eot,woff,woff2,ttf,svg}", "src/font/**/*.{eot,woff,woff2,ttf,svg}"]
        tasks: ["copy:font"]
      img:
        options: x
        files: ["src/img/**/*.{png,jpg,jpeg,gif,svg}"]
        tasks: ["imagemin:dev"]
      jade:
        options: x
        files: ["src/jade/**/*.{jade,json}"]
        tasks: ["jade:dev"]
      js:
        options: x
        files: ["src/js/**/*.js"]
        tasks: ["uglify:dev"]
      vendorJs:
        options: x
        files: ["tmp/bower/js/**/*.js"]
        tasks: ["uglify:vendor"]
      sass:
        options: x
        files: ["src/sass/**/*.scss"]
        tasks: ["sass:dev"]
      watch:
        options:
          spawn: false
          reload: true
        files: ["Gruntfile.coffee"]

  grunt.loadNpmTasks 'grunt-bower-task'
  grunt.loadNpmTasks 'grunt-contrib-clean'
  grunt.loadNpmTasks 'grunt-contrib-compress'
  grunt.loadNpmTasks 'grunt-contrib-copy'
  grunt.loadNpmTasks 'grunt-contrib-imagemin'
  grunt.loadNpmTasks 'grunt-contrib-jade'
  grunt.loadNpmTasks 'grunt-contrib-sass'
  grunt.loadNpmTasks 'grunt-contrib-uglify'
  grunt.loadNpmTasks 'grunt-contrib-watch'
  grunt.loadNpmTasks 'grunt-express'
  grunt.loadNpmTasks 'grunt-parallel'

  grunt.registerTask "live", ["bower", "parallel:dev", "express", "watch"]

  grunt.registerTask "release", ["clean", "bower", "parallel:prod", "compress"]