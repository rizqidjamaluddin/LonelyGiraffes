<?php  namespace Giraffe\Buddies\Requests;

use Giraffe\Common\ValidationException;
use Respect\Validation\Exceptions\AbstractNestedException;
use Respect\Validation\Validator as V;

class BuddyRequestCreationValidator
{
    public function validate(array $data)
    {
        $validator = V::key('from_user_id', V::not(V::equals($data['to_user_id'])));

        try {
            $validator->assert($data);
        } catch (AbstractNestedException $e) {
            throw new ValidationException('Could not create buddy request.', []);
        }

        return true;
    }
} 