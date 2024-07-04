<?php

namespace App\Form\Artworks;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class ArtworksFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class)
            ->add('height', NumberType::class)
            ->add('width', NumberType::class)
            ->add('quantity', NumberType::class)
            ->add('createdAt', DateType::class, [
                'widget' => 'single_text' // Pour un sélecteur de date simple
            ])
            ->add('description', TextareaType::class)
            ->add('method', TextType::class)
            ->add('prize', NumberType::class);

            // ->add('image', FileType::class, [
            //     'mapped' => false, // Ne pas mapper ce champ avec l'entité
            //     'required' => false, // Le champ n'est pas obligatoire
            //     'constraints' => [
            //         new File([
            //             'maxSize' => '1024k',
            //             'mimeTypes' => [
            //                 'image/jpeg',
            //                 'image/png',
            //             ],
            //             'mimeTypesMessage' => 'Veuillez télécharger une image valide',
            //         ])
            //     ],
            // ]),

    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null,
        ]);
    }
}
