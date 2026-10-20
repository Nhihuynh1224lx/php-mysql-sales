SET NAMES utf8mb4;

-- =====================================================================
-- Dữ liệu mẫu cho cửa hàng Bida Hoàng Nhi
-- Thứ tự nạp tuân thủ ràng buộc khoá ngoại:
--   categories -> suppliers -> customers -> employees -> shippers
--   -> products -> product_images -> orders -> orderdetail
-- =====================================================================

INSERT INTO categories (CategoryName, Description)
VALUES
    ('Bàn bida', 'Bàn bida các loại'),
    ('Cơ bida', 'Cơ bida từ loại thường đến chuyên nghiệp'),
    ('Bao cơ bida', 'Bao cơ các loại');

INSERT INTO suppliers
    (SupplierName, ContactName, Address, City, PostalCode, Country, Phone)
VALUES
    ('Thế giới Bida Hoàng Royal', 'Nguyễn Văn An',
     'Số D17/4A Đinh Đức Thiện, Ấp 4, Xã Bình Chánh, Huyện Bình Chánh',
     'TP. Hồ Chí Minh', '700000', 'Việt Nam', '0901234567'),
    ('Xưởng chuyên Cung cấp cơ bida', 'Trần Minh Bình',
     '22 Lý Chiêu Hoàng, Phường 10, Quận 6',
     'TP. Hồ Chí Minh', '550000', 'Việt Nam', '0912345678');

INSERT INTO customers
    (CustomerName, ContactName, Address, City, PostalCode, Country)
VALUES
    ('Nguyễn Minh Anh', 'Nguyễn Minh Anh',
     '12 Nguyễn Trãi', 'TP. Hồ Chí Minh', '700000', 'Việt Nam'),
    ('Trần Thanh Hà', 'Trần Thanh Hà',
     '35 Hùng Vương', 'Cần Thơ', '900000', 'Việt Nam'),
    ('Lê Quốc Bảo', 'Lê Quốc Bảo',
     '18 Lê Lợi', 'Trà Vinh', '870000', 'Việt Nam');

INSERT INTO employees
    (LastName, FirstName, BirthDate, Photo, Notes)
VALUES
    ('Nguyễn', 'Hoàng Nam', '1995-05-15', NULL, 'Nhân viên bán hàng'),
    ('Trần', 'Thúy An', '1997-09-20', NULL, 'Nhân viên chăm sóc khách hàng');

INSERT INTO shippers (ShipperName, Phone)
VALUES
    ('Giao hàng nhanh', '19001001'),
    ('Giao hàng tiết kiệm', '19001002');

INSERT INTO products
    (ProductCode, ProductName, Description, Unit, Price,
     StockQuantity, IsActive, SupplierID, CategoryID)
VALUES
    ('SP001', 'Bàn bida A9022 Việt',
     'Kích thước 1m53x2.8\nMàu sắc: Trắng xám, Đen, Xám tất\n* Giá bao gồm thi công lắp đặt *',
     'Cái', 14000000.00, 6, TRUE, 1, 1),
    ('BANTND-01', 'Bàn Tonardo 9017',
     'Kích thước bì: 2m9x1m65\nKích thước lòng bàn: chuẩn 2m55x1m57\nĐá 3 tấm TaiWan phẳng đến 99%',
     'Chiếc', 30000000.00, 15, TRUE, 1, 1),
    ('JOSSI-01', 'Gậy Joss I',
     'Ngọn: Thuần mộc\nSize ngọn: 13mm',
     'Chiếc', 990000.00, 48, TRUE, 2, 2);

INSERT INTO product_images
    (ProductID, ImageFile, AltText, IsPrimary, SortOrder)
VALUES
    (1, 'product-b8e86f5b7624404c.png', 'Bàn bida A9022 Việt - ảnh chính', TRUE, 1),
    (1, 'product-211292520a4d1c2b.png', 'Bàn bida A9022 Việt - ảnh 2', FALSE, 2),
    (2, 'product-8da576828a947e86.png', 'Bàn Tonardo 9017 - ảnh chính', TRUE, 1),
    (2, 'product-edf065a9fc3973e6.png', 'Bàn Tonardo 9017 - ảnh 2', FALSE, 2),
    (3, 'product-e0a33e59ec18a036.png', 'Gậy Joss I - ảnh chính', TRUE, 1),
    (3, 'product-0369b1b065d2289b.png', 'Gậy Joss I - ảnh 2', FALSE, 2);

INSERT INTO orders (OrderDate, TotalAmount, Status, CustomerID, EmployeeID, ShipperID)
VALUES
    ('2026-08-10', 15980000.00, 'Completed', 1, 1, 1),
    ('2026-08-11', 30000000.00, 'Shipping',  2, 2, 2),
    ('2026-08-12',  1980000.00, 'Pending',   3, 1, 1);

INSERT INTO orderdetail (Quantity, UnitPrice, OrderID, ProductID)
VALUES
    (1, 14000000.00, 1, 1),
    (2,   990000.00, 1, 3),
    (1, 30000000.00, 2, 2),
    (2,   990000.00, 3, 3);
