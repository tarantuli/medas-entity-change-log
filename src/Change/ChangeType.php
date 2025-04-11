<?php

declare(strict_types=1);

namespace Medas\EntityChangeLog\Change;

enum ChangeType: int
{
    case NewValue = 0;
    case Diff = 1;
}
