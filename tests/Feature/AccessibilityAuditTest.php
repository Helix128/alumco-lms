<?php

namespace Tests\Feature;

use App\Models\Curso;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesUsers;

class AccessibilityAuditTest extends TestCase
{
    use CreatesUsers, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    /**
     * Test basic accessibility structural requirements on the courses index.
     */
    public function test_courses_index_has_basic_a11y_structure(): void
    {
        $user = $this->createTrabajador();

        $response = $this->actingAs($user)->get(route('cursos.index'));
        $html = $response->getContent();

        $this->assertBasicA11yRequirements($html);
    }

    /**
     * Test basic accessibility structural requirements on a course page.
     */
    public function test_course_show_has_basic_a11y_structure(): void
    {
        $user = $this->createTrabajador();
        $curso = Curso::factory()->create();

        // We need to ensure the user is assigned or at least can see the course
        // for the purpose of the audit, we'll just actingAs.

        $response = $this->actingAs($user)->get(route('cursos.show', $curso));
        $html = $response->getContent();

        $this->assertBasicA11yRequirements($html);
    }

    /**
     * Helper to assert basic A11y rules in HTML output.
     */
    protected function assertBasicA11yRequirements(string $html): void
    {
        // 1. Every image MUST have an alt attribute (even if empty for decorative)
        // This is a loose check but ensures the attribute exists.
        if (preg_match_all('/<img[^>]+>/i', $html, $matches)) {
            foreach ($matches[0] as $img) {
                $this->assertStringContainsString('alt=', $img, "Image missing alt attribute: $img");
            }
        }

        // 2. Interactive elements should have labels or aria-labels
        // Check for common buttons and links.
        if (preg_match_all('/<button[^>]*>(.*?)<\/button>/is', $html, $matches)) {
            foreach ($matches[0] as $index => $button) {
                $content = trim(strip_tags($matches[1][$index]));
                $hasAriaLabel = str_contains($button, 'aria-label=') || str_contains($button, 'aria-labelledby=');

                if (empty($content) && ! $hasAriaLabel) {
                    // SVG only buttons are okay if they have aria-label, which we checked
                    $this->assertTrue($hasAriaLabel, "Button is empty and has no aria-label: $button");
                }
            }
        }

        // 3. Progress bars should have role="progressbar" and ARIA attributes
        // (This was part of our H003 fix)
        if (preg_match_all('/class="[^"]*progressbar[^"]*"/i', $html, $matches)) {
            // If someone uses a class name with progressbar but forgets the role
        }

        // Check actual roles
        if (preg_match_all('/role="progressbar"[^>]*>/i', $html, $matches)) {
            foreach ($matches[0] as $pb) {
                // Skip if hidden from SR
                if (str_contains($pb, 'aria-hidden="true"')) {
                    continue;
                }

                $this->assertStringContainsString('aria-valuenow', $pb, "Progress bar missing aria-valuenow: $pb");
                $this->assertStringContainsString('aria-valuemin', $pb, "Progress bar missing aria-valuemin: $pb");
                $this->assertStringContainsString('aria-valuemax', $pb, "Progress bar missing aria-valuemax: $pb");
            }
        }

        // 4. Modals and dialogs (if present in the initial render or template)
        if (preg_match_all('/role="dialog"[^>]*>/i', $html, $matches)) {
            foreach ($matches[0] as $dialog) {
                $this->assertStringContainsString('aria-modal="true"', $dialog, "Dialog missing aria-modal=\"true\": $dialog");
                $this->assertTrue(
                    str_contains($dialog, 'aria-label=') || str_contains($dialog, 'aria-labelledby='),
                    "Dialog missing identification (aria-label or aria-labelledby): $dialog"
                );
            }
        }
    }
}
