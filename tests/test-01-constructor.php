<?php

namespace Test\Imagine\Imagine;

use PHPUnit\Framework\TestCase;
use Imagine\Imagine;

final class ImagineConstructorTest extends TestCase
{
    private $fileValid = './tests/assets/file-valid.jpg';

    private $fileNotExist = './tests/assets/file-no-exist.jpg';

    private $fileCorrupted = './tests/assets/file-corrupted.jpg';

    /**
     *
     */
    public function test01(): void
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage('No image');

        $image = new Imagine();

        $this->assertInstanceOf(Imagine::class, $image);
    }

    /**
     *
     */
    public function test02(): void
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage('No image');

        $image = new Imagine('');

        $this->assertInstanceOf(Imagine::class, $image);
    }

    /**
     *
     */
    public function test03(): void
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage('Image Corrupted');

        $image = new Imagine(42);

        $this->assertInstanceOf(Imagine::class, $image);
    }

    /**
     *
     */
    public function test04(): void
    {
        $this->expectException(\TypeError::class);

        $image = new Imagine(null);

        $this->assertInstanceOf(Imagine::class, $image);
    }

    /**
     *
     */
    public function test05(): void
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage('Image Corrupted');

        $image = new Imagine($this->fileNotExist);

        $this->assertInstanceOf(Imagine::class, $image);
    }

    /**
     *
     */
    public function test06(): void
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage('Image Corrupted');

        $image = new Imagine($this->fileCorrupted);

        $this->assertInstanceOf(Imagine::class, $image);
    }

    /**
     *
     */
    public function test07(): void
    {
        $image = new Imagine($this->fileValid);

        $this->assertInstanceOf(Imagine::class, $image);
    }
}
