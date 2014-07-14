<?php  namespace Giraffe\Shouts;

use Giraffe\Common\ValidationException;
use Respect\Validation\Exceptions\AbstractNestedException;
use Respect\Validation\Validator as V;

class ShoutCreationValidator
{
    public function validate(array $data)
    {
        $validator = V::key('body', V::string()->notEmpty()->length(10, 1000));

        try {
            $validator->assert($data);
        } catch (AbstractNestedException $e) {
            $errors = $e->findMessages(['string', 'length']);
            throw new ValidationException('Could not create shout.', $errors);
        }

        return true;
    }
} 