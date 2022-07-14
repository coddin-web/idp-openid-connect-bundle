<?php

declare(strict_types=1);

namespace Tests\Unit\Coddin\IdentityProvider\Request\Validation;

use Coddin\IdentityProvider\Attribute\RequestValidation;
use Coddin\IdentityProvider\Request\Exception\RequestInvalidException;
use Coddin\IdentityProvider\Request\UserRegistration;
use Coddin\IdentityProvider\Request\Validation\RequestObjectResolver;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\InputBag;
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
    /** @var MockObject & Request */
    private MockObject|Request $request;
    private DenormalizerInterface $serializer;
    private ValidatorInterface $validator;
    /** @var MockObject & ArgumentMetadata */
    private MockObject|ArgumentMetadata $argumentData;

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

        $this->request = $this->createMock(Request::class);
        $this->argumentData = $this->createMock(ArgumentMetadata::class);
    }

    /**
     * @test
     * @covers ::supports
     */
    public function supports_false(): void
    {
        // Completely missing getType check.
        $requestObjectResolver = new RequestObjectResolver($this->serializer, $this->validator);
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

        $requestObjectResolver = new RequestObjectResolver($this->serializer, $this->validator);
        $supports = $requestObjectResolver->supports($this->request, $this->argumentData);

        self::assertFalse($supports);
    }

    /**
     * @test
     * @covers ::supports
     */
    public function supports(): void
    {
        $requestObjectResolver = new RequestObjectResolver($this->serializer, $this->validator);

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
     * @throws RequestInvalidException
     */
    public function resolve_supports_not_called(): void
    {
        $this->argumentData
            ->expects(self::once())
            ->method('getType')
            ->willReturn(null);

        self::expectException(\LogicException::class);
        self::expectExceptionMessage('`supports` should have been called and have handled the argumentType check');

        $requestObjectResolver = new RequestObjectResolver($this->serializer, $this->validator);
        $generator = $requestObjectResolver->resolve($this->request, $this->argumentData);

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

        $this->request
            ->expects(self::once())
            ->method('getMethod')
            ->willReturn('PUT');

        self::expectException(\LogicException::class);
        self::expectExceptionMessage('Other methods are not supported by this Application');

        $requestObjectResolver = new RequestObjectResolver($this->serializer, $this->validator);
        $generator = $requestObjectResolver->resolve($this->request, $this->argumentData);

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

        $this->request
            ->expects(self::once())
            ->method('getMethod')
            ->willReturn('POST');

        $inputBag = $this->createMock(InputBag::class);
        $inputBag
            ->expects(self::once())
            ->method('all')
            ->willReturn([]);
        $this->request
            ->request = $inputBag;

        self::expectException(RequestInvalidException::class);
        self::expectExceptionMessage(
            'Constraint violations found: `username` is a required field; `password` is a required field; `password_repeat` is a required field',
        );

        $requestObjectResolver = new RequestObjectResolver($this->serializer, $this->validator);
        $generator = $requestObjectResolver->resolve($this->request, $this->argumentData);

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

        $this->request
            ->expects(self::once())
            ->method('getMethod')
            ->willReturn('POST');

        $inputBag = $this->createMock(InputBag::class);
        $inputBag
            ->expects(self::once())
            ->method('all')
            ->willReturn([
                'username' => 'username',
                'password' => 'password',
                'password_repeat' => 'password_not_repeated',
            ]);
        $this->request
            ->request = $inputBag;

        self::expectException(RequestInvalidException::class);
        self::expectExceptionMessage(
            'Constraint violations found: Passwords do not match',
        );

        $requestObjectResolver = new RequestObjectResolver($this->serializer, $this->validator);
        $generator = $requestObjectResolver->resolve($this->request, $this->argumentData);

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

        $this->request
            ->expects(self::once())
            ->method('getMethod')
            ->willReturn('POST');

        $inputBag = $this->createMock(InputBag::class);
        $inputBag
            ->expects(self::once())
            ->method('all')
            ->willReturn([
                'username' => 'username',
                'password' => 'password',
                'password_repeat' => 'password',
            ]);
        $this->request
            ->request = $inputBag;

        $requestObjectResolver = new RequestObjectResolver($this->serializer, $this->validator);
        $generator = $requestObjectResolver->resolve($this->request, $this->argumentData);

        self::assertInstanceOf(\Generator::class, $generator);
        $dto = $generator->current();

        self::assertInstanceOf(UserRegistration::class, $dto);
    }
}
