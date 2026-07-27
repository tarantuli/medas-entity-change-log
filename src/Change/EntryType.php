<?php

declare(strict_types=1);

namespace Medas\EntityChangeLog\Change;

enum EntryType: int
{
    case EntityDeletion = 0;
    case EntityCreation = 1;
    case PropertyChange = 2;
    case PropertySet = 5;
    case PropertyUnset = 6;
    case AddedConnection = 3;
    case RemovedConnection = 4;
}
