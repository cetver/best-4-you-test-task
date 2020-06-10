<?php declare(strict_types=1);

namespace App\Serializer\Normalizer;

use ArrayObject;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * The "ApiPlatformDocumentationNormalizer" class
 */
class ApiPlatformDocumentationNormalizer implements NormalizerInterface
{
    private $normalizer;

    public function __construct(NormalizerInterface $normalizer)
    {
        $this->normalizer = $normalizer;
    }

    public function supportsNormalization($data, string $format = null)
    {
        return $this->normalizer->supportsNormalization($data, $format);
    }

    public function normalize($object, string $format = null, array $context = [])
    {
        $docs = $this->normalizer->normalize($object, $format, $context);
        $docs['components']['schemas'] = $this->newSchemas($docs['components']['schemas']);
        $this->updatePaths($docs['paths']);

        return $docs;
    }

    private function updateMoviesDigestPath(array &$path)
    {
        foreach ($path as $method => $item) {
            $item['parameters'] = [
                [
                    'name' => 'weekNumber',
                    'in' => 'path',
                    'required' => true,
                    'description' => 'Day of the week of the release date',
                    'schema' => [
                        'type' => 'integer',
                        'default' => (new \DateTimeImmutable())->format('W')
                    ],
                ],
                [
                    'name' => 'page',
                    'in' => 'query',
                    'required' => false,
                    'description' => 'The collection page number',
                    'schema' => [
                        'type' => 'integer',
                        'default' => 1,
                    ],
                ],
                [
                    'name' => 'itemsPerPage',
                    'in' => 'query',
                    'required' => false,
                    'description' => 'The number of items per page',
                    'schema' => [
                        'type' => 'integer',
                        'default' => 30,
                        'minimum' => 0,
                    ],
                ],
            ];
        }
    }

    private function updatePaths(ArrayObject $paths)
    {
        foreach ($paths as $name => $path) {
            if ($name === '/api/v1/movies/digest/{weekNumber}') {
                $this->updateMoviesDigestPath($path);
            }
            foreach ($path as $method => $item) {
                if (isset($item['requestBody']['content'])) {
                    foreach ($item['requestBody']['content'] as $mimeType => $data) {
                        $item['requestBody']['content'][$mimeType]['schema']['$ref'] .= '.save';
                    }
                }

                if (isset($item['responses'][200]['content'])) {
                    foreach ($item['responses'][200]['content'] as $mimeType => $data) {
                        if (isset($item['responses'][200]['content'][$mimeType]['schema']['items'])) {
                            $item['responses'][200]['content'][$mimeType]['schema']['items']['$ref'] .= '.all';
                        } else {
                            $item['responses'][200]['content'][$mimeType]['schema']['$ref'] .= '.one';
                        }
                    }
                }

                if (isset($item['responses'][201]['content'])) {
                    foreach ($item['responses'][201]['content'] as $mimeType => $data) {
                        $item['responses'][201]['content'][$mimeType]['schema']['$ref'] .= '.one';
                    }
                }
            }
        }
    }

    private function linksObject()
    {
        return new ArrayObject(
            [
                'type' => 'object',
                'description' => 'Pagination URI paths',
                'properties' => new ArrayObject(
                    [
                        'self' => new ArrayObject(
                            [
                                'type' => 'string',
                                'description' => 'The current URI path of data',
                            ]
                        ),
                        'first' => new ArrayObject(
                            [
                                'type' => 'string',
                                'description' => 'The first URI path of data (may not exist)',
                            ]
                        ),
                        'last' => new ArrayObject(
                            [
                                'type' => 'string',
                                'description' => 'The last URI path of data (may not exist)',
                            ]
                        ),
                        'prev' => new ArrayObject(
                            [
                                'type' => 'string',
                                'description' => 'The prev URI path of data (may not exist)',
                            ]
                        ),
                        'next' => new ArrayObject(
                            [
                                'type' => 'string',
                                'description' => 'The next URI path of data (may not exist)',
                            ]
                        ),
                    ]
                ),
            ]
        );
    }

    private function metaObject()
    {
        return new ArrayObject(
            [
                'type' => 'object',
                'description' => 'Pagination meta',
                'properties' => new ArrayObject(
                    [
                        'totalItems' => new ArrayObject(
                            [
                                'type' => 'integer',
                                'description' => 'Total items',
                            ]
                        ),
                        'itemsPerPage' => new ArrayObject(
                            [
                                'type' => 'integer',
                                'description' => 'Items per page',
                            ]
                        ),
                        'currentPage' => new ArrayObject(
                            [
                                'type' => 'integer',
                                'description' => 'Current page',
                            ]
                        ),
                    ]
                ),
            ]
        );
    }

