<?php

namespace App\Providers;

use App\Gateways\DiysmsGateway;
use Illuminate\Support\ServiceProvider;
use Overtrue\EasySms\EasySms;

class EasySmsServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->app->singleton(EasySms::class,function ($app){
            $easySms  = new EasySms(config('easysms'));
            $easySms->extend('diysms', function(){
                // $gatewayConfig 来自配置文件里的 `gateways.mygateway`
                return new DiysmsGateway(config('easysms.gateways.diysms'));
            });
            return $easySms;

        });



//        $this->app->make('easySms')->extend('diysms', function () {
//            return new DiysmsGateway(config('easysms.gateways.diysms'));
//        });

        $this->app->alias(EasySms::class,'easysms');
    }
}
