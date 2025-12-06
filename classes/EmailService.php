<?php

declare(strict_types=1);

namespace App;

/**
 * Email Service for sending notifications
 */
class EmailService
{
    private array $config;
    
    public function __construct()
    {
        $this->config = require __DIR__ . '/../settings/config.php';
    }
    
    /**
     * Send email using PHP mail() or SMTP
     */
    public function send(string $to, string $subject, string $body, ?string $from = null): bool
    {
        $from = $from ?? ($this->config['email']['from'] ?? 'noreply@buildmate.com');
        $fromName = $this->config['email']['from_name'] ?? 'Build Mate Ghana';
        
        $headers = [
            'MIME-Version: 1.0',
            'Content-type: text/html; charset=UTF-8',
            'From: ' . $fromName . ' <' . $from . '>',
            'Reply-To: ' . $from,
            'X-Mailer: PHP/' . phpversion()
        ];
        
        $fullHeaders = implode("\r\n", $headers);
        
        return mail($to, $subject, $body, $fullHeaders);
    }
    
    /**
     * Send order confirmation email
     */
    public function sendOrderConfirmation(int $orderId, array $order, string $buyerEmail, string $buyerName): bool
    {
        $subject = "Order Confirmation - Order #{$orderId}";
        
        // Get currency from order, default to GHS if not set
        $currency = $order['currency'] ?? 'GHS';
        $total = \App\Money::format($order['total_cents'] ?? 0, $currency);
        
        $body = $this->getOrderConfirmationTemplate($orderId, $order, $buyerName, $total, $currency);
        
        return $this->send($buyerEmail, $subject, $body);
    }
    
    /**
     * Send payment confirmation email
     */
    public function sendPaymentConfirmation(int $orderId, array $order, string $buyerEmail, string $buyerName, string $paymentReference): bool
    {
        $subject = "Payment Confirmed - Order #{$orderId}";
        
        // Get currency from order, default to GHS if not set
        $currency = $order['currency'] ?? 'GHS';
        $total = \App\Money::format($order['total_cents'] ?? 0, $currency);
        
        $body = $this->getPaymentConfirmationTemplate($orderId, $order, $buyerName, $total, $paymentReference);
        
        return $this->send($buyerEmail, $subject, $body);
    }
    
    /**
     * Send delivery update email
     */
    public function sendDeliveryUpdate(int $orderId, string $status, string $buyerEmail, string $buyerName, ?array $driverInfo = null): bool
    {
        $subject = "Delivery Update - Order #{$orderId}";
        
        $body = $this->getDeliveryUpdateTemplate($orderId, $status, $buyerName, $driverInfo);
        
        return $this->send($buyerEmail, $subject, $body);
    }
    
