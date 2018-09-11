<?php

namespace App\Http\Helper;

class Helper
{
    public static function stopConversation($message)
    {
        return $message == 'cancelar' || $message == 'stop';
    }
}
