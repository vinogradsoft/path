<?php

namespace Test;

use Vinograd\Path\DefaultUrlQueryStrategy;
use PHPUnit\Framework\TestCase;

class DefaultUrlQueryStrategyTest extends TestCase
{
    public function testUpdateQuery()
    {
        $strategy = new DefaultUrlQueryStrategy();
        $result = $strategy->updateQuery([]);
        self::assertEquals('', $result);
        $result = $strategy->updateQuery(['key' => 'value']);
        self::assertEquals('key=value', $result);
    }
}
