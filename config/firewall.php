<?php

return [
    'firewall' => false, // Bat/tat firewall
    'ip_allow' => '', // Danh sach IP bo qua
    'ip_deny' => '', // Danh sach IP cam truy cap
    'max_lockcount' => 2, // So lan toi da phat hien dau hieu DDOS va khoa IP vinh vien
    'max_connect' => 5, // So ket noi toi da duoc gioi han boi time_limit
    'time_limit' => 3, // Thoi gian de dat max_connect ket noi
    'time_wait' => 5, // Thoi gian cho de mo khoa IP bi khoa tam thoi
    'email_admin' => 'LARAVEL@LARAVEL.vn', // Email lien lac voi admin
];
