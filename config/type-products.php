<?php

return [
    'san-pham' => [
        'title_main' => "Sản Phẩm",
        'website' => [
            'type' => [
                'index' => 'object',
                'detail' => 'article'
            ],
            'title' => 'sanpham'
        ],
        'routes' => true,
        'copy' => true,
        'tags' => false,
        'slug' => true,
        'status' => ["noibat" => "Nổi bật", "moi" => "Mới", "hienthi" => "Hiển thị"],
        'images' => [
            'photo' => [
                'title' => 'Hình đại diện',
                'width' => '584',
                'height' => '784',
                'opt' => '1',
                'thumb' => '584x784x1',
                'thumb_detail' => '510x640x1'
            ],
            'icon' => [
                'title' => 'Hình đại diện 2',
                'width' => '584',
                'height' => '784',
                'opt' => '1',
                'thumb' => '584x784x2',
            ]
        ],
        'show_images' => true,
        'gallery' => [
            'san-pham' => [
                "title_main_photo" => "Hình ảnh sản phẩm",
                "title_sub_photo" => "Hình ảnh",
                "status_photo" => ["hienthi" => "Hiển thị"],
                "number_photo" => 3,
                "images_photo" => true,
                "avatar_photo" => true,
                "name_photo" => true,
                "photo_width" => 950,
                "photo_height" => 630,
                'photo_opt' => '1',
                "photo_thumb" => '100x100x1'
            ],
            // 'hinh-anh' => [
            //     "title_main_photo" => "Hình ảnh sản phẩm",
            //     "title_sub_photo" => "Hình ảnh",
            //     "status_photo" => ["hienthi" => "Hiển thị"],
            //     "number_photo" => 3,
            //     "images_photo" => true,
            //     "avatar_photo" => true,
            //     "name_photo" => true,
            //     "photo_width" => 950,
            //     "photo_height" => 630,
            //     "photo_thumb" => '100x100x1'
            // ],
        ],
        // 'posts' => [
        //     'khuyen-mai-san-pham' => 'Khuyến mãi',
        //     'uu-dai-san-pham' => 'Ưu đãi',
        //     'ho-tro-san-pham' => 'Hỗ trợ',
        // ],
        // 'excel' => [
        //     'import' => [
        //         'title_main_excel' => "Import",
        //     ],
        //     'export' => [
        //         'title_main_excel' => "Export",
        //     ]
        // ],
        'view' => true,
        'comment' => true,
        'properties' => true,
        'code' => true,
        'regular_price' => true,
        'sale_price' => true,
        'discount' => true,
        'datePublish' => false,
        'name' => true,
        'desc' => true,
        'desc_cke' => true,
        'content' => true,
        'content_cke' => true,
        // 'parameter' => false,
        // 'parameter_cke' => false,
        // 'promotion' => false,
        // 'promotion_cke' => false,
        // 'incentives' => false,
        // 'incentives_cke' => false,
        'schema' => true,
        'seo' => true,
        'group' => false,
        'categories' => [
            'list' => [
                'title_main_categories' => "Danh mục cấp 1",
                'images' => [
                    'photo' => [
                        'title' => 'Hình đại diện',
                        'width' => '500',
                        'height' => '500',
                        'opt' => '1',
                        'thumb' => '500x500x1'
                    ]
                ],
                'copy_categories' => true,
                'show_images_categories' => true,
                'slug_categories' => true,
                'status_categories' => ["noibat" => "Nổi bật", "hienthi" => "Hiển thị"],
                'gallery_categories' => [],
                'name_categories' => true,
                'desc_categories' => true,
                'desc_categories_cke' => false,
                'content_categories' => false,
                'content_categories_cke' => false,
                'seo_categories' => true,
            ],
            'cat' => [
                'title_main_categories' => "Danh mục cấp 2",
                'images' => [
                    'photo' => [
                        'title' => 'Hình đại diện',
                        'width' => '500',
                        'height' => '500',
                        'opt' => '1',
                        'thumb' => '500x500x1'
                    ],
                    'photo1' => [
                        'title' => 'Hình đại diện 1',
                        'width' => '314',
                        'height' => '540',
                        'opt' => '1',
                        'thumb' => '500x500x1'
                    ]
                ],
                'copy_categories' => false,
                'show_images_categories' => true,
                'slug_categories' => true,
                'status_categories' => ["noibat" => "Nổi bật", "noibat1" => "Nổi bật 1", "hienthi" => "Hiển thị"],
                'gallery_categories' => [],
                'name_categories' => true,
                'desc_categories' => true,
                'desc_categories_cke' => false,
                'content_categories' => false,
                'content_categories_cke' => false,
                'seo_categories' => true,
            ],
        ],
        // 'brand' => [
        //     'title_main_brand' => "Danh mục hãng",
        //     'images' => [
        //         'photo' => [
        //             'title' => 'Hình đại diện',
        //             'width' => '500',
        //             'height' => '500',
        //             'opt' => '1',
        //             'thumb' => '500x500x1'
        //         ]
        //     ],
        //     'copy_brand' => false,
        //     'show_images_brand' => true,
        //     'slug_brand' => true,
        //     'status_brand' => ["hienthi" => "Hiển thị", "noibat" => "Nổi bật"],
        //     'name_brand' => true,
        //     'desc_brand' => true,
        //     'desc_brand_cke' => false,
        //     'content_brand' => false,
        //     'content_brand_cke' => false,
        //     'seo_brand' => true
        // ]
    ]
];