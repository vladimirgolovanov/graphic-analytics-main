<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\FreshPortfolioParser;

class FreshPortfolioParserTest extends TestCase
{
    // test store in cache

    // test get all img data from portfolio

    // test there is 100 pictures in first page

    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function testContentIsSortedByFresh(): void
    {
        $portfolio = new FreshPortfolioParser;
        $images = $portfolio->getPageImages(1);

        $firstImageId = $images[0]['img_id'];
        $lastImageId = $images[count($images) - 1]['img_id'];

        echo PHP_EOL;
        echo 'firstImageId: ' . $firstImageId . PHP_EOL;
        echo 'lastImageId: ' . $lastImageId . PHP_EOL;
        echo PHP_EOL;

        $this->assertTrue($firstImageId > $lastImageId);
    }

    public function testThereIsHundredItemsOnPage(): void
    {
        $portfolio = new FreshPortfolioParser;
        $images = $portfolio->getPageImages(1);

        echo PHP_EOL;
        echo count($images) . PHP_EOL;
        echo PHP_EOL;

        $this->assertCount(100, $images);

    }

    public function testImageDetailsHaveValue(): void
    {
        $portfolio = new FreshPortfolioParser(1);
        $images = $portfolio->getPageImages();
        foreach ($images as $image) {
            $this->assertNotEmpty($image['img_page_url']);
            $this->assertNotEmpty($image['img_title']);
            // $this->assertNotEmpty($image['img_url']); // can be empty
            $this->assertGreaterThan(0, $image['img_id']);
            $this->assertContains($image['img_type'], ['image-vector', 'image-photo', 'image-illustration']);
        }

        $this->assertTrue(true);
    }
}
