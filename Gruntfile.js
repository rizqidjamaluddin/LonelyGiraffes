module.exports = function(grunt) {

	grunt.initConfig({
		shell: {

			/**
			 * All development tasks
			 * @type {Object}
			 */
			development: {
				command: [
					"sudo composer self-update",
					"sudo composer update",
					"sudo composer install",
					"php artisan migrate"
				].join("&&"),
				options: {
					stdout: true
				}
			},

			/**
			 * Composer
			 * @type {Object}
			 */
			composer: {
				command: [
					"sudo composer self-update",
					"sudo composer update -vvv",
					"sudo composer install",
				].join("&&"),
				options: {
					stdout: true
				}
			},

			/**
			 * Migrate
			 * @type {Object}
			 */
			migrate: {
				command: [
					"php artisan migrate"
				].join("&&"),
				options: {
					stdout: true
				}
			},

			phpunit: {
				command: "phpunit",
				options: {
					stdout: true
				}
			},

			/**
			 * Intern
			 * @type {Object}
			 */
			intern: {
				command: "node_modules/.bin/intern-client config=public/tests/intern",
				options: {
					stdout: true
				}
			},

			/**
			 * Seed
			 * @type {Object}
			 */
			seed: {
				command: [
					"php artisan db:seed",
					"lgdb:geonames"
				].join("&&"),
				options: {
					stdout: true
				}
			},

			/**
			 * SASS
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
	grunt.registerTask('composer', ["shell:composer"]);
	grunt.registerTask('migrate', ["shell:migrate"]);
	grunt.registerTask('phpunit', ["shell:phpunit"]);
	grunt.registerTask('intern', ["shell:intern"]);
	grunt.registerTask('seed', ["shell:seed"]);
	grunt.registerTask('sass', ["shell:sass"]);
};