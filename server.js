const express = require('express');
const axios = require('axios');
const mysql = require('mysql2/promise');
require('dotenv').config();

const app = express();
const PORT = process.env.PORT || 3000;

app.use(express.static('public'));

// Database connection pool setup
const pool = mysql.createPool({
    host: process.env.DB_HOST || 'localhost',
    user: process.env.DB_USER || 'root',
    password: process.env.DB_PASSWORD || '',
    database: process.env.DB_NAME || 'jewell_shop',
    waitForConnections: true,
    connectionLimit: 10,
    queueLimit: 0
});

app.get('/api/orders-summary', async (req, res) => {
    try {
        // 1. Fetch Gold Spot Price from MetalpriceAPI (API 1)
        const metalPriceRes = await axios.get(`https://api.metalpriceapi.com/v1/latest`, {
            params: {
                api_key: process.env.METALPRICE_API_KEY,
                base: 'USD',
                currencies: 'XAU'
            }
        });

        // XAU rate in USD per troy ounce = 1 / rate_of_XAU_in_USD_base
        const xauRateInUSD = 1 / metalPriceRes.data.rates.XAU;
        const metalPriceTimestamp = new Date(metalPriceRes.data.timestamp * 1000).toISOString();

        // 2. Fetch USD-to-MYR Rate from AbstractAPI (API 2)
        const exchangeRateRes = await axios.get(`https://exchange-rates.abstractapi.com/v1/live/`, {
            params: {
                api_key: process.env.ABSTRACT_API_KEY,
                base: 'USD',
                target: 'MYR'
            }
        });

        const usdToMyrRate = exchangeRateRes.data.exchange_rates.MYR;
        const exchangeRateTimestamp = exchangeRateRes.data.last_updated || new Date().toISOString();

        // 3. Perform Calculations
        // Formula: Pure-gold price (MYR/g) = Gold spot price (USD/oz) * USD-to-MYR rate / 31.1035
        const pureGoldPricePerGramMYR = (xauRateInUSD * usdToMyrRate) / 31.1035;

        // 4. Fetch Database Joined Orders
        const [rows] = await pool.query(`
            SELECT 
                o.order_id,
                c.customer_name,
                p.product_name,
                p.weight_g,
                p.purity,
                o.quantity,
                o.order_date
            FROM Orders o
            JOIN Customers c ON o.customer_id = c.customer_id
            JOIN GoldProducts p ON o.product_id = p.product_id
        `);

        // 5. Calculate intrinsic value for each order record
        const processedOrders = rows.map(order => {
            const totalWeightGrams = Number(order.weight_g) * Number(order.quantity);
            const purityFactor = Number(order.purity) / 1000;
            const estimatedGoldValueMYR = pureGoldPricePerGramMYR * totalWeightGrams * purityFactor;

            return {
                order_id: order.order_id,
                customer_name: order.customer_name,
                product_name: order.product_name,
                weight_g: Number(order.weight_g).toFixed(2),
                purity: order.purity,
                quantity: order.quantity,
                total_weight_g: totalWeightGrams.toFixed(2),
                order_date: new Date(order.order_date).toISOString().split('T')[0],
                estimated_gold_value_myr: estimatedGoldValueMYR.toFixed(2)
            };
        });

        // Response Data
        res.json({
            api_1_info: {
                provider_name: 'MetalpriceAPI',
                label: 'latest available gold spot price',
                gold_price_usd_per_oz: xauRateInUSD.toFixed(2),
                source_response_field: 'rates.XAU',
                update_timestamp: metalPriceTimestamp
            },
            api_2_info: {
                provider_name: 'AbstractAPI',
                label: 'latest available USD-to-MYR exchange rate',
                usd_to_myr_rate: usdToMyrRate.toFixed(4),
                base_currency: 'USD',
                target_currency: 'MYR',
                source_response_field: 'exchange_rates.MYR',
                update_timestamp: exchangeRateTimestamp
            },
            pure_gold_price_myr_per_gram: pureGoldPricePerGramMYR.toFixed(2),
            orders: processedOrders
        });

    } catch (error) {
        console.error('API or DB Error:', error.message);
        res.status(500).json({ error: 'Failed to process request', details: error.message });
    }
});

app.listen(PORT, () => console.log(`Server running on http://localhost:${PORT}`));
