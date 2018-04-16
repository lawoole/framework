<?php
namespace Lawoole\Homer\Serialization;

use Illuminate\Contracts\Validation\Validator as ValidatorContract;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator as ValidatorFacade;

class ValidatorSerialization
{
    /**
     * 验证信息
     *
     * @var array
     */
    protected $messages;

    /**
     * 创建验证器包
     *
     * @param \Illuminate\Contracts\Validation\Validator $validator
     */
    public function __construct(ValidatorContract $validator)
    {
        $this->messages = $validator->errors()->messages();
    }

    /**
     * 获得验证器
     *
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function getValidator()
    {
        $validator = ValidatorFacade::make([], []);

        foreach ($this->messages as $key => $value) {
            foreach (Arr::wrap($value) as $message) {
                $validator->errors()->add($key, $message);
            }
        }

        return $validator;
    }
}
