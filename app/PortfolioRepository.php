<?php

namespace App;

class PortfolioRepository
{
    public static function filterOldImages(array $images): array
    {
        $currentImageIds = self::getCurrentImages();

        $images = array_filter($images, static function ($image) use ($currentImageIds) {
            return !in_array($image['img_id'], $currentImageIds, true);
        });

        return $images;
    }

    public static function getCountOfImages(): int
    {
        $currentImageIds = self::getCurrentImages();

        return count($currentImageIds);
    }

    /** @todo: not hardcoded user_id */
    private static function getCurrentImages(): array
    {
        return Image::select('shutter_id')
            ->where('user_id', 1)
            ->get()
            ->pluck('shutter_id')
            ->toArray();
    }
}
