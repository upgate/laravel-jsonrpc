<?php

use Upgate\LaravelJsonRpc\Utility\DeepClone;
use PHPUnit\Framework\TestCase;

class DeepCloneTest extends TestCase
{

    public function testDeepCloneInnerStdClass()
    {
        $src = new stdClass();
        $src->inner = new stdClass();
        $src->inner->value = 'test';
        $clone = DeepClone::deepClone($src);
        $this->assertEquals($src, $clone);
        $this->assertNotSame($src, $clone);
        $this->assertNotSame($src->inner, $clone->inner);
    }

    public function testDeepCloneArrayOfStdClass()
    {
        $inner1 = new stdClass();
        $inner1->value = 'test1';
        $inner2 = new stdClass();
        $inner2->value = 'test2';
        $src = [$inner1, $inner2];
        $clone = DeepClone::deepClone($src);
        $this->assertEquals($src, $clone);
        $this->assertNotSame($src[0], $clone[0]);
        $this->assertNotSame($src[1], $clone[1]);
    }

}
