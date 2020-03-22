<?php

namespace App\Jobs;

use App\Handlers\ShopeeHandler;
use App\Models\CrawlerItem;
use App\Models\CrawlerShop;
use App\Repositories\Member\MemberCoreRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;

class _CrawlerTaskJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $url;
    private $shopeeHandler;
    private $crawlerTask;

    public function __construct($crawlerTask, $url)
    {
        $this->url = $url;
        $this->crawlerTask = $crawlerTask;
        $this->shopeeHandler = new ShopeeHandler();
    }

    //處理的工作
    public function handle()
    {
        $url = $this->url;
        $ClientResponse = $this->shopeeHandler->ClientHeader_Shopee($url);
        $json = json_decode($ClientResponse->getBody(), true);

        $member_id = Auth::guard('member')->check()?  Auth::guard('member')->user()->id: '1';
        foreach ($json['items'] as $item){
            $row_items[] = [
                'itemid' => $item['itemid'],
                'shopid' => $item['shopid'],
                'images' => $item['image'],
                'sold' => $item['sold']!==null? $item['sold']: 0,
                'historical_sold' => $item['historical_sold'],
                'domain_name' =>  $this->crawlerTask->domain_name,
                'local' =>  $this->crawlerTask->local,
                'member_id' => $member_id,
            ];

            $row_shops[] =  [
                'shopid' => $item['shopid'],
                'shop_location' => "",
                'local' => $this->crawlerTask->local,
                'domain_name' => $this->crawlerTask->domain_name,
                'member_id' => $member_id
            ];
            $value_arr[] = [ $item['itemid'],  $item['shopid'], $this->crawlerTask->local];
        };

        //批量儲存Item
        $crawlerItem = new CrawlerItem();
        $TF = (new MemberCoreRepository())->massUpdate($crawlerItem, $row_items);

        //CrawlerTasks sync Items
        //$crawlerItem_ids = CrawlerItem::whereNull('created_at')->pluck('ci_id');
        $crawlerItem_ids = CrawlerItem::whereInMultiple( ['itemid','shopid','local'], $value_arr)
            ->pluck('ci_id');

        $this->crawlerTask->crawlerItems()->syncwithoutdetaching($crawlerItem_ids);

        $crawlerItem->timestamps = false;
        $crawlerItem->whereIn('ci_id',$crawlerItem_ids)->update(['created_at' => now()]);

        //批量儲存Shop
        $crawlerShop = new CrawlerShop();
        $TF = (new MemberCoreRepository())->massUpdate($crawlerShop, $row_shops);

        //多餘的
//        $this->crawlerTask->crawlerItems()->update(['domain_name'=> $this->crawlerTask->domain_name]);

        dispatch((new CrawlerItemJob())->onQueue('low'));
        dispatch((new CrawlerShopJob())->onQueue('low'));

    }
}
