<?php

declare(strict_types=1);

namespace Leapt\ImBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class DefaultControllerTest extends WebTestCase
{
    public function testWithValidImageAndConfig(): void
    {
        $generatedImagePath = __DIR__ . '/../fixtures/cache/im/thumbnail/base-picture.jpg';
        $client = self::createClient();

        self::assertFileDoesNotExist($generatedImagePath);
        $client->request('GET', 'cache/im/thumbnail/base-picture.jpg');
        self::assertResponseIsSuccessful();
        self::assertFileExists($generatedImagePath);
        unlink(__DIR__ . '/../fixtures/cache/im/thumbnail/base-picture.jpg');
    }

    public function testCachedImageIsServedWithoutBeingRegenerated(): void
    {
        $generatedImagePath = __DIR__ . '/../fixtures/cache/im/thumbnail/base-picture.jpg';
        $client = self::createClient();

        if (!is_dir(\dirname($generatedImagePath))) {
            mkdir(\dirname($generatedImagePath), 0777, true);
        }
        file_put_contents($generatedImagePath, 'cached content');

        try {
            $client->request('GET', 'cache/im/thumbnail/base-picture.jpg');
            self::assertResponseIsSuccessful();
            self::assertSame('cached content', $client->getInternalResponse()->getContent());
        } finally {
            unlink($generatedImagePath);
        }
    }

    public function testResponseIsCacheable(): void
    {
        $generatedImagePath = __DIR__ . '/../fixtures/cache/im/thumbnail/base-picture.jpg';
        $client = self::createClient();

        try {
            $client->request('GET', 'cache/im/thumbnail/base-picture.jpg');
            self::assertResponseIsSuccessful();
            $response = $client->getResponse();
            self::assertTrue($response->headers->hasCacheControlDirective('public'));
            self::assertSame('31536000', $response->headers->getCacheControlDirective('max-age'));
            self::assertNotNull($response->getEtag());
            self::assertNotNull($response->getLastModified());

            $client->request('GET', '/cache/im/thumbnail/base-picture.jpg', server: ['HTTP_IF_NONE_MATCH' => $response->getEtag()]);
            self::assertResponseStatusCodeSame(304);
        } finally {
            unlink($generatedImagePath);
        }
    }

    public function testWithPathTraversal(): void
    {
        $client = self::createClient();
        $client->request('GET', '/cache/im/thumbnail/../../../base-picture.jpg');
        self::assertResponseStatusCodeSame(404);
    }

    public function testWithValidImageAndInvalidConfig(): void
    {
        $client = self::createClient();
        $client->request('GET', 'cache/im/unknown/base-picture.jpg');
        self::assertResponseStatusCodeSame(500);
    }

    public function testWithInvalidImageAndValidConfig(): void
    {
        $client = self::createClient();
        $client->request('GET', 'cache/im/thumbnail/unknown.jpg');
        self::assertResponseStatusCodeSame(404);
    }
}
