<?php
/*
 * This file is part of Aspetos
 *
 * (c)2014 Ludwig Ruderstaller <lr@cwd.at>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace CwdMediaBundle\Tests\Twig;

use Cwd\GenericBundle\Tests\Repository\DoctrineTestCase;
use Cwd\MediaBundle\Model\Entity\Media;
use Cwd\MediaBundle\Twig\ImageExtension;
use Gregwar\Image\Image;

/**
 * Class MediaTest
 *
 * @package CwdMediaBundle\Tests\Service
 * @author  Ludwig Ruderstaller <lr@cwd.at>
 */
class ImageExtensionTest extends DoctrineTestCase
{
    /**
     * @var \Cwd\MediaBundle\Service\MediaService
     */
    protected $service;

    /**
     * @var string
     */
    protected $tmpDir;

    public function setUp()
    {
        parent::setUp();
        $this->service = $this->container->get('cwd.media.service');
        $config = $this->service->getConfig();
        $config['storage']['path'] = '/tmp/unitest-mediastore-'.date("U");
        $config['cache']['path'] = '/tmp/unitest-cache-'.date("U");
        if (!is_dir($config['cache']['path'])) {
            mkdir($config['cache']['path']);
        }

        $this->service->setConfig($config);
        $this->tmpDir = sys_get_temp_dir();
    }

    public function tearDown()
    {
        parent::tearDown();
        $this->getEntityManager()->clear();
        $repository = $this->getEntityManager()->getRepository($this->service->getConfig('entity_class'));
        $result = $repository->findBy(array());
        foreach ($result as $row) {
            $this->getEntityManager()->remove($row);
        }

        $this->getEntityManager()->flush();
    }

    public function testExtension()
    {
        $media = $this->service->create(__DIR__.'/../data/demo.jpg');
        $this->assertEquals(get_class($media), $this->getEntityManager()->getRepository($this->service->getConfig('entity_class'))->getClassName());
        $this->assertNull($media->getId());
        $this->getEntityManager()->flush($media);

        $output = $this->getTemplate('{{ cwdImage(media).cropResize(200) }}')->render(array('media' => $media));
        $this->assertTrue(file_exists($this->service->getConfig('cache')['path'].'/'.$output));
        $this->assertEquals(200, getimagesize($this->service->getConfig('cache')['path'].'/'.$output)[0]);

        $output = $this->getTemplate('{{ cwdImage(media).cropResize(null, 200) }}')->render(array('media' => $media));
        $this->assertTrue(file_exists($this->service->getConfig('cache')['path'].'/'.$output));
        $this->assertEquals(200, getimagesize($this->service->getConfig('cache')['path'].'/'.$output)[1]);

        $output = $this->getTemplate('{{ media|cwdImage.cropResize(null, 200) }}')->render(array('media' => $media));
        $this->assertTrue(file_exists($this->service->getConfig('cache')['path'].'/'.$output));
        $this->assertEquals(200, getimagesize($this->service->getConfig('cache')['path'].'/'.$output)[1]);

        $output = $this->getTemplate('{{ media|cwdImage.cropResize(200) }}')->render(array('media' => $media));
        $this->assertTrue(file_exists($this->service->getConfig('cache')['path'].'/'.$output));
        $this->assertEquals(200, getimagesize($this->service->getConfig('cache')['path'].'/'.$output)[0]);
    }

    protected function getTemplate($template)
    {
        if (is_array($template)) {
            $loader = new \Twig_Loader_Array($template);
        } else {
            $loader = new \Twig_Loader_Array(array('index' => $template));
        }
        $twig = new \Twig_Environment($loader, array('debug' => true, 'cache' => false));
        $twig->addExtension(new ImageExtension($this->service));

        return $twig->loadTemplate('index');
    }
}
