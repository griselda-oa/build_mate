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
        // Check if table exists first
        try {
            $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE order_id = ? LIMIT 1");
            $stmt->execute([$orderId]);
            return $stmt->fetch() ?: null;
        } catch (\PDOException $e) {
            // Table doesn't exist, return null
            if (strpos($e->getMessage(), "doesn't exist") !== false) {
                error_log("Invoices table doesn't exist yet. Please import the database schema.");
                return null;
            }
            throw $e;
        }
    }
    
    /**
     * Generate invoice number
     */
    public function generateInvoiceNo(): string
    {
        $year = date('Y');
        $month = date('m');
        $prefix = "INV-{$year}{$month}";
        
        // Check if table exists first
        try {
            // Get last invoice number for this month
            $stmt = $this->db->prepare("
                SELECT invoice_no 
                FROM {$this->table} 
                WHERE invoice_no LIKE ? 
                ORDER BY id DESC 
                LIMIT 1
            ");
            $stmt->execute([$prefix . '%']);
            $last = $stmt->fetch();
            
            if ($last) {
                $seq = (int)substr($last['invoice_no'], -4) + 1;
            } else {
                $seq = 1;
            }
        } catch (\PDOException $e) {
            // Table doesn't exist, start from 1
            if (strpos($e->getMessage(), "doesn't exist") !== false) {
                error_log("Invoices table doesn't exist. Using default sequence.");
                $seq = 1;
            } else {
                throw $e;
            }
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
        // Get project root directory (one level up from classes/)
        $projectRoot = dirname(__DIR__);
        
        // Always use absolute path - ignore config path that might have ../
        // This avoids issues with paths like "settings/../storage/invoices"
        $invoicePath = $projectRoot . '/storage/invoices';
        
        // Normalize using realpath on parent directory
        $parentDir = dirname($invoicePath);
        $resolvedParent = @realpath($parentDir);
        if ($resolvedParent) {
            $invoicePath = $resolvedParent . '/invoices';
        }
        
        // Ensure it's an absolute path
        if (!str_starts_with($invoicePath, '/')) {
            $invoicePath = $projectRoot . '/storage/invoices';
        }
        
        // Ensure directory exists and is writable
        if (!is_dir($invoicePath)) {
            if (!mkdir($invoicePath, 0755, true)) {
                throw new \Exception("Failed to create invoices directory: {$invoicePath}. Please check directory permissions.");
            }
        }
        
        // Check if directory is writable
        if (!is_writable($invoicePath)) {
            // Try to make it writable with different permission levels
            $permissions = [0777, 0755, 0700];
            $fixed = false;
            
            foreach ($permissions as $perm) {
                if (@chmod($invoicePath, $perm)) {
                    if (is_writable($invoicePath)) {
                        $fixed = true;
                        break;
                    }
                }
            }
            
            if (!$fixed) {
                throw new \Exception("Invoices directory is not writable: {$invoicePath}. Please run: chmod -R 775 {$invoicePath}");
            }
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
        
        // Write PDF file with error handling
        $result = @file_put_contents($filePath, $dompdf->output());
        if ($result === false) {
            throw new \Exception("Failed to write invoice PDF to: {$filePath}. Please check directory permissions.");
        }
        
        // Save invoice record (if table exists)
        try {
            $this->create([
                'order_id' => $orderId,
                'invoice_no' => $invoiceNo,
                'file_path' => $filename
            ]);
        } catch (\PDOException $e) {
            // Table doesn't exist, but PDF was generated successfully
            if (strpos($e->getMessage(), "doesn't exist") !== false) {
                error_log("Invoices table doesn't exist. PDF generated but not saved to database. Please import the database schema.");
            } else {
                // Re-throw other database errors
                throw $e;
            }
        }
        
        return $filename;
    }
}

