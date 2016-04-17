<?php
/*
 * This file is part of Aspetos
 *
 * (c)2014 Ludwig Ruderstaller <lr@cwd.at>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace CwdMediaBundle\Tests\Service;

use Cwd\GenericBundle\Tests\Repository\DoctrineTestCase;
use Cwd\MediaBundle\Model\Entity\Media;
use Gregwar\Image\Image;

/**
 * Class MediaTest
 *
 * @package CwdMediaBundle\Tests\Service
 * @author  Ludwig Ruderstaller <lr@cwd.at>
 */
class MediaTest extends DoctrineTestCase
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

        if (!is_dir('./tmp')) {
            mkdir('./tmp');
        }

        $config['storage']['path'] = './tmp/unitest-mediastore-'.date("U");
        $config['cache']['path'] = './tmp/unitest-cache-'.date("U");
        if (!is_dir($config['cache']['path'])) {
            mkdir($config['cache']['path']);
        }

        $this->service->setConfig($config);
        $this->tmpDir = './tmp';
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

    public function testSetup()
    {
        $this->assertFalse(is_dir($this->service->getConfig('storage')['path']));
        $this->assertTrue(is_dir($this->service->getConfig('cache')['path']));
        $this->assertTrue(is_writeable($this->service->getConfig('cache')['path']));

        try {
            $repository = $this->getEntityManager()->getRepository($this->service->getConfig('entity_class'));
            $this->assertInstanceOf('Cwd\MediaBundle\Model\Repository\MediaRepository', $repository);
        } catch (\Exception $e) {
            $this->assertTrue(false, $this->service->getConfig('entity_class').' is not a valid Repository');

        }
    }

    public function testConfig()
    {
        $this->assertNotNull($this->service->getConfig('throw_exception'));
        $this->assertTrue(is_array($this->service->getConfig()));
        $this->assertGreaterThanOrEqual(5, count($this->service->getConfig()));

        $this->setExpectedException('Exception');
        $this->service->getConfig('foobar');
    }

    public function testStoreImage()
    {
        $result = $this->service->storeImage(__DIR__.'/../data/demo.jpg');
        $this->assertArrayHasKey('path', $result);
        $this->assertArrayHasKey('md5', $result);
        $this->assertArrayHasKey('width', $result);
        $this->assertArrayHasKey('height', $result);
        $this->assertArrayHasKey('type', $result);
        $this->assertContains($result['md5'], $result['path'], 'MD5 is not part of path');
        $this->assertLessThanOrEqual($this->service->getConfig('converter')['size']['max_width'], $result['width']);
        $this->assertLessThanOrEqual($this->service->getConfig('converter')['size']['max_height'], $result['height']);
        $this->assertTrue(is_dir($this->service->getConfig('storage')['path']));
        $this->setExpectedException('Exception');
        $this->service->storeImage('not-exisisting');
    }

    public function testCreate()
    {
        $media = $this->service->create(__DIR__.'/../data/demo.jpg');
        $this->assertEquals(get_class($media), $this->getEntityManager()->getRepository($this->service->getConfig('entity_class'))->getClassName());
        $this->assertNull($media->getId());
        $this->getEntityManager()->flush($media);
        $this->assertNotNull($media->getId());
        $this->assertGreaterThan(0, $media->getId());

        $id = $media->getId();

        $media = $this->service->create(__DIR__.'/../data/demo.jpg', true);
        $this->assertGreaterThan(0, $media->getId());
        $this->assertEquals($id, $media->getId());
        $this->getEntityManager()->flush($media);

        return $media;
    }

    /**
     * @depends testCreate
     */
    public function testCreateFail(Media $media)
    {

        $media = $this->service->create(__DIR__.'/../data/demo.jpg', true);
        $this->assertNull($media->getId());
        $this->getEntityManager()->flush($media);

        $this->setExpectedException('Exception');
        $this->service->create(__DIR__.'/../data/demo.jpg', false);
    }

    /**
     * @depends testCreate
     */
    public function testCreateInstance(Media $media)
    {
        /** @var Image $instance */
        $instance = $this->service->createInstance($media);
        $this->assertInstanceOf('Gregwar\Image\Image', $instance);
    }
}
