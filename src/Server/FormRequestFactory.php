<?php
declare(strict_types=1);

namespace Upgate\LaravelJsonRpc\Server;

use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Factory as ValidationFactory;

final class FormRequestFactory
{

    /**
     * @var Container
     */
    private $container;

    /**
     * @var ValidationFactory|null
     */
    private $validationFactory = null;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function makeFormRequest(string $formRequestClass, RequestParams $requestParams = null): FormRequest
    {
        if (!class_exists($formRequestClass)) {
            throw new \InvalidArgumentException("FormRequest class does not exist: '$formRequestClass'");
        }

        if (!is_subclass_of($formRequestClass, FormRequest::class)) {
            throw new \InvalidArgumentException("$formRequestClass must be a subclass of " . FormRequest::class);
        }

        /** @var FormRequest $formRequest */
        $formRequest = $this->container->make($formRequestClass);

        return $formRequest->setRequestParams($requestParams);
    }

    public function makeValidator(FormRequest $formRequest): Validator
    {
        if (null === $this->validationFactory) {
            if (!class_exists(ValidationFactory::class)) {
                throw new \RuntimeException(
                    ValidationFactory::class . ' does not exist. '
                    . 'The Illuminate Validation package is required by LaravelJsonRpc FormRequest'
                );
            }

            $this->validationFactory = $this->container->make(ValidationFactory::class);
        }

        return $this->validationFactory->make(
            $formRequest->all(),
            $this->container->call([$formRequest, 'rules']),
            $formRequest->messages(),
            $formRequest->attributes()
        );
    }

}
