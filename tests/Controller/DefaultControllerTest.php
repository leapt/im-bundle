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
