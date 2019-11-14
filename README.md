# 快速开始

1. 使用 composer 安装
    ```
    composer require sczts/upload
    ```

2. 发布配置文件
    ```
    php artisan vendor:publish --provider="Sczts\Upload\Providers\UploadProvider"
    ```

3. 向 `.env` 添加环境变量
    ```bash
    # 上传配置
    UPLOAD_DRIVER=qiniu
    QINIU_AK=xxxxxxxxxxxxxx
    QINIU_SK=xxxxxxxxxxxxx
    QINIU_BUCKET=xxxx
    QINIU_DOMAIN=http://cdn.xxxxxxx.com
    ```
