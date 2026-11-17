<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Error de transcripción que NO se arregla reintentando (audio vacío, texto demasiado largo, etc.).
 */
class TranscriptionException extends RuntimeException
{
}
