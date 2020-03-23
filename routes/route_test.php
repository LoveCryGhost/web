<?php

use App\Handlers\ShopeeHandler;
use App\Jobs\CrawlerItemJob;
use App\Jobs\CrawlerShopJob;
use App\Jobs\CrawlerTaskJob;
use App\Models\CrawlerItem;
use App\Models\CrawlerItemSKU;
use App\Models\CrawlerItemSKUDetail;
use App\Models\CrawlerShop;
use App\Models\CrawlerTask;
use App\Repositories\Member\MemberCoreRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

Route::get('/shopee-crawleritem', function () {

    $this->shopeeHandler = new ShopeeHandler();
    $crawlerTask = CrawlerTask::where(function ($query) {
                         $query->whereDate('updated_at','<>',Carbon::today())->orWhereNull('updated_at');
                    })->first();

    if($crawlerTask) {
        $urls = $this->shopeeHandler->crawlerTaskGenerateAPIUrl($crawlerTask);
        foreach ($urls as $url) {
            $ClientResponse = $this->shopeeHandler->ClientHeader_Shopee($url);
            $json = json_decode($ClientResponse->getBody(), true);

            $member_id = Auth::guard('member')->check() ? Auth::guard('member')->user()->id : '1';
            foreach ($json['items'] as $item) {
                $row_items[] = [
                    'itemid' => $item['itemid'],
                    'shopid' => $item['shopid'],
                    'sold' => $item['sold'] !== null ? $item['sold'] : 0,
                    'historical_sold' => $item['historical_sold'],
                    'domain_name' => $crawlerTask->domain_name,
                    'local' => $crawlerTask->local,
                    'member_id' => $member_id,
                    'updated_at' => null
                ];

                $row_shops[] = [
                    'shopid' => $item['shopid'],
                    'shop_location' => "",
                    'local' => $crawlerTask->local,
                    'domain_name' => $crawlerTask->domain_name,
                    'member_id' => $member_id
                ];
                $value_arr[] = [$item['itemid'], $item['shopid'], $crawlerTask->local];
                $items_order[]=$item['itemid'];
            };

            //批量儲存Item
            $crawlerItem = new CrawlerItem();
            $TF = (new MemberCoreRepository())->massUpdate($crawlerItem, $row_items);

            //CrawlerTasks sync Items
            $crawlerItem_ids = CrawlerItem::whereInMultiple(['itemid', 'shopid', 'local'], $value_arr)
                ->pluck('ci_id', 'itemid');
            $index=0;
            foreach ($items_order as $itemid){
                $sync_ids[$crawlerItem_ids[$itemid]]= ['sort_order'=>$index++];
            }
            $crawlerTask->crawlerItems()->syncwithoutdetaching($sync_ids);

            $crawlerItem->timestamps = false;
            $crawlerItem->whereIn('ci_id', $crawlerItem_ids)->update(['created_at' => now()]);

            //批量儲存Shop
            $crawlerShop = new CrawlerShop();
            $TF = (new MemberCoreRepository())->massUpdate($crawlerShop, $row_shops);

            dispatch((new CrawlerTaskJob())->onQueue('high'));
            dispatch((new CrawlerItemJob())->onQueue('low'));
            dispatch((new CrawlerShopJob())->onQueue('low'));
        }
        $crawlerTask->updated_at = now();
        $crawlerTask->save();
    }
});

Route::get('/shopee-crawleritem-updated-time', function () {
    $crawlerItem = CrawlerItem::find(1);
    $crawlerItem->updated_at = null;
    $crawlerItem->save();
    dd('ok');
});
