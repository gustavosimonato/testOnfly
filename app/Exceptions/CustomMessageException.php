<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Response;

class CustomMessageException extends Exception
{
    protected $message;
    protected $code;

    public function __construct($message = "An error occurred", $code = Response::HTTP_BAD_REQUEST)
    {
        $this->message = $message;
        $this->code = $code;
        parent::__construct($this->message, $this->code);
    }

    public function render()
    {
        return response()->json([
            'success' => false,
            'message' => $this->message,
        ], $this->code);
    }
}
