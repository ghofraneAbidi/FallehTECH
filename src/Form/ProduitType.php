<?php

namespace App\Form;

use App\Entity\Produit;
use App\Entity\Categorie;
use App\Entity\SousCategorie;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class ProduitType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom du Produit',
                'attr' => [
                    'placeholder' => 'Entrez le nom du produit',
                    'minlength' => 3, // Ensure at least 3 characters
                ],
                'required' => true
            ])
            ->add('prix', NumberType::class, [
                'label' => 'Prix (DT)',
                'scale' => 2, // Allows two decimal places
                'html5' => true, // Enables number input validation
                'attr' => [
                    'step' => '0.01', // Allows decimal values
                    'min' => '1', // Ensures price is greater than 1
                    'placeholder' => 'Entrez le prix en dinars'
                ],
                'required' => true
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'attr' => [
                    'placeholder' => 'Ajoutez une description...',
                    'rows' => 4
                ],
                'required' => true
            ])
            ->add('categorie', EntityType::class, [
                'class' => Categorie::class,
                'choice_label' => 'nom',
                'placeholder' => 'Sélectionnez une catégorie',
                'attr' => ['id' => 'produit_categorie'], // ID for JavaScript script
                'required' => true
            ])
            ->add('sousCategorie', EntityType::class, [
                'class' => SousCategorie::class,
                'choice_label' => 'nom',
                'placeholder' => 'Sélectionnez une sous-catégorie',
                'required' => true,
                'attr' => ['id' => 'produit_sousCategorie'], // ID for JavaScript script
            ])
            ->add('stock', ChoiceType::class, [
                'choices' => [
                    'En stock' => 'En stock',
                    'Rupture de stock' => 'Rupture de stock',
                ],
                'data' => 'En stock', // Set default value
                'label' => 'Stock',
            ])
            ->add('imageFile', FileType::class, [
                'label' => 'Image du Produit',
                'mapped' => false, // Prevents automatic mapping to entity
                'required' => false, // Image is optional in edit mode
                'constraints' => [
                    new File([
                        'maxSize' => '2M',
                        'mimeTypes' => ['image/jpeg', 'image/png'],
                        'mimeTypesMessage' => 'Veuillez télécharger une image valide (JPEG ou PNG).',
                    ])
                ],
                'attr' => [
                    'accept' => 'image/jpeg, image/png' // Restrict file types in UI
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Produit::class,
        ]);
    }
    public function findAllCategories()
{
    return $this->createQueryBuilder('p')
        ->select('DISTINCT c.id, c.nom')
        ->leftJoin('p.categorie', 'c')
        ->orderBy('c.nom', 'ASC')
        ->getQuery()
        ->getResult();
}

public function findAllSubCategories()
{
    return $this->createQueryBuilder('p')
        ->select('DISTINCT sc.id, sc.nom')
        ->leftJoin('p.sousCategorie', 'sc')
        ->orderBy('sc.nom', 'ASC')
        ->getQuery()
        ->getResult();
}

}
