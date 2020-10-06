<?php

namespace App\SalesImport;

use OutOfRangeException;
use PHPHtmlParser\Dom;

class RequestParser
{
    public static function parseDayData($dayData): array
    {
        $data = [];

        // read current header, date & time
        $dom = new Dom;
        $dom->load(json_decode($dayData));
        $links = $dom->find('ul.pad-t-med .active a');

        $currentTabLink = parse_url($links[0]->href, PHP_URL_QUERY);
        parse_str(htmlspecialchars_decode($currentTabLink), $currentTabLink);
        // $currentTabLink array date:2020-10-05 category:25_a_day language:en
        $data['date'] = $currentTabLink['date'];

        $currentTabHeader = trim($links[0]->text);
        $currentTabHeader = explode('(', $currentTabHeader);
        $currentTabHeader = trim($currentTabHeader[0]);
        if (in_array($currentTabHeader, [
            'Subscriptions',
            'On demand',
            'Enhanced',
            'Cart sales',
            'Clip packs',
            'Single & other',
            'Referrals',
        ])) {
            $data['tab_header'] = $currentTabHeader;
        }

        $table = $dom->find('table.mrg-t-med tbody tr');
        foreach ($table as $row) {
            $cols = $row->find('td');
            $data['rows'][] = [
                'shutter_image_id' => (int)$cols[1]->text,
                'price' => (float)substr($cols[2]->text, 1),
                'quantity' => (int)$cols[3]->text,
            ];
        }

        return $data;
    }

    /**
     * @param $tabHeader
     * @return int
     * @throws OutOfRangeException
     */
    public static function getSalesTypeIdFromTabHeader($tabHeader): int
    {
        $salesTypes = [
            0 => 'By month',
            1 => 'Subscriptions',
            2 => 'On demand',
            3 => 'Enhanced',
            4 => 'Cart sales',
            5 => 'Clip packs',
            6 => 'Single & other',
            7 => 'Referrals',
        ];

        $salesTypeId = array_search($tabHeader, $salesTypes);

        if ($salesTypeId === false) {
            throw new OutOfRangeException('Unknown tab');
        }

        return $salesTypeId;
    }

    /**
     * @param float $price
     * @param int $quantity
     * @param int $iterator
     * @return float
     */
    public static function splitPrice(float $price, int $quantity, int $iterator): float
    {
        if ($quantity === 1) {
            return $price;
        }

        if (($price / $quantity) === round($price / $quantity, 2)) {
            return $price / $quantity;
        }

        $floatPrice = round($price/$quantity, 2, PHP_ROUND_HALF_DOWN);
        $diffirenceQuantity = $price - ($floatPrice * $quantity);
        $diffirenceQuantity = (int)($diffirenceQuantity * 100);
        $prices = [];
        for ($i = 0; $i < $quantity; $i++) {
            $prices[$i] = round($i < $diffirenceQuantity ? ($floatPrice + 0.01) : $floatPrice, 2);
        }

        return $prices[$iterator];
    }
}
