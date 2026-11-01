<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\ExamController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\TopicController;
use App\Http\Controllers\TosController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');

Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.attempt');

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/instructor/dashboard', function () {
        return view('dashboard.instructor');
    })->name('dashboard.instructor');

    Route::resource('courses', CourseController::class)->except(['show']);

    Route::resource('courses.topics', TopicController::class)
        ->except(['show'])
        ->parameters(['topics' => 'topic']);

    Route::resource('courses.topics.questions', QuestionController::class)
        ->except(['show'])
        ->parameters(['topics' => 'topic', 'questions' => 'question']);

    Route::resource('courses.tos', TosController::class)
        ->only(['index', 'create', 'store', 'show', 'edit', 'update', 'destroy'])
        ->parameters(['tos' => 'tos']);

    Route::get('/courses/{course}/exams', [ExamController::class, 'index'])->name('courses.exams.index');
    Route::post('/courses/{course}/tos/{tos}/generate', [ExamController::class, 'generate'])->name('courses.tos.generate');
    Route::get('/courses/{course}/exams/{exam}', [ExamController::class, 'show'])->name('courses.exams.show');
    Route::get('/courses/{course}/exams/{exam}/format', [ExamController::class, 'editFormat'])->name('courses.exams.format');
    Route::get('/courses/{course}/exams/{exam}/answer-key', [ExamController::class, 'answerKey'])->name('courses.exams.answerkey');
    Route::post('/courses/{course}/exams/{exam}/format', [ExamController::class, 'updateFormat'])->name('courses.exams.format.update');
    Route::get('/courses/{course}/exams/{exam}/questions/{question}/replace', [ExamController::class, 'replaceOptions'])->name('courses.exams.replace.options');
    Route::post('/courses/{course}/exams/{exam}/questions/{question}/replace', [ExamController::class, 'replace'])->name('courses.exams.replace');
    Route::delete('/courses/{course}/exams/{exam}', [ExamController::class, 'destroy'])->name('courses.exams.destroy');
});

Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/admin/dashboard', [AdminController::class, 'dashboard'])->name('dashboard.admin');
    Route::post('/admin/accounts/{account}/approve', [AdminController::class, 'approve'])->name('admin.accounts.approve');
    Route::post('/admin/accounts/{account}/reject', [AdminController::class, 'reject'])->name('admin.accounts.reject');
});