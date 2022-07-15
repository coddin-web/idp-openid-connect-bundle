<?php

/** @noinspection PhpMissingFieldTypeInspection */

declare(strict_types=1);

namespace Tests\Unit\Coddin\IdentityProvider\Request\Validation;

use Coddin\IdentityProvider\Attribute\RequestValidation;
use Coddin\IdentityProvider\Request\UserRegistration;
use Coddin\IdentityProvider\Request\Validation\Exception\RequestConstraintException;
use Coddin\IdentityProvider\Request\Validation\RequestObjectDtoInterface;
use Coddin\IdentityProvider\Request\Validation\RequestObjectResolver;
use Coddin\IdentityProvider\Request\Validation\ValidationDataResolver;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\PropertyInfo\PropertyInfoExtractor;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\ConstraintViolationListNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\TraceableValidator;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @coversDefaultClass \Coddin\IdentityProvider\Request\Validation\RequestObjectResolver
 * @covers ::__construct
 * @covers ::isSupportedArgument
 * @covers ::isInstanceOf
 */
final class RequestObjectResolverTest extends TestCase
{
    private Request $request;
    private DenormalizerInterface $serializer;
    private ValidatorInterface $validator;
    /** @var MockObject & ArgumentMetadata */
    private $argumentData;
    /** @var ValidationDataResolver & MockObject */
    private $validationDataResolver;

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
        $this->validationDataResolver = $this->createMock(ValidationDataResolver::class);

        $this->request = new Request();
        $this->argumentData = $this->createMock(ArgumentMetadata::class);
    }

    /**
     * @test
     * @covers ::supports
     */
    public function supports_false(): void
    {
        // Completely missing getType check.
        $requestObjectResolver = $this->createRequestObjectResolver();
        $supports = $requestObjectResolver->supports($this->request, $this->argumentData);

        self::assertFalse($supports);

        // Not the correct instanceOf.
        $this->argumentData
            ->expects(self::exactly(3))
            ->method('getType')
            ->willReturn(Request::class);

        $randomRequest = $this->createMock(Request::class);
        $this->argumentData
            ->expects(self::once())
            ->method('getAttributes')
            ->willReturn([$randomRequest]);

        $requestObjectResolver = $this->createRequestObjectResolver();
        $supports = $requestObjectResolver->supports($this->request, $this->argumentData);

        self::assertFalse($supports);
    }

    /**
     * @test
     * @covers ::supports
     */
    public function supports(): void
    {
        $requestObjectResolver = $this->createRequestObjectResolver();

        $this->argumentData
            ->expects(self::exactly(4))
            ->method('getType')
            ->willReturn(UserRegistration::class);

        $requestValidation = $this->createMock(RequestValidation::class);
        $this->argumentData
            ->expects(self::once())
            ->method('getAttributes')
            ->willReturn([$requestValidation]);

        $supports = $requestObjectResolver->supports($this->request, $this->argumentData);
        self::assertTrue($supports);
    }

    /**
     * @test
     * @covers ::resolve
     */
    public function resolve_supports_not_called(): void
    {
        $this->argumentData
            ->expects(self::once())
            ->method('getType')
            ->willReturn(null);

        self::expectException(\LogicException::class);
        self::expectExceptionMessage('`supports` should have been called and have handled the argumentType check');

        $generator = $this->getGenerator();
        self::assertInstanceOf(\Generator::class, $generator);
        $generator->next();
    }

    /**
     * @test
     * @covers ::resolve
     */
    public function resolve_incorrect_request_method(): void
    {
        $this->argumentData
            ->expects(self::once())
            ->method('getType')
            ->willReturn(UserRegistration::class);

        $this->request->setMethod('PUT');

        self::expectException(\Exception::class);
        self::expectExceptionMessage('Other methods are not supported by this Request resolver');

        $generator = $this->getGenerator();
        self::assertInstanceOf(\Generator::class, $generator);
        $generator->next();
    }

    /**
     * @test
     * @covers ::resolve
     */
    public function resolve_for_method_post_denormalization_fails(): void
    {
        $this->argumentData
            ->expects(self::once())
            ->method('getType')
            ->willReturn(UserRegistration::class);

        $this->request = new Request(
            request: [],
        );
        $this->request->setMethod('POST');

        self::expectException(RequestConstraintException::class);
        self::expectExceptionMessage(
            'A constraint violation has occurred',
        );

        $generator = $this->getGenerator();
        self::assertInstanceOf(\Generator::class, $generator);
        $generator->next();
    }

    /**
     * @test
     * @covers ::resolve
     */
    public function resolve_for_method_post_body_incorrect(): void
    {
        $this->argumentData
            ->expects(self::once())
            ->method('getType')
            ->willReturn(UserRegistration::class);

        $this->request = new Request(
            request: [
                'username' => 'username',
                'password' => 'password',
                'password_repeat' => 'password_not_repeated',
            ],
        );
        $this->request->setMethod('POST');

        self::expectException(RequestConstraintException::class);
        self::expectExceptionMessage(
            'A constraint violation has occurred',
        );

        $generator = $this->getGenerator();
        self::assertInstanceOf(\Generator::class, $generator);
        $generator->next();
    }

    /**
     * @test
     * @covers ::resolve
     */
    public function resolve(): void
    {
        $this->argumentData
            ->expects(self::once())
            ->method('getType')
            ->willReturn(UserRegistration::class);

        $data = [
            'username' => 'username',
            'password' => 'password',
            'password_repeat' => 'password',
        ];
        $this->request = new Request(
            request: $data,
        );
        $this->request->setMethod('POST');

        $this->validationDataResolver
            ->expects(self::once())
            ->method('resolve')
            ->willReturn($data);

        $generator = $this->getGenerator();
        self::assertInstanceOf(\Generator::class, $generator);
        $dto = $generator->current();

        self::assertInstanceOf(UserRegistration::class, $dto);
    }

    private function createRequestObjectResolver(): RequestObjectResolver
    {
        return new RequestObjectResolver(
            serializer: $this->serializer,
            validator: $this->validator,
            validationDataResolver: $this->validationDataResolver,
        );
    }

    /**
     * @return iterable<RequestObjectDtoInterface>
     * @noinspection PhpUnhandledExceptionInspection
     * @noinspection PhpDocMissingThrowsInspection
     */
    private function getGenerator(): iterable
    {
        $requestObjectResolver = $this->createRequestObjectResolver();

        return $requestObjectResolver->resolve($this->request, $this->argumentData);
    }
}
