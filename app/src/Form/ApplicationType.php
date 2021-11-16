<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\Service;
use App\Entity\Application;
use App\Entity\References\Domaine;
use App\Repository\References\DomaineRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use App\Repository\ServiceRepository;

class ApplicationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
        ->add('label', TextType::class, [
            'required' => true,
        ])
        ->add('sousDomaine', EntityType::class, [
            'required' => true,
            'multiple' => false,
            'expanded' => false,
            'placeholder' => '',
            'class' => Domaine::class,
            'choice_label' => 'label',
            'query_builder' => function (DomaineRepository $er) {
                return $er->createQueryBuilder('sd')
                ->where('sd.domaineParent IS NOT NULL')
                ->andWhere('sd.supprimeLe is NULL')
                ->orderBy('sd.label', 'ASC');
            },
            'choice_attr' => function ($choice, $key, $value) {
                $domaine = $choice->getDomaineParent();
                return ['data-parent' => $domaine->getId(), 'class' => 'd-none idParent '.$domaine->getId()];
            }
        ])
        ->add('exploitant', EntityType::class, [
            'required' => true,
            'placeholder' => '',
            'class' => Service::class,
            'choice_label' => 'label',
            'multiple' => false,
            'expanded' => false,
            'query_builder' => function (ServiceRepository $er) {
                return $er->createQueryBuilder('s')
                ->orderBy('s.label', 'ASC')
                ->Where('s.archiveLe is null');
            }
        ])
        ->add('MOE', EntityType::class, [
            'required' => false,
            'class' => Service::class,
            'choice_label' => 'label',
            'multiple' => false,
            'expanded' => false,
            'query_builder' => function (ServiceRepository $er) {
                return $er->createQueryBuilder('s')
                ->orderBy('s.label', 'ASC')
                ->Where('s.archiveLe is null');
            }
        ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Application::class,
        ]);
    }
}
