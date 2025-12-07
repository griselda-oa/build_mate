<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Invoice <?= \App\View::e($invoice_no) ?></title>
    <style>
        body { font-family: Arial, sans-serif; }
        .header { text-align: center; margin-bottom: 30px; }
        .invoice-info { margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .total { text-align: right; font-weight: bold; }
        .escrow-note { background-color: #fff3cd; padding: 10px; border-left: 4px solid #ffc107; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Build Mate Ghana Ltd</h1>
        <h2>INVOICE</h2>
        <p>Invoice No: <?= \App\View::e($invoice_no) ?></p>
    </div>
    
    <div class="invoice-info">
        <p><strong>Order Date:</strong> <?= date('F d, Y', strtotime($order['created_at'])) ?></p>
        <p><strong>Date:</strong> <?= date('F d, Y', strtotime($order['created_at'])) ?></p>
        <p><strong>Buyer:</strong> <?= \App\View::e($order['buyer_name'] ?? '') ?></p>
    </div>
    
    <table>
        <thead>
            <tr>
                <th>Product</th>
                <th>Quantity</th>
                <th>Unit Price</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($order['items'] as $item): ?>
                <tr>
                    <td><?= \App\View::e($item['product_name']) ?></td>
                    <td><?= $item['qty'] ?></td>
                    <td><?= \App\Money::format($item['price_cents'], $order['currency']) ?></td>
                    <td><?= \App\Money::format($item['qty'] * $item['price_cents'], $order['currency']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3" class="total">Total:</td>
                <td><?= \App\Money::format($order['total_cents'], $order['currency']) ?></td>
            </tr>
        </tfoot>
    </table>
    
    <?php if (!empty($order['escrow_held']) || !empty($order['paystack_secure_held'])): ?>
        <div class="escrow-note">
            <strong>Paystack Secure Payment Notice:</strong> Payment for this order is held securely by Paystack and will be released to the supplier upon buyer confirmation of delivery.
        </div>
    <?php endif; ?>
    
    <div style="margin-top: 30px; font-size: 12px; color: #666;">
        <p>Thank you for your business!</p>
        <p>Build Mate Ghana Ltd - Your trusted partner for construction materials</p>
    </div>
</body>
</html>