    /**
     * Order confirmation email template
     */
    private function getOrderConfirmationTemplate(int $orderId, array $order, string $buyerName, string $total, string $currency = 'GHS'): string
    {
        $itemsHtml = '';
        foreach ($order['items'] ?? [] as $item) {
            // Use order currency for all items (items don't have individual currency)
            $itemPriceCents = (int)($item['price_cents'] ?? $item['price'] ?? 0);
            $itemQty = (int)($item['qty'] ?? $item['quantity'] ?? 1);
            $itemTotal = \App\Money::format($itemPriceCents * $itemQty, $currency);
            $itemName = \App\View::e($item['product_name'] ?? $item['name'] ?? 'Product');
            $itemsHtml .= "
                <tr>
                    <td style='padding: 10px; border-bottom: 1px solid #eee;'>{$itemName}</td>
                    <td style='padding: 10px; border-bottom: 1px solid #eee; text-align: center;'>{$itemQty}</td>
                    <td style='padding: 10px; border-bottom: 1px solid #eee; text-align: right;'>{$itemTotal}</td>
                </tr>
            ";
        }
        
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #8b5a2b 0%, #6b4423 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
                .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
                .order-details { background: white; padding: 20px; border-radius: 8px; margin: 20px 0; }
                table { width: 100%; border-collapse: collapse; }
                .total { font-size: 1.2em; font-weight: bold; color: #8b5a2b; }
                .footer { text-align: center; margin-top: 20px; color: #666; font-size: 0.9em; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>Order Confirmed!</h1>
                    <p>Thank you for your order, {$buyerName}</p>
                </div>
                <div class='content'>
                    <p>Your order has been received and is being processed.</p>
                    <div class='order-details'>
                        <h3>Order #{$orderId}</h3>
                        <table>
                            <thead>
                                <tr style='background: #f5f5f5;'>
                                    <th style='padding: 10px; text-align: left;'>Item</th>
                                    <th style='padding: 10px; text-align: center;'>Qty</th>
                                    <th style='padding: 10px; text-align: right;'>Price</th>
                                </tr>
                            </thead>
                            <tbody>
                                {$itemsHtml}
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan='2' style='padding: 10px; text-align: right; font-weight: bold;'>Total:</td>
                                    <td style='padding: 10px; text-align: right;' class='total'>{$total}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    <p>You will receive a payment confirmation email shortly.</p>
                </div>
                <div class='footer'>
                    <p>Build Mate Ghana - Your trusted construction materials marketplace</p>
                </div>
            </div>
        </body>
        </html>
        ";
    }
    
    /**
     * Payment confirmation email template
     */
    private function getPaymentConfirmationTemplate(int $orderId, array $order, string $buyerName, string $total, string $paymentReference): string
    {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
                .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
                .payment-info { background: white; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #28a745; }
                .footer { text-align: center; margin-top: 20px; color: #666; font-size: 0.9em; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>✓ Payment Confirmed!</h1>
                    <p>Your payment has been successfully processed</p>
                </div>
                <div class='content'>
                    <p>Dear {$buyerName},</p>
                    <p>Your payment for <strong>Order #{$orderId}</strong> has been confirmed.</p>
                    <div class='payment-info'>
                        <p><strong>Payment Reference:</strong> {$paymentReference}</p>
                        <p><strong>Amount Paid:</strong> {$total}</p>
                        <p><strong>Payment Method:</strong> Paystack Secure Payment</p>
                    </div>
                    <p><strong>Important:</strong> Your payment is held securely by Paystack until you confirm delivery. This protects both you and the supplier.</p>
                    <p>You can track your order status in your account dashboard.</p>
                    <p style='margin-top: 30px;'>
                        <a href='" . (isset($this->config['app_url']) ? $this->config['app_url'] : 'http://localhost/build_mate') . "/orders/{$orderId}' 
                           style='background: #8b5a2b; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; display: inline-block;'>
                            View Order Details
                        </a>
                    </p>
                </div>
                <div class='footer'>
                    <p>Build Mate Ghana - Your trusted construction materials marketplace</p>
                </div>
            </div>
        </body>
        </html>
        ";
    }
    
    /**
     * Send delivery code email to buyer
     */
    public function sendDeliveryCode(int $orderId, string $deliveryCode, string $buyerEmail, string $buyerName): bool
    {
        $subject = "Your Build Mate order is on the way! Order #{$orderId}";
        $body = $this->getDeliveryCodeTemplate($orderId, $deliveryCode, $buyerName);
        return $this->send($buyerEmail, $subject, $body);
    }
    
    /**
     * Delivery code email template
     */
    private function getDeliveryCodeTemplate(int $orderId, string $deliveryCode, string $buyerName): string
    {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #8b5a2b 0%, #6b4423 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
                .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
                .code-box { background: white; border: 3px solid #8b5a2b; padding: 20px; text-align: center; margin: 20px 0; border-radius: 8px; }
                .code { font-size: 36px; font-weight: bold; color: #8b5a2b; letter-spacing: 8px; }
                .footer { text-align: center; margin-top: 20px; color: #666; font-size: 0.9em; }
                .button { display: inline-block; background: #8b5a2b; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; margin-top: 20px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>🚚 Your Order is On the Way!</h1>
                    <p>Order #{$orderId}</p>
                </div>
                <div class='content'>
                    <p>Hi {$buyerName},</p>
                    <p>Great news! Your Build Mate Ghana order #{$orderId} is now in transit and on its way to you.</p>
                    
                    <div class='code-box'>
                        <p style='margin: 0 0 10px 0; font-size: 14px; color: #666;'>Your Delivery Code:</p>
                        <div class='code'>{$deliveryCode}</div>
                        <p style='margin: 10px 0 0 0; font-size: 12px; color: #666;'>Share this code with the delivery person</p>
                    </div>
                    
                    <p><strong>Important:</strong></p>
                    <ul>
                        <li>When your order arrives, you'll be asked for this delivery code</li>
                        <li>Please inspect your items before confirming delivery</li>
                        <li>Only confirm delivery after you've verified everything is correct</li>
                    </ul>
                    
                    <p style='text-align: center;'>
                        <a href='https://buildmate.com/orders/{$orderId}' class='button'>Track Your Order</a>
                    </p>
                    
                    <p>Thank you for shopping with Build Mate Ghana!</p>
                </div>
                <div class='footer'>
                    <p>Build Mate Ghana - Ghana's Trusted Marketplace</p>
                </div>
            </div>
        </body>
        </html>
        ";
    }
    
    /**
     * Delivery update email template
     */
    private function getDeliveryUpdateTemplate(int $orderId, string $status, string $buyerName, ?array $driverInfo = null): string
    {
        $statusMessages = [
            'pending_pickup' => 'Your order is being prepared for pickup.',
            'ready_for_pickup' => 'Your order is ready for pickup by our delivery team.',
            'picked_up' => 'Your order has been picked up and is on the way!',
            'in_transit' => 'Your order is in transit to your delivery address.',
            'delivered' => 'Your order has been delivered! Please confirm receipt.'
        ];
        
        $statusMessage = $statusMessages[$status] ?? 'Your delivery status has been updated.';
        
        $driverSection = '';
        if ($driverInfo) {
            $driverSection = "
                <div style='background: #e8f5e9; padding: 15px; border-radius: 8px; margin: 15px 0;'>
                    <h4 style='margin-top: 0; color: #2e7d32;'>Delivery Driver Information</h4>
                    <p><strong>Name:</strong> {$driverInfo['name']}</p>
                    <p><strong>Phone:</strong> {$driverInfo['phone']}</p>
                    <p><strong>Vehicle:</strong> {$driverInfo['vehicle_type']} (" . (isset($driverInfo['vehicle_number']) ? $driverInfo['vehicle_number'] : 'N/A') . ")</p>
                </div>
            ";
        }
        
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #8b5a2b 0%, #6b4423 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
                .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
                .status-badge { display: inline-block; background: #28a745; color: white; padding: 8px 16px; border-radius: 20px; font-weight: bold; }
                .footer { text-align: center; margin-top: 20px; color: #666; font-size: 0.9em; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>🚚 Delivery Update</h1>
                    <p>Order #{$orderId}</p>
                </div>
                <div class='content'>
                    <p>Dear {$buyerName},</p>
                    <p>{$statusMessage}</p>
                    <p><span class='status-badge'>" . ucwords(str_replace('_', ' ', $status)) . "</span></p>
                    {$driverSection}
                    <p style='margin-top: 30px;'>
                        <a href='" . (isset($this->config['app_url']) ? $this->config['app_url'] : 'http://localhost/build_mate') . "/orders/{$orderId}/track-delivery' 
                           style='background: #8b5a2b; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; display: inline-block;'>
                            Track Delivery
                        </a>
                    </p>
                </div>
                <div class='footer'>
                    <p>Build Mate Ghana - Your trusted construction materials marketplace</p>
                </div>
            </div>
        </body>
        </html>
        ";
    }
}

