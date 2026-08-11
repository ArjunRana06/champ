<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    protected $fillable = ['name', 'email', 'password', 'xp', 'level', 'streak', 'last_active_date', 'badges'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'xp' => 'integer',
            'level' => 'integer',
            'streak' => 'integer',
            'last_active_date' => 'datetime',
            'badges' => 'array',
        ];
    }

    // Inside the User class, add:
    public function subjects()
    {
        return $this->hasMany(Subject::class);
    }

    public function documents()
    {
        return $this->hasMany(Document::class);
    }

    public function mcqs()
    {
        return $this->hasMany(Mcq::class);
    }

    public function trueFalseQuestions()
    {
        return $this->hasMany(TrueFalseQuestion::class);
    }

    public function shortAnswers()
    {
        return $this->hasMany(ShortAnswer::class);
    }

    public function fillBlanks()
    {
        return $this->hasMany(FillBlank::class);
    }

    public function matchingQuestions()
    {
        return $this->hasMany(MatchingQuestion::class);
    }

    public function flashcards()
    {
        return $this->hasMany(Flashcard::class);
    }

    public function studyPlans()
    {
        return $this->hasMany(StudyPlan::class);
    }

    public function exams()
    {
        return $this->hasMany(Exam::class);
    }

    public function activities()
    {
        return $this->hasMany(Activity::class);
    }

    public function pomodoroSessions()
    {
        return $this->hasMany(PomodoroSession::class);
    }

    public function timeEntries()
    {
        return $this->hasMany(TimeEntry::class);
    }

    public function appNotifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function bookmarks()
    {
        return $this->hasMany(Bookmark::class);
    }

    public function quizAttempts()
    {
        return $this->hasMany(QuizAttempt::class);
    }

    public function searchHistories()
    {
        return $this->hasMany(SearchHistory::class);
    }

    public function chatConversations()
    {
        return $this->hasMany(ChatConversation::class);
    }
}
