<?php

return [
    'product' => require "type-products.php",
    'news' => require "type-news.php",
    'photo' =>  require "type-photo.php",
    'newsletters' => require "type-newsletters.php",
    'static' => require "type-static.php",
    'openai' => [
        'key' => env('OPENAI_API_KEY'),
    ],

    // 'group' => [
    //     'group-1' => [
    //         'title_main' => "Nhóm sản phẩm",
    //         'group' => ['san-pham', 'tin-tuc', 'gioi-thieu'],
    //     ],
    //     'group-2' => [
    //         'title_main' => "Nhóm media",
    //         'group' => ['album', 'video', 'banner-top'],
    //     ]
    // ],

    'seo' => [
        'page' => (function () {
            $pages = array_merge(require "type-products.php", require "type-news.php", require "type-photo.php");
            $result = ['trang-chu' => 'Trang chủ'];
            foreach ($pages as $key => $value) {
                if (isset($value['routes']) && $value['routes'] === true) {
                    $result[$key] = $value['title_main'];
                }
            }
            return $result;
        })(),
        'sitemap' => (function () {
            $sitemaps = array_merge(require "type-products.php", require "type-news.php", require "type-static.php", require "type-photo.php");
            foreach ($sitemaps as $key => $value) {
                if (isset($value['website']) && (!empty($value['website']['type']['index']) || !empty($value['website']['type']))) {
                    $result[$key] = $value['title_main'];
                }
            }
            return $result;
        })(),
        'type' => [
            'trang-chu' => "Trang chủ",
        ],
        'width' => 300,
        'height' => 300,
        'thumb' => '300x300x1',
    ],

    'setting' => [
        'cau-hinh' => [
            'title_main' => "Thông tin công ty",
            'address' => true,
            'phone' => true,
            'hotline' => true,
            'zalo' => true,
            'email' => true,
            'website' => true,
            'fanpage' => true,
            'coords' => true,
            'coords_iframe' => true,
            'worktime' => true,
            'link_googlemaps'  => true,
            'ship_area' => true,
        ],
        // 'dieu-huong' => [
        //     'title_main' => "Điều hướng link",
        //     'old_link' => true,
        //     'new_link' => true,
        //     '302' => true
        // ]
    ],

    'extensions' => [
        'popup' => [
            'title_main' => "Popup",
            'images' => true,
            'status' => ["hienthi" => "Hiển thị", "repeat" => "Lặp lại"],
            'width' => 800,
            'height' => 500,
            'thumb' => '800x500x1',
        ],
        'hotline' => [
            'title_main' => "Điện thoại",
            'status' => ["hienthi" => "Hiển thị"],
            'images' => true,
            'width' => 35,
            'height' => 35,
            'thumb' => '35x35x1',
        ],
        'social' => [
            'title_main' => "Tiện ích",
            'status' => ["hienthi" => "Hiển thị"],
            'images' => false,
            'width' => 35,
            'height' => 35,
            'thumb' => '35x35x1',
        ],
    ],

    'quicklink' => [
        'san-pham' => [
            'link' => ['com' => 'product', 'act' => 'add', 'type' => 'san-pham'],
            'icon' => '<i class="ti ti-package-import fs-4"></i>',
            'title' => 'Sản phẩm',
            'sub_title' => 'Thêm sản phẩm'
        ],
        'tin-tuc' => [
            'link' => ['com' => 'news', 'act' => 'add', 'type' => 'tin-tuc'],
            'icon' => '<i class="ti ti-news fs-4"></i>',
            'title' => 'Tin tức',
            'sub_title' => 'Thêm bài viết'
        ]
    ],

    'users' => [
        'active' => true,
        'admin' => false,
        'permission' => false,
        'member' => true,
    ],

    'crawler' => [
        'icondenim' => [
            'active' => false,
            'enabled' => true,
            'allow_custom_source' => true,
            'allow_product_url' => true,
            'allow_collection_url' => true,
            'allow_fetch_all' => true,
            'fetch_all_default' => true,
            'allow_history' => true,
            'history_show_limit' => 15,
            'history_store_limit' => 30,
            'default_batch_size' => 10,
            'max_batch_size' => 50,
            'default_variant_quantity' => 10,
            'collection_max_pages' => 200,
            'collection_default_url' => 'https://icondenim.com/collections/tat-ca-san-pham',
        ],
    ],

    'order' => [
        'don-hang' => [
            'title_main' => "Đơn hàng",
            'excel' => true,
            'search' => true,
            'ship' => true,
            'dashboard' => [
                'enabled' => true,
                'tabs' => [
                    'overview' => true,
                    'product' => true,
                    'shipping' => true,
                    'customer' => true,
                ],
                'sections' => [
                    'header_filter' => true,
                    'kpi_cards' => true,
                    'core_charts' => true,
                    'ai_actions' => true,
                    'drilldown' => true,
                ],
                'channel_mode' => 'website_only',
            ],
        ],
    ],

    'voucher' => [
        'ma-giam-gia' => [
            'title_main' => "Voucher",
            'search' => true,
            'images' => [
                'photo' => [
                    'title' => "Hình ảnh",
                    'width' => 70,
                    'height' => 115,
                    'thumb' => '70x115x1',
                    'opt'  => 1
                ],
            ],
            'status' => ["hienthi" => "Hiển thị"],
        ],
    ],

    'link' => [
        'url' => [
            'title_main' => "Link nội dung",
            'name' => true,
            'link' => true,
            'status' => ["hienthi" => "Hiển thị"],
        ]
    ],

    'properties' => [
        'san-pham' => [
            'title_main' => "Thuộc tính",
            'slug' => true,
            'images' => true,
            'name' => true,
            'status' => ["hienthi" => "Hiển thị"],
            'categories' => [
                'list' => [
                    'title_main_categories' => "Danh mục cấp 1",
                    'slug_categories' => true,
                    'name_categories' => true,
                    'status_categories' => ["hienthi" => "Hiển thị", "search" => "Tìm kiếm", "cart" => "Giỏ hàng"],
                ]
            ]
        ]
    ],
    'places' => [
        'dia-chi' => [
            'title_main' => "Địa chỉ",
            'slug' => true,
            'status' => ["hienthi" => "Hiển thị"],
            'categories' => [
                'list' => [
                    'title_main_categories' => "Tỉnh/Thành phố",
                    'name_categories' => true,
                    'slug_categories' => true,
                    'status_categories' => [
                        "hienthi" => "Hiển thị",
                        "noi-thanh" => "Nội thành",
                        "ngoai-thanh" => "Ngoại thành",
                        "tinh-xa" => "Tỉnh xa"
                    ],
                ],
                // 'cat' => [
                //     'title_main_categories' => "Quận/Huyện",
                //     'name_categories' => true,
                //     'slug_categories' => true,
                //     'status_categories' => [
                //         "hienthi" => "Hiển thị",
                //         "noi-thanh" => "Nội thành",
                //         "ngoai-thanh" => "Ngoại thành",
                //         "tinh-xa" => "Tỉnh xa"
                //     ],
                // ],
                'item' => [
                    'title_main_categories' => "Phường/Xã",
                    'name_categories' => true,
                    'slug_categories' => true,
                    'status_categories' => [
                        "hienthi" => "Hiển thị",
                        "noi-thanh" => "Nội thành",
                        "ngoai-thanh" => "Ngoại thành",
                        "tinh-xa" => "Tỉnh xa"
                    ],
                ]
            ]
        ]
    ],
    'categoriesProperties' => false, // Them danh muc cap 1 cho danh muc thuoc tinh
    'type_img' => 'jpg,gif,png,jpeg,gif,webp,WEBP,heic,HEIC,heif,HEIF',
    'type_file' => 'doc,docx,pdf,rar,zip,ppt,pptx,xls,xlsx',
    'type_video' => 'mp3,mp4',
    'table' => ['product', 'news', 'static'],
    'link_content' => ['content', 'promotion'],
    'comment' => [
        'binh-luan' => [
            'title_main' => "Bình luận",
            'search' => true,
            'status' => ["hienthi" => "Hiển thị"],
        ],
    ],
];
