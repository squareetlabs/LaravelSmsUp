<?php

namespace SquareetLabs\LaravelSmsUp\Exceptions;

use Exception;

/**
 * Class ValidationException
 * @package SquareetLabs\LaravelSmsUp\Exceptions
 */
class ValidationException extends Exception
{
    /**
     * @var array
     */
    protected $errors = [];

    /**
     * ValidationException constructor.
     *
     * @param string $message
     * @param array $errors
     * @param int $code
     */
    public function __construct($message = "", $errors = [], $code = 0)
    {
        parent::__construct($message, $code);
        $this->errors = $errors;
    }

    /**
     * Get validation errors.
     *
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Create a new validation exception with multiple errors.
     *
     * @param array $errors
     * @return static
     */
    public static function withErrors(array $errors)
    {
        $message = 'Errores de validación: ' . implode(', ', $errors);
        return new static($message, $errors);
    }
} 