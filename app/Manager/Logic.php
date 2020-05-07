<?php

namespace App\Manager;

class Logic
{
    public function matchPlayer($playerId)
    {
        DataCenter::pushPlayerToWaitList($playerId);
    }
}
