/*
 * Xử lý bảng mặt hàng trong đơn hàng.
 *
 * Dùng chung cho orders/create.php và orders/edit.php.
 * Yêu cầu trong trang phải có:
 *   - #itemsBody        : <tbody> chứa các dòng
 *   - #addItem          : nút thêm dòng
 *   - #itemRowTemplate  : <template> của một dòng
 *   - #grandTotal       : nơi hiển thị tổng tiền
 *
 * Mỗi dòng cần có: .product-select, .line-qty, .line-unit, .line-total, .remove-item
 * Mỗi <option> sản phẩm cần thuộc tính data-price.
 *
 * Lưu ý: đây chỉ là phần tính toán cho tiện nhập liệu.
 * Giá trị thật luôn được server tính lại từ bảng products khi lưu.
 */

(function () {
    var body = document.getElementById('itemsBody');
    var addButton = document.getElementById('addItem');
    var template = document.getElementById('itemRowTemplate');
    var totalElement = document.getElementById('grandTotal');

    if (!body || !addButton || !template || !totalElement) {
        return;
    }

    function formatVnd(value) {
        return value.toLocaleString('vi-VN') + ' đ';
    }

    function readPrice(row) {
        var select = row.querySelector('.product-select');

        if (!select || !select.value) {
            return 0;
        }

        var option = select.options[select.selectedIndex];

        if (!option) {
            return 0;
        }

        var price = parseFloat(option.getAttribute('data-price'));

        return isNaN(price) ? 0 : price;
    }

    function readQuantity(row) {
        var input = row.querySelector('.line-qty');
        var quantity = parseInt(input.value, 10);

        return isNaN(quantity) || quantity < 1 ? 0 : quantity;
    }

    function recalc() {
        var rows = body.querySelectorAll('.order-line');
        var grandTotal = 0;

        for (var i = 0; i < rows.length; i++) {
            var row = rows[i];
            var price = readPrice(row);
            var quantity = readQuantity(row);
            var lineTotal = price * quantity;

            row.querySelector('.line-unit').textContent =
                price > 0 ? formatVnd(price) : '—';

            row.querySelector('.line-total').textContent =
                formatVnd(lineTotal);

            grandTotal += lineTotal;
        }

        totalElement.textContent = formatVnd(grandTotal);

        var removeButtons = body.querySelectorAll('.remove-item');

        for (var j = 0; j < removeButtons.length; j++) {
            removeButtons[j].disabled = rows.length <= 1;
        }
    }

    function addRow() {
        body.appendChild(template.content.cloneNode(true));
        recalc();
    }

    addButton.addEventListener('click', addRow);

    body.addEventListener('click', function (event) {
        var button = event.target.closest('.remove-item');

        if (!button) {
            return;
        }

        var row = button.closest('.order-line');

        if (row && body.querySelectorAll('.order-line').length > 1) {
            row.remove();
            recalc();
        }
    });

    body.addEventListener('change', function (event) {
        if (event.target.classList.contains('product-select')) {
            recalc();
        }
    });

    body.addEventListener('input', function (event) {
        if (event.target.classList.contains('line-qty')) {
            recalc();
        }
    });

    recalc();
})();
