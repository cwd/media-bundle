<?php
/*
 * This file is part of cwd media bundle.
 *
 * (c)2015 Ludwig Ruderstaller <lr@cwd.at>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Cwd\MediaBundle\Form\Type;

use Cwd\MediaBundle\Form\Transformer\MediaTransformer;
use Cwd\MediaBundle\Model\Entity\Media;
use Cwd\MediaBundle\Service\MediaService;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ImageType
 *
 * @package Cwd\MediaBundle\Form\Type
 * @author  Ludwig Ruderstaller <lr@cwd.at>
 */
class ImageType extends AbstractType
{
    /**
     * @var MediaService
     */
    private $mediaService;

    /**
     * @param MediaService $mediaService
     */
    public function __construct(MediaService $mediaService)
    {
        $this->mediaService = $mediaService;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(
            FormEvents::PRE_SUBMIT,
            function (FormEvent $event) {
                $media = $event->getData();

                if ($media instanceof Media) {
                    return $media;
                } elseif (is_numeric($media)) {
                    $event->setData($this->mediaService->find($media));
                } elseif ($media instanceof UploadedFile) {
                    $event->setData($this->mediaService->create($media->getPathname(), true));
                }
            }
        );

        parent::buildForm($builder, $options);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'validation_groups'     => array('default'),
                'data_class'            => 'Cwd\MediaBundle\Model\Entity\Media',
                'cascade_validation'    => true,
            )
        );
    }

    /**
     *
     * @return string
     */
    public function getParent()
    {
        return 'file';
    }

    /**
     *
     * @return string
     */
    public function getName()
    {
        return 'cwd_image_type';
    }
}
