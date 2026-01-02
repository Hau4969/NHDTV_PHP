<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cloudinary Configuration
    |--------------------------------------------------------------------------
    |
    | Lưu trữ thông tin cấu hình cho Cloudinary. Bạn cần đặt biến CLOUDINARY_URL
    | trong file .env theo dạng:
    | CLOUDINARY_URL=cloudinary://API_KEY:API_SECRET@CLOUD_NAME
    |
    */

    'cloud_url' => env('CLOUDINARY_URL'),

    /*
    |--------------------------------------------------------------------------
    | Upload Presets (tùy chọn)
    |--------------------------------------------------------------------------
    |
    | Nếu bạn có upload preset trong Cloudinary dashboard, bạn có thể khai báo ở đây.
    |
    */

    'upload_preset' => env('CLOUDINARY_UPLOAD_PRESET', null),

    /*
    |--------------------------------------------------------------------------
    | Default Upload Options (tùy chọn)
    |--------------------------------------------------------------------------
    |
    | Các tùy chọn mặc định khi upload ảnh/video.
    |
    */

    'upload' => [
        'folder' => env('CLOUDINARY_FOLDER', 'uploads'), // Thư mục mặc định
        'overwrite' => true,                             // Cho phép ghi đè
        'resource_type' => 'auto',                       // Tự động nhận dạng file ảnh/video
    ],

];
