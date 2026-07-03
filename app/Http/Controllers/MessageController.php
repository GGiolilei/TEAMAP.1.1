<?php

namespace App\Http\Controllers;

use App\Models\Channel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class MessageController extends Controller
{
    public function store(Request $request, Channel $channel)
    {
        $validated = $request->validate([
            'content' => 'required|string|max:2000',
        ]);

        // Save user's message
        $channel->messages()->create([
            'content' => $validated['content'],
            'user_id' => auth()->id(),
        ]);

        $aiUserId = 2;

        if (str_contains(strtolower($validated['content']), '@timmy')) {

            $cleanPrompt = trim(
                str_ireplace('@timmy', '', $validated['content'])
            );

            try {

                $response = Http::timeout(120)
                    ->post(env('OLLAMA_URL') . '/api/chat', [
                        'model' => env('OLLAMA_MODEL'),
                        'messages' => [
                            [
                                'role' => 'system',
                                'content' => 'You are Timmy, a friendly AI assistant inside a chat application. Keep your answers concise, natural, and helpful.'
                            ],
                            [
                                'role' => 'user',
                                'content' => $cleanPrompt
                            ]
                        ],
                        'stream' => false,
                    ]);

                // ==========================
                // DEBUG
                // ==========================
                dd([
                    'status' => $response->status(),
                    'successful' => $response->successful(),
                    'json' => $response->json(),
                    'body' => $response->body(),
                ]);

                $responseData = $response->json();

                $aiReplyText = $responseData['message']['content']
                    ?? 'No content returned';

                $channel->messages()->create([
                    'content' => $aiReplyText,
                    'user_id' => $aiUserId,
                ]);

            } catch (\Exception $e) {

                dd([
                    'exception' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);

            }
        }

        return redirect()->route('chat.index', [
            'lobby' => $channel->lobby_id,
            'channel' => $channel->id,
        ]);
    }
}