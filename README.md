# Website quản lý bán hàng và phân phối — Bida Hoàng Nhi

Đồ án môn **Phát triển ứng dụng Web**, Trường Đại học Trà Vinh.
Sinh viên thực hiện: **Huỳnh Yến Nhi**.

Website bán bàn bida, cơ bida và phụ kiện, kèm khu quản trị (sản phẩm, danh mục,
nhà cung cấp, khách hàng, nhân viên, đơn hàng, vận chuyển, tin tức).

## Công nghệ

| Thành phần | Phiên bản |
|---|---|
| PHP | 8.3 (Apache) |
| MySQL | 8.4 |
| Bootstrap | 5.3.8 |
| Font Awesome | 6.7.2 |
| Docker Compose | — |

## Yêu cầu

Chỉ cần cài **Docker Desktop** (đã bao gồm Docker Compose). Không cần cài PHP,
Apache hay MySQL riêng.

## Cách chạy

```bash
# 1. Tạo file cấu hình môi trường từ file mẫu
cp .env.example .env

# 2. Dựng và khởi động các container
docker compose up -d --build

# 3. Mở trình duyệt
#    Trang khách hàng:  http://localhost:8080/
#    Khu quản trị:      http://localhost:8080/admin/
```

Lần chạy đầu tiên mất khoảng 30–60 giây để MySQL khởi tạo. Nếu trình duyệt báo
lỗi kết nối cơ sở dữ liệu, đợi thêm vài giây rồi tải lại trang.

Xem log khi cần gỡ lỗi:

```bash
docker compose logs -f db
docker compose logs -f web
```

Dừng ứng dụng:

```bash
docker compose down
```

## Cơ chế nạp cơ sở dữ liệu

Đây là phần **quan trọng nhất** cần hiểu trước khi sửa dữ liệu.

`compose.yaml` gắn hai file SQL vào thư mục khởi tạo của MySQL:

```yaml
volumes:
  - ./database/schema.sql:/docker-entrypoint-initdb.d/01-schema.sql:ro
  - ./database/seed.sql:/docker-entrypoint-initdb.d/02-seed.sql:ro
```

- `schema.sql` — tạo **11 bảng** (categories, suppliers, products, product_images,
  customers, employees, shippers, orders, orderdetail, news, contact_messages).
  File này **không** chứa lệnh `INSERT`.
- `seed.sql` — nạp **dữ liệu mẫu** (3 danh mục, 2 nhà cung cấp, 3 sản phẩm kèm 6 ảnh,
  3 khách hàng, 2 nhân viên, 2 đơn vị vận chuyển, 3 đơn hàng và 4 dòng chi tiết).
  File này **không** chứa lệnh `CREATE`/`ALTER`.

Thứ tự nạp tuân thủ khoá ngoại:
`categories → suppliers → customers → employees → shippers → products →
product_images → orders → orderdetail`.

> **Lưu ý:** MySQL chỉ chạy các script trong `/docker-entrypoint-initdb.d/`
> **một lần duy nhất**, khi thư mục dữ liệu còn trống (tức là lần `up` đầu tiên).
> Vì vậy sửa `seed.sql` **không** làm thay đổi cơ sở dữ liệu đang chạy.

### Nạp lại cơ sở dữ liệu từ đầu

Khi đã sửa `schema.sql` hoặc `seed.sql` và muốn áp dụng, phải xoá volume dữ liệu:

```bash
docker compose down -v     # -v xoá volume mysql_data
docker compose up -d
```

> **Không chạy `seed.sql` hai lần.** File này không có lệnh `DELETE`/`TRUNCATE` ở đầu,
> nên nếu nạp lại lần thứ hai trên cơ sở dữ liệu đã có dữ liệu, MySQL sẽ dừng ở dòng
> `INSERT INTO products` với lỗi `1062 Duplicate entry 'SP001' for key
> 'products.ProductCode'` và để lại các bảng phía trên bị nhân đôi một nửa.
> Muốn nạp lại, luôn dùng `docker compose down -v` như trên.

**Cảnh báo:** `down -v` xoá toàn bộ dữ liệu đã nhập. Sao lưu trước nếu cần:

```bash
docker compose exec -T db sh -c \
  'mysqldump -u"$MYSQL_USER" -p"$MYSQL_PASSWORD" --single-transaction ql_banhang' \
  > backup.sql
```

## Cấu trúc thư mục

```
.
├── compose.yaml            # Khai báo 2 service: web (PHP+Apache) và db (MySQL)
├── .env.example            # Mẫu biến môi trường, chép thành .env
├── database/
│   ├── schema.sql          # 11 lệnh CREATE TABLE
│   └── seed.sql            # Dữ liệu mẫu
├── docker/php/Dockerfile   # Image PHP 8.3 + Apache + mysqli
├── public/                 # Document root
│   ├── index.php           # Trang chủ
│   ├── products.php        # Danh sách sản phẩm
│   ├── product-detail.php  # Chi tiết sản phẩm
│   ├── cart.php            # Giỏ hàng
│   ├── checkout.php        # Thanh toán
│   ├── tin-tuc.php         # Tin tức
│   ├── login.php           # Đăng nhập / đăng ký khách hàng
│   ├── admin/              # Khu quản trị (8 phân hệ)
│   ├── assets/             # CSS, JS, hình ảnh giao diện
│   └── uploads/products/   # Ảnh sản phẩm do người dùng tải lên
└── src/
    ├── config/database.php # Kết nối MySQLi, đọc biến môi trường
    ├── config/brand.php    # Thông tin thương hiệu
    ├── config/session.php  # Khởi tạo session
    └── includes/           # Header, footer, hàm dùng chung
```

## Biến môi trường

Khai báo trong `.env` (file này **không** đưa lên Git):

| Biến | Giá trị mẫu | Ý nghĩa |
|---|---|---|
| `MYSQL_ROOT_PASSWORD` | `root` | Mật khẩu tài khoản root của MySQL |
| `MYSQL_DATABASE` | `ql_banhang` | Tên cơ sở dữ liệu |
| `MYSQL_USER` | `appuser` | Tài khoản ứng dụng dùng để kết nối |
| `MYSQL_PASSWORD` | `apppassword` | Mật khẩu tài khoản ứng dụng |

## Ghi chú

Khu quản trị (`/admin/`) hiện **chưa có kiểm tra đăng nhập** — đây là hạn chế đã
được nêu rõ trong phần nhận xét và hướng phát triển của báo cáo.
