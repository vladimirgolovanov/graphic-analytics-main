<?php

namespace Tests\Unit;

use App\SalesImport\RequestParser;
use OutOfRangeException;
use PHPUnit\Framework\TestCase;

class RequestParserTest extends TestCase
{
    const DAYDATA = '"<div id=\"earnings-container\" class=\"container-fluid pad-t-med earnings-container\">\n    <div class=\"row\">\n      <div class=\"col-md-12\">\n        <div class=\"h3\">\n  <a id=\"daily-earnings-header\" href=\"/earnings?year=2020&amp;month=10&amp;language=en\">\n    Earnings Summary\n  </a>\n   &gt; \n  October 5, 2020\n</div>\n\n\n<ul class=\"nav nav-tabs pad-t-med\">\n  \n    \n    \n      \n    \n    <li role=\"presentation\" class=\"active\">\n      <a href=\"/earnings/daily?date=2020-10-05&amp;category=25_a_day&amp;language=en\">\n        Subscriptions (3)\n      </a>\n    </li>\n  \n    \n    \n    <li role=\"presentation\" class=\"\">\n      <a href=\"/earnings/daily?date=2020-10-05&amp;category=on_demand&amp;language=en\">\n        On demand (0)\n      </a>\n    </li>\n  \n    \n    \n    <li role=\"presentation\" class=\"\">\n      <a href=\"/earnings/daily?date=2020-10-05&amp;category=enhanced&amp;language=en\">\n        Enhanced (0)\n      </a>\n    </li>\n  \n    \n    \n    <li role=\"presentation\" class=\"\">\n      <a href=\"/earnings/daily?date=2020-10-05&amp;category=cart_sales&amp;language=en\">\n        Cart sales (0)\n      </a>\n    </li>\n  \n    \n    \n    <li role=\"presentation\" class=\"\">\n      <a href=\"/earnings/daily?date=2020-10-05&amp;category=clip_packs&amp;language=en\">\n        Clip packs (0)\n      </a>\n    </li>\n  \n    \n    \n    <li role=\"presentation\" class=\"\">\n      <a href=\"/earnings/daily?date=2020-10-05&amp;category=single_image_and_other&amp;language=en\">\n        Single &amp; other (0)\n      </a>\n    </li>\n  \n  <li role=\"presentation\" class=\"\">\n    <a href=\"/earnings/daily?date=2020-10-05&amp;category=referrals&amp;language=en\">Referrals</a>\n  </li>\n</ul>\n\n      </div>\n    </div>\n    \n      \n        <div class=\"row\">\n          <div class=\"col-md-12\">\n              <div class=\"pull-right pad-t-med\">\n                \n\n              </div>\n          </div>\n        </div>\n        <div class=\"row\">\n          <div class=\"col-md-12\">\n              <table class=\"table table-hover details-table mrg-t-med\">\n  <thead>\n    <tr>\n      <th></th>\n      <th>ID</th>\n      <th class=\"sortable\">\n        <a href=\"?date=2020-10-05&amp;category=25_a_day&amp;sorted_by=total&amp;sort=desc\">\n          Earnings\n          <span class=\"sstk-icon icon-arrow-right invisible\">\n        </span></a>\n\n      </th>\n      <th class=\"sortable\">\n        <a href=\"?date=2020-10-05&amp;category=25_a_day&amp;sorted_by=count&amp;sort=asc\" class=\"sorting\">\n          Downloads\n          <span class=\"sstk-icon icon-arrow-right invisible\">\n        </span></a>\n      </th>\n    </tr>\n  </thead>\n  <tbody>\n    \n      <tr>\n        <td>\n          <a href=\"//www.shutterstock.com/image/1234\">\n            <div class=\"thumbnail-gallery thumbnail-gallery-extra-small\">\n              <div class=\"thumbnail thumbnail-letterbox\">\n                <img class=\"thumbnail-image\" onerror=\"Ss.thumbnailErrorHandler({target: this})\" src=\"https://image.shutterstock.com/image-vector/asdf-250nw-1234.jpg\">\n              </div>\n            </div>\n          </a>\n        </td>\n        <td>1234</td>\n        <td>$0.17</td>\n        <td>1</td>\n      </tr>\n    \n      <tr>\n        <td>\n          <a href=\"//www.shutterstock.com/image/23456\">\n            <div class=\"thumbnail-gallery thumbnail-gallery-extra-small\">\n              <div class=\"thumbnail thumbnail-letterbox\">\n                <img class=\"thumbnail-image\" onerror=\"Ss.thumbnailErrorHandler({target: this})\" src=\"https://image.shutterstock.com/image-vector/qwerty-250nw-23456.jpg\">\n              </div>\n            </div>\n          </a>\n        </td>\n        <td>23456</td>\n        <td>$0.17</td>\n        <td>1</td>\n      </tr>\n    \n      <tr>\n        <td>\n          <a href=\"//www.shutterstock.com/image/34567\">\n            <div class=\"thumbnail-gallery thumbnail-gallery-extra-small\">\n              <div class=\"thumbnail thumbnail-letterbox\">\n                <img class=\"thumbnail-image\" onerror=\"Ss.thumbnailErrorHandler({target: this})\" src=\"https://image.shutterstock.com/image-vector/zxc-250nw-34567.jpg\">\n              </div>\n            </div>\n          </a>\n        </td>\n        <td>34567</td>\n        <td>$0.10</td>\n        <td>1</td>\n      </tr>\n    \n  </tbody>\n</table>\n\n          </div>\n        </div>\n        <div class=\"row\">\n          <div class=\"col-md-12\">\n              <div class=\"pull-right\">\n                \n\n              </div>\n          </div>\n        </div>\n      \n    \n  </div>"';

