<?php

declare(strict_types = 1);

namespace AssinaMe\Exception;

use Exception;

class SDKError extends Exception {
    protected $message = 'Error while tring to send a request.';
}
