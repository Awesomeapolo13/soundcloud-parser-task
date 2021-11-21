<?php

namespace App\Exception;

class NotFoundAuthorIdException extends \Exception
{
    protected $message = 'Не удалось найти id автора';
}