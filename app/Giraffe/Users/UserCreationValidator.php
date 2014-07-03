<?php  namespace Giraffe\Users;

use Giraffe\Common\ValidationException;
use InvalidArgumentException;
use Respect\Validation\Exceptions\AbstractNestedException;
use Respect\Validation\Validator as V;

class UserCreationValidator
{
    public function validate(array $data)
    {
        $validator = V::key('name', V::string()->length(0, 100), false)
                      ->key('email', V::email()->length(0, 200),false)
                      ->key('password', V::string()->length(0, 200), false)
                      ->key('gender', V::string()->in(['M', 'F', 'X']), false);

        try {
            $validator->assert($data);
        } catch (AbstractNestedException $e) {
            $errors = $e->findMessages(['email', 'length', 'in']);
            throw new ValidationException('Could not create user.', $errors);
        }

        return true;
    }
} 