    // todo: check on empty page
    public function testParseDayData()
    {
        $result = RequestParser::parseDayData(self::DAYDATA);

        $this->assertEquals('2020-10-05', $result['date']);
        $this->assertEquals('Subscriptions', $result['tab_header']);
        $this->assertEquals([
            "shutter_image_id" => 1234,
            "price" => 0.17,
            "quantity" => 1,
        ], $result['rows'][0]);
    }

    public function salesTypeFromHeaderDataProvider()
    {
        return [
            ['By month', 0],
            ['Subscriptions', 1],
            ['On demand', 2],
            ['Enhanced', 3],
            ['Cart sales', 4],
            ['Clip packs', 5],
            ['Single & other', 6],
            ['Referrals', 7],
        ];
    }

    /**
     * @dataProvider salesTypeFromHeaderDataProvider
     * @param string $tabHeader
     * @param int $expectedSalesTypeId
     */
    public function testGetSalesTypeIdFromTabHeader(string $tabHeader, int $expectedSalesTypeId)
    {
        $salesTypeId = RequestParser::getSalesTypeIdFromTabHeader($tabHeader);

        $this->assertSame($expectedSalesTypeId, $salesTypeId);
    }

    public function salesTypeFromHeaderExceptionsDataProvider()
    {
        return [
            ['Subscription (1)', OutOfRangeException::class],
            ['Subscription ', OutOfRangeException::class],
            ['', OutOfRangeException::class],
        ];
    }

    /**
     * @dataProvider salesTypeFromHeaderExceptionsDataProvider
     * @param string $tabHeader
     * @param string $exception
     */
    public function testGetSalesTypeIdFromTabHeaderExceptions(string $tabHeader, string $exception)
    {
        $this->expectException($exception);
        RequestParser::getSalesTypeIdFromTabHeader($tabHeader);
    }

    public function splitPriceDataProvider()
    {
        return [
            [0.10, 1, [0.10]],
            [0.27, 2, [0.14, 0.13]],
            [0.34, 3, [0.12, 0.11, 0.11]],
        ];
    }

    /**
     * @dataProvider splitPriceDataProvider
     * @param float $price
     * @param int $quantity
     * @param array $prices
     */
    public function testSplitPrice(float $price, int $quantity, array $prices)
    {
        for ($i = 0; $i < $quantity; $i++) {
            $calculatedPrice = RequestParser::splitPrice($price, $quantity, $i);
            $this->assertSame($prices[$i], $calculatedPrice);
        }
    }
}
