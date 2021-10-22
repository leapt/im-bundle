<?php

declare(strict_types=1);

namespace Leapt\ImBundle\Form\Extension;

use Leapt\CoreBundle\Form\Type\ImageType;
use Leapt\ImBundle\Manager;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class ImageTypeExtension extends AbstractTypeExtension
{
    public function __construct(protected Manager $imManager)
    {
    }

    public static function getExtendedTypes(): iterable
    {
        return [ImageType::class];
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'im_format' => null,
        ]);
    }

    /**
     * @param array<string|null> $options
     */
    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        if (isset($view->vars['file_url']) && null !== $options['im_format']) {
            $view->vars['file_url'] = $this->imManager->getUrl($options['im_format'], $view->vars['file_url']);
        }
    }
}
