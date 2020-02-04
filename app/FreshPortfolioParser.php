<?php

namespace App;

use App\Jobs\ParseImage;
use App\Jobs\ParsePage;
use PHPHtmlParser\Dom;
use PHPHtmlParser\Exceptions\ChildNotFoundException;
use PHPHtmlParser\Exceptions\NotLoadedException;
use Carbon\Carbon;
use App\Attribution;
use DB;

class FreshPortfolioParser
{
    protected $dom;
    protected $cache;
    protected $numberOfPages;
    protected $currentPage;

    public function __construct(int $pageNumber = 1)
    {
        $this->currentPage = $pageNumber;

        $this->cache = new \Memcached;
        $this->cache->addServer('127.0.0.1', 11211);

        if ($this->cache->get('portfolio-' . $pageNumber) !== false) {
            $this->dom = $this->cache->get('portfolio-1');
        } else {
            echo PHP_EOL;
            echo 'not cache';
            echo PHP_EOL;
            $this->dom = new Dom;
            $this->dom->loadFromUrl('https://www.shutterstock.com/g/29Graphic?sort=newest');
            $this->cache->set('portfolio-' . $pageNumber, $this->dom);
        }

        // b_ay_g
        $number = $this->dom->find('div.b_ay_g');
        $number = (int)substr($number->text, 3);
        $this->numberOfPages = $number;
    }

    /**
     * @return array
     * @throws ChildNotFoundException
     * @throws NotLoadedException
     */
    public function getPageImages(): array
    {
        $images = [];

        $imgs = $this->dom->find('a.z_g_e');
        foreach ($imgs as $img) {
            // parse $img to strings
            $imgPageUrl = $img->getAttribute('href');
            $imgTitle = $img->find('img')->getAttribute('alt');
            $imgUrl = $img->find('img')->getAttribute('src');
            $imgId = explode('-', $imgPageUrl);
            $imgId = (int)$imgId[count($imgId) - 1];
            $imgType = explode('/', $imgPageUrl);
            $imgType = $imgType[1];

            $images[] = [
                'img_page_url' => $imgPageUrl,
                'img_title' => $imgTitle,
                'img_url' => $imgUrl,
                'img_id' => $imgId,
                'img_type' => $imgType,
            ];
        }

        return $images;
    }

    public function getNumberOfPages(): int
    {
        return $this->numberOfPages;
    }

    public static function saveImages(array $imagesData): void
    {
        foreach ($imagesData as $imageData) {
            DB::transaction(static function () use ($imageData) {
                $image = new \App\Image;
                $image->user_id = 1;
                $image->shutter_id = $imageData['img_id'];
                $image->page_url = $imageData['img_page_url'];
                $image->image_url = $imageData['img_url'] ?: '';
                $image->added_on = Carbon::now();
                $image->save();

                $attribution = new Attribution;
                $attribution->caption = $imageData['img_title'];
                $image->attributions()->save($attribution);

                //add queue job to parse image
                ParseImage::dispatch($image)->delay(now()->addSeconds(30));
            });
        }
    }

    public static function checkForNewPages(int $numberOfPages, int $pageNumber, int $parsedImagesCount): void
    {
        if ($pageNumber !== 1) {
            return;
        }

        if ($numberOfPages === 1) {
            return;
        }

        $numberOfParsedPages = ceil($parsedImagesCount/100);

        $pagesToParse = 0;
        if ($numberOfPages > $numberOfParsedPages) {
            $pagesToParse = $numberOfPages-$numberOfParsedPages;
        }

        for ($i = 2; $i <= ($pagesToParse+1); $i++) {
            ParsePage::dispatch($i)->delay(now()->addSeconds(30));
        }
    }


}
