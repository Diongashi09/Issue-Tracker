<?php

use App\Http\Controllers\Issue\CommentController as IssueCommentController;
use App\Http\Controllers\Issue\MemberController as IssueMemberController;
use App\Http\Controllers\Issue\TagController as IssueTagController;
use App\Http\Controllers\IssueController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TagController;
use Illuminate\Support\Facades\Route;

// Root redirect: blueprint §8 — / redirects to projects.index
Route::get('/', fn () => redirect()->route('projects.index'));

Route::get('/dashboard', fn () => view('dashboard'))
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::resource('projects', ProjectController::class);
    Route::resource('issues', IssueController::class);

    // Nested comment sub-resource (AJAX — blueprint §8)
    Route::get ('issues/{issue}/comments', [IssueCommentController::class, 'index'])->name('issues.comments.index');
    Route::post('issues/{issue}/comments', [IssueCommentController::class, 'store'])->name('issues.comments.store');

    // Nested tag sub-resource (AJAX — blueprint §8)
    Route::post  ('issues/{issue}/tags',       [IssueTagController::class, 'store'])->name('issues.tags.store');
    Route::delete('issues/{issue}/tags/{tag}', [IssueTagController::class, 'destroy'])->name('issues.tags.destroy');

    // Nested member sub-resource (AJAX — blueprint §8)
    Route::post  ('issues/{issue}/members',        [IssueMemberController::class, 'store'])->name('issues.members.store');
    Route::delete('issues/{issue}/members/{user}', [IssueMemberController::class, 'destroy'])->name('issues.members.destroy');

    // Tag library — creation only (index served inline; blueprint §8)
    Route::post('tags', [TagController::class, 'store'])->name('tags.store');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
