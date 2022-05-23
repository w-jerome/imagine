<?php

namespace Test\Imagine\Imagine;

use PHPUnit\Framework\TestCase;
use AssertGD\GDAssertTrait;
use Imagine\Imagine;

final class ImagineSaveTest extends TestCase
{
    use GDAssertTrait;

    private $file = './tests/assets/file-valid.jpg';

    /**
     *
     */
    public function test01(): void
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage('The destination path does not exist');

        $image = new Imagine($this->file);
        $isCreate = $image->save('');

        $this->assertSame($isCreate, false);
    }

    /**
     *
     */
    public function test02(): void
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage('The destination path does not exist');

        $outputPath = './tests/no-valid-folder/test-03-process-02.jpg';

        $image = new Imagine($this->file);
        $isCreate = $image->save($outputPath);

        $this->assertSame($isCreate, false);
        $this->assertFileDoesNotExist($outputPath);
    }

    /**
     *
     */
    public function test03(): void
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage('File rewriting is disabled');

        $outputPath = './tests/assets/output/test-03-process-03.jpg';

        $image = new Imagine($this->file);
        $image->setIsOverride(false);
        $isCreate = $image->save($outputPath);

        $this->assertSame($isCreate, false);
        $this->assertFileExists($outputPath);
    }

    /**
     *
     */
    public function test04(): void
    {
        $inputPath = './tests/assets/input/test-03-process-04.jpg';
        $outputPath = './tests/assets/output-temp/test-03-process-04.jpg';

        $image = new Imagine($this->file);
        $image = $image->save($outputPath);

        $this->assertFileExists($outputPath);
        $this->assertSimilarGD($inputPath, $outputPath);
    }
}
