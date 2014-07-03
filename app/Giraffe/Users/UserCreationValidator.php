<?php  namespace Giraffe\Users;

use Giraffe\Common\ValidationException;
use InvalidArgumentException;
use Respect\Validation\Exceptions\AbstractNestedException;
use Respect\Validation\Validator as V;

class UserCreationValidator
{
    public function validate(array $data)
    {
        $validator = V::key('name', V::string()->length(0, 100), true)
                      ->key('email', V::email()->length(0, 200), true)
                      ->key('password', V::string()->length(0, 200), true);

        try {
            $validator->assert($data);
        } catch (AbstractNestedException $e) {
            $errors = $e->findMessages(['email', 'length', 'in']);
            throw new ValidationException('Could not create user.', $errors);
        }

        return true;
    }
} 