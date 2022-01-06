<?php

namespace Test\Imagine\Imagine;

use PHPUnit\Framework\TestCase;
use Imagine\Imagine;

final class ImagineDisplayOnBrowserTest extends TestCase
{
    private $file = './tests/assets/file-valid.jpg';

    /**
     *
     */
    public function test01(): void
    {
        $image = new Imagine($this->file);

        ob_start();

        $image->displayOnBrowser();

        $fileStream = ob_get_contents();
        ob_end_clean();

        $this->assertStringContainsString('gd-jpeg', substr($fileStream, 0, 100));
    }
}
