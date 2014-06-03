<?php  namespace Giraffe\Users;

use Giraffe\Common\ValidationException;
use InvalidArgumentException;
use Respect\Validation\Validator as V;

class UserCreationValidator
{

    public function validate(array $data)
    {
        $validator = V::key('firstname', V::string()->length(0, 100))
                      ->key('lastname', V::string()->length(0, 100))
                      ->key('email', V::email()->length(0, 200))
                      ->key('password', V::string()->length(0, 200))
                      ->key('gender', V::string()->in(['M', 'F', 'X']));

        try {
            $validator->assert($data);
        } catch (InvalidArgumentException $e) {
            $errors = $e->findMessages(
              [
                  'email' => '{{name}} is not a valid email',               // custom error message
                  'password' => 'Password must be under 200 characters',    // shown if 'password' field fails to pass
                  'length'                                                  // use default error message, shown if any 'length' test fails
              ]
            );
            throw new ValidationException('Could not create a new user.', $errors);
        }

        return true;
    }

}