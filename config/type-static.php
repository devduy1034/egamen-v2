<?php

return [
    'gioi-thieu' => [
        'routes' => true,
        'title_main' => "Giới thiệu",
        'website' => [
            'type' => 'object',
            'title' => 'gioithieu'
        ],
        'status' => [
            "hienthi" => 'Hiển thị'
        ],
        'images' => [
            'photo' => [
                'title' => 'Hình ảnh',
                'width' => '300',
                'height' => '300',
                'opt' => '1',
                'thumb' => '300x300x1'
            ]
        ],
        // 'gallery' => [
        //     'gioi-thieu' => [
        //         "title_main_photo" => "Hình ảnh",
        //         "status_photo" => ["hienthi" => "Hien thi"],
        //         "number_photo" => 3,
        //         "images_photo" => true,
        //         "name_photo" => true,
        //         "width_photo" => 500,
        //         "height_photo" => 340,
        //         "thumb_photo" => '500x340x1'
        //     ],
        // ],
        'name' => true,
        'desc' => true,
        'desc_cke' => true,
        'content' => true,
        'content_cke' => true,
        'seo' => true,
    ],
    'lien-he' => [
        'title_main' => "Liên hệ",
        'website' => [
            'type' => 'object',
            'title' => 'Lien he'
        ],
        'status' => [
            "hienthi" => 'Hiển thị'
        ],
        'images' => [
            'photo' => [
                'title' => 'Hình ảnh',
                'width' => '300',
                'height' => '300',
                'thumb' => '300x300x1'
            ]
        ],
        'name' => true,
        'content' => true,
        'content_cke' => true,
        'seo' => true,
    ],
    'footer' => [
        'title_main' => "Footer",
        'status' => [
            "hienthi" => 'Hiển thị'
        ],
        'images' => [
            'photo' => [
                'title' => 'Hình ảnh',
                'width' => '164',
                'height' => '50',
                'thumb' => '164x50x1',
                'opt' => '1'
            ]
        ],
        'name' => true,
        'desc' => false,
        'content' => true,
        'content_cke' => true,
    ]
];
