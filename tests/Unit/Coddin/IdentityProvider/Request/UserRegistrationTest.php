<?php

declare(strict_types=1);

namespace Tests\Unit\Coddin\IdentityProvider\Request;

use Coddin\IdentityProvider\Request\Exception\RequestInvalidException;
use Coddin\IdentityProvider\Request\UserRegistration;
use Coddin\IdentityProvider\Request\Validation\RequestDtoValidator;
use Coddin\IdentityProvider\Request\Validation\RequestObjectDtoInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\PropertyInfo\PropertyInfoExtractor;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\ConstraintViolationListNormalizer;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\TraceableValidator;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @coversDefaultClass \Coddin\IdentityProvider\Request\UserRegistration
 */
final class UserRegistrationTest extends TestCase
{
    private ValidatorInterface $validator;
    private Serializer $serializer;

    protected function setUp(): void
    {
        $validation = Validation::createValidatorBuilder()
            ->enableAnnotationMapping()
            ->addDefaultDoctrineAnnotationReader()
            ->getValidator();
        $propertyInfoExtractor = new PropertyInfoExtractor(
            [new ReflectionExtractor()],
            [new PhpDocExtractor(), new ReflectionExtractor()],
            [new PhpDocExtractor()],
            [new ReflectionExtractor()],
            [new ReflectionExtractor()],
        );
        $this->validator = new TraceableValidator($validation);
        $this->serializer = new Serializer(
            [
                new GetSetMethodNormalizer(
                    nameConverter: new CamelCaseToSnakeCaseNameConverter(),
                    propertyTypeExtractor: $propertyInfoExtractor,
                ),
                new ArrayDenormalizer(),
                new ConstraintViolationListNormalizer(),
            ],
            [
                new JsonEncoder(),
            ],
        );
    }

    /**
     * @test
     * @covers ::validatePasswordRepeat
     * @noinspection PhpUnhandledExceptionInspection
     */
    public function missing_username(): void
    {
        self::expectException(RequestInvalidException::class);

        $data = [
            'username' => 'username',
            'password' => 'password',
            'password_repeat' => 'passwordd',
        ];
        /** @var RequestObjectDtoInterface $userRegistration */
        $userRegistration = $this->serializer->denormalize($data, UserRegistration::class);

        RequestDtoValidator::validate($this->validator, $userRegistration);
    }
}
