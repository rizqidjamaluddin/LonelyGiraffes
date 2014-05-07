<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Where the templates for the generators are stored...
    |--------------------------------------------------------------------------
    |
    */
    'model_template_path' => '/vagrant/app/templates/model.txt',

    'scaffold_model_template_path' => '/vagrant/app/templates/scaffolding/model.txt',

    'controller_template_path' => '/vagrant/app/templates/controller.txt',

    'scaffold_controller_template_path' => '/vagrant/app/templates/scaffolding/controller.txt',

    'migration_template_path' => '/vagrant/app/templates/migration.txt',

    'seed_template_path' => '/vagrant/app/templates/seed.txt',

    'view_template_path' => '/vagrant/app/templates/view.txt',


    /*
    |--------------------------------------------------------------------------
    | Where the generated files will be saved...
    |--------------------------------------------------------------------------
    |
    */
    'model_target_path'   => app_path('Giraffe/Models'),

    'controller_target_path'   => app_path('controllers'),

    'migration_target_path'   => app_path('database/migrations'),

    'seed_target_path'   => app_path('database/seeds'),

    'view_target_path'   => app_path('views')

];