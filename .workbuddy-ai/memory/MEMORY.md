# MEMORY.md — php-mysql-sales (dự án PTUDW-2026)

Ghi chú dài hạn về dự án. Đọc file này trước khi bắt đầu công việc mới.

## Ngôn ngữ & phong cách

- **Trả lời bằng tiếng Việt.** Người dùng yêu cầu rõ ràng.
- Code, tên biến, đường dẫn, thuật ngữ kỹ thuật giữ nguyên dạng gốc.
- Viết gọn, đi thẳng vào việc.

## Bối cảnh dự án

Ứng dụng quản lý bán hàng (TECHSTORE) — PHP thuần + MySQL, chạy bằng Docker Compose.
Nhiều khả năng là **bài tập môn học** (`PTUDW` = Phát triển ứng dụng web), nên ưu tiên
giữ code đơn giản, dễ đọc, dễ giải thích — **không refactor mạnh tay**, không thêm
framework, không thêm dependency ngoài Bootstrap/Bootstrap Icons qua CDN.

## Kiến trúc

- `public/` là document root. `src/` mount vào `/var/www/src`.
- Mọi trang đều `require_once '/var/www/src/config/database.php'` rồi
  `header.php` → `navbar.php` → nội dung → `footer.php` (đường dẫn tuyệt đối trong container).
- Mỗi trang nội dung nằm trong `<div class="page">`.
- `delete.php` chỉ nhận POST và redirect, không include header/navbar.

## Quy ước code (giữ nguyên khi thêm trang mới)

- Đặt `$pageTitle` trước khi include `header.php`.
- Escape mọi output bằng `htmlspecialchars()`.
- Tiền tệ: `number_format($v, 0, ',', '.') . ' đ'`.
- Ghi dữ liệu: prepared statement (`bind_param`), không nối chuỗi SQL.
- Thao tác nhiều bước (sản phẩm + ảnh): dùng transaction, rollback cả file đã ghi.
- Upload: kiểm tra MIME thật bằng `finfo`, 1–4 ảnh, ≤ 2 MB, tên `product-<hex16>.<ext>`.
- Xoá: form POST + `onsubmit="return confirm(...)"`.

## Hệ thống giao diện admin (trạng thái THẬT, cập nhật 2026-09-20)

> Lưu ý: đoạn mô tả cũ về `public/assets/css/app.css` + sidebar 264px KHÔNG còn đúng.
> Thiết kế đó chỉ còn trong bản sao lưu `../php-mysql-sales - Copy`, không được dùng.

- **CSS nằm ở `public/assets/css/admin.css`** (tách riêng từ 2026-09-20). Toàn bộ màu sắc
  khai báo tập trung ở khối `:root` đầu file — đổi tông chỉ cần sửa các biến `--admin-*`.
- Thư viện: Bootstrap 5.3 (jsdelivr) + FontAwesome 6.7.2 (cdnjs) qua CDN.
  **Không** dùng Bootstrap Icons — đã chuyển hết 55 icon sang FontAwesome.
- `header.php` phải link `admin.css` **SAU** Bootstrap, nếu không sẽ không ghi đè được.
- Bố cục: `header.app-header` (logo + tên hệ thống bên trái, chip ngày bên phải)
  rồi `nav.app-navbar` (menu ngang). **Không có sidebar.**
- Footer: `footer.app-footer` (KHÔNG dùng `bg-dark` của Bootstrap). 4 cột: logo + giới thiệu,
  "Danh mục", "Đối tác", "Liên hệ" — đủ cả 8 module. Có dải màu nhấn 3px ở mép trên bằng
  `::before`. Màu dùng biến `--admin-footer-*` trong `admin.css`.
- **Menu (`navbar.php`) và footer (`footer.php`) đều viết theo mảng dữ liệu**
  (`$navItems` / `$footerColumns` + `$footerContacts`) — thêm bớt mục chỉ sửa 1 dòng.
- Tông màu "Indigo + Slate": header gradient `#312E81` → `#1E1B4B`; menu `#0F172A`;
  chữ menu `#CBD5E1`; icon `#94A3B8`; hover `rgba(99,102,241,.20)` + icon `#A5B4FC`;
  mục đang mở `#6366F1`.
- **Menu chỉnh ở mảng `$navItems`** trong `navbar.php` (`href` / `label` / `icon`) — thêm
  bớt mục chỉ sửa 1 dòng. Trạng thái đang mở do hàm `nav_is_active()` quyết định, thêm
  class `is-active`.
- `header.php` có `date_default_timezone_set('Asia/Ho_Chi_Minh')` — container chạy UTC.
- **Lưu ý quan trọng:** `.table-responsive` phải là `overflow-x: auto; overflow-y: hidden`.
  Nếu đặt `overflow: hidden` sẽ cắt mất cột thao tác trên màn hình nhỏ.

## Kiểm thử

- Bật stack: `docker compose up -d` → http://localhost:8080
- Kiểm tra cú pháp: `docker compose exec -T web bash -c 'find /var/www/html /var/www/src -name "*.php" -exec php -l {} \;'`
  (**đừng** dùng `xargs -n1 php -l`: nó dừng ở file lỗi đầu tiên và che các file lỗi sau)
- Chụp ảnh giao diện (không cần cài Chromium): dùng skill `headless-ui-verification`,
  hoặc gọi trực tiếp `~/.workbuddy-ai/skills/headless-ui-verification/scripts/screenshot.sh <url> <out.png> <WxH>`
