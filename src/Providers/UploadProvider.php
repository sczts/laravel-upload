<?php


namespace Sczts\Upload\Providers;

use Sczts\Upload\Upload;
use \Illuminate\Support\ServiceProvider;

class UploadProvider extends ServiceProvider
{
    protected $defer = true;

    /**
     * 服务引导方法
     *
     * @return void
     */
    public function boot(): void
    {

        //发布配置文件到项目的 config 目录中
        $path = realpath(__DIR__.'/../../config/upload.php');
        $this->publishes([
            $path => config_path('upload.php'),
        ]);
    }

    /**
     * 注册服务
     */
    public function register(): void
    {
        $this->app->singleton(Upload::class, function () {
            return new Upload();
        });
    }

}
