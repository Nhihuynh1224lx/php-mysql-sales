<?php

/*
 * THÔNG TIN THƯƠNG HIỆU — nguồn duy nhất.
 *
 * File này gom tên công ty, khẩu hiệu, địa chỉ, liên hệ vào một chỗ.
 * Cả giao diện cửa hàng lẫn khu vực quản trị đều đọc từ đây, nên muốn
 * đổi tên hay địa chỉ chỉ cần sửa MỘT chỗ duy nhất.
 *
 * Cách dùng:
 *     require_once '/var/www/src/config/brand.php';
 *     echo $brand['name'];
 */

$brand = [
    /* Tên đầy đủ, dùng cho tiêu đề trang và footer */
    'name'     => 'Bida Hoàng Nhi',

    /* Chữ viết tắt để làm logo chữ lồng */
    'initials' => 'HN',

    /* Khẩu hiệu ngắn, hiển thị dưới tên thương hiệu */
    'tagline'  => 'Bán hàng và phân phối phụ kiện bida',

    /* Mô tả dài hơn, dùng ở footer và trang Giới thiệu */
    'about'    => 'Hệ thống quản lý bán hàng và cửa hàng dụng cụ bida trực '
                . 'tuyến: bàn bida, cơ, bao cơ, lơ và phụ kiện chính hãng.',

    /* ---------- Liên hệ ---------- */
    'address'  => 'Phường Sóc Trăng, TP. Cần Thơ',
    'phone'    => '0909 123 456',
    'email'    => 'lienhe@bidahoangnhi.vn',
    'hours'    => 'Thứ 2 – Chủ nhật: 8:00 – 21:00',

    /* ---------- Mạng xã hội ---------- */
    'facebook' => '/lien-he.php',
    'youtube'  => '/lien-he.php',
];
