SET NAMES utf8mb4;

CREATE TABLE categories (
    CategoryID INT AUTO_INCREMENT PRIMARY KEY,
    CategoryName VARCHAR(200) NOT NULL,
    Description TEXT
) CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

CREATE TABLE suppliers (
    SupplierID INT AUTO_INCREMENT PRIMARY KEY,
    SupplierName VARCHAR(200) NOT NULL,
    ContactName VARCHAR(100),
    Address VARCHAR(200),
    City VARCHAR(100),
    PostalCode VARCHAR(20),
    Country VARCHAR(100),
    Phone VARCHAR(20)
) CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

CREATE TABLE customers (
    CustomerID INT AUTO_INCREMENT PRIMARY KEY,
    CustomerName VARCHAR(100) NOT NULL,
    ContactName VARCHAR(100),
    Address VARCHAR(200),
    City VARCHAR(100),
    PostalCode VARCHAR(20),
    Country VARCHAR(100),
    Phone VARCHAR(20),
    Email VARCHAR(255) NULL,
    PasswordHash VARCHAR(255) NULL,
    CONSTRAINT uq_customers_email UNIQUE (Email)
) CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

CREATE TABLE employees (
    EmployeeID INT AUTO_INCREMENT PRIMARY KEY,
    LastName VARCHAR(50) NOT NULL,
    FirstName VARCHAR(50) NOT NULL,
    BirthDate DATE,
    Photo VARCHAR(255),
    Notes TEXT
) CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

CREATE TABLE shippers (
    ShipperID INT AUTO_INCREMENT PRIMARY KEY,
    ShipperName VARCHAR(100) NOT NULL,
    Phone VARCHAR(20)
) CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

CREATE TABLE products (
    ProductID INT AUTO_INCREMENT PRIMARY KEY,
    ProductCode VARCHAR(50) NOT NULL UNIQUE,
    ProductName VARCHAR(255) NOT NULL,
    Description TEXT,
    Unit VARCHAR(20),
    Price DECIMAL(12,2) NOT NULL DEFAULT 0,
    StockQuantity INT NOT NULL DEFAULT 0,
    IsActive BOOLEAN NOT NULL DEFAULT TRUE,
    SupplierID INT NOT NULL,
    CategoryID INT NOT NULL,

    CONSTRAINT chk_products_price CHECK (Price >= 0),
    CONSTRAINT chk_products_stock CHECK (StockQuantity >= 0),
    CONSTRAINT fk_products_supplier
        FOREIGN KEY (SupplierID) REFERENCES suppliers(SupplierID),
    CONSTRAINT fk_products_category
        FOREIGN KEY (CategoryID) REFERENCES categories(CategoryID)
) CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

CREATE TABLE product_images (
    ProductImageID INT AUTO_INCREMENT PRIMARY KEY,
    ProductID INT NOT NULL,
    ImageFile VARCHAR(255) NOT NULL,
    AltText VARCHAR(255),
    IsPrimary BOOLEAN NOT NULL DEFAULT FALSE,
    SortOrder INT NOT NULL DEFAULT 0,

    CONSTRAINT uq_product_image UNIQUE (ProductID, ImageFile),
    CONSTRAINT chk_product_image_sort CHECK (SortOrder >= 0),
    CONSTRAINT fk_product_images_product
        FOREIGN KEY (ProductID)
        REFERENCES products(ProductID)
        ON DELETE CASCADE
) CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

CREATE TABLE orders (
    OrderID INT AUTO_INCREMENT PRIMARY KEY,

    OrderDate DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    TotalAmount DECIMAL(12,2) NOT NULL DEFAULT 0,
    Status VARCHAR(30) NOT NULL DEFAULT 'Pending',

    CustomerID INT NOT NULL,
    EmployeeID INT,
    ShipperID INT,

    CONSTRAINT chk_orders_total
        CHECK (TotalAmount >= 0),

    CONSTRAINT fk_orders_customer
        FOREIGN KEY (CustomerID) REFERENCES customers(CustomerID),
    CONSTRAINT fk_orders_employee
        FOREIGN KEY (EmployeeID) REFERENCES employees(EmployeeID),
    CONSTRAINT fk_orders_shipper
        FOREIGN KEY (ShipperID) REFERENCES shippers(ShipperID)
) CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

CREATE TABLE orderdetail (
    OrderDetailID INT AUTO_INCREMENT PRIMARY KEY,
    Quantity INT NOT NULL,
    UnitPrice DECIMAL(12,2) NOT NULL,
    OrderID INT NOT NULL,
    ProductID INT NOT NULL,

    CONSTRAINT chk_orderdetail_quantity CHECK (Quantity > 0),
    CONSTRAINT chk_orderdetail_unitprice CHECK (UnitPrice >= 0),
    CONSTRAINT fk_orderdetail_order
        FOREIGN KEY (OrderID)
        REFERENCES orders(OrderID)
        ON DELETE CASCADE,
    CONSTRAINT fk_orderdetail_product
        FOREIGN KEY (ProductID) REFERENCES products(ProductID)
) CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- Hai bang duoi day phuc vu trang Tin tuc va trang Lien he cua cua hang.
-- Khong co khoa ngoai: bai viet va tin nhan lien he doc lap voi cac bang khac.
-- ---------------------------------------------------------------------------

CREATE TABLE news (
    NewsID INT AUTO_INCREMENT PRIMARY KEY,
    Title VARCHAR(255) NOT NULL,
    Summary VARCHAR(500),
    Content TEXT NOT NULL,
    ImageFile VARCHAR(255) NULL,
    Category VARCHAR(100) NOT NULL DEFAULT 'Tin tức',
    Author VARCHAR(100) NULL,
    IsPublished TINYINT(1) NOT NULL DEFAULT 1,
    PublishedAt DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

CREATE TABLE contact_messages (
    ContactID INT AUTO_INCREMENT PRIMARY KEY,
    FullName VARCHAR(100) NOT NULL,
    Email VARCHAR(255) NOT NULL,
    Phone VARCHAR(20) NOT NULL,
    Subject VARCHAR(150) NOT NULL,
    Message TEXT NOT NULL,
    IsRead TINYINT(1) NOT NULL DEFAULT 0,
    CreatedAt DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;
