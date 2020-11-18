<?php

declare(strict_types=1);

namespace App\Controller\ArgumentResolver;

use App\Controller\InputValue;
use Exception;
use ReflectionClass;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class InputValueResolver implements ArgumentValueResolverInterface
{
    /**
     * @var SerializerInterface
     */
    private SerializerInterface $serializer;

    /**
     * @var ValidatorInterface
     */
    private ValidatorInterface $validator;

    public function __construct(SerializerInterface $serializer, ValidatorInterface $validator)
    {
        $this->serializer = $serializer;
        $this->validator = $validator;
    }

    public function supports(Request $request, ArgumentMetadata $argument)
    {
        $reflection = new ReflectionClass($argument->getType());

        return $reflection->implementsInterface(InputValue::class);
    }

    public function resolve(Request $request, ArgumentMetadata $argument)
    {
        try {
            $object = $this->serializer->deserialize($request->getContent(), $argument->getType(), 'json');
        } catch (Exception $e) {
            throw new BadRequestHttpException('Invalid body: ' . $e->getMessage());
        }

        $errors = $this->validator->validate($object);

        if ($errors->count()) {
            $messages = [];
            foreach ($errors as $error) {
                $messages[] = sprintf('%s: %s', $error->getPropertyPath(), $error->getMessage());
            }
            throw new BadRequestHttpException(implode(' ', $messages));
        }

        yield $object;
    }
}