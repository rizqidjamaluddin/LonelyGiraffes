<?php  namespace Giraffe\Users;

use Giraffe\Common\ValidationException;
use Respect\Validation\Exceptions\AbstractNestedException;
use Respect\Validation\Validator as V;

class UserProfileValidator
{
    public function validate(array $data)
    {
        $validator = V::key('bio', V::string()->length(0, 140), false);

        try {
            $validator->assert($data);
        } catch (AbstractNestedException $e) {
            $errors = $e->findMessages(['length']);
            throw new ValidationException('Could not update user.', $errors);
        }

        return true;
    }
} 