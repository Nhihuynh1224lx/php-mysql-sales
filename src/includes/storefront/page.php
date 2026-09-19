<?php

/*
 * Thành phần dùng chung cho các trang nội dung của storefront.
 *
 * 12 trang ở chân trang (giới thiệu, chính sách, hỗ trợ...) đều theo
 * cùng một khuôn: breadcrumb → dải tiêu đề → thân nội dung → dải kêu
 * gọi hành động. Gom vào đây để mỗi trang chỉ còn khai báo dữ liệu,
 * không lặp lại markup.
 *
 * Tất cả hàm ở đây CHỈ sinh HTML tĩnh. Không hàm nào truy vấn MySQL —
 * theo yêu cầu của đề bài, duy nhất trang tra cứu đơn hàng được đọc
 * dữ liệu.
 */

if (!function_exists('sf_breadcrumb')) {
    /**
     * Thanh điều hướng breadcrumb.
     *
     * @param array $trail Mảng [nhãn => href]. Phần tử cuối cùng là trang
     *                     hiện tại — truyền href bằng null để in ra <strong>.
     */
    function sf_breadcrumb(array $trail): string
    {
        $html = '<nav class="sf-breadcrumb" aria-label="Đường dẫn">'
            . '<a href="/shop/"><i class="bi bi-house-door"></i> Trang chủ</a>';
        $last = count($trail) - 1;

        foreach (array_values($trail) as $index => [$label, $href]) {
            $html .= '<i class="bi bi-chevron-right" aria-hidden="true"></i>';

            if ($index === $last || $href === null) {
                $html .= '<strong aria-current="page">' . htmlspecialchars($label) . '</strong>';
            } else {
                $html .= '<a href="' . htmlspecialchars($href) . '">'
                    . htmlspecialchars($label) . '</a>';
            }
        }

        return $html . '</nav>';
    }
}

if (!function_exists('sf_page_hero')) {
    /**
     * Dải tiêu đề đầu trang.
     *
     * @param array $options eyebrow, title, lead, meta (mảng [icon, nhãn, href?])
     */
    function sf_page_hero(array $options): string
    {
        $eyebrow = (string) ($options['eyebrow'] ?? '');
        $title   = (string) ($options['title'] ?? '');
        $lead    = (string) ($options['lead'] ?? '');
        $meta    = $options['meta'] ?? [];

        $html = '<header class="sf-page-hero">';

        if ($eyebrow !== '') {
            $html .= '<span class="sf-page-eyebrow">'
                . '<i class="bi ' . htmlspecialchars($options['icon'] ?? 'bi-info-circle') . '"></i>'
                . htmlspecialchars($eyebrow) . '</span>';
        }

        $html .= '<h1>' . htmlspecialchars($title) . '</h1>';

        if ($lead !== '') {
            $html .= '<p>' . htmlspecialchars($lead) . '</p>';
        }

        if (!empty($meta)) {
            $html .= '<div class="sf-page-hero-meta">';

            foreach ($meta as $item) {
                $icon  = (string) ($item['icon'] ?? 'bi-dot');
                $text  = (string) ($item['text'] ?? '');
                $href  = $item['href'] ?? null;
                $value = $link = null;

                if (isset($item['href'], $item['link'])) {
                    $link = '<a href="' . htmlspecialchars($item['href']) . '">'
                        . htmlspecialchars($item['link']) . '</a>';
                }

                $html .= '<span><i class="bi ' . htmlspecialchars($icon) . '"></i> '
                    . htmlspecialchars($text) . ($text !== '' && $link !== null ? ' ' : '')
                    . ($link ?? '') . '</span>';
            }

            $html .= '</div>';
        }

        return $html . '</header>';
    }
}

if (!function_exists('sf_doc_nav')) {
    /**
     * Mục lục dính bên trái. Mỗi mục trỏ tới id của một .sf-doc-block.
     *
     * @param array $items Mảng [id => nhãn]; kèm 'icon' tuỳ chọn
     */
    function sf_doc_nav(array $items, string $heading = 'Nội dung trang'): string
    {
        $html = '<aside class="sf-doc-nav"><h2>' . htmlspecialchars($heading) . '</h2><ul>';

        foreach ($items as $id => $item) {
            $label = is_array($item) ? (string) $item['label'] : (string) $item;
            $icon  = is_array($item) ? (string) ($item['icon'] ?? 'bi-chevron-right') : 'bi-chevron-right';

            $html .= '<li><a href="#' . htmlspecialchars((string) $id) . '">'
                . '<i class="bi ' . htmlspecialchars($icon) . '"></i>'
                . htmlspecialchars($label) . '</a></li>';
        }

        $html .= '</ul>';

        if ($shop = ($GLOBALS['sfShop'] ?? null)) {
            $html .= '<div class="sf-doc-hotline">Cần hỗ trợ thêm?'
                . '<strong>' . htmlspecialchars($shop['hotline']) . '</strong>'
                . '<a href="tel:' . htmlspecialchars(preg_replace('/\s+/', '', $shop['hotline']))
                . '"><i class="bi bi-telephone-fill"></i> Gọi ngay</a>'
                . '</div>';
        }

        return $html . '</aside>';
    }
}

