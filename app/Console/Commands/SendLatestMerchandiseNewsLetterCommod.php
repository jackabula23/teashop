<?php

namespace App\Console\Commands;

use App\Jobs\SendMerchandiseNewsletterJob;
use App\Shop\Entity\Merchandise;
use App\Shop\Entity\User;
use DB;
use Illuminate\Console\Command;
use Log;


class SendLatestMerchandiseNewsLetterCommod extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'shop:sendLatestMerchandiseNewsletter';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '[郵件] 寄送最新商品電子報';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        DB::enableQueryLog();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {   
        $this->info('寄送最新商品電子報開始');
        $this->info('撈取最新商品');
        // 撈取最新更新10筆可販售商品
        $total_row = 10;
        $MerchandiseCollection = Merchandise::OrderBy('created_at','desc')->where('status','S')->take($total_row)->get();

        // 寄送電子信箱給所有會員，每次撈取100筆會員資料
        $row_per_page = 100;
        $page = 1;
        while (true) {
            // 略過資料筆數
            $skip = ($page - 1) * $row_per_page;
            // 取得分頁會員資料
            $this->comment('取得使用者資料，第'.$page.'頁，每頁'.$row_per_page.'筆');
            $UserCollection = User::orderBy('id', 'asc')->skip($skip)->take($row_per_page)->get();

            if (!$UserCollection->count()) {
                // 沒有使用者資料了，停止派送電子報
                break;
            }

            // 派送會員電子報工作
            $this->comment('派送會員電子信開始');
            foreach ($UserCollection as $User) {
                SendMerchandiseNewsletterJob::dispatch($User,$MerchandiseCollection)->onQueue('low');
            }
            $this->comment('派送會員電子信結束');
            // 繼續找看看還有沒有需要寄送電子信的使用者
            $page++;
        }
        $this->info('寄送最新會員電子報結束');
    }
}
