<?php

namespace Tests\Feature;

use Symfony\Component\Process\Process;
use Tests\TestCase;

class QrVendorCompatibilityTest extends TestCase
{
    public function test_qr_generator_initializes_without_php84_deprecations(): void
    {
        $bootstrap = var_export(base_path('vendor/autoload.php'), true);
        $code = "require {$bootstrap}; new SimpleSoftwareIO\\QrCode\\Generator(); echo 'qr-ok';";

        $process = new Process([
            PHP_BINARY,
            '-d',
            'error_reporting=E_ALL',
            '-d',
            'display_errors=1',
            '-r',
            $code,
        ], base_path());

        $process->run();

        $output = $process->getOutput() . $process->getErrorOutput();

        $this->assertSame(0, $process->getExitCode(), $output);
        $this->assertStringContainsString('qr-ok', $output);
        $this->assertStringNotContainsString('Deprecated:', $output);
    }
}
