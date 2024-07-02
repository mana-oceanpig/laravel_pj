<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ConversationsController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/home', function() {
    return view('home');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/conversations', [ConversationsController::class, 'index'])->name('conversations.index');
    Route::get('/conversations/{id}', [ConversationsController::class, 'show'])->name('conversations.show');
    Route::get('/conversations/{id}/listen', [ConversationsController::class, 'listen'])->name('conversations.listen');
    Route::get('/conversations/start', [ConversationsController::class, 'start'])->name('conversations.start');
    Route::post('/conversations', [ConversationsController::class, 'store'])->name('conversations.store');
    Route::post('/conversations/{id}/complete', [ConversationsController::class, 'complete'])->name('conversations.complete');
    Route::post('/conversations/{id}/cancel', [ConversationsController::class, 'cancel'])->name('conversations.cancel');
    Route::get('/conversations/{id}/check-expired', [ConversationsController::class, 'checkExpired'])->name('conversations.checkExpired');
     // メッセージ関連のルート
    Route::get('/conversations/{conversation}/messages', [ConversationMessagesController::class, 'store'])->name('conversationMessages.store');
    Route::post('/conversations/{conversation}/messages', [ConversationMessagesController::class, 'store'])->name('conversationMessages.store');
    Route::post('/conversations/{conversation}/update-last-activity', [ConversationsController::class, 'updateLastActivity'])
    ->name('conversations.updateLastActivity');

});

require __DIR__.'/auth.php';
