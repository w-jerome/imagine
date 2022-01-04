<?php

namespace Test\Imagine\Imagine;

use PHPUnit\Framework\TestCase;
use Imagine\Imagine;

final class ImagineConstructorRenderTest extends TestCase
{
    private $fileValid = './tests/assets/example-valid.jpg';

    private $fileNotExist = './tests/assets/example-no-exist.jpg';

    private $fileCorrupted = './tests/assets/example-corrupted.jpg';

    /**
     *
     */
    public function test1(): void
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage('No image');

        $image = new Imagine();
        $image->render();
    }

    /**
     *
     */
    public function test2(): void
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage('No image');

        $image = new Imagine('');
        $image->render();
    }

    /**
     *
     */
    public function test3(): void
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage('Image Corrupted');

        $image = new Imagine(42);
        $image->render();
    }

    /**
     *
     */
    public function test4(): void
    {
        $this->expectException(\TypeError::class);

        $image = new Imagine(null);
        $image->render();
    }

    /**
     *
     */
    public function test5(): void
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage('Image Corrupted');

        $image = new Imagine($this->fileNotExist);
        $image->render();
    }

    /**
     *
     */
    public function test6(): void
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage('Image Corrupted');

        $image = new Imagine($this->fileCorrupted);
        $image->render();
    }

    /**
     *
     */
    public function test7(): void
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage('The destination path does not exist');

        $image = new Imagine($this->fileValid);
        $image->render();
    }
}