    private function dataObject(ArrayObject $attributes, string $name)
    {
        return new ArrayObject(
            [
                'type' => 'object',
                'properties' => new ArrayObject(
                    [
                        'id' => new ArrayObject(
                            [
                                'type' => 'string',
                                'description' => sprintf(
                                    'URI path to retrieve the "%s" object',
                                    $name
                                ),
                            ]
                        ),
                        'type' => new ArrayObject(
                            [
                                'type' => 'string',
                                'example' => $name,
                            ]
                        ),
                        'attributes' => $attributes,
                    ]
                ),
            ]
        );
    }

    private function saveObject(ArrayObject $dataObject)
    {
        $saveObject = $this->cloneArrayObjectRecursively($dataObject);
        unset(
            $saveObject['properties']['id'],
            $saveObject['properties']['attributes']['properties']['_id']
        );

        return $saveObject;
    }

    private function cloneArrayObjectRecursively(ArrayObject $object)
    {
        $clonedObject = new ArrayObject();
        foreach ($object as $key => $value) {
            $clonedObject[$key] = ($value instanceof ArrayObject)
                ? clone $this->cloneArrayObjectRecursively($value)
                : $value;
        }

        return $clonedObject;
    }

    private function newSchemas(ArrayObject $schemas)
    {
        $newSchemas = new ArrayObject();
        foreach ($schemas as $name => $attributes) {
            $attributes['properties']['_id'] = $attributes['properties']['id'];
            unset($attributes['properties']['id']);
            ksort($attributes['properties'], SORT_STRING);

            $dataObject = $this->dataObject($attributes, $name);

            $schemeExtension = 'all';
            $schemeName = sprintf('%s.%s', $name, $schemeExtension);
            $newSchemas[$schemeName] = new ArrayObject(
                [
                    'type' => 'object',
                    'description' => sprintf('%s items', $schemeExtension),
                    'properties' => new ArrayObject(
                        [
                            'links' => $this->linksObject(),
                            'meta' => $this->metaObject(),
                            'data' => new ArrayObject(
                                [
                                    'type' => 'array',
                                    'description' => $name . ' collection',
                                    'items' => $dataObject,
                                ]
                            ),
                        ]
                    ),
                ]
            );

            $schemeExtension = 'one';
            $schemeName = sprintf('%s.%s', $name, $schemeExtension);
            $newSchemas[$schemeName] = new ArrayObject(
                [
                    'type' => 'object',
                    'description' => sprintf('%s item', $schemeExtension),
                    'properties' => new ArrayObject(
                        [
                            'data' => $dataObject,
                        ]
                    ),
                ]
            );


            $schemeExtension = 'save';
            $schemeName = sprintf('%s.%s', $name, $schemeExtension);
            $saveObject = $this->saveObject($dataObject);
            $newSchemas[$schemeName] = new ArrayObject(
                [
                    'type' => 'object',
                    'description' => sprintf('%s (create/update) item', $schemeExtension),
                    'properties' => new ArrayObject(
                        [
                            'data' => $saveObject,
                        ]
                    ),
                ]
            );
        }

        $newSchemas['errors.validation'] = new ArrayObject(
            [
                'type' => 'object',
                'description' => 'Validation errors',
                'properties' => new ArrayObject(
                    [
                        'errors' => new ArrayObject(
                            [
                                'type' => 'array',
                                'description' => 'Errors collection',
                                'items' => new ArrayObject(
                                    [
                                        'type' => 'object',
                                        'properties' => new ArrayObject(
                                            [
                                                'detail' => new ArrayObject(
                                                    [
                                                        'type' => 'string',
                                                        'description' => 'Error message',
                                                        'example' => 'This value should not be blank.',
                                                    ]
                                                ),
                                                'source' => new ArrayObject(
                                                    [
                                                        'type' => 'object',
                                                        'properties' => new ArrayObject(
                                                            [
                                                                'pointer' => new ArrayObject(
                                                                    [
                                                                        'type' => 'string',
                                                                        'description' => 'The path to invalid attribute',
                                                                        'example' => 'data/attributes/title',
                                                                    ]
                                                                ),
                                                            ]
                                                        ),
                                                    ]
                                                ),
                                            ]
                                        ),
                                    ]
                                ),
                            ]
                        ),
                    ]
                ),
            ]
        );

        $newSchemas['errors.unexpected-behavior'] = new ArrayObject(
            [
                'type' => 'object',
                'description' => 'Unexpected behavior errors',
                'properties' => new ArrayObject(
                    [
                        'title' => new ArrayObject(
                            [
                                'type' => 'string',
                                'example' => 'An error occurred',
                            ]
                        ),
                        'description' => new ArrayObject(
                            [
                                'type' => 'string',
                                'example' => 'Cannot create the item',
                            ]
                        ),
                    ]
                ),
            ]
        );

        return $newSchemas;
    }
}