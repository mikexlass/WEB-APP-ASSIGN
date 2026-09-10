-- Disable foreign key checks during cleanup
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS Orders;
DROP TABLE IF EXISTS GoldProducts;
DROP TABLE IF EXISTS Customers;
SET FOREIGN_KEY_CHECKS = 1;

-- 1. Create Customers Table
CREATE TABLE Customers (
    customer_id INT AUTO_INCREMENT PRIMARY KEY,
    customer_name VARCHAR(100) NOT NULL
);

-- 2. Create GoldProducts Table
CREATE TABLE GoldProducts (
    product_id INT AUTO_INCREMENT PRIMARY KEY,
    product_name VARCHAR(100) NOT NULL,
    product_type VARCHAR(50) NOT NULL,
    weight_g DECIMAL(10, 2) NOT NULL,
    purity INT NOT NULL -- e.g., 999, 916, 750
);

-- 3. Create Orders Table
CREATE TABLE Orders (
    order_id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    order_date DATE NOT NULL,
    FOREIGN KEY (customer_id) REFERENCES Customers(customer_id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES GoldProducts(product_id) ON DELETE CASCADE
);

-- Insert 3 Records into Customers
INSERT INTO Customers (customer_id, customer_name) VALUES
(1, 'Alice Tan'),
(2, 'Badrul Hisham'),
(3, 'Ching Wei');

-- Insert 3 Records into GoldProducts
INSERT INTO GoldProducts (product_id, product_name, product_type, weight_g, purity) VALUES
(1, '999 Fine Gold Bar', 'Bar', 10.00, 999),
(2, '916 Gold Bangle', 'Jewellery', 5.00, 916),
(3, '750 Gold Ring', 'Jewellery', 3.50, 750);

-- Insert 3 Records into Orders
INSERT INTO Orders (order_id, customer_id, product_id, quantity, order_date) VALUES
(101, 1, 1, 1, '2026-09-01'),
(102, 2, 2, 2, '2026-09-03'),
(103, 3, 3, 1, '2026-09-05');

-- SQL JOIN Query Requirement
SELECT 
    o.order_id,
    c.customer_name,
    p.product_name,
    p.weight_g AS product_weight_g,
    p.purity AS gold_purity,
    o.quantity,
    o.order_date
FROM Orders o
JOIN Customers c ON o.customer_id = c.customer_id
JOIN GoldProducts p ON o.product_id = p.product_id;