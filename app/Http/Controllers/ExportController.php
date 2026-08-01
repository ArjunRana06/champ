<?php

namespace App\Http\Controllers;

use App\Models\Mcq;
use App\Models\TrueFalseQuestion;
use App\Models\ShortAnswer;
use App\Models\FillBlank;
use App\Models\MatchingQuestion;
use App\Models\Flashcard;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class ExportController extends Controller
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function form()
    {
        $counts = [
            'mcqs' => Mcq::where('user_id', auth()->id())->count(),
            'true_false' => TrueFalseQuestion::where('user_id', auth()->id())->count(),
            'short_answers' => ShortAnswer::where('user_id', auth()->id())->count(),
            'fill_blanks' => FillBlank::where('user_id', auth()->id())->count(),
            'matching' => MatchingQuestion::where('user_id', auth()->id())->count(),
            'flashcards' => Flashcard::where('user_id', auth()->id())->count(),
        ];
        return view('Backend.export.form', compact('counts'));
    }

    public function exportCsv(Request $request)
    {
        $type = $request->input('type', 'mcqs');
        $questions = $this->getQuestions($type);
        $headers = $this->getHeaders($type);

        $output = fopen('php://temp', 'w+');
        fputcsv($output, $headers);

        foreach ($questions as $q) {
            fputcsv($output, $this->formatRow($type, $q));
        }

        rewind($output);
        $content = stream_get_contents($output);
        fclose($output);

        $this->notificationService->notifyExportCompleted(auth()->id(), 'CSV');

        return response($content, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $type . '_' . date('Y-m-d') . '.csv"',
        ]);
    }

    public function exportJson(Request $request)
    {
        $type = $request->input('type', 'mcqs');
        $questions = $this->getQuestions($type);

        $this->notificationService->notifyExportCompleted(auth()->id(), 'JSON');

        return response()->json($questions)->withHeaders([
            'Content-Disposition' => 'attachment; filename="' . $type . '_' . date('Y-m-d') . '.json"',
        ]);
    }

    public function exportAnki(Request $request)
    {
        $flashcards = Flashcard::where('user_id', auth()->id())->get();

        $output = fopen('php://temp', 'w+');
        fputcsv($output, ['Question', 'Answer', 'Subject'], "\t");

        foreach ($flashcards as $card) {
            fputcsv($output, [
                strip_tags($card->front),
                strip_tags($card->back),
                $card->subject?->name ?? 'General',
            ], "\t");
        }

        rewind($output);
        $content = stream_get_contents($output);
        fclose($output);

        $this->notificationService->notifyExportCompleted(auth()->id(), 'Anki');

        return response($content, 200, [
            'Content-Type' => 'text/tab-separated-values',
            'Content-Disposition' => 'attachment; filename="anki_import_' . date('Y-m-d') . '.tsv"',
        ]);
    }

    public function exportPdf(Request $request)
    {
        $type = $request->input('type', 'mcqs');
        $questions = $this->getQuestions($type);

        $html = view('Backend.export.pdf', compact('questions', 'type'))->render();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html);

        $this->notificationService->notifyExportCompleted(auth()->id(), 'PDF');

        return $pdf->download($type . '_' . date('Y-m-d') . '.pdf');
    }

    private function getQuestions(string $type)
    {
        $userId = auth()->id();
        return match ($type) {
            'mcqs' => Mcq::where('user_id', $userId)->with('subject')->get(),
            'true_false' => TrueFalseQuestion::where('user_id', $userId)->with('subject')->get(),
            'short_answers' => ShortAnswer::where('user_id', $userId)->with('subject')->get(),
            'fill_blanks' => FillBlank::where('user_id', $userId)->with('subject')->get(),
            'matching' => MatchingQuestion::where('user_id', $userId)->with('subject')->get(),
            'flashcards' => Flashcard::where('user_id', $userId)->with('subject')->get(),
            default => collect(),
        };
    }

    private function getHeaders(string $type): array
    {
        $common = ['ID', 'Subject', 'Created At'];
        $specific = match ($type) {
            'mcqs' => ['Question', 'Option A', 'Option B', 'Option C', 'Option D', 'Correct Answer', 'Explanation'],
            'true_false' => ['Statement', 'Correct Answer', 'Explanation'],
            'short_answers' => ['Question', 'Expected Answer', 'Keywords'],
            'fill_blanks' => ['Sentence', 'Answers', 'Context'],
            'matching' => ['Left Items', 'Right Items', 'Correct Pairs'],
            'flashcards' => ['Front', 'Back'],
            default => [],
        };
        return array_merge($common, $specific);
    }

    private function formatRow(string $type, $q): array
    {
        $common = [$q->id, $q->subject?->name ?? 'General', $q->created_at->format('Y-m-d')];
        $specific = match ($type) {
            'mcqs' => [
                $q->question,
                $q->options[0] ?? '', $q->options[1] ?? '',
                $q->options[2] ?? '', $q->options[3] ?? '',
                $q->correct_answer, $q->explanation ?? ''
            ],
            'true_false' => [$q->statement, $q->correct_answer ? 'True' : 'False', $q->explanation ?? ''],
            'short_answers' => [$q->question, $q->expected_answer, ''],
            'fill_blanks' => [$q->sentence_with_blanks, is_array($q->answers) ? implode(', ', $q->answers) : $q->answers, ''],
            'matching' => [
                is_array($q->left_items) ? implode('; ', $q->left_items) : $q->left_items,
                is_array($q->right_items) ? implode('; ', $q->right_items) : $q->right_items,
                is_array($q->correct_pairs) ? json_encode($q->correct_pairs) : $q->correct_pairs
            ],
            'flashcards' => [$q->front, $q->back],
            default => [],
        };
        return array_merge($common, $specific);
    }
}
