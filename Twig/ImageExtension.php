<?php
/*
 * This file is part of aspetos.
 *
 * (c)2015 Ludwig Ruderstaller <lr@cwd.at>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Cwd\MediaBundle\Twig;

use Cwd\MediaBundle\Model\Entity\Media;
use Cwd\MediaBundle\Service\MediaService;
use Gregwar\Image\Image;

/**
 * Class ImageExtension
 *
 * @package Cwd\MediaBundle\Twig
 * @author  Ludwig Ruderstaller <lr@cwd.at>
 */
class ImageExtension extends \Twig_Extension
{
    /**
     * @var MediaService
     */
    protected $service;

    /**
     * @param MediaService $service
     */
    public function __construct(MediaService $service)
    {
        $this->service = $service;
    }

    /**
     * @return array
     */
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('cwdImage', array($this, 'image'), array('is_safe' => array('html'))),
        );
    }

    /**
     * @return array
     */
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('cwdImage', array($this, 'image', array('is_safe' => array('html')))),
        );
    }

    /**
     * @param Media $media
     *
     * @return Image
     */
    public function image($media)
    {
        if (is_string($media) && strpos($media, 'http') === 0) {
            $newMedia = new Media();
            $newMedia->setFilename($media);
            $media = $newMedia;
        }

        if (!($media instanceof Media)) {
            $media = $this->service->find($media);
        }

        return $this->service->createInstance($media);

    }

    /**
     *
     * @return string
     */
    public function getName()
    {
        return 'cwd_media_image_extension';
    }
}
