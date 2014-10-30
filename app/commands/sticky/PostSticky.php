<?php

use Giraffe\Stickies\StickyService;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;

class PostSticky extends Command
{
    protected $name = 'lg:sticky:post';
    protected $description = "Set a sticky to the site.";

    public function __construct()
    {
        parent::__construct();
    }

    public function fire()
    {
        /** @var StickyService $stickyService */
        $stickyService = \App::make(StickyService::class);

        $body = $this->argument('body');
        $class = $this->argument('class');
        $stickyService->post($body, $class);

        $this->info('Sticky set with body: ' . $body);
    }


    protected function getArguments()
    {
        return [
            ['body', InputArgument::REQUIRED, "Text for the sticky to display."],
            ['class', InputArgument::OPTIONAL, "CSS class to apply to the sticky.", 'general'],
        ];
    }

    protected function getOptions()
    {
        return [];
    }

}
