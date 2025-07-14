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
     * Execute script command safely in controlled environment using proc_open
     */
    protected function executeScriptCommand(string $scriptPath, string $command, array $env = []): string
    {
        $descriptorSpec = [
            0 => ['pipe', 'r'],  // stdin
            1 => ['pipe', 'w'],  // stdout
            2 => ['pipe', 'w'],  // stderr
        ];

        $cmd = ['bash', $scriptPath, $command];
        $environment = array_merge(getenv(), $env);

        $process = proc_open($cmd, $descriptorSpec, $pipes, null, $environment);

        if (is_resource($process)) {
            fclose($pipes[0]); // Close stdin

            $output = stream_get_contents($pipes[1]);
            $error = stream_get_contents($pipes[2]);

            fclose($pipes[1]);
            fclose($pipes[2]);

            proc_close($process);

            return $output.$error;
        }

        return '';
    }
}
