<?php

namespace App\Console\Commands;

use App\FreshPortfolioParser;
use App\Image;
use Illuminate\Console\Command;

class ParsePage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'parse:page';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Parse and save first page';

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
    public function handle()
    {
        \App\Jobs\ParsePage::dispatch(1);
        /*$pageNumber = 1;
        $portfolio = new FreshPortfolioParser($pageNumber);*/
    }
}
