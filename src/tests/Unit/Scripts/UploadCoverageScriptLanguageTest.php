<?php

namespace Tests\Unit\Scripts;

class UploadCoverageScriptLanguageTest extends UploadCoverageScriptTestBase
{
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
}
