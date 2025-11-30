<?php

declare(strict_types=1);

namespace App;

use App\Model;

/**
 * KYC Document model
 */
class KycDocument extends Model
{
    protected string $table = 'kyc_documents';
    
    /**
     * Get documents by supplier
     */
    public function getBySupplier(int $supplierId): array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM {$this->table} 
            WHERE supplier_id = ? 
            ORDER BY uploaded_at DESC
        ");
        $stmt->execute([$supplierId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Create multiple documents
     */
    public function createMultiple(int $supplierId, array $documents): void
    {
        foreach ($documents as $doc) {
            // Map file type to document_type enum
            $typeMap = [
                'business_reg' => 'business_registration',
                'id_card' => 'id_card',
                'store_photo' => 'other'
            ];
            
            $documentType = $typeMap[$doc['type']] ?? 'other';
            
            $this->create([
                'supplier_id' => $supplierId,
                'document_type' => $documentType,
                'file_path' => $doc['path'],
                'file_name' => $doc['file_name'] ?? basename($doc['path'])
            ]);
        }
    }
}



