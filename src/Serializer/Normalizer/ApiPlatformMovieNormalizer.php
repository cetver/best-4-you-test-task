<?php declare(strict_types=1);

namespace App\Serializer\Normalizer;

use App\Entity\Movie;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerInterface;

class ApiPlatformMovieNormalizer implements NormalizerInterface, DenormalizerInterface, SerializerAwareInterface
{
    private NormalizerInterface $normalizer;

    public function __construct(NormalizerInterface $normalizer)
    {
        if (!$normalizer instanceof DenormalizerInterface) {
            throw new \InvalidArgumentException(
                sprintf('The decorated normalizer must implement the %s.', DenormalizerInterface::class)
            );
        }
        $this->normalizer = $normalizer;
    }

    public function normalize($object, string $format = null, array $context = [])
    {
        $data = $this->normalizer->normalize($object, $format, $context);

        $resourceClass = $context['resource_class'] ?? null;
        if ($resourceClass === Movie::class) {
            $releasedAt = new \DateTimeImmutable($data['data']['attributes']['releasedAt']);
            $data['data']['attributes']['releasedAt'] = $releasedAt->format('Y-m-d');
        }

        return $data;
    }

    public function supportsNormalization($data, string $format = null)
    {
        return $this->normalizer->supportsNormalization($data, $format);
    }

    public function denormalize($data, string $type, string $format = null, array $context = [])
    {
        return $this->normalizer->denormalize($data, $type, $format, $context);
    }

    public function supportsDenormalization($data, string $type, string $format = null)
    {
        return $this->normalizer->supportsDenormalization($data, $type, $format);
    }

    public function setSerializer(SerializerInterface $serializer)
    {
        if ($this->normalizer instanceof SerializerAwareInterface) {
            $this->normalizer->setSerializer($serializer);
        }
    }
}
