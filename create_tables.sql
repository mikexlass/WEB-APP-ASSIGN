-- =====================================================================
-- Gold Jewellery Consumer Web APIs Integration
-- Database: gold_jewellery
-- =====================================================================

CREATE DATABASE IF NOT EXISTS gold_jewellery;
USE gold_jewellery;

DROP TABLE IF EXISTS Orders;
DROP TABLE IF EXISTS GoldProducts;
DROP TABLE IF EXISTS Customers;

-- ---------------------------------------------------------------------
-- Table: Customers
-- ---------------------------------------------------------------------
CREATE TABLE Customers (
    customer_id    INT AUTO_INCREMENT PRIMARY KEY,
    customer_name  VARCHAR(100) NOT NULL
);

-- ---------------------------------------------------------------------
-- Table: GoldProducts
-- ---------------------------------------------------------------------
CREATE TABLE GoldProducts (
    product_id     INT AUTO_INCREMENT PRIMARY KEY,
    product_name   VARCHAR(100) NOT NULL,
    product_type   VARCHAR(50)  NOT NULL,
    weight_g       DECIMAL(8,2) NOT NULL,
    purity         INT          NOT NULL   -- e.g. 999, 916, 750
);

-- ---------------------------------------------------------------------
-- Table: Orders
-- customer_id and product_id are foreign keys linking back to
-- Customers and GoldProducts respectively.
-- ---------------------------------------------------------------------
CREATE TABLE Orders (
    order_id       INT AUTO_INCREMENT PRIMARY KEY,
    customer_id    INT NOT NULL,
    product_id     INT NOT NULL,
    quantity       INT NOT NULL,
    order_date     DATE NOT NULL,
    CONSTRAINT fk_orders_customer
        FOREIGN KEY (customer_id) REFERENCES Customers(customer_id),
    CONSTRAINT fk_orders_product
        FOREIGN KEY (product_id) REFERENCES GoldProducts(product_id)
);

-- =====================================================================
-- Sample data (THREE records per table)
-- =====================================================================

INSERT INTO Customers (customer_name) VALUES
('Aisyah Rahman'),
('Wei Ming Tan'),
('Priya Devan');

INSERT INTO GoldProducts (product_name, product_type, weight_g, purity) VALUES
('Classic Wedding Band', 'Ring',      5.00, 916),
('Rope Chain Necklace',  'Necklace', 15.50, 999),
('Hoop Earrings',        'Earring',   3.20, 750);

INSERT INTO Orders (customer_id, product_id, quantity, order_date) VALUES
(1, 1, 2, '2026-08-01'),
(2, 2, 1, '2026-08-03'),
(3, 3, 3, '2026-08-05');

-- =====================================================================
-- SQL JOIN Query (Section 3)
-- Combines Customers, GoldProducts and Orders via their foreign keys.
-- =====================================================================

SELECT
    o.order_id,
    c.customer_name,
    p.product_name,
    p.weight_g   AS product_weight_g,
    p.purity     AS gold_purity,
    o.quantity,
    o.order_date
FROM Orders o
JOIN Customers c    ON o.customer_id = c.customer_id
JOIN GoldProducts p ON o.product_id  = p.product_id
ORDER BY o.order_id;
