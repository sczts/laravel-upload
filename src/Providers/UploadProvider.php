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
        $path = realpath(__DIR__ . '/../../config/upload.php');
        //发布配置文件到项目的 config 目录中
        $this->publishes([
            $path => config_path('upload.php'),
        ]);
    }

    /**
     * 注册服务
     */
    public function register(): void
    {
        $default = config('upload.default');
        $service = config('upload.services.' . $default . '.service');

        $this->app->bind(
            'Sczts\Upload\UploadService',
            $service
        );

        $this->app->singleton(Upload::class, function ($app) {
            return new Upload($app->make('Sczts\Upload\UploadService'));
        });
    }

}
