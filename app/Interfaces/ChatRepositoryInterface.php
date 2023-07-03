<?php

namespace App\Interfaces;

interface ChatRepositoryInterface
{
    public function postMessage(array $dto);
    public function loadMessages(array $dto);
}
