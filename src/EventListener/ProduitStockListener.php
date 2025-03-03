<?php
namespace App\EventListener;

use App\Entity\Produit;
use App\Service\MailService;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;

#[AsEntityListener(event: 'preUpdate', entity: Produit::class)]
class ProduitStockListener
{
    private MailService $mailService;

    public function __construct(MailService $mailService)
    {
        $this->mailService = $mailService;
    }

    public function preUpdate(Produit $produit, PreUpdateEventArgs $event): void
    {
        // Check if the stock field was updated
        if ($event->hasChangedField('stock')) {
            $newStock = $event->getNewValue('stock');

            // Send email when stock reaches 5
            if ($newStock === 5) {
                $userEmail = 'sarah.faleh@esprit.tn'; // Change this to dynamic email if needed
                $this->mailService->sendStockAlertEmail($userEmail, $produit->getName());
            }
        }
    }
}
