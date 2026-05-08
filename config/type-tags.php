<?php

return [
    'san-pham' => [
        'title_main' => 'Tags Sản Phẩm',
        'website' => [
            'type' => [
                'index' => 'object',
                'detail' => 'article'
            ],
            'title' => 'tagsanpham'
        ],
        'slug' => true,
        'name' => true,
        'images' => true,
        'show_images' => true,
        'status' => [
            "noibat" => 'Nổi bật',
            "hienthi" => 'Hiển thị'
        ],
        'seo' => true,
        'width' => 300,
        'height' => 200,
        'thumb' => '100x100x1',
    ]
];