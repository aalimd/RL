<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../../install/helpers.php';

class InstallerHelpersTest extends TestCase
{
    public function test_installer_completion_requires_a_validated_installation_before_locking(): void
    {
        $tempDir = sys_get_temp_dir() . '/rl-installer-' . uniqid('', true);
        mkdir($tempDir, 0777, true);

        $envPath = $tempDir . '/.env';
        $lockPath = $tempDir . '/install.lock';

        file_put_contents($envPath, "APP_NAME=Test\nINSTALLED=true\n");

        $this->assertFalse(installer_is_complete($envPath, $lockPath, fn () => false));
        $this->assertFileDoesNotExist($lockPath);

        $this->assertTrue(installer_is_complete($envPath, $lockPath, fn () => true));
        $this->assertFileExists($lockPath);

        @unlink($envPath);
        @unlink($lockPath);
        @rmdir($tempDir);
    }

    public function test_installer_mark_complete_sets_flag_and_creates_lock_file(): void
    {
        $tempDir = sys_get_temp_dir() . '/rl-installer-' . uniqid('', true);
        mkdir($tempDir, 0777, true);

        $envPath = $tempDir . '/.env';
        $lockPath = $tempDir . '/install.lock';

        file_put_contents($envPath, "APP_NAME=Test\nINSTALLED=false\n");

        $this->assertTrue(installer_mark_complete($envPath, $lockPath));
        $this->assertStringContainsString('INSTALLED=true', (string) file_get_contents($envPath));
        $this->assertFileExists($lockPath);

        @unlink($envPath);
        @unlink($lockPath);
        @rmdir($tempDir);
    }
}
