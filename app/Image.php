<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Eloquent;
use PHPHtmlParser\Dom;

/**
 * Model for images table
 *
 * @property int $user_id
 * @property int $shutter_id
 * @property string $page_url
 * @property string $image_url
 * @property int $added_on
 * @mixin Eloquent
 *
 * @todo: extract parser from model
 */
class Image extends Model
{
    const KEYWORDS = 'div[data-automation="ExpandableKeywordsList_container_div] a'; // div.C_a_03061 data-automation="ExpandableKeywordsList_container_div"
    const CAPTION = 'h1[data-automation="ImageDetailsPage_Details"]'; //'h1.m_b_b';
    const IMAGE_URL = 'img[data-automation="PictureFrame_highRes_img"]'; // 'img.m_i_g';

    protected $table = 'images';

    public $timestamps = true;

    /**
     * @var Dom
     */
    protected $dom;
    /**
     * @var \Memcached
     */
    private $cache;

    public function attributions()
    {
        return $this->hasMany('App\Attribution');
    }

    public function parseData(): void
    {
        // todo: use framework cache
        $this->cache = new \Memcached;
        $this->cache->addServer('127.0.0.1', 11211);

        if ($this->cache->get('images-' . $this->shutter_id) !== false) {
            $this->dom = $this->cache->get('images-' . $this->shutter_id);
        } else {
            echo 'not cache' . PHP_EOL;
            echo $this->page_url . PHP_EOL;
            $this->dom = new Dom;
            // todo: move url to config()
            $this->dom->loadFromUrl('https://www.shutterstock.com' . $this->page_url);
            $this->cache->set('images-' . $this->shutter_id, $this->dom);
        }

        $keywordsList = $this->getKeywords($this->dom);

        $caption = $this->getCaption($this->dom);

        $attribution = $this->getAttributionForParse();
        $oldKeywordsList = $attribution->getKeywordsList();

        if (empty($this->image_url)) {
            $imageUrl = $this->getImageUrl($this->dom);
            $this->image_url = $imageUrl;
            $this->save();
        }

        if (
            $attribution->caption === $caption
            &&
            implode(', ', $oldKeywordsList) === implode(', ', $keywordsList)
        ) {
            return;
        }

        $attribution = $this->getAttributionForParse(true);

        $order = 1;
        foreach ($keywordsList as $word) {
            /** @var Keyword $keyword */
            $keyword = Keyword::firstOrCreate(['word' => $word]);

            $keyword->attributions()->attach($attribution, [
                'selling' => 0,
                'order' => $order,
            ]);

            $order++;

            echo $word . PHP_EOL;
        }

        $attribution->keywords_count = count($keywordsList);
        $attribution->caption = $caption;

        $attribution->save();
    }

    public function getCurrentAttribution(): Attribution
    {
        return Attribution::where('image_id', $this->id)
            ->orderBy('created_at')
            ->first();
    }

    public function getCurrentKeywords(): array
    {
        $attribution = $this->getCurrentAttribution();

        $keywords = $attribution->keywords();

        return $keywords->pluck('word')->toArray();
    }

    /**
     * @param bool $create
     * @return Attribution
     * @todo: pass caption here for creating new Attribution
     */
    protected function getAttributionForParse(bool $create = false): Attribution
    {
        $attribution = $this->getCurrentAttribution();

        if ($create === true && $attribution->keywords_count !== 0) {
            $attribution = new Attribution;
            $this->attributions()->save($attribution);
        }

        return $attribution;
    }

    public function getKeywords($dom)
    {
        $keywords = $dom->find(self::KEYWORDS);
        $keywordsList = [];
        foreach ($keywords as $keywordTag) {
            $keyword = $keywordTag->text;
            $keywordsList[] = $keyword;
        }

        return $keywordsList;
    }

    public function getCaption($dom)
    {
        $caption = $dom->find(self::CAPTION);
        return $caption->text;
    }

    public function getImageUrl($dom)
    {
        $imageUrl = $dom->find(self::IMAGE_URL);
        return $imageUrl->src;
    }
}
