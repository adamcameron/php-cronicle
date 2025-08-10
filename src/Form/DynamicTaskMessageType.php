<?php

namespace App\Form;

use App\Entity\DynamicTaskMessage;
use App\Enum\TaskTimezone;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class DynamicTaskMessageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('type', TextType::class, [
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length(['min' => 2, 'max' => 255]),
                    new Assert\Regex([
                        'pattern' => '/^[a-zA-Z_][a-zA-Z0-9_]*$/',
                        'message' => 'Task type should contain only letters, numbers and underscores, starting with a letter or underscore.'
                    ])
                ]
            ])
            ->add('name', TextType::class, [
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length(['min' => 2, 'max' => 255])
                ]
            ])
            ->add('schedule', TextType::class, [
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length(['min' => 1, 'max' => 500])
                ]
            ])
            ->add('timezone', EnumType::class, [
                'class' => TaskTimezone::class,
                'choice_label' => fn (TaskTimezone $timezone) => $timezone->value,
                'constraints' => [
                    new Assert\NotBlank()
                ]
            ])
            ->add('priority', IntegerType::class, [
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Range(['min' => 1, 'max' => 100])
                ],
                'data' => $options['data']->getPriority() ?? DynamicTaskMessage::DEFAULT_PRIORITY
            ])
            ->add('active', CheckboxType::class, [
                'required' => false,
                'data' => $options['data']->isActive() ?? true
            ])
            ->add('scheduledAt', DateTimeType::class, [
                'required' => false,
                'widget' => 'single_text',
                'input' => 'datetime_immutable'
            ])
            ->add('metadata', TextareaType::class, [
                'required' => false,
                'constraints' => [
                    new Assert\Callback([
                        'callback' => [$this, 'validateJsonMetadata']
                    ])
                ],
                'attr' => [
                    'data-json-field' => 'true'
                ]
            ])
        ;

        // Add transformer to handle JSON conversion for metadata field
        $builder->get('metadata')->addModelTransformer(new CallbackTransformer(
            function ($arrayData) {
                // Transform from array to JSON string (for display in form)
                return $arrayData ? json_encode($arrayData, JSON_PRETTY_PRINT) : '';
            },
            function ($jsonString) {
                // Transform from JSON string to array (for saving to entity)
                if (empty($jsonString)) {
                    return null;
                }
                return json_decode($jsonString, true);
            }
        ));
    }

    public function validateJsonMetadata($value, $context): void
    {
        if (empty($value)) {
            return; // null/empty is allowed
        }

        // If we get here and value is already an array, the transformer worked fine
        if (is_array($value)) {
            return; // Validation passed - it's a valid array from JSON
        }

        // If we get here with a string, the transformer failed to decode it
        if (is_string($value)) {
            $context->buildViolation('Invalid JSON format')
                ->addViolation();
            return;
        }

        // If we get any other type, it's invalid
        $context->buildViolation('Metadata must be a JSON object')
            ->addViolation();
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => DynamicTaskMessage::class,
        ]);
    }
}
