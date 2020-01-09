<?php

namespace Leapt\ImBundle\Form\Extension;

use Leapt\CoreBundle\Form\Type\ImageType;
use Leapt\ImBundle\Manager;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form type to show a preview of the image
 */
class ImageTypeExtension extends AbstractTypeExtension
{
    /**
     * @var Manager
     */
    protected $imManager;

    public function __construct(Manager $imManager)
    {
        $this->imManager = $imManager;
    }

    public static function getExtendedTypes(): iterable
    {
        return [ImageType::class];
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'im_format' => null,
        ]);
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        if (isset($view->vars['file_url']) && null !== $options['im_format']) {
            $view->vars['file_url'] = $this->imManager->getUrl($options['im_format'], $view->vars['file_url']);
        }
    }
}
