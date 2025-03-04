<?php

namespace App\Form;

use App\Entity\OffreEmploi;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OffreEmploiType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre', TextType::class, [
                'required' => true,
                'empty_data' => '',  // ✅ Ensures an empty input is not treated as null
                'attr' => ['placeholder' => 'Entrez le titre de l\'offre']
            ])
            ->add('description', TextareaType::class, [
                'required' => true,
                'empty_data' => '',
                'attr' => ['placeholder' => 'Ajoutez une description de l\'offre']
            ])
            ->add('salaire', NumberType::class, [
                'required' => true,
                'empty_data' => '0',  // ✅ Prevents "Expected float, null given" error
                'attr' => ['min' => 10, 'max' => 10000, 'placeholder' => 'Salaire en DT']
            ])
            ->add('lieu', TextType::class, [
                'required' => true,
                'empty_data' => '',
                'attr' => ['placeholder' => 'Entrez le lieu du travail']
            ])
            ->add('startDate', DateType::class, [
                'widget' => 'single_text',
                'required' => true,
                'label' => 'Date de début',
                'attr' => ['class' => 'form-control']
            ])
            ->add('dateExpiration', DateType::class, [
                'widget' => 'single_text',
                'required' => true,
                'label' => 'Date d\'expiration',
                'attr' => ['class' => 'form-control']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => OffreEmploi::class,
        ]);
    }
}
