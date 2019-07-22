<?php

namespace App\Form;

use App\Entity\Documentary;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EditDocumentaryForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class)
            ->add('slug', TextType::class)
            ->add('storyline', TextType::class)
            ->add('summary', TextType::class)
            ->add('year', IntegerType::class)
            ->add('length', IntegerType::class)
            ->add('status', TextType::class)
            ->add('short_url', TextType::class)
            ->add('poster', FileType::class, [
                'mapped' => false
            ])
            ->add('wide_image', FileType::class, [
                'mapped' => false
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Documentary::class,
        ]);
    }
}