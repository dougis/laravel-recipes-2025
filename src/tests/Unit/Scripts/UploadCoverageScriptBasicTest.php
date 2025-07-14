<?php

namespace Tests\Unit\Scripts;

class UploadCoverageScriptBasicTest extends UploadCoverageScriptTestBase
{
    /** @test */
    public function script_exists_and_is_executable()
    {
        $this->assertFileExists($this->scriptPath);
        $this->assertTrue(is_executable($this->scriptPath));
    }

    /** @test */
    public function script_has_proper_shebang()
    {
        $content = file_get_contents($this->scriptPath);
        $this->assertStringStartsWith('#!/bin/bash', $content);
    }

    /** @test */
    public function script_requires_codacy_project_token()
    {
        // Test the check command without token using safe execution
        $output = $this->executeScriptCommand($this->scriptPath, 'check');
        $this->assertStringContainsString('CODACY_PROJECT_TOKEN', $output);
        $this->assertStringContainsString('environment variable is not set', $output);
    }

    /** @test */
    public function script_handles_usage_help()
    {
        $output = $this->executeScriptCommand($this->scriptPath, '--help');

        $this->assertStringContainsString('Usage:', $output);
        $this->assertStringContainsString('check', $output);
        $this->assertStringContainsString('install', $output);
        $this->assertStringContainsString('upload', $output);
    }

    /** @test */
    public function script_provides_install_command()
    {
        $scriptContent = file_get_contents($this->scriptPath);

        $this->assertStringContainsString('install_codacy_reporter()', $scriptContent);
        $this->assertStringContainsString('coverage.codacy.com/get.sh', $scriptContent);
    }

    /** @test */
    public function script_has_proper_error_handling()
    {
        $scriptContent = file_get_contents($this->scriptPath);

        $this->assertStringContainsString('set -e', $scriptContent);
        $this->assertStringContainsString('print_error', $scriptContent);
        $this->assertStringContainsString('exit 1', $scriptContent);
    }
}
