<?php

namespace App\Http\Controllers\Chat;

use App\Interfaces\ChatRepositoryInterface;
use App\Interfaces\Statements\CompatibleWithChat;
use App\Models\Message;
use App\Repository\AlertRepository;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Mockery\Exception;
use Throwable;

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
    public function postMessage(Request $request): string
    {
        $text = trim($request->input('text', ''));
        $files = $request->file('files');

        if (!strlen($text) && !$files) {
            return response()->json(['status' => false, 'msg' => 'Пожалуйста, введите сообщение или добавьте файл!']);
        }

        $dto['from'] = $request->input('from_user_id');
        $dto['to'] = $request->input('to_user_id');
        $dto['stmt'] = $request->input('stmt_id');
        $dto['files'] = $files;
        $dto['text'] = $text;

        $this->chatRepository->postMessage($dto);

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
        $dto['stmt'] = $request->input('stmt');
        $dto['from'] = $request->input('from');
        $dto['to'] = $request->input('to');

        return $this->chatRepository->loadMessages($dto);
    }
}
