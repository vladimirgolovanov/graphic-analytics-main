<?php

namespace Tests\Unit;

use PHPHtmlParser\Dom;
use PHPUnit\Framework\TestCase;
use App\Image;

class ImageTest extends TestCase
{
    public static $image;
    public static $cache;
    public static $dom;

    public static function setUpBeforeClass(): void
    {
        self::$image = new Image();
        self::$image->user_id = 1;
        self::$image->shutter_id = 1826219684;
        self::$image->page_url = '/image-vector/love-olive-oil-logo-design-1826219684';
        self::$image->image_url = 'https://image.shutterstock.com/image-vector/love-olive-oil-logo-design-260nw-1826219684.jpg';
        self::$image->added_on = '2020-10-04 20:34:16';

        // todo: use framework cache
        self::$cache = new \Memcached;
        self::$cache->addServer('127.0.0.1', 11211);

        if (self::$cache->get('images-' . self::$image->shutter_id) !== false) {
            self::$dom = self::$cache->get('images-' . self::$image->shutter_id);
        } else {
            echo 'not cache' . PHP_EOL;
            echo self::$image->page_url . PHP_EOL;
            self::$dom = new Dom;
            // todo: move url to config()
            self::$dom->loadFromUrl('https://www.shutterstock.com' . self::$image->page_url);
            self::$cache->set('images-' . self::$image->shutter_id, self::$dom);
        }
    }

    public function testGetKeywords()
    {
        $keywords = self::$image->getKeywords(self::$dom);
        $this->assertCount(50, $keywords);
    }

    public function testGetCaption()
    {
        $caption = self::$image->getCaption(self::$dom);
        $this->assertEquals('love olive oil logo design ', $caption);
    }

    public function testGetImageUrl()
    {
        $imageUrl = self::$image->getImageUrl(self::$dom);
        $this->assertEquals(
            'https://image.shutterstock.com/image-vector/love-olive-oil-logo-design-600w-1826219684.jpg',
            $imageUrl
        );
    }
}
