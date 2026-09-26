<?php

/*
 * Các hàm dùng chung.
 *
 * Được nạp bằng require_once ở đầu trang khi cần,
 * không nạp sẵn trong header.php vì có trang cần hàm
 * trước khi include header (ví dụ để tính toán dữ liệu).
 */

if (!function_exists('vnd')) {
    /**
     * Định dạng tiền Việt Nam: 1234567 -> "1.234.567 đ"
     */
    function vnd(float $amount): string
    {
        return number_format($amount, 0, ',', '.') . ' đ';
    }
}

if (!function_exists('format_date')) {
    /**
     * Đổi ngày ISO (Y-m-d) sang d/m/Y. Trả về chuỗi rỗng nếu không hợp lệ.
     */
    function format_date(?string $date): string
    {
        if ($date === null || $date === '') {
            return '';
        }

        $timestamp = strtotime($date);

        return $timestamp === false ? '' : date('d/m/Y', $timestamp);
    }
}

if (!function_exists('format_datetime')) {
    /**
     * Đổi ngày giờ ISO (Y-m-d H:i:s) sang d/m/Y H:i.
     * Dùng cho các cột kiểu DATETIME, ví dụ orders.OrderDate.
     */
    function format_datetime(?string $datetime): string
    {
        if ($datetime === null || $datetime === '') {
            return '';
        }

        $timestamp = strtotime($datetime);

        return $timestamp === false ? '' : date('d/m/Y H:i', $timestamp);
    }
}

if (!function_exists('format_quantity')) {
    /**
     * Định dạng số lượng: bỏ phần thập phân không cần thiết.
     */
    function format_quantity(float $quantity): string
    {
        return number_format($quantity, 0, ',', '.');
    }
}
