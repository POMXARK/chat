<?php

namespace Domain\Interfaces;

interface ObjAIInterface
{
    public function changeSts(int $sts): int;
}
