<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shipping Label - Order #{{ $order->order_number }}</title>
    <style>
        @media print {
            @page {
                size: 4in 6in;
                margin: 0.25in;
            }
            body {
                margin: 0;
                padding: 0;
            }
            .no-print {
                display: none !important;
            }
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Arial', 'Helvetica', sans-serif;
            font-size: 10px;
            line-height: 1.3;
            color: #000;
            background: #fff;
            padding: 10px;
        }
        .label-container {
            max-width: 4in;
            margin: 0 auto;
            border: 2px solid #000;
            padding: 8px;
        }
        .header {
            border-bottom: 2px solid #000;
            padding-bottom: 6px;
            margin-bottom: 6px;
        }
        .order-number {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 4px;
        }
        .tracking-number {
            font-size: 11px;
            font-weight: bold;
            color: #333;
            margin-top: 4px;
        }
        .section {
            margin-bottom: 8px;
        }
        .section-title {
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 3px;
            border-bottom: 1px solid #ccc;
            padding-bottom: 2px;
        }
        .address-block {
            font-size: 10px;
            line-height: 1.4;
        }
        .address-name {
            font-weight: bold;
            font-size: 11px;
            margin-bottom: 2px;
        }
        .address-line {
            margin-bottom: 1px;
        }
        .items-section {
            font-size: 9px;
        }
        .item-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2px;
            padding-bottom: 2px;
            border-bottom: 1px dotted #ccc;
        }
        .item-name {
            flex: 1;
            font-weight: bold;
        }
        .item-qty {
            margin: 0 8px;
            font-weight: bold;
        }
        .barcode-section {
            text-align: center;
            margin-top: 8px;
            padding-top: 6px;
            border-top: 2px solid #000;
        }
        .barcode {
            font-family: 'Courier New', monospace;
            font-size: 18px;
            font-weight: bold;
            letter-spacing: 2px;
            margin: 4px 0;
        }
        .footer {
            margin-top: 6px;
            padding-top: 6px;
            border-top: 1px solid #ccc;
            font-size: 8px;
            text-align: center;
            color: #666;
        }
        .print-button {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 12px 24px;
            background: #f7951d;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: bold;
            cursor: pointer;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
            z-index: 1000;
        }
        .print-button:hover {
            background: #d67a0f;
        }
        .return-button {
            position: fixed;
            top: 70px;
            right: 20px;
            padding: 12px 24px;
            background: #64748b;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: bold;
            cursor: pointer;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
            z-index: 1000;
            text-decoration: none;
            display: inline-block;
        }
        .return-button:hover {
            background: #475569;
        }
        @media print {
            .print-button,
            .return-button {
                display: none;
            }
        }
    </style>
</head>
<body>
    <!-- Print and Return Buttons -->
    <button class="print-button no-print" onclick="window.print()">🖨️ Print Label</button>
    <a href="{{ route('admin.orders.show', $order) }}" class="return-button no-print">← Back to Order</a>

    <div class="label-container">
        <!-- Header -->
        <div class="header">
            <div class="order-number">ORDER #{{ $order->order_number }}</div>
            @if($order->workshop_order_id)
            <div style="font-size: 9px; color: #666;">Workshop Order: {{ $order->workshop_order_id }}</div>
            @endif
            @if($order->tracking_number)
            <div class="tracking-number">TRACKING: {{ $order->tracking_number }}</div>
            @endif
        </div>

        <!-- Shipping Address -->
        <div class="section">
            <div class="section-title">Ship To:</div>
            <div class="address-block">
                <div class="address-name">{{ $shippingAddress['name'] ?? 'N/A' }}</div>
                @if(isset($shippingAddress['email']))
                <div class="address-line">{{ $shippingAddress['email'] }}</div>
                @endif
                @if(isset($shippingAddress['phone']))
                <div class="address-line">Tel: {{ $shippingAddress['phone'] }}</div>
                @endif
                <div class="address-line">
                    {{ $shippingAddress['address'] ?? '' }}
                    @if(isset($shippingAddress['address2']))
                    , {{ $shippingAddress['address2'] }}
                    @endif
                </div>
                <div class="address-line">
                    {{ $shippingAddress['city'] ?? '' }}
                    @if(isset($shippingAddress['state']))
                    , {{ $shippingAddress['state'] }}
                    @endif
                    {{ $shippingAddress['postal_code'] ?? '' }}
                </div>
                <div class="address-line">
                    @php
                        $countries = [
                            'US' => 'United States',
                            'GB' => 'United Kingdom',
                            'CA' => 'Canada',
                            'AU' => 'Australia',
                            'VN' => 'Vietnam',
                            'FR' => 'France',
                            'DE' => 'Germany',
                            'IT' => 'Italy',
                            'ES' => 'Spain',
                            'NL' => 'Netherlands',
                            'BE' => 'Belgium',
                            'AT' => 'Austria',
                            'SE' => 'Sweden',
                            'NO' => 'Norway',
                            'DK' => 'Denmark',
                            'FI' => 'Finland',
                            'PL' => 'Poland',
                            'CZ' => 'Czech Republic',
                            'IE' => 'Ireland',
                            'PT' => 'Portugal',
                            'GR' => 'Greece',
                            'CH' => 'Switzerland',
                        ];
                        $countryCode = $shippingAddress['country'] ?? '';
                        $countryName = $countries[$countryCode] ?? $countryCode;
                    @endphp
                    {{ $countryName }}
                </div>
            </div>
        </div>

        <!-- Order Items -->
        @if(count($items) > 0)
        <div class="section items-section">
            <div class="section-title">Items ({{ count($items) }})</div>
            @foreach($items as $item)
            <div class="item-row">
                <div class="item-name">{{ $item['product_name'] ?? 'N/A' }}</div>
                <div class="item-qty">Qty: {{ $item['quantity'] ?? 1 }}</div>
            </div>
            @if(isset($item['variant_name']))
            <div style="font-size: 8px; color: #666; margin-bottom: 3px; padding-left: 4px;">
                {{ $item['variant_name'] }}
            </div>
            @endif
            @endforeach
        </div>
        @endif

        <!-- Barcode Section -->
        <div class="barcode-section">
            <div class="barcode">*{{ $order->order_number }}*</div>
            @if($order->tracking_number)
            <div style="font-size: 9px; margin-top: 4px;">{{ $order->tracking_number }}</div>
            @endif
        </div>

        <!-- Footer -->
        <div class="footer">
            <div>Order Date: {{ $order->created_at->format('M d, Y') }}</div>
            @if($order->workshop)
            <div>Workshop: {{ $order->workshop->name }}</div>
            @endif
            @if(isset($apiRequest['shipping_method']))
            <div>Shipping: {{ ucfirst(str_replace('_', ' ', $apiRequest['shipping_method'])) }}</div>
            @endif
        </div>
    </div>

    <script>
        // Auto print when page loads (optional - can be removed if not needed)
        // window.onload = function() {
        //     setTimeout(function() {
        //         window.print();
        //     }, 500);
        // };
    </script>
</body>
</html>

