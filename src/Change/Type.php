<?php

declare(strict_types=1);

namespace Medas\EntitiesChangeLog\Change;

enum Type: int
{
    case EntityDeletion = 0;
    case EntityCreation = 1;
    case PropertyChange = 2;
    case AddedConnection = 3;
    case RemovedConnection = 4;
}
