<?php
namespace Govia\Fraser;


class InvalidUriException extends \Exception
{
    protected $code = 500;

    protected $message = 'Invalid Uniform Resource Identifier.';
}