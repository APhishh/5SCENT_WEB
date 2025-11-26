#!/bin/bash
# Test the POST /api/orders endpoint

# First, get a valid token by checking existing orders
curl -X GET "http://localhost:8000/api/orders" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json" \
  2>&1

echo ""
echo "---"
echo ""

# Now test creating an order with cart IDs
curl -X POST "http://localhost:8000/api/orders" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "cart_ids": [1, 2],
    "shipping_address": "123 Main St, Kelurahan, Kota, Provinsi 12345",
    "payment_method": "QRIS"
  }' \
  2>&1