if (!function_exists('sf_doc_block')) {
    /**
     * Một khối nội dung có tiêu đề.
     *
     * @param string $id       Neo để mục lục trỏ tới
     * @param string $title    Tiêu đề khối
     * @param string $body     HTML thân khối (đã dựng sẵn, không escape)
     * @param array  $options  icon, sub (dòng phụ)
     */
    function sf_doc_block(string $id, string $title, string $body, array $options = []): string
    {
        $icon = (string) ($options['icon'] ?? 'bi-dot');
        $sub  = (string) ($options['sub'] ?? '');

        return '<section class="sf-doc-block" id="' . htmlspecialchars($id) . '">'
            . '<div class="sf-doc-block-head">'
            . '<span class="sf-doc-ico"><i class="bi ' . htmlspecialchars($icon) . '"></i></span>'
            . '<div><h2>' . htmlspecialchars($title) . '</h2>'
            . ($sub !== '' ? '<p>' . htmlspecialchars($sub) . '</p>' : '')
            . '</div></div>'
            . $body
            . '</section>';
    }
}

if (!function_exists('sf_list')) {
    /**
     * Danh sách gạch đầu dòng có icon.
     *
     * @param array  $items Mảng chuỗi, hoặc [icon => ..., text => ...]
     * @param string $tone  '' | 'is-good' | 'is-bad'
     */
    function sf_list(array $items, string $tone = '', string $icon = 'bi-check2'): string
    {
        $class = 'sf-list' . ($tone !== '' ? ' ' . $tone : '');
        $html  = '<ul class="' . $class . '">';

        foreach ($items as $item) {
            $rowIcon = $icon;
            $text    = $item;

            if (is_array($item)) {
                $rowIcon = (string) ($item['icon'] ?? $icon);
                $text    = (string) ($item['text'] ?? '');
            }

            $html .= '<li><i class="bi ' . htmlspecialchars($rowIcon) . '"></i>'
                . '<span>' . $text . '</span></li>';
        }

        return $html . '</ul>';
    }
}

if (!function_exists('sf_steps')) {
    /**
     * Danh sách đánh số cho quy trình (bảo hành, đổi trả, mua hàng...).
     *
     * @param array $steps Mảng [tiêu đề => mô tả]
     */
    function sf_steps(array $steps): string
    {
        $html = '<ol class="sf-steps">';

        foreach ($steps as $title => $desc) {
            $html .= '<li><div><h3>' . htmlspecialchars((string) $title) . '</h3>'
                . '<p>' . $desc . '</p></div></li>';
        }

        return $html . '</ol>';
    }
}

if (!function_exists('sf_note')) {
    /**
     * Khối nhấn mạnh. $tone: '' | 'is-warn' | 'is-danger' | 'is-ok'
     */
    function sf_note(string $text, string $tone = '', string $icon = 'bi-info-circle-fill'): string
    {
        $class = 'sf-note' . ($tone !== '' ? ' ' . $tone : '');

        return '<div class="' . $class . '"><i class="bi ' . htmlspecialchars($icon) . '"></i>'
            . '<p>' . $text . '</p></div>';
    }
}

if (!function_exists('sf_page_nav')) {
    /**
     * Hai thẻ điều hướng sang trang nội dung khác.
     *
     * @param array|null $prev [nhãn mục, tiêu đề, href]
     * @param array|null $next
     */
    function sf_page_nav(?array $prev, ?array $next): string
    {
        if ($prev === null && $next === null) {
            return '';
        }

        $html = '<div class="sf-page-nav">';

        foreach ([['prev', $prev], ['next', $next]] as [$dir, $item]) {
            if ($item === null) {
                continue;
            }

            [$label, $title, $href] = $item;
            $icon = $dir === 'prev' ? 'bi-arrow-left' : 'bi-arrow-right';

            $html .= '<a href="' . htmlspecialchars($href) . '">'
                . '<i class="bi ' . $icon . '"></i><span>'
                . '<span class="sf-page-nav-label">' . htmlspecialchars($label) . '</span>'
                . '<span class="sf-page-nav-title">' . htmlspecialchars($title) . '</span>'
                . '</span></a>';
        }

        return $html . '</div>';
    }
}

if (!function_exists('sf_cta')) {
    /**
     * Dải kêu gọi hành động cuối trang nội dung.
     */
    function sf_cta(string $title, string $text): string
    {
        $shop = $GLOBALS['sfShop'] ?? ['hotline' => ''];

        return '<section class="sf-cta">'
            . '<div><h2>' . htmlspecialchars($title) . '</h2>'
            . '<p>' . htmlspecialchars($text) . '</p></div>'
            . '<div class="sf-cta-actions">'
            . '<a class="sf-btn sf-btn-light" href="/shop/">'
            . '<i class="bi bi-grid"></i> Xem sản phẩm</a>'
            . '<a class="sf-btn sf-btn-outline" href="/shop/lien-he.php">'
            . '<i class="bi bi-chat-dots"></i> Liên hệ tư vấn</a>'
            . '</div></section>';
    }
}

if (!function_exists('sf_table')) {
    /**
     * Bảng dữ liệu bọc trong khung cuộn ngang.
     *
     * @param array $head Mảng tiêu đề cột
     * @param array $rows Mảng các dòng, mỗi dòng là mảng ô (HTML thô)
     */
    function sf_table(array $head, array $rows, array $numericCols = []): string
    {
        $html = '<div class="sf-table-wrap"><table class="sf-table"><thead><tr>';

        foreach ($head as $index => $cell) {
            $isNum = in_array($index, $numericCols, true);
            $html .= '<th' . ($isNum ? ' class="is-num"' : '') . '>'
                . htmlspecialchars($cell) . '</th>';
        }

        $html .= '</tr></thead><tbody>';

        foreach ($rows as $row) {
            $html .= '<tr>';

            foreach ($row as $index => $cell) {
                $isNum = in_array($index, $numericCols, true);
                $html .= '<td' . ($isNum ? ' class="is-num"' : '') . '>' . $cell . '</td>';
            }

            $html .= '</tr>';
        }

        return $html . '</tbody></table></div>';
    }
}
