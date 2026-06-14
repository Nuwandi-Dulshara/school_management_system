<?php

namespace Tests\Feature;

use App\Models\Notice;
use App\Models\SchoolClass;
use App\Models\SchoolSection;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeacherSubjectAssignment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NoticeManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_filter_update_publish_and_delete_notices(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->post(route('notices.store'), [
                'title' => 'School Holiday',
                'message' => 'The school will be closed on Friday.',
                'audience' => 'all_users',
                'publish_date' => today()->toDateString(),
                'expiry_date' => today()->addWeek()->toDateString(),
                'priority' => 'important',
                'status' => 'draft',
            ])
            ->assertRedirect(route('notices.index'))
            ->assertSessionHas('success');

        $notice = Notice::firstOrFail();
        $this->assertSame($admin->id, $notice->created_by);

        $this->actingAs($admin)
            ->get(route('notices.index', ['search' => 'Holiday', 'audience' => 'all_users', 'status' => 'draft']))
            ->assertOk()
            ->assertSee('School Holiday');

        $this->actingAs($admin)
            ->put(route('notices.update', $notice), [
                'title' => 'Updated School Holiday',
                'message' => 'Updated message.',
                'audience' => 'student',
                'publish_date' => today()->toDateString(),
                'expiry_date' => today()->addDays(10)->toDateString(),
                'priority' => 'urgent',
                'status' => 'draft',
            ])
            ->assertRedirect(route('notices.index'));

        $this->actingAs($admin)->patch(route('notices.publish', $notice))->assertRedirect();
        $this->assertDatabaseHas('notices', ['id' => $notice->id, 'status' => 'published', 'priority' => 'urgent']);

        $this->actingAs($admin)
            ->get(route('notices.show', $notice))
            ->assertOk()
            ->assertSee('Updated School Holiday');

        $this->actingAs($admin)->delete(route('notices.destroy', $notice))->assertRedirect(route('notices.index'));
        $this->assertDatabaseMissing('notices', ['id' => $notice->id]);
    }

    public function test_section_notice_requires_a_section_belonging_to_the_selected_class(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        [$class] = $this->classSection('Grade 10', 'A');
        [, $otherSection] = $this->classSection('Grade 11', 'B');

        $this->actingAs($admin)
            ->post(route('notices.store'), [
                'title' => 'Section Notice',
                'message' => 'Message',
                'audience' => 'section',
                'school_class_id' => $class->id,
                'school_section_id' => $otherSection->id,
                'publish_date' => today()->toDateString(),
                'priority' => 'normal',
                'status' => 'published',
            ])
            ->assertSessionHasErrors('school_section_id');
    }

    public function test_student_sees_only_current_role_class_and_section_notices(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        [$class, $section] = $this->classSection();
        [$otherClass, $otherSection] = $this->classSection('Grade 11', 'B');
        $studentUser = User::factory()->create(['role' => 'student', 'email' => 'student@school.test']);
        Student::factory()->create([
            'email' => 'student@school.test',
            'school_class_id' => $class->id,
            'school_section_id' => $section->id,
            'class' => $class->name,
            'section' => $section->name,
        ]);

        $visible = [
            $this->notice($admin, ['title' => 'All Users Notice', 'audience' => 'all_users']),
            $this->notice($admin, ['title' => 'Student Notice', 'audience' => 'student']),
            $this->notice($admin, ['title' => 'Class Notice', 'audience' => 'class', 'school_class_id' => $class->id]),
            $this->notice($admin, ['title' => 'Section Notice', 'audience' => 'section', 'school_class_id' => $class->id, 'school_section_id' => $section->id]),
        ];
        $this->notice($admin, ['title' => 'Teacher Only', 'audience' => 'teacher']);
        $this->notice($admin, ['title' => 'Other Class', 'audience' => 'class', 'school_class_id' => $otherClass->id]);
        $this->notice($admin, ['title' => 'Other Section', 'audience' => 'section', 'school_class_id' => $otherClass->id, 'school_section_id' => $otherSection->id]);
        $this->notice($admin, ['title' => 'Draft Notice', 'status' => 'draft']);
        $this->notice($admin, ['title' => 'Expired Notice', 'expiry_date' => today()->subDay()]);
        $this->notice($admin, ['title' => 'Future Notice', 'publish_date' => today()->addDay()]);

        $response = $this->actingAs($studentUser)->get(route('notices.published'))->assertOk();
        foreach ($visible as $notice) {
            $response->assertSee($notice->title);
        }
        $response->assertDontSee('Teacher Only')
            ->assertDontSee('Other Class')
            ->assertDontSee('Other Section')
            ->assertDontSee('Draft Notice')
            ->assertDontSee('Expired Notice')
            ->assertDontSee('Future Notice');

        $this->actingAs($studentUser)->get(route('notices.show', $visible[1]))->assertOk();
        $hidden = Notice::where('title', 'Teacher Only')->firstOrFail();
        $this->actingAs($studentUser)->get(route('notices.show', $hidden))->assertForbidden();
        $this->actingAs($studentUser)->get(route('notices.index'))->assertForbidden();
    }

    public function test_teacher_sees_teacher_and_assigned_class_notices(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        [$class, $section] = $this->classSection();
        [$otherClass] = $this->classSection('Grade 11', 'B');
        $teacherUser = User::factory()->create(['role' => 'teacher', 'email' => 'teacher@school.test']);
        $teacher = Teacher::factory()->create(['email' => 'teacher@school.test']);
        $subject = Subject::factory()->create();
        TeacherSubjectAssignment::create([
            'teacher_id' => $teacher->id,
            'school_class_id' => $class->id,
            'subject_id' => $subject->id,
            'status' => 'active',
        ]);

        $this->notice($admin, ['title' => 'Teachers Announcement', 'audience' => 'teacher']);
        $this->notice($admin, ['title' => 'Assigned Class Notice', 'audience' => 'class', 'school_class_id' => $class->id]);
        $this->notice($admin, ['title' => 'Assigned Section Notice', 'audience' => 'section', 'school_class_id' => $class->id, 'school_section_id' => $section->id]);
        $this->notice($admin, ['title' => 'Different Class Notice', 'audience' => 'class', 'school_class_id' => $otherClass->id]);
        $this->notice($admin, ['title' => 'Students Announcement', 'audience' => 'student']);

        $this->actingAs($teacherUser)
            ->get(route('notices.published'))
            ->assertOk()
            ->assertSee('Teachers Announcement')
            ->assertSee('Assigned Class Notice')
            ->assertSee('Assigned Section Notice')
            ->assertDontSee('Different Class Notice')
            ->assertDontSee('Students Announcement');
    }

    public function test_dashboards_show_only_visible_current_notices(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $studentUser = User::factory()->create(['role' => 'student', 'email' => 'student@school.test']);
        [$class, $section] = $this->classSection();
        Student::factory()->create([
            'email' => 'student@school.test',
            'school_class_id' => $class->id,
            'school_section_id' => $section->id,
            'class' => $class->name,
            'section' => $section->name,
        ]);

        $this->notice($admin, ['title' => 'Dashboard Student Notice', 'audience' => 'student', 'priority' => 'urgent']);
        $this->notice($admin, ['title' => 'Hidden Dashboard Draft', 'audience' => 'student', 'status' => 'draft']);

        $this->actingAs($studentUser)
            ->get(route('dashboard.student'))
            ->assertOk()
            ->assertSee('Dashboard Student Notice')
            ->assertDontSee('Hidden Dashboard Draft')
            ->assertSee('View More');
    }

    private function classSection(string $className = 'Grade 10', string $sectionName = 'A'): array
    {
        $class = SchoolClass::factory()->create(['name' => $className]);
        $section = SchoolSection::factory()->create(['school_class_id' => $class->id, 'name' => $sectionName]);

        return [$class, $section];
    }

    private function notice(User $creator, array $overrides = []): Notice
    {
        return Notice::create(array_merge([
            'title' => 'General Notice '.str()->random(5),
            'message' => 'Notice message for testing.',
            'audience' => 'all_users',
            'publish_date' => today(),
            'expiry_date' => today()->addWeek(),
            'priority' => 'normal',
            'status' => 'published',
            'created_by' => $creator->id,
        ], $overrides));
    }
}
