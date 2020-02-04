<?php

namespace App\Jobs;

use App\FreshPortfolioParser;
use App\PortfolioRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ParsePage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** @var int $pageNumber */
    protected $pageNumber;

    /**
     * Create a new job instance.
     * @param int $pageNumber
     */
    public function __construct(int $pageNumber)
    {
        $this->pageNumber = $pageNumber;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        $portfolio = new FreshPortfolioParser($this->pageNumber);

        $images = $portfolio->getPageImages();
        $images = \App\PortfolioRepository::filterOldImages($images);
        FreshPortfolioParser::saveImages($images);

        // check if add other pages to queue
        $portfolio->getNumberOfPages();
        $pages = $portfolio->getNumberOfPages();
        $imagesCount = PortfolioRepository::getCountOfImages();
        FreshPortfolioParser::checkForNewPages($pages, $this->pageNumber, $imagesCount);
    }
}
