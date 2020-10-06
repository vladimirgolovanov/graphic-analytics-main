<?php

namespace App\Http\Controllers;

use App\Image;
use App\Sale;
use App\SalesImport\RequestParser;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class SalesStatController extends Controller
{
    /**
     * @param Request $request
     * @throws Exception
     * @noinspection PhpUnused
     */
    public function getDay(Request $request)
    {
        $dayData = $request->post('append');
        $result = RequestParser::parseDayData($dayData);

        $dayDate = new Carbon($result['date']);
        // todo: test this
        if ($dayDate->lessThan(Carbon::now()->subHours(10))) {
            return;
        }

        $salesTypeId = RequestParser::getSalesTypeIdFromTabHeader($result['tab_header']);

        if ($salesTypeId === 0) {
            return;
        }

        if (count($result['rows']) === 0) {
            return;
        }

        $user = auth()->loginUsingId(1);
        // todo: make $isPageSaved bool and remove from controller
        $isPageSaved = Sale::where('sales_type_id', $salesTypeId)
            ->where('date', $result['date'])
            ->where('sales_type_id', $salesTypeId)
            ->whereHas('image', function (Builder $query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->count();
        if ($isPageSaved > 0) {
            return;
        }

        // todo: remove images saving from controller
        $shutterImageIds = collect($result['rows'])->flatten()->only(0)->toArray();
        $imageIds = Image::whereIn('shutter_id', $shutterImageIds)
            ->pluck('id', 'shutter_id')
            ->toArray();

        foreach ($result['rows'] as $row) {
            for ($i = 0; $i < $row['quantity']; $i++) {
                $sale = new Sale;
                $sale->image_id = $imageIds[$row['shutter_image_id']];
                $sale->date = $result['date'];
                $sale->price = RequestParser::splitPrice($row['price'], $row['quantity'], $i);
                $sale->sales_type_id = $salesTypeId;
                $sale->save();
            }
        }
    }
}