- **Cạm bẫy:** `--window-size=412` cho ra viewport ~494px → ảnh bị crop, KHÔNG phải lỗi
  tràn ngang. Muốn kiểm tra tràn ngang thật thì so `documentElement.scrollWidth` với
  `clientWidth` qua `--dump-dom`.
- **Cạm bẫy:** Edge headless đôi khi treo, không ra output và không tạo file. Thêm
  `--user-data-dir=<thư mục profile mới>` là chạy lại được.
- **Cạm bẫy:** trên Windows, `curl -o /dev/null` báo lỗi ghi (exit 23) và làm gãy chuỗi
  `&&`. Dùng file tạm thật rồi xoá.
- PHP trong container **có sẵn `mbstring`** (dùng được `mb_substr`, `mb_strtoupper`).

## Thêm một module CRUD mới

1. Tạo `public/<tên>/` với `index.php`, `create.php`, `edit.php`, `delete.php`.
2. Mở đầu trang: đặt `$pageTitle`, rồi `require_once` `database.php` (+ `helpers.php` nếu cần tiền/ngày).
3. Nội dung bọc trong `<div class="page">`, mở đầu bằng `.page-head`
   (`.page-title` có icon + `.page-subtitle` + `.page-head-actions`).
4. `delete.php`: chỉ nhận POST, xử lý xong `header('Location: ...')` rồi `exit`.
5. Thêm mục vào `$navGroups` trong `src/includes/navbar.php`, và vào `$sectionLabels`
   nếu muốn breadcrumb hiển thị đúng.
6. Nếu cần bảng có nhiều dòng nhập liệu, xem mẫu ở `orders/create.php` +
   `public/assets/js/order-items.js`.

## Module đã có

Danh mục, Sản phẩm, Khách hàng, Nhà cung cấp, Nhà vận chuyển, **Đơn hàng**, **Nhân viên**.
Trang chủ là dashboard thật (thẻ thống kê, sản phẩm mới, danh mục, sắp hết hàng, đơn gần đây).

## Còn thiếu (chưa làm)

- `README.md` vẫn rỗng.
- Chưa có đăng nhập / phân quyền.
- `.env` không commit (chỉ có `.env.example`).
- Đơn hàng **không** tự trừ tồn kho — đây là quyết định có chủ ý để giữ đơn giản.
  Nếu muốn, phải xử lý cả chiều hoàn kho khi sửa/xoá đơn.
- `orderdetail` không có ràng buộc UNIQUE(OrderID, ProductID); trùng sản phẩm hiện
  được chặn ở tầng validate của PHP, không phải ở database.

---

## CẬP NHẬT 2026-09-20 — cấu trúc đã đổi, đọc kỹ trước khi sửa

Dự án đã được **tái cấu trúc** (chưa commit). Đường dẫn thật hiện tại:

- Module admin đã dời vào `public/admin/`: `categories`, `employees`, `products`, `shippers`.
  Còn **nguyên tại gốc** `public/`: `customers`, `orders`, `suppliers`.
- Partial admin đã dời vào `src/includes/admin/`: `header.php`, `navbar.php`, `footer.php`.
  → Mọi trang phải require `/var/www/src/includes/admin/...`.
  `src/includes/helpers.php` **vẫn ở nguyên** `/var/www/src/includes/helpers.php`.
- Vì vậy URL admin giờ là `/admin/categories/`, `/admin/products/`, `/admin/employees/`,
  `/admin/shippers/`; còn `/customers/`, `/orders/`, `/suppliers/` giữ nguyên.

**Quy ước đặt tên:** module `shippers` hiển thị là **"Nhân viên giao hàng"**
(không còn dùng "Nhà vận chuyển"). `$pageTitle` đã đổi; nhãn navbar/footer cũng phải đổi.

**Bẫy đã gặp khi tái cấu trúc (đừng lặp lại):**
- Find/replace `header('Location: ...')` rất dễ hỏng thành `href="..."` → lỗi parse.
  Sau mỗi lần đổi đường dẫn phải chạy `php -l` toàn bộ cây.
- Đổi chỗ thư mục mà quên đường dẫn **vật lý** trong code upload:
  `move_uploaded_file()` trỏ `/var/www/html/employees/upload/` trong khi thư mục thật là
  `/var/www/html/admin/employees/upload/` → upload luôn thất bại.
- Ảnh/JS dùng đường dẫn tuyệt đối (`src="/..."`) nên cũng phải sửa, không chỉ `href`.

**Trạng thái còn thiếu (cần người dùng quyết):**
- `public/index.php` hiện chỉ là trang placeholder 362 byte; **dashboard thật đã mất**.
  Bản dashboard 880 dòng còn trong `../php-mysql-sales - Copy/public/index.php`.
- Thư mục `public/shop/` (storefront) và `public/assets/css|js` đã bị xoá khỏi đĩa và
  **chưa từng được commit** → không khôi phục được từ git.
  `src/includes/storefront/*.php` còn nhưng là code chết (trỏ tới `/shop/*` không tồn tại).
- `public/assets/js/order-items.js` đã được khôi phục từ bản Copy (đã kiểm chứng chạy đúng).

**Sao lưu tham chiếu:** `D:\PTUDW-2026\php-mysql-sales - Copy\` (có dashboard + assets cũ).

