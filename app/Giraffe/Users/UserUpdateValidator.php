<?php  namespace Giraffe\Users;

use Giraffe\Common\ValidationException;
use InvalidArgumentException;
use Respect\Validation\Exceptions\AbstractNestedException;
use Respect\Validation\Validator as V;

class UserUpdateValidator
{
    public function validate(array $data)
    {
        $validator = V::key('name', V::string()->length(0, 100), false)
                      ->key('email', V::email()->length(0, 200), false)
                      ->key('password', V::string()->length(0, 200), false)
                      ->key('gender', V::string()->in(['M', 'F', 'X']), false)
                      ->key('city', V::string()->length(0, 150), false)
                      ->key('state', V::string()->length(0, 150), false)
                      ->key('country', V::string()->length(0, 150), false);

        try {
            $validator->assert($data);
        } catch (AbstractNestedException $e) {
            $errors = $e->findMessages(['email', 'length', 'in']);
            throw new ValidationException('Could not update user.', $errors);
        }

        return true;
    }
} 