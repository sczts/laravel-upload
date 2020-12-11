# 快速开始

1. 使用 composer 安装
    ```
    composer require sczts/laravel-upload
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
    
4. 使用
    
    例 (依赖注入)：
    ```php
    use Sczts\Upload\Upload;
    
    class CommonController extends Controller
    {
        // 上传文件
        public function upload(Request $request,Upload $upload)
        {
            $file = $request->file('file');
            $data = $upload->upload($file);
            return $this->json(StatusCode::SUCCESS, $data);
        }
    }
    ```
    
    例 (门面)：
    ```php
    use Sczts\Upload\Facades\Upload;
    
    class CommonController extends Controller
    {
        // 上传文件
        public function upload(Request $request)
        {
            $file = $request->file('file');
            $data = Upload::upload($file);
            return $this->json(StatusCode::SUCCESS, $data);
        }
    }
    ```

5. 按实际需求修改 `config/upload.php` 配置，可在 `channel` 中覆盖 `settings` 的配置