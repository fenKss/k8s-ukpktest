<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AdminUserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add('email')
            ->add('bornAt', DateType::class, [
                'label' => "Дата Рождения",
                'html5' => true,
                'widget' => 'single_text',
                'required' => true
            ])
            ->add('address')
            ->add('studyPlace')
            ->add('class')
            ->add('name')
            ->add('surname')//            ->add('avatar')
        ;
        if (in_array('ROLE_ADMIN', $options['roles'])) {
            $builder->add('roles', ChoiceType::class, [
                "label" => "Роли",
                'multiple' => true,
                'choices' => [
                    'Администратор'=>'ROLE_ADMIN' ,
                    'Редактор'=>'ROLE_EDITOR' ,
                    'Пользователь'=>'ROLE_USER' ,
                ],
                'required' => false,
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'roles' => ['ROLE_USER']
        ]);
    }
}
