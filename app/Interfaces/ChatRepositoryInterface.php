<?php

namespace App\Interfaces;

use App\DTO\MessageDTO;

interface ChatRepositoryInterface
{
    public function postMessage(MessageDTO $message);
    public function loadMessages(MessageDTO $message);
}
