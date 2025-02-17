<?php

namespace App\Form;

use App\Entity\Produit;
use App\Entity\Categorie;
use App\Entity\SousCategorie;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class ProduitType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom')
            ->add('prix')
            ->add('description')
            ->add('imageFile', FileType::class, [
                'label' => 'Upload Image',
                'mapped' => false, // This is handled separately by VichUploader
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '2M',
                        'mimeTypes' => ['image/jpeg', 'image/png'],
                        'mimeTypesMessage' => 'Please upload a valid image file (JPEG or PNG).',
                    ])
                ],
            ])
            ->add('categorie', EntityType::class, [
                'class' => Categorie::class,
                'choice_label' => 'nom',
                'placeholder' => 'Sélectionnez une catégorie',
                'attr' => ['id' => 'produit_categorie'], // ID for JavaScript script
            ])
            ->add('sousCategorie', EntityType::class, [
                'class' => SousCategorie::class,
                'choice_label' => 'nom',
                'placeholder' => 'Sélectionnez une sous-catégorie',
                'required' => true,
                'attr' => ['id' => 'produit_sousCategorie'], // ID for JavaScript script
            ])
            ->add('stock', ChoiceType::class, [
                'choices'  => [
                    'En stock' => 'En stock',
                    'Rupture de stock' => 'Rupture de stock',
                ],
                'data' => 'En stock', // Set default value
                'label' => 'Stock',
            ])
            ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Produit::class,
        ]);
    }
}
