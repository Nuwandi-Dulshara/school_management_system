<?php

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExaminationController;
use App\Http\Controllers\ExamScheduleController;
use App\Http\Controllers\FeesController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\MarksResultController;
use App\Http\Controllers\NoticeController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\SuperAdmin\ClassSectionManagementController;
use App\Http\Controllers\SuperAdmin\StudentClassAssignmentController;
use App\Http\Controllers\SuperAdmin\StudentManagementController;
use App\Http\Controllers\SuperAdmin\SubjectManagementController;
use App\Http\Controllers\SuperAdmin\TeacherManagementController;
use App\Http\Controllers\SuperAdmin\TeacherSubjectAssignmentController;
use App\Http\Controllers\SuperAdmin\UserManagementController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Registration routes
Route::get('/register', [RegisterController::class, 'showRegister'])->name('register');
Route::post('/register', [RegisterController::class, 'register'])->name('register.submit');

// Login routes
Route::get('/login', [LoginController::class, 'showLogin'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.submit');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Role-based dashboard routes
Route::middleware(['auth', 'super_admin'])->prefix('dashboard/super-admin')->group(function () {
    Route::get('/', [DashboardController::class, 'superAdmin'])->name('dashboard.super_admin');

    Route::get('/users', [UserManagementController::class, 'index'])->name('super_admin.users.index');
    Route::get('/users/create', [UserManagementController::class, 'create'])->name('super_admin.users.create');
    Route::post('/users', [UserManagementController::class, 'store'])->name('super_admin.users.store');
    Route::get('/users/{user}/edit', [UserManagementController::class, 'edit'])->name('super_admin.users.edit');
    Route::put('/users/{user}', [UserManagementController::class, 'update'])->name('super_admin.users.update');
    Route::patch('/users/{user}/status', [UserManagementController::class, 'toggleStatus'])->name('super_admin.users.status');
    Route::put('/users/{user}/password', [UserManagementController::class, 'resetPassword'])->name('super_admin.users.password');
    Route::delete('/users/{user}', [UserManagementController::class, 'destroy'])->name('super_admin.users.destroy');
    Route::get('/users/{user}', [UserManagementController::class, 'show'])->name('super_admin.users.show');
    Route::get('/roles', [UserManagementController::class, 'roles'])->name('super_admin.roles.index');

    Route::get('/students/status', [StudentManagementController::class, 'status'])->name('super_admin.students.status');
    Route::patch('/students/{student}/status', [StudentManagementController::class, 'updateStatus'])->name('super_admin.students.status.update');
    Route::get('/students', [StudentManagementController::class, 'index'])->name('super_admin.students.index');
    Route::get('/students/create', [StudentManagementController::class, 'create'])->name('super_admin.students.create');
    Route::post('/students', [StudentManagementController::class, 'store'])->name('super_admin.students.store');
    Route::get('/students/{student}/edit', [StudentManagementController::class, 'edit'])->name('super_admin.students.edit');
    Route::put('/students/{student}', [StudentManagementController::class, 'update'])->name('super_admin.students.update');
    Route::delete('/students/{student}', [StudentManagementController::class, 'destroy'])->name('super_admin.students.destroy');
    Route::get('/students/{student}/guardians', [StudentManagementController::class, 'guardians'])->name('super_admin.students.guardians');
    Route::put('/students/{student}/guardians', [StudentManagementController::class, 'updateGuardian'])->name('super_admin.students.guardians.update');
    Route::get('/students/{student}/documents', [StudentManagementController::class, 'documents'])->name('super_admin.students.documents');
    Route::post('/students/{student}/documents', [StudentManagementController::class, 'storeDocument'])->name('super_admin.students.documents.store');
    Route::delete('/students/{student}/documents/{document}', [StudentManagementController::class, 'destroyDocument'])->name('super_admin.students.documents.destroy');
    Route::get('/students/{student}', [StudentManagementController::class, 'show'])->name('super_admin.students.show');

    Route::get('/teachers', [TeacherManagementController::class, 'index'])->name('super_admin.teachers.index');
    Route::get('/teachers/create', [TeacherManagementController::class, 'create'])->name('super_admin.teachers.create');
    Route::post('/teachers', [TeacherManagementController::class, 'store'])->name('super_admin.teachers.store');
    Route::get('/teachers/{teacher}/edit', [TeacherManagementController::class, 'edit'])->name('super_admin.teachers.edit');
    Route::put('/teachers/{teacher}', [TeacherManagementController::class, 'update'])->name('super_admin.teachers.update');
    Route::patch('/teachers/{teacher}/status', [TeacherManagementController::class, 'toggleStatus'])->name('super_admin.teachers.status');
    Route::delete('/teachers/{teacher}', [TeacherManagementController::class, 'destroy'])->name('super_admin.teachers.destroy');
    Route::get('/teachers/{teacher}/assigned-classes', [TeacherManagementController::class, 'assignedClasses'])->name('super_admin.teachers.classes');
    Route::post('/teachers/{teacher}/assigned-classes', [TeacherManagementController::class, 'storeAssignedClass'])->name('super_admin.teachers.classes.store');
    Route::put('/teachers/{teacher}/assigned-classes/{classAssignment}', [TeacherManagementController::class, 'updateAssignedClass'])->name('super_admin.teachers.classes.update');
    Route::delete('/teachers/{teacher}/assigned-classes/{classAssignment}', [TeacherManagementController::class, 'destroyAssignedClass'])->name('super_admin.teachers.classes.destroy');
    Route::get('/teachers/{teacher}/assigned-subjects', [TeacherManagementController::class, 'assignedSubjects'])->name('super_admin.teachers.subjects');
    Route::post('/teachers/{teacher}/assigned-subjects', [TeacherManagementController::class, 'storeAssignedSubject'])->name('super_admin.teachers.subjects.store');
    Route::put('/teachers/{teacher}/assigned-subjects/{subjectAssignment}', [TeacherManagementController::class, 'updateAssignedSubject'])->name('super_admin.teachers.subjects.update');
    Route::delete('/teachers/{teacher}/assigned-subjects/{subjectAssignment}', [TeacherManagementController::class, 'destroyAssignedSubject'])->name('super_admin.teachers.subjects.destroy');
    Route::get('/teachers/{teacher}', [TeacherManagementController::class, 'show'])->name('super_admin.teachers.show');

    Route::get('/classes', [ClassSectionManagementController::class, 'index'])->name('super_admin.classes.index');
    Route::get('/classes/create', [ClassSectionManagementController::class, 'create'])->name('super_admin.classes.create');
    Route::post('/classes', [ClassSectionManagementController::class, 'store'])->name('super_admin.classes.store');
    Route::get('/classes/sections', [ClassSectionManagementController::class, 'sections'])->name('super_admin.sections.index');
    Route::get('/classes/sections/create', [ClassSectionManagementController::class, 'createSection'])->name('super_admin.sections.create');
    Route::post('/classes/sections', [ClassSectionManagementController::class, 'storeSection'])->name('super_admin.sections.store');
    Route::get('/classes/sections/{schoolSection}/edit', [ClassSectionManagementController::class, 'editSection'])->name('super_admin.sections.edit');
    Route::put('/classes/sections/{schoolSection}', [ClassSectionManagementController::class, 'updateSection'])->name('super_admin.sections.update');
    Route::patch('/classes/sections/{schoolSection}/status', [ClassSectionManagementController::class, 'toggleSectionStatus'])->name('super_admin.sections.status');
    Route::delete('/classes/sections/{schoolSection}', [ClassSectionManagementController::class, 'destroySection'])->name('super_admin.sections.destroy');
    Route::get('/classes/{schoolClass}/edit', [ClassSectionManagementController::class, 'edit'])->name('super_admin.classes.edit');
    Route::put('/classes/{schoolClass}', [ClassSectionManagementController::class, 'update'])->name('super_admin.classes.update');
    Route::patch('/classes/{schoolClass}/status', [ClassSectionManagementController::class, 'toggleStatus'])->name('super_admin.classes.status');
    Route::delete('/classes/{schoolClass}', [ClassSectionManagementController::class, 'destroy'])->name('super_admin.classes.destroy');
    Route::get('/classes/{schoolClass}/students', [ClassSectionManagementController::class, 'students'])->name('super_admin.classes.students');
    Route::patch('/classes/{schoolClass}/students/{student}', [ClassSectionManagementController::class, 'assignStudent'])->name('super_admin.classes.students.assign');
    Route::delete('/classes/{schoolClass}/students/{student}', [ClassSectionManagementController::class, 'removeStudent'])->name('super_admin.classes.students.remove');
    Route::get('/classes/{schoolClass}', [ClassSectionManagementController::class, 'show'])->name('super_admin.classes.show');

    Route::get('/subjects', [SubjectManagementController::class, 'index'])->name('super_admin.subjects.index');
    Route::get('/subjects/create', [SubjectManagementController::class, 'create'])->name('super_admin.subjects.create');
    Route::post('/subjects', [SubjectManagementController::class, 'store'])->name('super_admin.subjects.store');
    Route::get('/subjects/class-subjects', [SubjectManagementController::class, 'classSubjects'])->name('super_admin.subjects.classes');
    Route::post('/subjects/class-subjects', [SubjectManagementController::class, 'storeClassSubject'])->name('super_admin.subjects.classes.store');
    Route::put('/subjects/class-subjects/{classSubject}', [SubjectManagementController::class, 'updateClassSubject'])->name('super_admin.subjects.classes.update');
    Route::delete('/subjects/class-subjects/{classSubject}', [SubjectManagementController::class, 'destroyClassSubject'])->name('super_admin.subjects.classes.destroy');
    Route::get('/subjects/{subject}/edit', [SubjectManagementController::class, 'edit'])->name('super_admin.subjects.edit');
    Route::put('/subjects/{subject}', [SubjectManagementController::class, 'update'])->name('super_admin.subjects.update');
    Route::patch('/subjects/{subject}/status', [SubjectManagementController::class, 'toggleStatus'])->name('super_admin.subjects.status');
    Route::delete('/subjects/{subject}', [SubjectManagementController::class, 'destroy'])->name('super_admin.subjects.destroy');
    Route::get('/subjects/{subject}', [SubjectManagementController::class, 'show'])->name('super_admin.subjects.show');

    Route::get('/student-class-assignments', [StudentClassAssignmentController::class, 'index'])->name('super_admin.student_assignments.index');
    Route::get('/student-class-assignments/assign', [StudentClassAssignmentController::class, 'create'])->name('super_admin.student_assignments.create');
    Route::post('/student-class-assignments', [StudentClassAssignmentController::class, 'store'])->name('super_admin.student_assignments.store');
    Route::get('/student-class-assignments/transfers', [StudentClassAssignmentController::class, 'transfers'])->name('super_admin.student_assignments.transfers');
    Route::post('/student-class-assignments/{assignment}/transfer', [StudentClassAssignmentController::class, 'transfer'])->name('super_admin.student_assignments.transfer');
    Route::get('/student-class-assignments/academic-years', [StudentClassAssignmentController::class, 'academicYears'])->name('super_admin.student_assignments.academic_years');
    Route::put('/student-class-assignments/{assignment}', [StudentClassAssignmentController::class, 'update'])->name('super_admin.student_assignments.update');
    Route::delete('/student-class-assignments/{assignment}', [StudentClassAssignmentController::class, 'destroy'])->name('super_admin.student_assignments.destroy');
    Route::get('/student-class-assignments/{assignment}', [StudentClassAssignmentController::class, 'show'])->name('super_admin.student_assignments.show');

    Route::get('/teacher-subject-assignments', [TeacherSubjectAssignmentController::class, 'index'])->name('super_admin.teacher_assignments.index');
    Route::get('/teacher-subject-assignments/assign', [TeacherSubjectAssignmentController::class, 'create'])->name('super_admin.teacher_assignments.create');
    Route::post('/teacher-subject-assignments', [TeacherSubjectAssignmentController::class, 'store'])->name('super_admin.teacher_assignments.store');
    Route::get('/teacher-subject-assignments/class-wise', [TeacherSubjectAssignmentController::class, 'classWise'])->name('super_admin.teacher_assignments.class_wise');
    Route::get('/teacher-subject-assignments/subject-wise', [TeacherSubjectAssignmentController::class, 'subjectWise'])->name('super_admin.teacher_assignments.subject_wise');
    Route::put('/teacher-subject-assignments/{assignment}', [TeacherSubjectAssignmentController::class, 'update'])->name('super_admin.teacher_assignments.update');
    Route::patch('/teacher-subject-assignments/{assignment}/status', [TeacherSubjectAssignmentController::class, 'toggleStatus'])->name('super_admin.teacher_assignments.status');
    Route::delete('/teacher-subject-assignments/{assignment}', [TeacherSubjectAssignmentController::class, 'destroy'])->name('super_admin.teacher_assignments.destroy');
    Route::get('/teacher-subject-assignments/{assignment}', [TeacherSubjectAssignmentController::class, 'show'])->name('super_admin.teacher_assignments.show');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard/admin', [DashboardController::class, 'admin'])->name('dashboard.admin');
    Route::get('/dashboard/teacher', [DashboardController::class, 'teacher'])->name('dashboard.teacher');
    Route::get('/dashboard/student', [DashboardController::class, 'student'])->name('dashboard.student');
});

Route::middleware(['auth', 'attendance_manager'])->prefix('attendance')->name('attendance.')->group(function () {
    Route::get('/mark', [AttendanceController::class, 'mark'])->name('mark');
    Route::post('/', [AttendanceController::class, 'store'])->name('store');
    Route::get('/', [AttendanceController::class, 'index'])->name('index');
    Route::get('/edit', [AttendanceController::class, 'editList'])->name('edit_list');
    Route::get('/student-history', [AttendanceController::class, 'studentHistory'])->name('student_history');
    Route::get('/class-report', [AttendanceController::class, 'classReport'])->name('class_report');
    Route::get('/daily-report', [AttendanceController::class, 'dailyReport'])->name('daily_report');
    Route::get('/{attendance}/edit', [AttendanceController::class, 'edit'])->name('edit');
    Route::put('/{attendance}', [AttendanceController::class, 'update'])->name('update');
    Route::delete('/{attendance}', [AttendanceController::class, 'destroy'])->name('destroy');
});

Route::middleware('auth')->prefix('examinations')->name('examinations.')->group(function () {
    Route::get('/', [ExaminationController::class, 'index'])->name('index');
    Route::get('/class-exams', [ExaminationController::class, 'classExams'])->name('class_exams');
    Route::get('/upcoming', [ExaminationController::class, 'upcoming'])->name('upcoming');
    Route::get('/schedules', [ExamScheduleController::class, 'index'])->name('schedules.index');

    Route::middleware('examination_manager')->group(function () {
        Route::get('/create', [ExaminationController::class, 'create'])->name('create');
        Route::post('/', [ExaminationController::class, 'store'])->name('store');
        Route::get('/{exam}/edit', [ExaminationController::class, 'edit'])->name('edit');
        Route::put('/{exam}', [ExaminationController::class, 'update'])->name('update');
        Route::delete('/{exam}', [ExaminationController::class, 'destroy'])->name('destroy');
        Route::post('/schedules', [ExamScheduleController::class, 'store'])->name('schedules.store');
        Route::put('/schedules/{examSchedule}', [ExamScheduleController::class, 'update'])->name('schedules.update');
        Route::delete('/schedules/{examSchedule}', [ExamScheduleController::class, 'destroy'])->name('schedules.destroy');
    });
});

Route::middleware(['auth', 'marks_manager'])->prefix('marks')->name('marks.')->group(function () {
    Route::get('/entry', [MarksResultController::class, 'entry'])->name('entry');
    Route::post('/', [MarksResultController::class, 'store'])->name('store');
    Route::get('/', [MarksResultController::class, 'index'])->name('index');
    Route::get('/result-sheet', [MarksResultController::class, 'resultSheet'])->name('result_sheet');
    Route::get('/class-report', [MarksResultController::class, 'classReport'])->name('class_report');
    Route::get('/subject-report', [MarksResultController::class, 'subjectReport'])->name('subject_report');
    Route::get('/{mark}/edit', [MarksResultController::class, 'edit'])->name('edit');
    Route::put('/{mark}', [MarksResultController::class, 'update'])->name('update');
    Route::delete('/{mark}', [MarksResultController::class, 'destroy'])->name('destroy');
});

Route::middleware('auth')->get('/student-results', [MarksResultController::class, 'studentResults'])
    ->name('marks.student_results');

Route::middleware(['auth', 'fees_manager'])->prefix('fees')->name('fees.')->group(function () {
    Route::get('/types', [FeesController::class, 'feeTypes'])->name('types.index');
    Route::get('/types/create', [FeesController::class, 'createFeeType'])->name('types.create');
    Route::post('/types', [FeesController::class, 'storeFeeType'])->name('types.store');
    Route::get('/types/{feeType}/edit', [FeesController::class, 'editFeeType'])->name('types.edit');
    Route::put('/types/{feeType}', [FeesController::class, 'updateFeeType'])->name('types.update');
    Route::delete('/types/{feeType}', [FeesController::class, 'destroyFeeType'])->name('types.destroy');

    Route::get('/assign', [FeesController::class, 'assign'])->name('assign');
    Route::post('/assignments', [FeesController::class, 'storeAssignment'])->name('assignments.store');
    Route::put('/assignments/{feeAssignment}', [FeesController::class, 'updateAssignment'])->name('assignments.update');
    Route::delete('/assignments/{feeAssignment}', [FeesController::class, 'destroyAssignment'])->name('assignments.destroy');

    Route::get('/assignments/{feeAssignment}/payment', [FeesController::class, 'paymentForm'])->name('payments.create');
    Route::post('/assignments/{feeAssignment}/payments', [FeesController::class, 'storePayment'])->name('payments.store');
    Route::get('/payments/{feePayment}/edit', [FeesController::class, 'editPayment'])->name('payments.edit');
    Route::put('/payments/{feePayment}', [FeesController::class, 'updatePayment'])->name('payments.update');
    Route::delete('/payments/{feePayment}', [FeesController::class, 'destroyPayment'])->name('payments.destroy');
    Route::get('/pending', [FeesController::class, 'pending'])->name('pending');
});

Route::middleware('auth')->prefix('fees')->name('fees.')->group(function () {
    Route::get('/assignments', [FeesController::class, 'assignments'])->name('assignments.index');
    Route::get('/assignments/{feeAssignment}', [FeesController::class, 'showAssignment'])->name('assignments.show');
    Route::get('/payments', [FeesController::class, 'paymentHistory'])->name('payments.index');
    Route::get('/receipts/{feePayment}', [FeesController::class, 'receipt'])->name('receipt');
});

Route::middleware('auth')->prefix('notices')->name('notices.')->group(function () {
    Route::get('/published', [NoticeController::class, 'published'])->name('published');

    Route::middleware('notices_manager')->group(function () {
        Route::get('/', [NoticeController::class, 'index'])->name('index');
        Route::get('/create', [NoticeController::class, 'create'])->name('create');
        Route::post('/', [NoticeController::class, 'store'])->name('store');
        Route::get('/{notice}/edit', [NoticeController::class, 'edit'])->name('edit');
        Route::put('/{notice}', [NoticeController::class, 'update'])->name('update');
        Route::patch('/{notice}/publish', [NoticeController::class, 'togglePublish'])->name('publish');
        Route::delete('/{notice}', [NoticeController::class, 'destroy'])->name('destroy');
    });

    Route::get('/{notice}', [NoticeController::class, 'show'])->name('show');
});

Route::middleware('auth')->prefix('reports')->name('reports.')->group(function () {
    Route::get('/students', [ReportsController::class, 'students'])->name('students');
    Route::get('/teachers', [ReportsController::class, 'teachers'])->name('teachers');
    Route::get('/attendance', [ReportsController::class, 'attendance'])->name('attendance');
    Route::get('/exams', [ReportsController::class, 'exams'])->name('exams');
    Route::get('/marks', [ReportsController::class, 'marks'])->name('marks');
    Route::get('/fees', [ReportsController::class, 'fees'])->name('fees');
    Route::get('/classes', [ReportsController::class, 'classes'])->name('classes');
    Route::get('/academic-years', [ReportsController::class, 'academicYears'])->name('academic_years');
});
