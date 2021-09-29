<?php

namespace App\Form;

use App\Entity\Product;
use App\Service\FileUploader;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class ProductType extends AbstractType
{
    private FileUploader $fileUploader;

    public function __construct(FileUploader $fileUploader){

        $this->fileUploader=$fileUploader;
    }
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        
        $builder
            ->add('name',TextType::class)
            ->add('price',NumberType::class)
            ->add('isOnSale',CheckboxType::class,['required'=>false])
            ->add('description',TextType::class)
            ->add('url', FileType::class, [
                'label' => 'télécharge image',
                'mapped' => false, // Tell that there is no Entity to link
                'required' => true,
                'constraints' => [
                    new File([
                        'mimeTypes' => [ // We want to let upload only txt, csv or Excel files
                            'image/jpeg',
                            'image/png'
                        ],
                        'mimeTypesMessage' => "This document isn't valid.",
                    ])
                ],
            ])
            ->add('Envoyer',SubmitType::class)
            
            ->addEventListener(FormEvents::POST_SUBMIT, function(FormEvent $formEvent){
                $fileName=$this->fileUploader->upload($formEvent->getForm()->get('url')->getData());
                $formEvent->getData()->setImg($fileName);

            })
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
        ]);
    }
}
