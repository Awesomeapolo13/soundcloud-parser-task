<?php

namespace App\Exception;

class HttpRequestException extends \Exception
{
    protected $message = 'Не удалось получить данные при выполнении запроса';
}