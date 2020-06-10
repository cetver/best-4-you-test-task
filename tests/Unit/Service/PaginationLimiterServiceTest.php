<?php declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Service\PaginationLimiterService;
use PHPUnit\Framework\TestCase;

class PaginationLimiterServiceTest extends TestCase
{
    public function __constructDataProvider()
    {
        yield [
            'data' => [
                'page' => 'q',
                'perPage' => 'q',
                'maxPerPage' => 'q'
            ],
            'exception' => \TypeError::class,
        ];
    }

    /**
     * @dataProvider __constructDataProvider
     * @param array $data
     * @param string $exception
     */
    public function test__construct(array $data, string $exception)
    {
        $this->expectException($exception);
        new PaginationLimiterService($data['page'], $data['perPage'], $data['maxPerPage']);
    }

    /**
     * @see testLimit
     *
     * @return \Generator
     */
    public function limitDataProvider()
    {
        yield [
            'data' => [
                'page' => 0,
                'perPage' => 1,
                'maxPerPage' => 2
            ],
            'expected' => 1,
            'message' => 'perPage < maxPerPage'
        ];
        yield [
            'data' => [
                'page' => 0,
                'perPage' => 1,
                'maxPerPage' => 1
            ],
            'expected' => 1,
            'message' => 'perPage = maxPerPage'
        ];
        yield [
            'data' => [
                'page' => 0,
                'perPage' => 3,
                'maxPerPage' => 2
            ],
            'expected' => 2,
            'message' => 'perPage > maxPerPage'
        ];
    }

    /**
     * @dataProvider limitDataProvider
     *
     * @param array $data
     * @param int $expected
     * @param string $message
     */
    public function testLimit(array $data, int $expected, string $message)
    {
        $paginatorLimiter = new PaginationLimiterService($data['page'], $data['perPage'], $data['maxPerPage']);
        $this->assertSame($paginatorLimiter->limit(), $expected, $message);
    }

    /**
     * @see testOffset
     *
     * @return \Generator
     */
    public function offsetDataProvider()
    {
        yield [
            'data' => [
                'page' => -1,
                'perPage' => 10,
                'maxPerPage' => 20
            ],
            'expected' => 0,
            'message' => 'page < 0'
        ];

        yield [
            'data' => [
                'page' => 0,
                'perPage' => 10,
                'maxPerPage' => 20
            ],
            'expected' => 0,
            'message' => 'page = 0'
        ];

        yield [
            'data' => [
                'page' => 1,
                'perPage' => 10,
                'maxPerPage' => 20
            ],
            'expected' => 0,
            'message' => 'page = 1'
        ];

        yield [
            'data' => [
                'page' => 2,
                'perPage' => 10,
                'maxPerPage' => 20
            ],
            'expected' => 10,
            'message' => 'page = 2'
        ];

        yield [
            'data' => [
                'page' => 3,
                'perPage' => 10,
                'maxPerPage' => 20
            ],
            'expected' => 20,
            'message' => 'page = 3'
        ];

    }

    /**
     * @dataProvider offsetDataProvider
     *
     * @param array $data
     * @param int $expected
     * @param string $message
     */
    public function testOffset(array $data, int $expected, string $message)
    {
        $paginatorLimiter = new PaginationLimiterService($data['page'], $data['perPage'], $data['maxPerPage']);
        $this->assertSame($paginatorLimiter->offset(), $expected, $message);
    }
}
