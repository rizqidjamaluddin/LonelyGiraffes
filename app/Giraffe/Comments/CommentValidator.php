<?php  namespace Giraffe\Comments; 
use Giraffe\Common\ValidationException;

class CommentValidator
{
    public function validate($body)
    {
        if (!isset($body)) {
            throw new ValidationException("Failed to post comment.", ['body' => "Comment body is required."]);
        }

        if (strlen($body) < 1) {
            throw new ValidationException("Failed to post comment.", ['body' => "Comment body is required."]);
        }
    }
} 