<?php

namespace App\DTO;

class MessageDTO
{
    public string $from;
    public string $to;
    public int $stmt;
    public string|null $text;
    public object|null $files;

    public function __construct(string $from, string $to, int $stmt,
                                string $text = null, $files = null)
    {
        $this->from = $from;
        $this->to = $to;
        $this->stmt = $stmt;
        $this->files = $files;
        $this->text = $text;
    }
}
