<?php
namespace Lawoole\Homer\Serialize\Serializations;

use Illuminate\Contracts\Validation\Validator as ValidatorContract;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator as ValidatorFacade;

class ValidatorSerialization extends Serialization
{
    /**
     * Validate result's messages.
     *
     * @var array
     */
    protected $messages;

    /**
     * Create a validator serialization instance.
     *
     * @param \Illuminate\Contracts\Validation\Validator $validator
     */
    public function __construct(ValidatorContract $validator)
    {
        $this->messages = $validator->errors()->messages();
    }

    /**
     * Recover the validator from the serialization.
     *
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function recover()
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
