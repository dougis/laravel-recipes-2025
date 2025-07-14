<?php

namespace Tests\Unit\Scripts;

class UploadCoverageScriptDetectionTest extends UploadCoverageScriptTestBase
{
    /** @test */
    public function script_detects_when_no_coverage_files_exist()
    {
        // Create a temporary directory without coverage files
        $tempDir = $this->createTempDirectory();
        $tempScriptPath = $this->createModifiedScript($tempDir);

        try {
            // Run check command with controlled environment
            $output = $this->executeScriptCommand($tempScriptPath, 'check', ['CODACY_PROJECT_TOKEN' => 'test']);

            $this->assertStringContainsString('No coverage files found', $output);
            $this->assertStringContainsString('coverage-unit.xml', $output);
            $this->assertStringContainsString('coverage-feature.xml', $output);
            $this->assertStringContainsString('coverage.xml', $output);
            $this->assertStringContainsString('coverage/lcov.info', $output);
        } finally {
            $this->cleanupTempFiles([$tempScriptPath], [$tempDir]);
        }
    }

    /** @test */
    public function script_detects_php_coverage_files()
    {
        // Create a temporary directory with PHP coverage files
        $tempDir = $this->createTempDirectory();
        $tempScriptPath = $this->createModifiedScript($tempDir);
        $coverageFiles = [
            $tempDir.'/coverage-unit.xml',
            $tempDir.'/coverage-feature.xml',
            $tempDir.'/coverage.xml',
        ];

        try {
            // Create mock coverage files
            foreach ($coverageFiles as $file) {
                file_put_contents($file, '<?xml version="1.0"?><coverage></coverage>');
            }

            // Run check command with controlled environment
            $output = $this->executeScriptCommand($tempScriptPath, 'check', ['CODACY_PROJECT_TOKEN' => 'test']);

            $this->assertStringContainsString('Found 3 coverage file(s)', $output);
            $this->assertStringContainsString('coverage-unit.xml', $output);
            $this->assertStringContainsString('coverage-feature.xml', $output);
            $this->assertStringContainsString('coverage.xml', $output);
        } finally {
            $this->cleanupTempFiles(array_merge([$tempScriptPath], $coverageFiles), [$tempDir]);
        }
    }

    /** @test */
    public function script_detects_javascript_coverage_files()
    {
        // Create a temporary directory with JavaScript coverage files
        $tempDir = $this->createTempDirectory();
        $coverageDir = $tempDir.'/coverage';
        mkdir($coverageDir);
        $tempScriptPath = $this->createModifiedScript($tempDir);
        $lcovFile = $coverageDir.'/lcov.info';

        try {
            // Create mock LCOV coverage file
            file_put_contents($lcovFile, 'TN:\nSF:src/example.js\nFNF:0\nFNH:0\nLF:10\nLH:8\nend_of_record');

            // Run check command with controlled environment
            $output = $this->executeScriptCommand($tempScriptPath, 'check', ['CODACY_PROJECT_TOKEN' => 'test']);

            $this->assertStringContainsString('Found 1 coverage file(s)', $output);
            $this->assertStringContainsString('coverage/lcov.info', $output);
        } finally {
            $this->cleanupTempFiles([$tempScriptPath, $lcovFile], [$coverageDir, $tempDir]);
        }
    }
}
