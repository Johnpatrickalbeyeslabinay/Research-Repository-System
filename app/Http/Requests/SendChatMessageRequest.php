<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SendChatMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Route-level 'auth' middleware + the conversation-ownership check
        // in ChatController::message() already gate this — this class
        // owns validation only.
        return true;
    }

    public function rules(): array
    {
        return [
            'message' => 'required|string|max:'.(int) config('chat.context.max_question_chars', 1000),
        ];
    }
}
