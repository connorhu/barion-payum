<?php
declare(strict_types=1);

namespace CodeConjure\BarionPayum\Request;

use ArrayAccess;
use Payum\Core\Request\Generic;

final class GetBarionStatus extends Generic
{
    public function __construct(ArrayAccess|array $model)
    {
        parent::__construct($model);
    }
}
