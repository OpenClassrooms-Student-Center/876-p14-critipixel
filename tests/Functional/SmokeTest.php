<?php

declare(strict_types=1);

namespace App\Tests\Functional;

final class SmokeTest extends FunctionalTestCase
{
    /**
     * @dataProvider provideUri
     */
    public function testShouldTestUri(string $method, string $uri): void
    {
        $this->client->request($method, $uri);
        self::assertTrue(500 > $this->client->getResponse()->getStatusCode());
    }

    /**
     * @return iterable<array{string, string}>
     */
    public static function provideUri(): iterable
    {
        yield ['GET', '/'];
        yield ['GET', '/jeu-video-0'];
        yield ['POST', '/jeu-video-0'];
        yield ['GET', '/auth/login'];
        yield ['POST', '/auth/login'];
        yield ['GET', '/auth/register'];
        yield ['POST', '/auth/register'];
    }
}
