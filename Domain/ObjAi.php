<?php

namespace Domain;

use Domain\Interfaces\ObjAIInterface;

class ObjAi implements ObjAIInterface
{
     function changeSts(int $sts): int
     {
         return $sts == 1 ? 2 : 1;
     }
}
