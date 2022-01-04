<?php

namespace Test\Imagine\Imagine;

use PHPUnit\Framework\TestCase;
use Imagine\Imagine;

final class ImagineConstructorDestinationRenderTest extends TestCase
{
    private $fileValid = './tests/assets/example-valid.jpg';

    private $fileNotExist = './tests/assets/example-no-exist.jpg';

    private $fileCorrupted = './tests/assets/example-corrupted.jpg';

    private $folderDestinationValid = './tests/temps';

    private $folderDestinationNotExist = './tests/temps-no-exist';

    /**
     *
     */
    public function test1(): void
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage('No image');

        $image = new Imagine();
        $image->setDestination('');
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
        $image->setDestination(42);
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
        $image->setDestination($this->folderDestinationNotExist);
        $image->render();
    }

    /**
     *
     */
    public function test4(): void
    {
        $this->expectException(\TypeError::class);

        $image = new Imagine(null);
        $image->setDestination($this->folderDestinationNotExist);
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
        $image->setDestination($this->folderDestinationNotExist);
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
        $image->setDestination($this->folderDestinationNotExist);
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
        $image->setDestination('');
        $image->render();
    }

    /**
     *
     */
    public function test8(): void
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage('The destination path does not exist');

        $image = new Imagine($this->fileValid);
        $image->setDestination(42);
        $image->render();
    }

    /**
     *
     */
    public function test9(): void
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage('The destination path does not exist');

        $image = new Imagine($this->fileValid);
        $image->setDestination($this->folderDestinationNotExist);
        $image->render();
    }

    /**
     *
     */
    public function test10(): void
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage('Image name not register');

        $image = new Imagine($this->fileValid);
        $image->setDestination($this->folderDestinationValid);
        $image->render();
    }

    /**
     *
     */
    public function test11(): void
    {
        $image = new Imagine($this->fileValid);
        $image->setName('my-picture');
        $image->setDestination($this->folderDestinationValid);

        $this->assertSame('my-picture.jpg', $image->render());
    }

    /**
     *
     */
    public function test12(): void
    {
        $image = new Imagine($this->fileValid);
        $image->setDestination(null);

        ob_start();

        $image->render();

        $fileStream = ob_get_contents();
        ob_end_clean();

        $this->assertStringContainsString('gd-jpeg', $fileStream);
    }
}
