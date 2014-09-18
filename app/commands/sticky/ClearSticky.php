<?php

use Giraffe\Stickies\StickyService;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;

class ClearSticky extends Command
{
    protected $name = 'lg:sticky:clear';
    protected $description = "Remove existing sticky from site.";

    public function __construct()
    {
        parent::__construct();
    }

    public function fire()
    {
        /** @var StickyService $stickyService */
        $stickyService = \App::make(StickyService::class);

        $stickyService->clear();

        $this->info('Site stickies cleared.');
    }


    protected function getArguments()
    {
        return [];
    }

    protected function getOptions()
    {
        return [];
    }

}
