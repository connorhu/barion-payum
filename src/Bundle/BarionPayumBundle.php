<?php
declare(strict_types=1);

namespace CodeConjure\BarionPayum\Bundle;

use CodeConjure\BarionPayum\Bundle\DependencyInjection\BarionPayumExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class BarionPayumBundle extends Bundle
{
    public function getContainerExtension(): BarionPayumExtension
    {
        return new BarionPayumExtension();
    }
}
