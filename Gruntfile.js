module.exports = function(grunt) {

	grunt.initConfig({
		shell: {
			/**
			 * All commands to get development environment ready to go
			 * @type {Object}
			 */
			development: {
				command: [
					"sudo composer self-update",
					"sudo composer update",
					"sudo composer install",
					"php artisan migrate",
					"php artisan db:seed",
					"lgdb:geonames"
				].join("&&"),
				options: {
					stdout: true
				}
			},

			/**
			 * Command for sass
			 * @type {Object}
			 */
			sass: {
				command: "sass --watch public/stylesheets/sass/core.scss:public/stylesheets/css/core.css",
				options: {
					stdout: true
				}
			}
		}
	});

	/**
	 * Load tasks
	 */
	grunt.loadNpmTasks('grunt-shell');

	/**
	 * Development task
	 */
	grunt.registerTask('development', ["shell:development"]);
	grunt.registerTask('sass', ["shell:sass"]);
};