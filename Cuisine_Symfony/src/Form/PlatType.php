<?php

namespace App\Form;

use App\Entity\Plat;
use App\Entity\Categorie;


use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\Event\PreSubmitEvent;
use Symfony\Component\Form\Event\PostSubmitEvent;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use DateTimeImmutable;
use App\Form\FormListenerFactory;


class PlatType extends AbstractType
{
    private SluggerInterface $slugger;
    public function __construct(SluggerInterface $slugger,private FormListenerFactory $formListenerFactory)
    {
        $this->slugger = $slugger;
    }
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom',TextType::class, [
                'empty_data' => '',
            ])
            ->add('slug',TextType::class, [
                'required' => false,
            ])
            -> add('categorie',EntityType::class,[
                'class' => Categorie::class,
                'choice_label' => 'nom',
                'expanded' => true,
            ])
            ->add('description')
            ->add('duration')

            ->add('save', SubmitType::class, [
                'label' => 'Envoyer'
            ])
            ->addEventListener(FormEvents::PRE_SUBMIT, $this->formListenerFactory->autoSlug('nom'))
            ->addEventListener(FormEvents::POST_SUBMIT, $this->formListenerFactory->timestamps())
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Plat::class,
        ]);
    }
}
