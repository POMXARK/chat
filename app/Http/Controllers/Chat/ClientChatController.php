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
        $builder = Message::select(DB::raw('DATE_FORMAT(updated_at, "%d.%m.%Y %H:%i") as updated, stmt_id, text, from_user_id, to_user_id, created_at'));

        if ($stmtId = $request->input('stmt')) {
            $builder->where('stmt_id', $stmtId);
        }
        if ($from = $request->input('from')) {
            $builder->where('from_user_id', $from);
        }
        if ($to = $request->input('to')) {
            $builder->where('to_user_id', $to);
        }

        if ($request->input('from') && $request->input('to')) {
            $second = Message::select(DB::raw('DATE_FORMAT(updated_at, "%d.%m.%Y %H:%i") as updated_at, stmt_id, text, from_user_id, to_user_id, created_at'));

            if ($stmtId = $request->input('stmt')) {
                $second->where('stmt_id', $stmtId);
            }
            if ($from = $request->input('from')) {
                $second->where('to_user_id', $from);
            }
            if ($to = $request->input('to')) {
                $second->where('from_user_id', $to);
            }

            $builder->union($second);
        }

        return $builder->orderBy('created_at')->get();//self::getSqlWithBindings($builder);
    }

    public static function getSqlWithBindings($query): string
    {
        $bindings = $query->getBindings();

        return preg_replace_callback('/\?/', function ($match) use (&$bindings, $query) {
            return $query->getConnection()->getPdo()->quote(array_shift($bindings));
        }, $query->toSql());
    }

}
