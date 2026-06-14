<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $students = DB::table('students')
            ->whereNotIn('class', ['', 'Unassigned'])
            ->whereNotIn('section', ['', 'Unassigned'])
            ->get();

        foreach ($students->groupBy('class') as $className => $classStudents) {
            $classId = DB::table('school_classes')->insertGetId([
                'name' => $className,
                'capacity' => max(40, $classStudents->count()),
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            foreach ($classStudents->groupBy('section') as $sectionName => $sectionStudents) {
                $sectionId = DB::table('school_sections')->insertGetId([
                    'school_class_id' => $classId,
                    'name' => $sectionName,
                    'capacity' => max(40, $sectionStudents->count()),
                    'status' => 'active',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                DB::table('students')
                    ->whereIn('id', $sectionStudents->pluck('id'))
                    ->update([
                        'school_class_id' => $classId,
                        'school_section_id' => $sectionId,
                    ]);
            }
        }
    }

    public function down(): void
    {
        DB::table('students')->update([
            'school_class_id' => null,
            'school_section_id' => null,
        ]);
    }
};
