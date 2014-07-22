<?php  namespace Giraffe\Buddies;

use Giraffe\Common\ValidationException;
use Respect\Validation\Exceptions\AbstractNestedException;
use Respect\Validation\Validator as V;

class BuddyCreationValidator
{
    public function validate(array $data)
    {
        //user1_id < user2_id
        $validator = V::key('user1_id', V::max($data['user2_id']));
        // Note that this takes care of the case of user1_id==user2_id

        try {
            $validator->assert($data);
        } catch (AbstractNestedException $e) {
            $errors = $e->findMessages(['email', 'length', 'in']); //TODO: I have absolutely no idea what to put here.
            throw new ValidationException('Could not create buddy.', $errors);
        }

        return true;
    }
} 