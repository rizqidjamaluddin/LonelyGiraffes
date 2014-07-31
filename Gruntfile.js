/* global module */
module.exports = function(grunt) {

	grunt.initConfig({
		shell: {

			/**
			 * All development tasks
			 * @type {Object}
			 */
			development: {
				command: [
					'sudo composer self-update',
					'sudo composer update',
					'sudo composer install',
					'php artisan migrate'
				].join('&&'),
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
					'sudo composer self-update',
					'sudo composer update -vvv',
					'sudo composer install'
				].join('&&'),
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
					'php artisan migrate'
				].join('&&'),
				options: {
					stdout: true
				}
			},

			phpunit: {
				command: 'phpunit',
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
					'php artisan db:seed',
					'lgdb:geonames'
				].join('&&'),
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
	grunt.registerTask('development', ['shell:development']);
	grunt.registerTask('composer', ['shell:composer']);
	grunt.registerTask('migrate', ['shell:migrate']);
	grunt.registerTask('phpunit', ['shell:phpunit']);
	grunt.registerTask('seed', ['shell:seed']);
	grunt.registerTask('sass', ['shell:sass']);
};