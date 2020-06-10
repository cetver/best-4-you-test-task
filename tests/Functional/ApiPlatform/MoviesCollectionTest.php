<?php declare(strict_types=1);

namespace App\Tests\Functional\ApiPlatform;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
use Minwork\Helper\Arr;

/**
 * The "MoviesCollectionTest" class
 */
class MoviesCollectionTest extends ApiTestCase
{
    /**
     * @see test
     * @return \Generator
     */
    public function dataProvider()
    {
        yield [
            'data' => [
                'method' => 'GET',
                'url' => '/api/v1/movies',
                'headers' => [
                    'Content-Encoding' => 'gzip',
                    'Accept' => 'application/vnd.api+json',
                ],
            ],
            'expected' => [
                'statusCode' => 200,
                'headers' => [
                    'Content-Type' => 'application/vnd.api+json; charset=utf-8',
                ],
                'keys' => [
                    'links.self',
                    'links.first',
                    'links.last',
                    'links.next',
                    'meta.totalItems',
                    'meta.itemsPerPage',
                    'meta.currentPage',
                    'data.0.id',
                    'data.0.type',
                    'data.0.attributes',
                    'data.0.attributes._id',
                    'data.0.attributes.genre',
                    'data.0.attributes.title',
                    'data.0.attributes.releasedAt',
                ]
            ],
            'message' => 'request.headers.accept = application/vnd.api+json',
        ];

        yield [
            'data' => [
                'method' => 'GET',
                'url' => '/api/v1/movies',
                'headers' => [
                    'Content-Encoding' => 'gzip',
                    'Accept' => '*/*',
                ],
            ],
            'expected' => [
                'statusCode' => 200,
                'headers' => [
                    'Content-Type' => 'application/vnd.api+json; charset=utf-8',
                ],
                'keys' => [
                    'links.self',
                    'links.first',
                    'links.last',
                    'links.next',
                    'meta.totalItems',
                    'meta.itemsPerPage',
                    'meta.currentPage',
                    'data.0.id',
                    'data.0.type',
                    'data.0.attributes',
                    'data.0.attributes._id',
                    'data.0.attributes.genre',
                    'data.0.attributes.title',
                    'data.0.attributes.releasedAt',
                ]
            ],
            'message' => 'request.headers.accept = */*',
        ];

        yield [
            'data' => [
                'method' => 'GET',
                'url' => '/api/v1/movies',
                'headers' => [
                    'Content-Encoding' => 'gzip',
                ],
            ],
            'expected' => [
                'statusCode' => 406,
                'headers' => [
                    'Content-Type' => 'application/vnd.api+json; charset=utf-8',
                ],
                'keys' => [
                    'title',
                    'description',
                ]
            ],
            'message' => 'request.headers.accept = ',
        ];
    }

    /**
     * @dataProvider dataProvider
     *
     * @param array $data
     * @param array $expected
     *
     * @param string $message
     *
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function test(array $data, array $expected, string $message)
    {
        $response = static::createClient()->request(
            $data['method'],
            $data['url'],
            [
                'headers' => $data['headers'],
            ]
        );
        $this->assertResponseStatusCodeSame($expected['statusCode'], $message);
        foreach ($expected['headers'] as $headerName => $headerValue) {
            $this->assertResponseHeaderSame($headerName, $headerValue, $message);
        }
        $content = $response->getContent(false);
        $this->assertJson($content);
        $decodedContent = json_decode($content, true);
        foreach ($expected['keys'] as $key) {
            $this->assertTrue(Arr::has($decodedContent, $key), $message);
        }
    }
}