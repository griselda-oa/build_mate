<?php

declare(strict_types=1);

namespace App;

use App\Controller;
use App\Delivery;
use App\Order;
use App\FileUploadService;
use App\Security;

/**
 * Logistics controller
 */
class LogisticsController extends Controller
{
    /**
     * Logistics dashboard
     */
    public function dashboard(): void
    {
        $user = $this->user();
        $deliveryModel = new Delivery();
        $deliveries = $deliveryModel->getByLogistics($user['id']);
        
        $stats = $deliveryModel->getLogisticsStats($user['id']);
        $recentDeliveries = $deliveryModel->getRecent($user['id'], 10);
        
        // Get active advertisements for banner
        $advertisements = [];
        try {
            $adModel = new \App\Advertisement();
            $advertisements = $adModel->getActive();
            $advertisements = array_slice($advertisements, 0, 5);
        } catch (\Exception $e) {
            error_log("Error fetching advertisements: " . $e->getMessage());
        }
        
        echo $this->view->render('Logistics/dashboard', [
            'advertisements' => $advertisements,
            'deliveries' => $recentDeliveries,
            'stats' => $stats
        ]);
    }
    
    /**
     * Assignments page
     */
    public function assignments(): void
    {
        $user = $this->user();
        $deliveryModel = new Delivery();
        $deliveries = $deliveryModel->getByLogistics($user['id']);
        
        echo $this->view->render('Logistics/assignments', [
            'deliveries' => $deliveries
        ]);
    }
    
    /**
     * Start delivery - When delivery partner picks up → delivery.status = "picked_up"
     */
    public function startDelivery(int $id): void
    {
        $user = $this->user();
        $deliveryModel = new Delivery();
        $delivery = $deliveryModel->find($id);
        
        if (!$delivery || $delivery['logistics_id'] !== $user['id']) {
            $this->setFlash('error', 'Invalid delivery assignment');
            $this->redirect('/logistics/assignments');
        }
        
        $db = \App\DB::getInstance();
        $db->beginTransaction();
        
        try {
            // When delivery partner picks up → delivery.status = "picked_up"
            $deliveryModel->update($id, [
                'status' => 'picked_up',
                'picked_up_at' => date('Y-m-d H:i:s')
            ]);
            
            $db->commit();
            
            Security::log('delivery_picked_up', $user['id'], ['delivery_id' => $id]);
            $this->setFlash('success', 'Order picked up successfully');
            $this->redirect('/logistics/assignments');
        } catch (\Exception $e) {
            $db->rollBack();
            error_log("Error picking up delivery: " . $e->getMessage());
            $this->setFlash('error', 'Failed to update delivery status');
            $this->redirect('/logistics/assignments');
        }
    }
    
    /**
     * Mark in transit - When in transit → delivery.status = "in_transit"
     */
    public function markInTransit(int $id): void
    {
        $user = $this->user();
        $deliveryModel = new Delivery();
        $delivery = $deliveryModel->find($id);
        
        if (!$delivery || $delivery['logistics_id'] !== $user['id']) {
            $this->setFlash('error', 'Invalid delivery assignment');
            $this->redirect('/logistics/assignments');
        }
        
        $db = \App\DB::getInstance();
        $db->beginTransaction();
        
        try {
            // When in transit → delivery.status = "in_transit"
            $deliveryModel->update($id, [
                'status' => 'in_transit',
                'in_transit_at' => date('Y-m-d H:i:s')
            ]);
            
            $db->commit();
            
            Security::log('delivery_in_transit', $user['id'], ['delivery_id' => $id]);
            $this->setFlash('success', 'Delivery marked as in transit');
            $this->redirect('/logistics/assignments');
        } catch (\Exception $e) {
            $db->rollBack();
            error_log("Error marking in transit: " . $e->getMessage());
            $this->setFlash('error', 'Failed to update delivery status');
            $this->redirect('/logistics/assignments');
        }
    }
    
    /**
     * Mark delivery as delivered
     */
    public function markDelivered(int $id): void
    {
        $user = $this->user();
        $deliveryModel = new Delivery();
        $delivery = $deliveryModel->find($id);
        
        if (!$delivery || $delivery['logistics_id'] !== $user['id']) {
            $this->setFlash('error', 'Invalid delivery assignment');
            $this->redirect('/logistics/assignments');
        }
        
        $config = require __DIR__ . '/../settings/config.php';
        $uploadService = new FileUploadService(
            $config['uploads']['path'],
            ['jpg', 'jpeg', 'png'],
            $config['uploads']['max_size']
        );
        
        // Upload proof
        $proofPath = null;
        if (isset($_FILES['proof']) && $_FILES['proof']['error'] === UPLOAD_ERR_OK) {
            $result = $uploadService->upload($_FILES['proof']);
            if ($result['success']) {
                $proofPath = $result['filename'];
            }
        }
        
        $db = \App\DB::getInstance();
        $db->beginTransaction();
        
        try {
            // When delivered → delivery.status = "delivered" AND order.status = "delivered"
            $deliveryModel->update($id, [
                'status' => 'delivered',
                'proof_path' => $proofPath,
                'delivered_at' => date('Y-m-d H:i:s')
            ]);
            
            // Update order status
            $orderModel = new Order();
            $orderModel->update($delivery['order_id'], ['status' => 'delivered']);
            
            $db->commit();
            
            Security::log('delivery_completed', $user['id'], ['delivery_id' => $id]);
            $this->setFlash('success', 'Delivery marked as completed');
            $this->redirect('/logistics/assignments');
        } catch (\Exception $e) {
            $db->rollBack();
            error_log("Error marking delivery as delivered: " . $e->getMessage());
            $this->setFlash('error', 'Failed to update delivery status');
            $this->redirect('/logistics/assignments');
        }
    }
}

