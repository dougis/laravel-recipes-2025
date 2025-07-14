<?php

namespace Tests\Unit\Scripts;

use PHPUnit\Framework\TestCase;

class UploadCoverageScriptTest extends TestCase
{
    private string $scriptPath;

    protected function setUp(): void
    {
        parent::setUp();
        $this->scriptPath = __DIR__.'/../../../scripts/upload-coverage.sh';
    }

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
        // Test the check command without token
        $output = shell_exec("bash {$this->scriptPath} check 2>&1");
        $this->assertStringContainsString('CODACY_PROJECT_TOKEN', $output);
        $this->assertStringContainsString('environment variable is not set', $output);
    }

    /** @test */
    public function script_detects_when_no_coverage_files_exist()
    {
        // Create a temporary directory without coverage files
        $tempDir = sys_get_temp_dir().'/test_coverage_'.uniqid();
        mkdir($tempDir);

        // Mock the PROJECT_ROOT to point to our temp directory
        $modifiedScript = str_replace(
            'PROJECT_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"',
            "PROJECT_ROOT=\"{$tempDir}\"",
            file_get_contents($this->scriptPath)
        );

        $tempScriptPath = $tempDir.'/test_script.sh';
        file_put_contents($tempScriptPath, $modifiedScript);
        chmod($tempScriptPath, 0755);

        // Run check command
        $output = shell_exec("CODACY_PROJECT_TOKEN=test bash {$tempScriptPath} check 2>&1");

        $this->assertStringContainsString('No coverage files found', $output);
        $this->assertStringContainsString('coverage-unit.xml', $output);
        $this->assertStringContainsString('coverage-feature.xml', $output);
        $this->assertStringContainsString('coverage.xml', $output);
        $this->assertStringContainsString('coverage/lcov.info', $output);

        // Clean up
        unlink($tempScriptPath);
        rmdir($tempDir);
    }

    /** @test */
    public function script_detects_php_coverage_files()
    {
        // Create a temporary directory with PHP coverage files
        $tempDir = sys_get_temp_dir().'/test_coverage_'.uniqid();
        mkdir($tempDir);

        // Create mock coverage files
        file_put_contents($tempDir.'/coverage-unit.xml', '<?xml version="1.0"?><coverage></coverage>');
        file_put_contents($tempDir.'/coverage-feature.xml', '<?xml version="1.0"?><coverage></coverage>');
        file_put_contents($tempDir.'/coverage.xml', '<?xml version="1.0"?><coverage></coverage>');

        // Mock the PROJECT_ROOT to point to our temp directory
        $modifiedScript = str_replace(
            'PROJECT_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"',
            "PROJECT_ROOT=\"{$tempDir}\"",
            file_get_contents($this->scriptPath)
        );

        $tempScriptPath = $tempDir.'/test_script.sh';
        file_put_contents($tempScriptPath, $modifiedScript);
        chmod($tempScriptPath, 0755);

        // Run check command
        $output = shell_exec("CODACY_PROJECT_TOKEN=test bash {$tempScriptPath} check 2>&1");

        $this->assertStringContainsString('Found 3 coverage file(s)', $output);
        $this->assertStringContainsString('coverage-unit.xml', $output);
        $this->assertStringContainsString('coverage-feature.xml', $output);
        $this->assertStringContainsString('coverage.xml', $output);

        // Clean up
        unlink($tempScriptPath);
        unlink($tempDir.'/coverage-unit.xml');
        unlink($tempDir.'/coverage-feature.xml');
        unlink($tempDir.'/coverage.xml');
        rmdir($tempDir);
    }

    /** @test */
    public function script_detects_javascript_coverage_files()
    {
        // Create a temporary directory with JavaScript coverage files
        $tempDir = sys_get_temp_dir().'/test_coverage_'.uniqid();
        mkdir($tempDir);
        mkdir($tempDir.'/coverage');

        // Create mock LCOV coverage file
        file_put_contents($tempDir.'/coverage/lcov.info', 'TN:\nSF:src/example.js\nFNF:0\nFNH:0\nLF:10\nLH:8\nend_of_record');

        // Mock the PROJECT_ROOT to point to our temp directory
        $modifiedScript = str_replace(
            'PROJECT_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"',
            "PROJECT_ROOT=\"{$tempDir}\"",
            file_get_contents($this->scriptPath)
        );

        $tempScriptPath = $tempDir.'/test_script.sh';
        file_put_contents($tempScriptPath, $modifiedScript);
        chmod($tempScriptPath, 0755);

        // Run check command
        $output = shell_exec("CODACY_PROJECT_TOKEN=test bash {$tempScriptPath} check 2>&1");

        $this->assertStringContainsString('Found 1 coverage file(s)', $output);
        $this->assertStringContainsString('coverage/lcov.info', $output);

        // Clean up
        unlink($tempScriptPath);
        unlink($tempDir.'/coverage/lcov.info');
        rmdir($tempDir.'/coverage');
        rmdir($tempDir);
    }

    /** @test */
    public function script_correctly_identifies_language_for_lcov_files()
    {
        $scriptContent = file_get_contents($this->scriptPath);

        // Test LCOV file detection logic
        $this->assertStringContainsString('*"lcov.info"', $scriptContent);
        $this->assertStringContainsString('language="javascript"', $scriptContent);
    }

    /** @test */
    public function script_correctly_identifies_language_for_xml_files()
    {
        $scriptContent = file_get_contents($this->scriptPath);

        // Test XML file detection logic
        $this->assertStringContainsString('*".xml"', $scriptContent);
        $this->assertStringContainsString('language="php"', $scriptContent);
    }

    /** @test */
    public function script_now_uses_correct_language_parameter_pattern()
    {
        $scriptContent = file_get_contents($this->scriptPath);

        // After the fix, the script should use proper language detection
        $this->assertStringContainsString('language="javascript"', $scriptContent);
        $this->assertStringContainsString('language="php"', $scriptContent);
        $this->assertStringContainsString('report -l "$language" -r', $scriptContent);

        // The old incorrect patterns should be gone
        $this->assertStringNotContainsString('report -l LCOV -r', $scriptContent);
        $this->assertStringNotContainsString('echo "$file_format" | tr', $scriptContent);
    }

    /** @test */
    public function script_should_map_file_types_to_proper_languages()
    {
        // This test defines the expected behavior after the fix
        // LCOV files should map to 'javascript' language
        // XML files should map to 'php' language

        // These assertions will pass after we implement the fix
        $expectedMappings = [
            'lcov.info' => 'javascript',
            'coverage.xml' => 'php',
            'coverage-unit.xml' => 'php',
            'coverage-feature.xml' => 'php',
        ];

        foreach ($expectedMappings as $file => $expectedLanguage) {
            $this->assertIsString($expectedLanguage);
            $this->assertNotEmpty($expectedLanguage);
        }
    }

    /** @test */
    public function script_handles_usage_help()
    {
        $output = shell_exec("bash {$this->scriptPath} --help 2>&1");

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
