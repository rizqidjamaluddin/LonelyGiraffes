<?php

use Giraffe\Images\ImageTypeModel;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class SeedLookup extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'lgsetup:seed_lookup';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Seeds basic data to lookup tables.';

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function fire()
	{
        // Create the ImageType(s)
		$profile_pic = ImageTypeModel::firstOrNew(['name' =>'profile_pic']);
        if(!$profile_pic->exists) {
            $profile_pic->unique_per_user = true;
            $profile_pic->save();
        }
	}

	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments()
	{
		return array();
	}

	/**
	 * Get the console command options.
	 *
	 * @return array
	 */
	protected function getOptions()
	{
		return array();
	}

}
