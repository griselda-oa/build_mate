<?php

declare(strict_types=1);

namespace App;

use App\Model;
use App\View;
use App\Security;
use Dompdf\Dompdf;
use Dompdf\Options;

/**
 * Invoice model
 */
class Invoice extends Model
{
    protected string $table = 'invoices';
    
    /**
     * Find by order ID
     */
    public function findByOrderId(int $orderId): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE order_id = ? LIMIT 1");
        $stmt->execute([$orderId]);
        return $stmt->fetch() ?: null;
    }
    
    /**
     * Generate invoice number
     */
    public function generateInvoiceNo(): string
    {
        $year = date('Y');
        $month = date('m');
        
        // Get last invoice number for this month
        $stmt = $this->db->prepare("
            SELECT invoice_no 
            FROM {$this->table} 
            WHERE invoice_no LIKE ? 
            ORDER BY id DESC 
            LIMIT 1
        ");
        $prefix = "INV-{$year}{$month}";
        $stmt->execute([$prefix . '%']);
        $last = $stmt->fetch();
        
        if ($last) {
            $seq = (int)substr($last['invoice_no'], -4) + 1;
        } else {
            $seq = 1;
        }
        
        return $prefix . str_pad((string)$seq, 4, '0', STR_PAD_LEFT);
    }
    
    /**
     * Generate invoice PDF
     */
    public function generatePdf(int $orderId, Order $orderModel): string
    {
        $order = $orderModel->getWithItems($orderId);
        
        if (!$order) {
            throw new \Exception('Order not found');
        }
        
        $invoiceNo = $this->generateInvoiceNo();
        
        $config = require __DIR__ . '/../settings/config.php';
        $invoicePath = $config['invoices']['path'];
        
        if (!is_dir($invoicePath)) {
            mkdir($invoicePath, 0755, true);
        }
        
        // Generate PDF
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        
        $dompdf = new Dompdf($options);
        
        $view = new View();
        $html = $view->renderPartial('Order/invoice-pdf', [
            'order' => $order,
            'invoice_no' => $invoiceNo
        ]);
        
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        
        $filename = Security::randomFilename('pdf');
        $filePath = $invoicePath . '/' . $filename;
        file_put_contents($filePath, $dompdf->output());
        
        // Save invoice record
        $this->create([
            'order_id' => $orderId,
            'invoice_no' => $invoiceNo,
            'pdf_path' => $filename
        ]);
        
        return $filename;
    }
}

