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
 */
class Image extends Model
{
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
        $this->cache = new \Memcached;
        $this->cache->addServer('127.0.0.1', 11211);

        if ($this->cache->get('images-' . $this->shutter_id) !== false) {
            $this->dom = $this->cache->get('images-' . $this->shutter_id);
        } else {
            echo 'not cache' . PHP_EOL;
            echo $this->page_url . PHP_EOL;
            $this->dom = new Dom;
            $this->dom->loadFromUrl('https://www.shutterstock.com' . $this->page_url);
            $this->cache->set('images-' . $this->shutter_id, $this->dom);
        }

        $keywords = $this->dom->find('div.C_a_c a');

        $keywordsList = [];
        foreach ($keywords as $keywordTag) {
            $keyword = $keywordTag->text;
            $keywordsList[] = $keyword;
        }

        $caption = $this->dom->find('h1.m_b_b');
        $caption = $caption->text;

        $attribution = $this->getAttributionForParse();
        $oldKeywordsList = $attribution->getKeywordsList();

        if (empty($this->image_url)) {
            $imageUrl = $this->dom->find('img.m_i_g');
            $imageUrl = $imageUrl->src;
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
}
