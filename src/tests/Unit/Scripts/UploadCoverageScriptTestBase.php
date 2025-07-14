<?php

namespace Tests\Unit\Scripts;

use PHPUnit\Framework\TestCase;

abstract class UploadCoverageScriptTestBase extends TestCase
{
    protected string $scriptPath;

    protected function setUp(): void
    {
        parent::setUp();
        $this->scriptPath = __DIR__.'/../../../scripts/upload-coverage.sh';
    }

    protected function createTempDirectory(): string
    {
        $tempDir = sys_get_temp_dir().'/test_coverage_'.uniqid();
        mkdir($tempDir);

        return $tempDir;
    }

    protected function createModifiedScript(string $tempDir): string
    {
        $tempScriptPath = $tempDir.'/test_script.sh';

        // Mock the PROJECT_ROOT to point to our temp directory
        $modifiedScript = str_replace(
            'PROJECT_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"',
            "PROJECT_ROOT=\"{$tempDir}\"",
            file_get_contents($this->scriptPath)
        );

        file_put_contents($tempScriptPath, $modifiedScript);
        chmod($tempScriptPath, 0755);

        return $tempScriptPath;
    }

    protected function cleanupTempFiles(array $files, array $dirs): void
    {
        foreach ($files as $file) {
            if (file_exists($file)) {
                unlink($file);
            }
        }

        foreach ($dirs as $dir) {
            if (is_dir($dir)) {
                rmdir($dir);
            }
        }
    }

    /**
     * Execute script command safely in controlled environment
     */
    protected function executeScriptCommand(string $scriptPath, string $command, array $env = []): string
    {
        $envVars = '';
        foreach ($env as $key => $value) {
            $envVars .= $key.'='.escapeshellarg($value).' ';
        }

        $fullCommand = $envVars.'bash '.escapeshellarg($scriptPath).' '.escapeshellarg($command).' 2>&1';

        return shell_exec($fullCommand) ?? '';
    }
}
