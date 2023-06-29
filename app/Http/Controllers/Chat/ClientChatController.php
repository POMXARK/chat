<?php

namespace App\Http\Controllers\Chat;

use App\DTO\MessageDTO;
use App\Interfaces\ChatRepositoryInterface;
use Illuminate\Http\Request;

class ClientChatController extends ParentChatController
{
    protected $chatRow = 'client.child_stmt.chat';

    private ChatRepositoryInterface $chatRepository;

    public function __construct(ChatRepositoryInterface $chatRepository)
    {
        $this->chatRepository = $chatRepository;
    }

    /**
     * Добавление сообщения в чат
     *
     * @param Request $request
     * @return string
     */
    public function postMessage(Request $request)
    {
        $text = trim($request->input('text', ''));
        $files = $request->file('files');

        if (!strlen($text) && !$files) {
            return response()->json(['status' => false, 'msg' => 'Пожалуйста, введите сообщение или добавьте файл!']);
        }

        $message = new MessageDTO(
            $request->input('from'),
            $request->input('to'),
            $request->input('stmt'),
            $text,
            $files
        );

        $this->chatRepository->postMessage($message);

        return response()->json(['status' => true]);
    }

    /**
     * Загрузка сообщений в чат (обновление сообщений)
     *
     *
     * @return string
     */
    public function loadMessages(Request $request)
    {
        $message = new MessageDTO(
            $request->input('from'),
            $request->input('to'),
            $request->input('stmt')
        );

        return $this->chatRepository->loadMessages($message);
    }
}
