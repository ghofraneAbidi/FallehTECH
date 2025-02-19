<?php

namespace App\Form;

use App\Entity\Post;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class PostType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre', ChoiceType::class, [
                'choices'  => [
                    'Agriculture News' => 'agriculture_news',
                    'Farming Tips' => 'farming_tips',
                    'Crop Management' => 'crop_management',
                    'Livestock Care' => 'livestock_care',
                ],
                'placeholder' => 'Choose a title',
                'attr' => ['class' => 'form-control']
            ])
            ->add('contenu')
            // ->add('date')
            ->add('imageFile', FileType::class, [
                'label' => 'Post Image',
                'mapped' => false, // This tells Symfony that it's not directly mapped to the entity
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '15M',
                        'mimeTypes' => ['image/jpeg', 'image/png', 'image/gif'],
                        'mimeTypesMessage' => 'Please upload a valid image file (JPEG, PNG, GIF).',
                    ])
                ],
                'attr' => ['class' => 'form-control']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Post::class,
        ]);
    }
}
