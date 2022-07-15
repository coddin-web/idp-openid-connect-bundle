<?php

declare(strict_types=1);

namespace Coddin\IdentityProvider\Request\Validation;

use Coddin\IdentityProvider\Attribute\RequestValidation;
use Coddin\IdentityProvider\Request\Exception\NotInstanceOfException;
use Coddin\IdentityProvider\Request\Exception\RequestInvalidException;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class RequestObjectResolver implements ArgumentValueResolverInterface
{
    public function __construct(
        private readonly DenormalizerInterface $serializer,
        private readonly ValidatorInterface $validator,
        private readonly ValidationDataResolver $validationDataResolver,
    ) {
    }

    public function supports(Request $request, ArgumentMetadata $argument): bool
    {
        return $this->isSupportedArgument($argument);
    }

    /**
     * @return iterable<RequestObjectDtoInterface>
     * @throws RequestInvalidException
     * @throws \Exception
     */
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        $type = $argument->getType();
        if (!is_string($type)) {
            throw new \LogicException('`supports` should have been called and have handled the argumentType check');
        }

        $requestMethod = $request->getMethod();
        $requestData = $request->request->all();
        $data = match ($requestMethod) {
            Request::METHOD_POST => $requestData,
            default => throw new \Exception('Other methods are not supported by this Request resolver'),
        };

        $validationData = $this->validationDataResolver->resolve($type, $data);

        try {
            /** @var RequestObjectDtoInterface $dto */
            $dto = $this->serializer->denormalize($validationData, $type);
            RequestDtoValidator::validate($this->validator, $dto);

            yield $dto;
        // @codeCoverageIgnoreStart
        } catch (\TypeError | ExceptionInterface | NotNormalizableValueException) {
            throw RequestInvalidException::createForDenormalization('The structure of the request is malformed');
        }
        // @codeCoverageIgnoreEnd
    }

    private function isSupportedArgument(ArgumentMetadata $argument): bool
    {
        if (!is_string($argument->getType()) || $argument->getType() === '' || !class_exists($argument->getType())) {
            return false;
        }

        // If the Attribute has been added to the argument.
        try {
            array_map([$this, 'isInstanceOf'], $argument->getAttributes());
        } catch (NotInstanceOfException) {
            return false;
        }

        // If the Attribute has been added to the class.
        try {
            $typeReflectionClass = new ReflectionClass($argument->getType());
            $classAttributes = $typeReflectionClass->getAttributes(
                RequestValidation::class,
                ReflectionAttribute::IS_INSTANCEOF,
            );

            if (count($classAttributes) === 0) {
                return false;
            }
        // @codeCoverageIgnoreStart
        } catch (ReflectionException) {
            return false;
        // @codeCoverageIgnoreEnd
        }

        return true;
    }

    /**
     * @throws NotInstanceOfException
     */
    private function isInstanceOf(object $attribute): void
    {
        if (!$attribute instanceof RequestValidation) {
            throw new NotInstanceOfException();
        }
    }
}
