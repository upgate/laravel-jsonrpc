<?php
declare(strict_types=1);

use Upgate\LaravelJsonRpc\Server\FormRequest;
use Upgate\LaravelJsonRpc\Server\FormRequestFactory;
use Upgate\LaravelJsonRpc\Server\RequestParams;

class FormRequestTest extends \PHPUnit\Framework\TestCase
{

    /**
     * @var FormRequestFactory
     */
    private $factory;

    protected function setUp(): void
    {
        $container = new Illuminate\Container\Container();
        $container->bind(
            \Illuminate\Contracts\Translation\Translator::class,
            function () {
                return new \Illuminate\Translation\Translator(new \Illuminate\Translation\ArrayLoader(), 'en');
            }
        );
        $this->factory = new FormRequestFactory($container);
    }

    public function testValidationPasses()
    {
        $formRequest = $this->factory->makeFormRequest(FormRequestTest_FormRequest::class);
        $formRequest->setRequestParams(RequestParams::constructNamed(['id' => 1, 'email' => 'test@example.com']));
        /** @var Illuminate\Contracts\Validation\Validator $validator */
        $validator = $this->factory->makeValidator($formRequest);
        $this->assertFalse($validator->fails());
    }

    public function testValidationFails()
    {
        $formRequest = $this->factory->makeFormRequest(FormRequestTest_FormRequest::class);
        $formRequest->setRequestParams(RequestParams::constructNamed(['email' => 'not an email']));
        /** @var Illuminate\Contracts\Validation\Validator $validator */
        $validator = $this->factory->makeValidator($formRequest);
        $this->assertTrue($validator->fails());
    }

    public function testValidationMessages()
    {
        $formRequest = $this->factory->makeFormRequest(FormRequestTest_FormRequest::class);
        $formRequest->setRequestParams(RequestParams::constructNamed([]));
        /** @var Illuminate\Contracts\Validation\Validator $validator */
        $validator = $this->factory->makeValidator($formRequest);
        $expectedMessages = [
            'id' => ['I need a nice id'],
            'email' => ['I really need a great email'],
        ];
        $this->assertSame($expectedMessages, $validator->getMessageBag()->toArray());
    }

}

class FormRequestTest_FormRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'id'    => 'required|numeric',
            'email' => 'required|email'
        ];
    }

    public function messages(): array
    {
        return [
            'id.required'    => 'I need :attribute',
            'email.required' => 'I really need :attribute',
        ];
    }

    public function attributes(): array
    {
        return [
            'id'    => 'a nice id',
            'email' => 'a great email',
        ];
    }

}
