<?php
namespace App\Form;

use App\Entity\Commande;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class CommandeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $isEdit = $options['is_edit']; // Determine if it's an edit form

        $builder
            ->add('user', ChoiceType::class, [
                'choices' => array_combine(
                    array_column($options['users'], 'name'),
                    array_column($options['users'], 'id')
                ),
                'placeholder' => 'Selectionner un utilisateur',
                'attr' => ['class' => 'form-control',
            'role' => 'select'
],
                
                'mapped' => false, // Not mapped to an entity
            ])
            ->add('adresseLivraison', TextType::class, [
                'label' => 'Adresse Livraison',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Saisir votre adresse'
                ],
            ])
            ->add('dateCreation', DateType::class, [
                'widget' => 'single_text',
                'data' => new \DateTime(),
                'attr' => ['class' => 'form-control'],
            ])
            ->add('status', ChoiceType::class, [
                'choices' => [
                    'En Attente' => 'En Attente',
                    'Confirmée' => 'Confirmée',
                    'Annulée' => 'Annulée',
                    'Remboursée' => 'Remboursée',
                ],
                'label' => 'Statut de la commande',
                'attr' => ['class' => 'form-control'],
                'disabled' => !$isEdit, // Désactivé en mode édition si nécessaire
            ])
            ->add('total', NumberType::class, [
                'label' => 'Total (TND)',
                'attr' => [
                    'class' => 'form-control',
                    'id' => 'total-field', // ID for potential JS
                ],
            ])
           
            ->add('ModePaiement', ChoiceType::class, [
                'choices' => [
                    'Espèce' => 'Espèces',
                    'Carte bancaire' => 'Carte_Bancaire',
                    'e-Dinar' => 'e_DINAR',
                ],
                'data' => 'espece', // Valeur par défaut
                'disabled' => !$isEdit,
                'attr' => ['class' => 'form-control'],
            ]);

            

            if ($isEdit) {
                $builder->add('statusPaiement', ChoiceType::class, [
                    'choices' => [
                        'En attente' => 'En attente',
                        'Payé' => 'Payé',
                        'Échoué' => 'Échoué',
                        'Remboursé' => 'Remboursé',
                    ],
                    'label' => 'Statut du paiement',
                    'attr' => ['class' => 'form-control'],
                ]);
            }

        $builder->add('submit', SubmitType::class, [
            'label' => $isEdit ? 'Valider Les  Changements' : 'Créer Commande',
            'attr' => ['class' => 'btn btn-success btn-lg w-100 mt-3'],
        ]);

        
    }

   public function configureOptions(OptionsResolver $resolver): void
{
    $resolver->setDefaults([
        'data_class' => Commande::class,
        'is_edit' => false,  // Default to create mode
        'users' => [],       // Default empty users array
        'panier' => [],      // Add this line to define "panier" option
    ]);
}
}
